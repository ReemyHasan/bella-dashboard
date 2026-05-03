<?php

namespace App\Services\Mobile;

use App\Enums\OrderStatus;
use App\Enums\PaginationEnum;
use App\Enums\VaultTransactionType;
use App\Exceptions\CustomException;
use App\Models\Address;
use App\Models\AppUser;
use App\Models\CustomerOrder;
use App\Models\Offer;
use App\Models\OfferWarehouse;
use App\Models\OfferZonePrice;
use App\Models\OrderOffer;
use App\Models\OrderProduct;
use App\Models\OrderStatusLog;
use App\Models\ProductWarehouse;
use App\Models\ProductZonePrice;
use App\Models\Vault;
use App\Models\VaultTransaction;
use App\Services\Shared\OrderSharedService;
use App\Services\Shared\StockHandleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private StockHandleService $stockHandleService,
        private OrderSharedService $orderSharedService
    ) {}

    public function list($request)
    {

        return CustomerOrder::with('customer', 'currency', 'marketer', 'warehouseMan', 'lastStatusLog')
            ->where('app_user_id', auth()->user()->id)->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function managedOrders($request)
    {
        return CustomerOrder::with('customer', 'currency', 'marketer', 'warehouseMan', 'lastStatusLog')
            ->visibleTo(auth()->user())->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }
    public function show(CustomerOrder $order)
    {
        $allowedUsers = [$order->app_user_id, $order->teamleader_id, $order->manager_id];
        if (!in_array(auth()->user()->id, $allowedUsers)) {
            throw new CustomException('لا يمكن رؤية الطلب إلا من قبل المسوق المنشئ له أو مديره.');
        }
        $order->load('customer', 'statusLogs.changedBy', 'currency', 'marketer', 'warehouseMan', 'teamleader', 'manager', 'warehouse', 'reviewedBy', 'address', 'createdBy', 'products.product', 'offers.offer');
        return $order;
    }


    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $user = auth()->user();
            $user->load('team', 'subTeam.team');
            $team = $user->subTeam
                ? $user->subTeam->team
                : $user->team;

            if (!$team) {
                throw new CustomException('يجب أن تنتمي إلى فريق');
            }

            $teamleaderId = $user->subTeam?->team_leader_id;

            $isDirectTeam = $user->subTeam?->is_direct;

            $resolved = $this->orderSharedService->resolvePercentages(
                $user,
                $team,
                $teamleaderId,
                $isDirectTeam
            );

            $orderData = array_merge($resolved, [
                'is_stock_reserved' => true,
                'team_id' => $team->id,
                'sub_team_id' => $user->subteam_id,
            ]);
            $address = Address::with('region.city.zone', 'region.warehouse.keeper')->find($data['address_id']);

            if (!$address) {
                throw new CustomException('العنوان غير موجود');
            }

            $zone = $address->region->city->zone;
            $region = $address->region;
            $warehouse = $address->region->warehouse;

            $orderData += [
                'delivery_cost' => $region->delivery_cost,
                'currency_id' => $zone->currency_id,
                'current_exchange_rate' => $zone->currency->exchange_value,
                'zone_id' => $zone->id,
                'warehouse_id' => $warehouse->id,
                'warehouse_man_id' => $warehouse?->keeper_id
            ];
            $orderData = array_merge($orderData, collect($data)->except(['products', 'offers'])->toArray());

            $orderNumber = $this->orderSharedService->generateOrderNumber();

            $order = CustomerOrder::create([
                ...$orderData,
                'order_status' => OrderStatus::new->value,
                'order_number' => $orderNumber,
                'created_by_type' => AppUser::class,
                'created_by_id' => auth()->user()->id,
                'app_user_id' => auth()->user()->id,

            ]);
            $totalBasePrice = 0;

            $zoneId = $zone->id;
            $productIds = collect($data['products'])->pluck('product_id');
            $offerIds = collect($data['offers'])->pluck('offer_id');

            $productZonePrices = ProductZonePrice::where('zone_id', $zoneId)
                ->whereIn('product_id', $productIds ?? [])
                ->where('is_available', true)
                ->get()
                ->keyBy('product_id');

            $offerZonePrices = OfferZonePrice::where('zone_id', $zoneId)
                ->whereIn('offer_id', $offerIds ?? [])
                ->where('is_available', true)
                ->get()
                ->keyBy('offer_id');

            if (!empty($data['products'])) {


                $warehouseProducts = ProductWarehouse::where('warehouse_id', $warehouse->id)
                    ->whereIn('product_id', $productIds)
                    ->lockForUpdate() // 🔥 prevent race condition
                    ->get()
                    ->keyBy('product_id');

                foreach ($data['products'] as $item) {

                    $warehouseProduct = $warehouseProducts->get($item['product_id']);
                    if (!$warehouseProduct) {
                        throw new CustomException('المنتج غير موجود في المستودع');
                    }


                    $zonePrice = $productZonePrices->get($item['product_id']);

                    if (!$zonePrice) {
                        throw new CustomException("لا يوجد سعر للمنتج في هذه المنطقة");
                    }

                    $price = $zonePrice->price_after_adjustment ?? $zonePrice->price;

                    OrderProduct::create([
                        'customer_order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $price,
                        'total_price' => $price * $item['quantity']
                    ]);

                    $totalBasePrice += $price * $item['quantity'];

                    $warehouseProduct->increment('reserved_quantity', $item['quantity']);
                }
            }

            if (!empty($data['offers'])) {

                $warehouseOffers = OfferWarehouse::where('warehouse_id', $warehouse->id)
                    ->whereIn('offer_id', $offerIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('offer_id');

                $offers = Offer::with('products')
                    ->whereIn('id', $offerIds)
                    ->get()
                    ->keyBy('id');

                $allOfferProductIds = $offers
                    ->flatMap(fn($offer) => $offer->products->pluck('id'))
                    ->unique();

                $warehouseProducts = ProductWarehouse::where('warehouse_id', $warehouse->id)
                    ->whereIn('product_id', $allOfferProductIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                foreach ($data['offers'] as $item) {

                    $warehouseOffer = $warehouseOffers->get($item['offer_id']);

                    if (!$warehouseOffer) {
                        throw new CustomException('العرض غير موجود في المستودع');
                    }

                    $offer = $offers->get($item['offer_id']);

                    if (!$offer) {
                        throw new CustomException('العرض غير موجود');
                    }

                    foreach ($offer->products as $product) {

                        $requiredQty = $product->pivot->quantity * $item['quantity'];

                        $warehouseProduct = $warehouseProducts->get($product->id);

                        if (!$warehouseProduct) {
                            throw new CustomException("منتج داخل العرض غير موجود في المستودع");
                        }
                    }

                    foreach ($offer->products as $product) {

                        $requiredQty = $product->pivot->quantity * $item['quantity'];

                        $warehouseProducts
                            ->get($product->id)
                            ->increment('reserved_quantity', $requiredQty);
                    }


                    $zonePrice = $offerZonePrices->get($item['offer_id']);

                    if (!$zonePrice) {
                        throw new CustomException("لا يوجد سعر للعرض في هذه المنطقة");
                    }

                    $price = $zonePrice->price;

                    OrderOffer::create([
                        'customer_order_id' => $order->id,
                        'offer_id' => $item['offer_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $price,
                        'total_price' => $price * $item['quantity'],
                    ]);

                    $totalBasePrice += $price * $item['quantity'];
                }
            }
            $totalPrice = $totalBasePrice;

            if (!empty($data['adjustment_value'])) {

                $type = $data['adjustment_type'];
                $operation = $data['adjustment_operation'];
                $value = (float) $data['adjustment_value'];

                $amount = $type === 'percentage'
                    ? ($totalBasePrice * $value / 100)
                    : $value;

                if ($operation === 'increase') {
                    $totalPrice = $totalBasePrice + $amount;
                } else {
                    $totalPrice = $totalBasePrice - $amount;
                }

                // prevent negative price
                $totalPrice = max(0, $totalPrice);
            }

            $baseAmount = $totalBasePrice * $orderData['current_exchange_rate'];

            $amounts = $this->orderSharedService->calculateAmounts($baseAmount, $resolved, $data, $zone->currency->exchange_value);

            $order->update([
                'total_base_price' => $totalBasePrice,
                'total_price' => max($totalPrice, 0),

                ...$amounts
            ]);
            OrderStatusLog::create([
                'customer_order_id' => $order->id,
                'status' => OrderStatus::new->value,
                'changed_by_type' => get_class($user),
                'changed_by_id' => $user->id,
            ]);

            $order->load('customer', 'currency', 'marketer', 'warehouseMan', 'teamleader', 'manager', 'warehouse', 'reviewedBy', 'address', 'createdBy', 'products.product', 'offers.offer');
            return $order;
        });
    }

    public function update(CustomerOrder $order, array $data)
    {
        if ($order->order_status != OrderStatus::new->value) {
            throw new CustomException('لا يمكن تعديل الطلب بعد مراجعته.');
        }

        if ($order->app_user_id != auth()->user()->id) {
            throw new CustomException('لا يمكن تعديل الطلب إلا من قبل المسوق المنشئ له.');
        }
        return DB::transaction(function () use ($order, $data) {

            $order->load('products', 'offers');

            // =========================
            // 🔥 STEP 1: RESTORE STOCK
            // =========================

            foreach ($order->products as $oldProduct) {
                ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                    ->where('product_id', $oldProduct->product_id)
                    ->lockForUpdate()
                    ->decrement('reserved_quantity', $oldProduct->quantity);
            }

            $offers = Offer::with('products')
                ->whereIn('id', $order->offers->pluck('offer_id'))
                ->get()
                ->keyBy('id');

            $allOfferProductIds = $offers
                ->flatMap(fn($offer) => $offer->products->pluck('id'))
                ->unique();

            $warehouseProducts = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                ->whereIn('product_id', $allOfferProductIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            foreach ($order->offers as $oldOffer) {


                $offer = $offers->get($oldOffer->offer_id);

                if (!$offer) continue;

                foreach ($offer->products as $product) {

                    $restoreQty = $product->pivot->quantity * $oldOffer->quantity;

                    $warehouseProduct = $warehouseProducts->get($product->id);

                    if (!$warehouseProduct) {
                        throw new CustomException("منتج داخل العرض غير موجود في المستودع");
                    }

                    $warehouseProduct
                        ->decrement('reserved_quantity', $restoreQty);
                }
            }

            $order->products()->delete();
            $order->offers()->delete();

            $user = auth()->user();
            $user->load('team', 'subTeam.team');
            $team = $user->subTeam
                ? $user->subTeam->team
                : $user->team;

            if (!$team) {
                throw new CustomException('يجب أن تنتمي إلى فريق');
            }

            $teamleaderId = $user->subTeam?->team_leader_id;
            $isDirectTeam = $user->subTeam?->is_direct;



            $resolved = $this->orderSharedService->resolvePercentages(
                $user,
                $team,
                $teamleaderId,
                $isDirectTeam
            );

            $orderData = array_merge($resolved, [
                'is_stock_reserved' => true,
                'team_id' => $team->id,
                'sub_team_id' => $user->subteam_id,
            ]);

            $address = Address::with('region.city.zone', 'region.warehouse.keeper')->find($data['address_id']);

            if (!$address) {
                throw new CustomException('العنوان غير موجود');
            }

            $zone = $address->region->city->zone;
            $region = $address->region;
            $warehouse = $address->region->warehouse;

            $orderData += [
                'delivery_cost' => $region->delivery_cost,
                'currency_id' => $zone->currency_id,
                'current_exchange_rate' => $zone->currency->exchange_value,
                'zone_id' => $zone->id,
                'warehouse_id' => $warehouse->id,
                'warehouse_man_id' => $warehouse?->keeper_id
            ];

            $orderData = array_merge(
                $orderData,
                collect($data)->except(['products', 'offers'])->toArray()
            );

            $order->update($orderData);

            $zoneId = $zone->id;

            $productIds = collect($data['products'] ?? [])->pluck('product_id');
            $offerIds   = collect($data['offers'] ?? [])->pluck('offer_id');

            $productZonePrices = ProductZonePrice::where('zone_id', $zoneId)
                ->whereIn('product_id', $productIds)
                ->where('is_available', true)
                ->get()
                ->keyBy('product_id');

            $offerZonePrices = OfferZonePrice::where('zone_id', $zoneId)
                ->whereIn('offer_id', $offerIds)
                ->where('is_available', true)
                ->get()
                ->keyBy('offer_id');

            $totalBasePrice = 0;

            if (!empty($data['products'])) {

                $warehouseProducts = ProductWarehouse::where('warehouse_id', $warehouse->id)
                    ->whereIn('product_id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                foreach ($data['products'] as $item) {

                    $warehouseProduct = $warehouseProducts->get($item['product_id']);

                    if (!$warehouseProduct) {
                        throw new CustomException('المنتج غير موجود في المستودع');
                    }

                    $zonePrice = $productZonePrices->get($item['product_id']);

                    if (!$zonePrice) {
                        throw new CustomException("لا يوجد سعر للمنتج في هذه المنطقة");
                    }

                    $price = $zonePrice->price_after_adjustment ?? $zonePrice->price;

                    OrderProduct::create([
                        'customer_order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $price,
                        'total_price' => $price * $item['quantity'],
                    ]);

                    $totalBasePrice += $price * $item['quantity'];

                    $warehouseProduct->increment('reserved_quantity', $item['quantity']);
                }
            }

            if (!empty($data['offers'])) {

                $warehouseOffers = OfferWarehouse::where('warehouse_id', $warehouse->id)
                    ->whereIn('offer_id', $offerIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('offer_id');

                $offers = Offer::with('products')
                    ->whereIn('id', $offerIds)
                    ->get()
                    ->keyBy('id');

                $allOfferProductIds = $offers
                    ->flatMap(fn($offer) => $offer->products->pluck('id'))
                    ->unique();

                $warehouseProducts = ProductWarehouse::where('warehouse_id', $warehouse->id)
                    ->whereIn('product_id', $allOfferProductIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                foreach ($data['offers'] as $item) {

                    $warehouseOffer = $warehouseOffers->get($item['offer_id']);

                    if (!$warehouseOffer) {
                        throw new CustomException('العرض غير موجود في المستودع');
                    }


                    $offer = $offers->get($item['offer_id']);

                    if (!$offer) {
                        throw new CustomException('العرض غير موجود');
                    }

                    foreach ($offer->products as $product) {

                        $requiredQty = $product->pivot->quantity * $item['quantity'];

                        $warehouseProduct = $warehouseProducts->get($product->id);

                        if (!$warehouseProduct) {
                            throw new CustomException("منتج داخل العرض غير موجود في المستودع");
                        }
                    }

                    foreach ($offer->products as $product) {

                        $requiredQty = $product->pivot->quantity * $item['quantity'];

                        $warehouseProducts
                            ->get($product->id)
                            ->increment('reserved_quantity', $requiredQty);
                    }

                    $zonePrice = $offerZonePrices->get($item['offer_id']);

                    if (!$zonePrice) {
                        throw new CustomException("لا يوجد سعر للعرض في هذه المنطقة");
                    }

                    $price = $zonePrice->price;

                    OrderOffer::create([
                        'customer_order_id' => $order->id,
                        'offer_id' => $item['offer_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $price,
                        'total_price' => $price * $item['quantity'],
                    ]);

                    $totalBasePrice += $price * $item['quantity'];
                }
            }
            $totalPrice = $totalBasePrice;

            if (!empty($data['adjustment_value'])) {

                $type = $data['adjustment_type'];
                $operation = $data['adjustment_operation'];
                $value = (float) $data['adjustment_value'];

                $amount = $type === 'percentage'
                    ? ($totalBasePrice * $value / 100)
                    : $value;

                if ($operation === 'increase') {
                    $totalPrice = $totalBasePrice + $amount;
                } else {
                    $totalPrice = $totalBasePrice - $amount;
                }

                $totalPrice = max(0, $totalPrice);
            }


            $baseAmount = $totalBasePrice * $orderData['current_exchange_rate'];

            $amounts = $this->orderSharedService->calculateAmounts($baseAmount, $resolved, $data, $orderData['current_exchange_rate']);

            $order->update([
                'total_base_price' => $totalBasePrice,
                'total_price' => max($totalPrice, 0),
                ...$amounts
            ]);

            return $order->fresh()->load([
                'customer',
                'currency',
                'products.product',
                'offers.offer'
            ]);
        });
    }

    private array $allowedTransitions = [

        'new' => ['delivering', 'waiting', 'cancelled'],

        'delivering' => ['waiting', 'cancelled', 'completed', 'refund'],

        'waiting' => ['cancelled', 'delivering', 'completed', 'refund'],

        'completed' => ['refund'],

        'refund' => [],

    ];

    public function addNotes(CustomerOrder $order, array $data)
    {
        $allowedUsers = [$order->app_user_id, $order->teamleader_id, $order->manager_id];
        if (!in_array(auth()->user()->id, $allowedUsers)) {
            throw new CustomException('لا يمكن إضافة ملاحظة إلا من قبل المسوق المنشئ له أو مديره.');
        }

        OrderStatusLog::create([
            'customer_order_id' => $order->id,
            'status' => OrderStatus::note->value,
            'changed_by_type' => get_class(Auth::user()),
            'changed_by_id' => Auth::id(),
            'notes' => $data['notes']
        ]);
    }
    public function handle(CustomerOrder $order, array $data)
    {
        if ($order->app_user_id != auth()->user()->id) {
            throw new CustomException('لا يمكن معالجة الطلب إلا من قبل المسوق المنشئ له.');
        }
        $status = OrderStatus::from($data['status']);

        return DB::transaction(function () use ($order, $status, $data) {
            $currentStatus = OrderStatus::from($order->order_status);
            $reserveStock = $order->is_stock_reserved;
            if (
                !isset($this->allowedTransitions[$currentStatus->value]) ||
                !in_array($status->value, $this->allowedTransitions[$currentStatus->value])
            ) {

                $from = OrderStatus::from($currentStatus->value)->label();
                $to   = OrderStatus::from($status->value)->label();

                throw new CustomException("تغيير الحالة غير مسموح من {$from} إلى {$to}");
            }


            if ($status == OrderStatus::completed) {

                $this->handleCompleteOrder($order);
                $this->stockHandleService->removeFromStock($order);
            }

            if ($status == OrderStatus::refund) {

                $this->handleRefund($order);
                $this->stockHandleService->returnToStock($order);
                $reserveStock = false;
            }

            if (
                in_array($status, [OrderStatus::cancelled])
            ) {
                $this->stockHandleService->releaseStock($order);
                $reserveStock = false;
            }

            $order->update([
                'order_status' => $status->value,
                'cancellation_reason' => $data['cancellation_reason'] ?? null,
                'waiting_reason' => $data['waiting_reason'] ?? null,

                'is_stock_reserved' => $reserveStock,
                'cancelled_at' => in_array($status, [OrderStatus::cancelled]) ? now() : null,
                'waiting_until' => $data['waiting_until'] ?? null

            ]);

            OrderStatusLog::create([
                'customer_order_id' => $order->id,
                'status' => $status->value,
                'changed_by_type' => get_class(Auth::user()),
                'changed_by_id' => Auth::id(),
                'notes' => $data['notes'] ?? "status changed from {$currentStatus->value} to {$status->value}"
            ]);

            return $order->refresh();
        });
    }


    public function handleFinancialProcess(CustomerOrder $order)
    {
        $vault = Vault::where('owner_id', $order->warehouse_man_id)->first();
        if ($vault == null || $order->warehouse_man_id == null)
            throw new CustomException('من فضلك تواصل مع الإدارة, الموزع ليس لديه معلومات كافية.');

        return $this->orderSharedService->handleFinancialProcess($order, $vault);
    }

    private function handleCompleteOrder(CustomerOrder $order)
    {
        if ($order->is_financial_processed)
            return;
        $company_amount = $order->total_price * $order->current_exchange_rate; //  BASE

        $vault = Vault::where('owner_id', $order->warehouse_man_id)->first();
        if ($vault == null || $order->warehouse_man_id == null)
            throw new CustomException('يرجى التواصل مع الإدارة لحل المشكلة, يوجد نقص في معلومات الموزع');

        $oldVaultBalance = $vault->balance;
        $newVaultBalance = $vault->balance + $company_amount;

        $vault->update([
            'balance' => $newVaultBalance,
        ]);
        $user = auth()->user();
        VaultTransaction::create([
            'to_vault_id' => $vault->id,

            'type' => VaultTransactionType::ORDER_COMPANY_PROFIT->value,

            'amount' => $company_amount,

            'transaction_date' => now(),

            'notes' => '..',

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'reference_type' => CustomerOrder::class,
            'reference_id' => $order->id,

            'to_vault_balance_before' => $oldVaultBalance,
            'to_vault_balance_after' => $newVaultBalance,
        ]);
        $this->handleFinancialProcess($order);
    }

    private function handleRefund(CustomerOrder $order)
    {
        if (!$order->is_financial_processed)
            return;
        $company_amount = $order->total_price * $order->current_exchange_rate; //  BASE

        $vault = Vault::where('owner_id', $order->warehouse_man_id)->first();
        if ($vault == null)
            throw new CustomException('الموزع ليس لديه خزنة, من فضلك أضف له خزنة ثم أعد المحاولة.');

        $oldVaultBalance = $vault->balance;
        $vaultAmount = $company_amount; // + $order->marketer_amount + $order->teamleader_amount + $order->manager_amount;
        $newVaultBalance = $vault->balance - $vaultAmount;

        $vault->update([
            'balance' => $newVaultBalance,
        ]);
        $user = auth()->user();
        VaultTransaction::create([
            'to_vault_id' => $vault->id,

            'type' => VaultTransactionType::ORDER_REFUND->value,

            'amount' => $vaultAmount,

            'transaction_date' => now(),

            'notes' => '..',

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'reference_type' => CustomerOrder::class,
            'reference_id' => $order->id,

            'to_vault_balance_before' => $oldVaultBalance,
            'to_vault_balance_after' => $newVaultBalance,
        ]);

        $this->orderSharedService->subtractBalance($vault, $order->app_user_id, $order->marketer_amount, VaultTransactionType::refund_marketer->value, $order, $order->marketer_percentage);
        if ($order->teamleader_id)
            $this->orderSharedService->subtractBalance($vault, $order->teamleader_id, $order->teamleader_amount, VaultTransactionType::refund_teamleader->value, $order, $order->teamleader_percentage);

        if ($order->manager_id) {
            $this->orderSharedService->subtractBalance($vault, $order->manager_id, $order->manager_amount, VaultTransactionType::refund_manager->value, $order,  $order->manager_percentage);
        }
    }
}
