<?php

namespace App\Services\DashUser\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\Address;
use App\Models\AppUser;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\DashUser;
use App\Models\Offer;
use App\Models\OfferWarehouse;
use App\Models\OfferZonePrice;
use App\Models\OrderOffer;
use App\Models\OrderProduct;
use App\Models\OrderStatusLog;
use App\Models\ProductWarehouse;
use App\Models\ProductZonePrice;
use App\Models\VaultTransaction;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function list($request)
    {

        return CustomerOrder::with('customer', 'currency', 'marketer', 'warehouseMan', 'warehouse', 'address')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }


    public function vaultTransactions(CustomerOrder $vault, $request)
    {
        $query = VaultTransaction::with(
            'actionBy',
            'reference'
        );

        $query->where(function ($q) use ($vault) {
            $q->where('reference_type', CustomerOrder::class)
                ->where('reference_id', $vault->id);
        });

        return $query
            ->filterBy($request->except('direction'))
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);
    }


    private function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');

        $lastOrder = CustomerOrder::whereDate('created_at', now()->toDateString())
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $sequence = 1;

        if ($lastOrder && $lastOrder->order_number) {
            $lastSequence = (int) substr($lastOrder->order_number, -6);
            $sequence = $lastSequence + 1;
        }

        return 'ORD-' . $date . '-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    public function create(array $data)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {

            $user = AppUser::with('team', 'subTeam.team')->find($data['app_user_id']);

            $team = $user->subTeam
                ? $user->subTeam->team
                : $user->team;

            if (!$team) {
                throw new CustomException('المسوق لا ينتمي إلى فريق');
            }

            $teamleaderId = $user->subTeam?->team_leader_id;

            $isDirectTeam = $user->subTeam?->is_direct;

            $orderData = [
                'teamleader_id' => $isDirectTeam ? $team->manager_id : $teamleaderId,
                'manager_id' => $isDirectTeam ? null : $team->manager_id,

                'marketer_percentage' => $team->marketer_percentage,
                'teamleader_percentage' => $team->team_leader_percentage,
                'manager_percentage' => $isDirectTeam ? 0 : $team->manager_percentage,
                'is_stock_reserved' => true,
                'team_id' => $team->id,
                'sub_team_id' => $user->subteam_id


            ];

            $address = Address::with('region.city.zone')->find($data['address_id']);

            if (!$address) {
                throw new CustomException('العنوان غير موجود');
            }

            $zone = $address->region->city->zone;

            $orderData += [
                'delivery_cost' => $zone->delivery_cost,
                'currency_id' => $zone->currency_id,
                'current_exchange_rate' => $zone->currency->exchange_value,
                'zone_id' => $zone->id,

            ];
            $orderData = array_merge($orderData, collect($data)->except(['products', 'offers'])->toArray());

            $orderNumber = $this->generateOrderNumber();

            $order = CustomerOrder::create([
                ...$orderData,
                'order_status' => OrderStatus::pending->value,
                'order_number' => $orderNumber,
                'created_by_type' => DashUser::class,
                'created_by_id' => auth()->user()->id,
            ]);
            $totalBasePrice = 0;

            $zoneId = $zone->id;
            $productIds = collect($data['products'])->pluck('product_id');
            $offerIds = collect($data['offers'])->pluck('offer_id');

            // PRODUCTS PRICES
            $productZonePrices = ProductZonePrice::where('zone_id', $zoneId)
                ->whereIn('product_id', $productIds ?? [])
                ->where('is_available', true)
                ->get()
                ->keyBy('product_id');

            // OFFERS PRICES
            $offerZonePrices = OfferZonePrice::where('zone_id', $zoneId)
                ->whereIn('offer_id', $offerIds ?? [])
                ->where('is_available', true)
                ->get()
                ->keyBy('offer_id');

            // =========================
            // ✅ HANDLE PRODUCTS
            // =========================
            if (!empty($data['products'])) {


                $warehouseProducts = ProductWarehouse::where('warehouse_id', $data['warehouse_id'])
                    ->whereIn('product_id', $productIds)
                    ->lockForUpdate() // 🔥 prevent race condition
                    ->get()
                    ->keyBy('product_id');

                foreach ($data['products'] as $item) {

                    $warehouseProduct = $warehouseProducts->get($item['product_id']);
                    if (!$warehouseProduct) {
                        throw new CustomException('المنتج غير موجود في المستودع');
                    }

                    $available = $warehouseProduct->quantity - $warehouseProduct->reserved_quantity;

                    if ($item['quantity'] > $available) {
                        throw new CustomException("الكمية غير متوفرة للمنتج {$item['product_id']}");
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

                    // Optional: reserve stock
                    $warehouseProduct->increment('reserved_quantity', $item['quantity']);
                }
            }

            // =========================
            // ✅ HANDLE OFFERS (FIXED)
            // =========================
            if (!empty($data['offers'])) {

                $warehouseOffers = OfferWarehouse::where('warehouse_id', $data['warehouse_id'])
                    ->whereIn('offer_id', $offerIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('offer_id');

                // 🔥 LOAD OFFERS WITH PRODUCTS
                $offers = Offer::with('products')
                    ->whereIn('id', $offerIds)
                    ->get()
                    ->keyBy('id');

                // 🔥 PRELOAD ALL PRODUCTS INSIDE OFFERS
                $allOfferProductIds = $offers
                    ->flatMap(fn($offer) => $offer->products->pluck('id'))
                    ->unique();

                $warehouseProducts = ProductWarehouse::where('warehouse_id', $data['warehouse_id'])
                    ->whereIn('product_id', $allOfferProductIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                foreach ($data['offers'] as $item) {

                    $warehouseOffer = $warehouseOffers->get($item['offer_id']);

                    if (!$warehouseOffer) {
                        throw new CustomException('العرض غير موجود في المستودع');
                    }

                    $available = $warehouseOffer->quantity - $warehouseOffer->reserved_quantity;

                    if ($item['quantity'] > $available) {
                        throw new CustomException("الكمية غير متوفرة للعرض {$item['offer_id']}");
                    }

                    $offer = $offers->get($item['offer_id']);

                    if (!$offer) {
                        throw new CustomException('العرض غير موجود');
                    }

                    // =========================
                    // 🔥 CHECK PRODUCTS INSIDE OFFER
                    // =========================
                    foreach ($offer->products as $product) {

                        $requiredQty = $product->pivot->quantity * $item['quantity'];

                        $warehouseProduct = $warehouseProducts->get($product->id);

                        if (!$warehouseProduct) {
                            throw new CustomException("منتج داخل العرض غير موجود في المستودع");
                        }

                        $availableProduct = $warehouseProduct->quantity - $warehouseProduct->reserved_quantity;

                        if ($requiredQty > $availableProduct) {
                            throw new CustomException(
                                "الكمية غير كافية لمنتج {$product->name} داخل العرض"
                            );
                        }
                    }

                    // =========================
                    // 🔥 RESERVE PRODUCTS FIRST
                    // =========================
                    foreach ($offer->products as $product) {

                        $requiredQty = $product->pivot->quantity * $item['quantity'];

                        $warehouseProducts
                            ->get($product->id)
                            ->increment('reserved_quantity', $requiredQty);
                    }

                    // =========================
                    // 🔥 RESERVE OFFER
                    // =========================
                    $warehouseOffer->increment('reserved_quantity', $item['quantity']);

                    // =========================
                    // 💰 PRICE
                    // =========================
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

            // =========================
            // ✅ CALCULATE TOTALS
            // =========================

            $totalPrice = $totalBasePrice;

            // deduction
            if (!empty($data['deduction_amount'])) {
                if (($data['deduction_type'] ?? 'fixed') == 'percentage') {
                    $totalPrice -= ($totalBasePrice * $data['deduction_amount'] / 100);
                } else {
                    $totalPrice -= $data['deduction_amount'];
                }
            }

            // tips
            if (!empty($data['additional_tips'])) {
                $totalPrice += $data['additional_tips'];
            }

            // update order totals
            $order->update([
                'total_base_price' => $totalBasePrice,
                'total_price' => max($totalPrice, 0),
            ]);

            // =========================
            // ✅ STATUS LOG
            // =========================
            OrderStatusLog::create([
                'customer_order_id' => $order->id,
                'status' => OrderStatus::pending->value,
                'changed_by_type' => get_class($user),
                'changed_by_id' => $user->id,
            ]);

            $order->load('customer', 'currency', 'marketer', 'warehouseMan', 'teamleader', 'manager', 'warehouse', 'reviewedBy', 'address', 'createdBy', 'products.product', 'offers.offer');
            return $order;
        });
    }

    public function update(CustomerOrder $order, array $data)
    {
        if ($order->order_status !== OrderStatus::pending->value) {
            throw new CustomException('لا يمكن تعديل الطلب بعد مراجعته.');
        }

        return DB::transaction(function () use ($order, $data) {

            // =========================
            // ✅ LOAD OLD DATA
            // =========================
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

            // 🔥 Load offers with products
            $offers = Offer::with('products')
                ->whereIn('id', $order->offers->pluck('offer_id'))
                ->get()
                ->keyBy('id');

            // 🔥 preload product warehouses
            $allOfferProductIds = $offers
                ->flatMap(fn($offer) => $offer->products->pluck('id'))
                ->unique();

            $warehouseProducts = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                ->whereIn('product_id', $allOfferProductIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            foreach ($order->offers as $oldOffer) {

                // ✅ restore offer itself
                OfferWarehouse::where('warehouse_id', $order->warehouse_id)
                    ->where('offer_id', $oldOffer->offer_id)
                    ->lockForUpdate()
                    ->decrement('reserved_quantity', $oldOffer->quantity);

                $offer = $offers->get($oldOffer->offer_id);

                if (!$offer) continue;

                // 🔥 restore products inside offer
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

            // =========================
            // 🔥 STEP 2: DELETE OLD ITEMS
            // =========================
            $order->products()->delete();
            $order->offers()->delete();

            // =========================
            // ✅ STEP 3: REBUILD ORDER DATA
            // =========================

            $user = AppUser::with('team', 'subTeam.team')->find($data['app_user_id']);

            $team = $user->subTeam
                ? $user->subTeam->team
                : $user->team;

            if (!$team) {
                throw new CustomException('المستخدم لا ينتمي إلى فريق');
            }

            $teamleaderId = $user->subTeam?->team_leader_id;
            $isDirectTeam = $user->subTeam?->is_direct;

            $orderData = [
                'teamleader_id' => $isDirectTeam ? $team->manager_id : $teamleaderId,
                'manager_id' => $isDirectTeam ? null : $team->manager_id,

                'marketer_percentage' => $team->marketer_percentage,
                'teamleader_percentage' => $team->team_leader_percentage,
                'manager_percentage' => $isDirectTeam ? 0 : $team->manager_percentage,
                'is_stock_reserved' => true,
                'team_id' => $team->id,
                'sub_team_id' => $user->subteam_id

            ];

            $address = Address::with('region.city.zone')->find($data['address_id']);

            if (!$address) {
                throw new CustomException('العنوان غير موجود');
            }

            $zone = $address->region->city->zone;

            $orderData += [
                'delivery_cost' => $zone->delivery_cost,
                'currency_id' => $zone->currency_id,
                'zone_id' => $zone->id,
                'current_exchange_rate' => $zone->currency->exchange_value,
            ];

            $orderData = array_merge(
                $orderData,
                collect($data)->except(['products', 'offers'])->toArray()
            );

            $order->update($orderData);

            // =========================
            // ✅ STEP 4: PRELOAD PRICES
            // =========================

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

            // =========================
            // ✅ STEP 5: ADD PRODUCTS
            // =========================
            if (!empty($data['products'])) {

                $warehouseProducts = ProductWarehouse::where('warehouse_id', $data['warehouse_id'])
                    ->whereIn('product_id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                foreach ($data['products'] as $item) {

                    $warehouseProduct = $warehouseProducts->get($item['product_id']);

                    if (!$warehouseProduct) {
                        throw new CustomException('المنتج غير موجود في المستودع');
                    }

                    $available = $warehouseProduct->quantity - $warehouseProduct->reserved_quantity;

                    if ($item['quantity'] > $available) {
                        throw new CustomException("الكمية غير متوفرة");
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

            // =========================
            // ✅ HANDLE OFFERS (FIXED)
            // =========================
            if (!empty($data['offers'])) {

                $warehouseOffers = OfferWarehouse::where('warehouse_id', $data['warehouse_id'])
                    ->whereIn('offer_id', $offerIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('offer_id');

                // 🔥 LOAD OFFERS WITH PRODUCTS
                $offers = Offer::with('products')
                    ->whereIn('id', $offerIds)
                    ->get()
                    ->keyBy('id');

                // 🔥 PRELOAD ALL PRODUCTS INSIDE OFFERS
                $allOfferProductIds = $offers
                    ->flatMap(fn($offer) => $offer->products->pluck('id'))
                    ->unique();

                $warehouseProducts = ProductWarehouse::where('warehouse_id', $data['warehouse_id'])
                    ->whereIn('product_id', $allOfferProductIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                foreach ($data['offers'] as $item) {

                    $warehouseOffer = $warehouseOffers->get($item['offer_id']);

                    if (!$warehouseOffer) {
                        throw new CustomException('العرض غير موجود في المستودع');
                    }

                    $available = $warehouseOffer->quantity - $warehouseOffer->reserved_quantity;

                    if ($item['quantity'] > $available) {
                        throw new CustomException("الكمية غير متوفرة للعرض {$item['offer_id']}");
                    }

                    $offer = $offers->get($item['offer_id']);

                    if (!$offer) {
                        throw new CustomException('العرض غير موجود');
                    }

                    // =========================
                    // 🔥 CHECK PRODUCTS INSIDE OFFER
                    // =========================
                    foreach ($offer->products as $product) {

                        $requiredQty = $product->pivot->quantity * $item['quantity'];

                        $warehouseProduct = $warehouseProducts->get($product->id);

                        if (!$warehouseProduct) {
                            throw new CustomException("منتج داخل العرض غير موجود في المستودع");
                        }

                        $availableProduct = $warehouseProduct->quantity - $warehouseProduct->reserved_quantity;

                        if ($requiredQty > $availableProduct) {
                            throw new CustomException(
                                "الكمية غير كافية لمنتج {$product->name} داخل العرض"
                            );
                        }
                    }

                    // =========================
                    // 🔥 RESERVE PRODUCTS FIRST
                    // =========================
                    foreach ($offer->products as $product) {

                        $requiredQty = $product->pivot->quantity * $item['quantity'];

                        $warehouseProducts
                            ->get($product->id)
                            ->increment('reserved_quantity', $requiredQty);
                    }

                    // =========================
                    // 🔥 RESERVE OFFER
                    // =========================
                    $warehouseOffer->increment('reserved_quantity', $item['quantity']);

                    // =========================
                    // 💰 PRICE
                    // =========================
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
            // =========================
            // ✅ STEP 7: RECALCULATE TOTAL
            // =========================

            $totalPrice = $totalBasePrice;

            if (!empty($data['deduction_amount'])) {
                if (($data['deduction_type'] ?? 'fixed') == 'percentage') {
                    $totalPrice -= ($totalBasePrice * $data['deduction_amount'] / 100);
                } else {
                    $totalPrice -= $data['deduction_amount'];
                }
            }

            if (!empty($data['additional_tips'])) {
                $totalPrice += $data['additional_tips'];
            }

            $totalPrice += $zone->delivery_cost;


            $order->update([
                'total_base_price' => $totalBasePrice,
                'total_price' => max($totalPrice, 0),
            ]);

            return $order->fresh()->load([
                'customer',
                'currency',
                'products.product',
                'offers.offer'
            ]);
        });
    }
    public function show(CustomerOrder $order)
    {
        $order->load('customer', 'statusLogs.changedBy', 'currency', 'marketer', 'warehouseMan', 'teamleader', 'manager', 'warehouse', 'reviewedBy', 'address', 'createdBy', 'products.product', 'offers.offer');
        return $order;
    }

    public function delete(CustomerOrder $order)
    {
        if (!in_array($order->order_status, [
            OrderStatus::pending->value,
            OrderStatus::rejected->value,
            OrderStatus::cancelled->value
        ])) {
            throw new CustomException('لا يمكن حذف الطلب بعد معالجته.');
        }

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

        // 🔥 Load offers with products
        $offers = Offer::with('products')
            ->whereIn('id', $order->offers->pluck('offer_id'))
            ->get()
            ->keyBy('id');

        // 🔥 preload product warehouses
        $allOfferProductIds = $offers
            ->flatMap(fn($offer) => $offer->products->pluck('id'))
            ->unique();

        $warehouseProducts = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
            ->whereIn('product_id', $allOfferProductIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        foreach ($order->offers as $oldOffer) {

            // ✅ restore offer itself
            OfferWarehouse::where('warehouse_id', $order->warehouse_id)
                ->where('offer_id', $oldOffer->offer_id)
                ->lockForUpdate()
                ->decrement('reserved_quantity', $oldOffer->quantity);

            $offer = $offers->get($oldOffer->offer_id);

            if (!$offer) continue;

            // 🔥 restore products inside offer
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

        // =========================
        // 🔥 STEP 2: DELETE OLD ITEMS
        // =========================
        $order->products()->delete();
        $order->offers()->delete();
        return $order->delete();
    }

    private function releaseStock(CustomerOrder $order)
    {
        if (!$order->is_stock_reserved)
            return;
        foreach ($order->products as $item) {
            $warehouseProduct = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                ->where('product_id', $item->product_id)
                ->lockForUpdate()
                ->first();

            if (!$warehouseProduct) {
                continue;
            }
            $warehouseProduct->update([
                'reserved_quantity' => max(
                    $warehouseProduct->reserved_quantity - $item->quantity,
                    0
                ),
            ]);
        }


        $offers = Offer::with('products')
            ->whereIn('id', $order->offers->pluck('offer_id'))
            ->get()
            ->keyBy('id');

        // 🔥 preload product warehouses
        $allOfferProductIds = $offers
            ->flatMap(fn($offer) => $offer->products->pluck('id'))
            ->unique();

        $warehouseProducts = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
            ->whereIn('product_id', $allOfferProductIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        foreach ($order->offers as $item) {

            $warehouseOffer = OfferWarehouse::where('warehouse_id', $order->warehouse_id)
                ->where('offer_id', $item->offer_id)
                ->lockForUpdate()
                ->first();

            if (!$warehouseOffer) {
                continue;
            }
            $warehouseOffer->update([
                'reserved_quantity' => max(
                    $warehouseOffer->reserved_quantity - $item->quantity,
                    0
                ),
            ]);

            $offer = $offers->get($item->offer_id);

            if (!$offer) continue;

            // 🔥 restore products inside offer
            foreach ($offer->products as $product) {

                $restoreQty = $product->pivot->quantity * $item->quantity;

                $warehouseProduct = $warehouseProducts->get($product->id);

                if (!$warehouseProduct) {
                    throw new CustomException("منتج داخل العرض غير موجود في المستودع");
                }

                $warehouseProduct->update([
                    'reserved_quantity' => max(
                        $warehouseProduct->reserved_quantity - $restoreQty,
                        0
                    ),
                ]);
            }
        }
    }

    private function reserveStock(CustomerOrder $order)
    {
        if ($order->is_stock_reserved)
            return;
        $order->load('products', 'offers');

        // PRODUCTS
        $productIds = $order->products->pluck('product_id');

        $warehouseProducts = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        foreach ($order->products as $item) {

            $warehouseProduct = $warehouseProducts->get($item->product_id);

            if (!$warehouseProduct) {
                throw new CustomException('المنتج غير موجود في المستودع');
            }

            $available = $warehouseProduct->quantity - $warehouseProduct->reserved_quantity;

            if ($item->quantity > $available) {
                throw new CustomException("لا يمكن إعادة الطلب، الكمية غير متوفرة للمنتج {$item->product_id}");
            }

            $warehouseProduct->increment('reserved_quantity', $item->quantity);
        }


        // OFFERS
        $offerIds = $order->offers->pluck('offer_id');

        $warehouseOffers = OfferWarehouse::where('warehouse_id', $order->warehouse_id)
            ->whereIn('offer_id', $offerIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('offer_id');

        $offers = Offer::with('products')
            ->whereIn('id', $order->offers->pluck('offer_id'))
            ->get()
            ->keyBy('id');
        foreach ($order->offers as $item) {

            $warehouseOffer = $warehouseOffers->get($item->offer_id);

            if (!$warehouseOffer) {
                throw new CustomException('العرض غير موجود');
            }


            $available = $warehouseOffer->quantity - $warehouseOffer->reserved_quantity;

            if ($item->quantity > $available) {
                throw new CustomException("لا يمكن إعادة الطلب، الكمية غير متوفرة للعرض {$item->offer_id}");
            }
            $warehouseOffer->increment('reserved_quantity', $item->quantity);

            //////////////////////////////////////


            $offer = $offers->get($item->offer_id);

            if (!$offer) {
                throw new CustomException('العرض غير موجود');
            }

            // =========================
            // 🔥 CHECK PRODUCTS INSIDE OFFER
            // =========================
            foreach ($offer->products as $product) {

                $requiredQty = $product->pivot->quantity * $item->quantity;

                $warehouseProduct = $warehouseProducts->get($product->id);

                if (!$warehouseProduct) {
                    throw new CustomException("منتج داخل العرض غير موجود في المستودع");
                }

                $availableProduct = $warehouseProduct->quantity - $warehouseProduct->reserved_quantity;

                if ($requiredQty > $availableProduct) {
                    throw new CustomException(
                        "الكمية غير كافية لمنتج {$product->name} داخل العرض"
                    );
                }
            }

            // =========================
            // 🔥 RESERVE PRODUCTS FIRST
            // =========================
            foreach ($offer->products as $product) {

                $requiredQty = $product->pivot->quantity * $item->quantity;

                $warehouseProducts
                    ->get($product->id)
                    ->increment('reserved_quantity', $requiredQty);
            }

            // =========================
            // 🔥 RESERVE OFFER
            // =========================
            $warehouseOffer->increment('reserved_quantity', $item->quantity);
        }
    }

    private function removeFromStock(CustomerOrder $order)
    {
        $order->load('products', 'offers');

        // PRODUCTS
        $productIds = $order->products->pluck('product_id');

        $warehouseProducts = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        foreach ($order->products as $item) {

            $warehouseProduct = $warehouseProducts->get($item->product_id);
            $warehouseProduct->decrement('reserved_quantity', $item->quantity);
            $warehouseProduct->decrement('quantity', $item->quantity);
        }



        // =========================
        // ✅ OFFERS + PRODUCTS INSIDE
        // =========================

        $offerIds = $order->offers->pluck('offer_id');

        $offers = Offer::with('products')
            ->whereIn('id', $offerIds)
            ->get()
            ->keyBy('id');

        $warehouseOffers = OfferWarehouse::where('warehouse_id', $order->warehouse_id)
            ->whereIn('offer_id', $offerIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('offer_id');

        // 🔥 preload all products inside offers
        $allOfferProductIds = $offers
            ->flatMap(fn($offer) => $offer->products->pluck('id'))
            ->unique();

        $warehouseProducts = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
            ->whereIn('product_id', $allOfferProductIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        foreach ($order->offers as $item) {

            $warehouseOffer = $warehouseOffers->get($item->offer_id);

            if (!$warehouseOffer) {
                throw new CustomException("العرض غير موجود في المستودع");
            }

            // ✅ remove offer
            $warehouseOffer->decrement('reserved_quantity', $item->quantity);
            $warehouseOffer->decrement('quantity', $item->quantity);

            $offer = $offers->get($item->offer_id);

            if (!$offer) continue;

            // 🔥 remove products inside offer
            foreach ($offer->products as $product) {

                $requiredQty = $product->pivot->quantity * $item->quantity;

                $warehouseProduct = $warehouseProducts->get($product->id);

                if (!$warehouseProduct) {
                    throw new CustomException("منتج داخل العرض غير موجود");
                }

                $warehouseProduct->decrement('reserved_quantity', $requiredQty);
                $warehouseProduct->decrement('quantity', $requiredQty);
            }
        }
    }


    private function returnToStock(CustomerOrder $order)
    {
        foreach ($order->products as $item) {
            $warehouseProduct = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                ->where('product_id', $item->product_id)
                ->lockForUpdate()
                ->first();

            if (!$warehouseProduct) {
                continue;
            }
            $warehouseProduct->update([
                'quantity' => $warehouseProduct->quantity + $item->quantity,

            ]);
        }
        // =========================
        // ✅ OFFERS + PRODUCTS
        // =========================

        $offerIds = $order->offers->pluck('offer_id');

        $offers = Offer::with('products')
            ->whereIn('id', $offerIds)
            ->get()
            ->keyBy('id');

        foreach ($order->offers as $item) {

            // ✅ restore offer
            OfferWarehouse::where('warehouse_id', $order->warehouse_id)
                ->where('offer_id', $item->offer_id)
                ->lockForUpdate()
                ->increment('quantity', $item->quantity);

            $offer = $offers->get($item->offer_id);

            if (!$offer) continue;

            // 🔥 restore products inside offer
            foreach ($offer->products as $product) {

                $restoreQty = $product->pivot->quantity * $item->quantity;

                ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->increment('quantity', $restoreQty);
            }
        }
    }



    private array $allowedTransitions = [
        'pending' => ['approved', 'rejected', 'cancelled'],

        'approved' => ['processing', 'delivering', 'cancelled'],

        'processing' => ['delivering', 'cancelled'],

        'delivering' => ['completed', 'refund'],

        'completed' => ['refund'],

        'rejected' => ['pending'],
        'cancelled' => ['pending'],

        'refund' => [],
    ];

    public function handle(CustomerOrder $order, array $data)
    {
        $status = OrderStatus::from($data['status']);

        return DB::transaction(function () use ($order, $status, $data) {
            $currentStatus = OrderStatus::from($order->order_status);
            $reserveStock = $order->is_stock_reserved;

            if (
                !isset($this->allowedTransitions[$currentStatus->value]) ||
                !in_array($status->value, $this->allowedTransitions[$currentStatus->value])
            ) {
                throw new CustomException('تغيير الحالة غير مسموح.');
            }

            if (
                $currentStatus != OrderStatus::pending &&
                $status == OrderStatus::approved
            ) {
                throw new CustomException('تمت معالجة الطلب مسبقاً');
            }

            // =========================
            // ✅ APPROVE
            // =========================
            if ($status == OrderStatus::approved) {

                // $this->handleApprove($order);
            }

            if ($status == OrderStatus::completed) {

                $this->handleComplete($order);
                $this->removeFromStock($order);
            }

            // =========================
            // 🔁 REFUND
            // =========================
            if ($status == OrderStatus::refund) {

                $this->handleRefund($order);
                $this->returnToStock($order);
                $reserveStock = false;
            }

            // =========================
            // ❌ REJECT / CANCEL
            // =========================
            if (
                in_array($status, [OrderStatus::rejected, OrderStatus::cancelled])
            ) {
                $this->releaseStock($order);
                $reserveStock = false;
            }

            if (
                $status == OrderStatus::pending &&
                in_array($currentStatus, [OrderStatus::cancelled])
            ) {
                $this->reserveStock($order);
                $reserveStock = true;
            }
            // =========================
            // ✅ UPDATE STATUS
            // =========================
            $order->update([
                'order_status' => $status->value,
                'reject_reason' => $data['reject_reason'] ?? null,
                'is_stock_reserved' => $reserveStock,
                'approved_at' => $status == OrderStatus::approved ? now() : null,
                'cancelled_at' => in_array($status, [OrderStatus::rejected, OrderStatus::cancelled]) ? now() : null,

            ]);

            // =========================
            // 📝 STATUS LOG
            // =========================
            OrderStatusLog::create([
                'customer_order_id' => $order->id,
                'status' => $status->value,
                'changed_by_type' => get_class(Auth::user()),
                'changed_by_id' => Auth::id(),
            ]);

            return $order->refresh();
        });
    }

    private function handleCompleteOrder(CustomerOrder $order)
    {
        if ($order->is_financial_processed)
            return;
        $amount = $order->total_base_price * $order->current_exchange_rate; // 🔥 ALWAYS BASE

        // =========================
        // CALCULATE SHARES
        // =========================
        $marketerAmount   = $amount * $order->marketer_percentage / 100;
        $teamleaderAmount = $amount * $order->teamleader_percentage / 100;
        $managerAmount    = $amount * ($order->manager_percentage ?? 0) / 100;

        $warehouseAmount  = $amount * ($order->warehouse_man_percentage ?? 0) / 100;

        // =========================
        // SAVE SNAPSHOT
        // =========================
        $order->update([
            'marketer_amount' => $marketerAmount,
            'teamleader_amount' => $teamleaderAmount,
            'manager_amount' => $managerAmount,
            'warehouse_man_amount' => $warehouseAmount,
            'is_financial_processed' => true
        ]);

        $warehouseAmount = $warehouseAmount + ($order->delivery_cost + $order->delivery_additional_cost) * $order->current_exchange_rate;
        // =========================
        // APPLY BALANCES
        // =========================
        $this->addBalance($order->app_user_id, $marketerAmount, 'marketer_percentage');
        $this->addBalance($order->teamleader_id, $teamleaderAmount, 'teamleader_percentage');

        if ($order->manager_id) {
            $this->addBalance($order->manager_id, $managerAmount, 'manager_percentage');
        }

        if ($order->warehouse_man_id) {
            $this->addBalance($order->warehouse_man_id, $warehouseAmount, 'warehouse_man_percentage');
        }
    }

    private function handleRefund(CustomerOrder $order)
    {
        if (!$order->is_financial_processed)
            return;

        $this->subtractBalance($order->app_user_id, $order->marketer_amount, 'refund_marketer');
        $this->subtractBalance($order->teamleader_id, $order->teamleader_amount, 'refund_teamleader');

        if ($order->manager_id) {
            $this->subtractBalance($order->manager_id, $order->manager_amount, 'refund_manager');
        }

        if ($order->warehouse_man_id) {
            $this->subtractBalance($order->warehouse_man_id, $order->warehouse_man_amount, 'refund_warehouse');
        }
    }

    private function addBalance($userId, $amount, $type)
    {
        if (!$userId || $amount <= 0) return;

        $user = AppUser::lockForUpdate()->find($userId);

        $before = $user->balance;
        $after = $before + $amount;

        $user->update([
            'balance' => $after
        ]);

        $this->createTransaction($userId, $amount, $before, $after, $type);
    }

    private function subtractBalance($userId, $amount, $type)
    {
        if (!$userId || $amount <= 0) return;

        $user = AppUser::lockForUpdate()->find($userId);

        if ($user->balance < $amount) {
            throw new CustomException('الرصيد غير كافٍ للاسترجاع');
        }

        $before = $user->balance;
        $after = $before - $amount;

        $user->update([
            'balance' => $after
        ]);

        $this->createTransaction($userId, -$amount, $before, $after, $type);
    }

    private function createTransaction($userId, $amount, $before, $after, $type)
    {
        VaultTransaction::create([
            'type' => $type,
            'amount' => abs($amount),
            'transaction_date' => now(),

            'reference_type' => CustomerOrder::class,
            'reference_id' => request()->route('customer_order')->id,

            'action_by_type' => get_class(Auth::user()),
            'action_by_id' => Auth::id(),

            'to_vault_balance_before' => $before,
            'to_vault_balance_after' => $after,
        ]);
    }


    public function selectMarketerInfo($marketeId)
    {

        $user = AppUser::with('team', 'subTeam.team')->find($marketeId);

        $team = $user->subTeam
            ? $user->subTeam->team
            : $user->team;

        if (!$team) {
            throw new CustomException('المسوق لا ينتمي إلى فريق');
        }

        if ($user->subTeam) {
            $teamArr = [
                'subTeam' =>  [
                    'id' => $user->subTeam?->id,
                    'name' => $user->subTeam?->name
                ],
                'team' =>  [
                    'id' => $user->subTeam?->team?->id,
                    'name' => $user->subTeam?->team?->name,
                ]
            ];
        } else if ($user->team) {
            $teamArr = [
                'team' =>  [
                    'id' => $user->team?->id,
                    'name' => $user->team?->name
                ],
                'subTeam' =>  null
            ];
        } else {
            $teamArr = [
                'team' =>  null,
                'subTeam' =>  null

            ];
        }

        $isDirectTeam = $user->subTeam?->is_direct;

        $requiredData = [

            'marketer_percentage' => $team->marketer_percentage,
            'teamleader_percentage' => $team->team_leader_percentage,
            'manager_percentage' => $isDirectTeam ? null : $team->manager_percentage
        ];

        return array_merge($requiredData, $teamArr);
    }
    public function selectAddressInfo($addressId)
    {
        $address = Address::with('region.city.zone')->find($addressId);

        if (!$address) {
            throw new CustomException('العنوان غير موجود');
        }

        $zone = $address->region->city->zone;

        return [
            'delivery_cost' => $zone->delivery_cost,
            'currency_id' => $zone->currency_id,
            'currency_name' => $zone->currency?->name,
            'currency_symbol' => $zone->currency?->symbol,
            'current_exchange_rate' => $zone->currency?->exchange_value,
            'zone_id' => $zone->id,
            'zone_name' => $zone->name . '(' . $zone->symbol . ')'
        ];
    }

    public function selectZoneProducts($zoneId)
    {

        // PRODUCTS PRICES
        $productZonePrices = ProductZonePrice::with('product:id,name')->where('zone_id', $zoneId)
            ->where('is_available', true)
            ->get();

        return $productZonePrices->map(fn($p) => [
            'product_id' => $p->product?->id,
            'product_name' => $p->product?->name,
            'product_price' => $p->price,
            'product_price_after_adjustment' => $p->price_after_adjustment,
        ]);
    }

    public function selectZoneOffers($zoneId)
    {
        $offerZonePrices = OfferZonePrice::with('offer:id,name')->where('zone_id', $zoneId)
            ->where('is_available', true)
            ->get();

        return $offerZonePrices->map(fn($p) => [
            'offer_id' => $p->offer?->id,
            'offer_name' => $p->offer?->name,
            'offer_price' => $p->price,

        ]);
    }


    public function warehouseProducts($warehouseId)
    {

        $products = ProductWarehouse::with(
            [
                'product:id,name',
            ]
        )->where('warehouse_id', $warehouseId)->get();

        return $products->map(fn($p) => [
            'id' => $p?->product?->id,
            'name' => $p?->product?->name,
            'available' => $p->quantity - $p->reserved_quantity
        ]);
    }

    public function warehouseOffers($warehouseId)
    {

        $offers = OfferWarehouse::with(
            [
                'offer:id,name',
            ]
        )->where('warehouse_id', $warehouseId)->get();

        return $offers->map(fn($p) => [
            'id' => $p?->offer?->id,
            'name' => $p?->offer?->name,
            'available' => $p->quantity
        ]);
    }


    public function customerAddresses($customerId)
    {

        $customer = Customer::find($customerId);

        return $customer->addresses->map(fn($address) =>
        [
            'id' => $address->id,
            'address' => $address->full_address ?? $address->name ?? '',
            'extra_details' => $address->pivot->extra_details,
            'is_main' => (bool)$address->pivot->is_main,
        ]);
    }


    public function selectWarehouseInfo($warehouseId)
    {

        $warehouse = Warehouse::with('keeper')->find($warehouseId);

        if (!$warehouse) {
            throw new CustomException('المستودع غير موجود');
        }

        $requiredData = [
            'warehouse_keeper' => [
                'id' => $warehouse?->keeper_id,
                'name' => $warehouse->keeper?->first_name . ' ' . $warehouse->keeper?->last_name . ' (' . $warehouse->keeper?->user_name . ')',
            ],
        ];

        return $requiredData;
    }
}
