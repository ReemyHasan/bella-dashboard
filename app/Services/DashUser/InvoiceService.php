<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function list($request)
    {
        return Invoice::with('warehouse', 'createdBy')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {
            $invoice = Invoice::create([
                'title' => $data['title'],
                'name_of_merchant' => $data['name_of_merchant'],
                'date' => $data['date'],
                'warehouse_id' => $data['warehouse_id'],
                'is_confirmed' => false,
                'created_by_type' => get_class($user),
                'created_by_id' => $user->id,
            ]);

            foreach ($data['products'] as $product) {
                $invoice->invoiceProductWarehouses()->create([
                    'product_id'     => $product['product_id'],
                    'added_quantity' => $product['added_quantity'],
                ]);
            }

            if ($data['is_confirmed']) {
                $invoice->update(['is_confirmed' => true]);
            }
            $invoice->load('warehouse', 'createdBy');
            return $invoice;
        });
    }

    public function update(Invoice $invoice, array $data)
    {
        return DB::transaction(function () use ($invoice, $data) {
            $oldWarehouse = $invoice->warehouse_id;
            $invoice->update([
                'title' => $data['title'],
                'name_of_merchant' => $data['name_of_merchant'],
                'date' => $data['date'],
                'warehouse_id' => $data['warehouse_id'],
                'is_confirmed' => false,
            ]);


            $existingItems = $invoice->invoiceProductWarehouses()
                ->get()
                ->keyBy('product_id');

            $incomingProducts = collect($data['products'])
                ->keyBy('product_id');


            foreach ($incomingProducts as $productId => $productData) {

                $newQty = $productData['added_quantity'];

                if ($existingItems->has($productId)) {

                    $item = $existingItems[$productId];
                    $oldQty = $item->added_quantity;

                    if ($oldQty != $newQty) {
                        $item->update([
                            'added_quantity' => $newQty
                        ]);
                    }
                } else {

                    $invoice->invoiceProductWarehouses()->create([
                        'product_id' => $productId,
                        'added_quantity' => $newQty,
                    ]);
                }
            }

            foreach ($existingItems as $productId => $item) {
                if (!$incomingProducts->has($productId)) {
                    $item->delete();
                }
            }

            if ($data['is_confirmed']) {
                $invoice->update(['is_confirmed' => true]);
            }
            $invoice->load('warehouse', 'createdBy');

            return $invoice;
        });
    }

    public function confirmInvoice(Invoice $invoice)
    {
        $invoice->update([
            'is_confirmed' => true,
        ]);
        $invoice->load('warehouse', 'createdBy');

        return $invoice;
    }
    public function show(Invoice $invoice)
    {
        $invoice->load(['warehouse', 'invoiceProductWarehouses.product.mainCategory', 'invoiceProductWarehouses.product.subCategory', 'createdBy']);
        return $invoice;
    }

    public function delete(Invoice $invoice)
    {

        if ($invoice->is_confirmed) {
            return false;
        }
        return $invoice->delete();
    }


    public function selectAvailable($zone = null, $is_main = null)
    {

        $cities = Invoice::when(!is_null($zone), function ($query) use ($zone) {
            $query->where('zone_id', $zone);
        })->when(!is_null($is_main), function ($query) use ($is_main) {
            $query->where('is_main', $is_main);
        })->where('active', true)->orderBy('id')->get([
            'id',
            'name',
            'active',
            'is_main',
            'zone_id'
        ]);

        return $cities;
    }
}
