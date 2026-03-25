<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\ProductWarehouse;
use Illuminate\Support\Facades\DB;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        if ($invoice->is_confirmed) {
            $this->applyStock($invoice);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged('is_confirmed') && $invoice->is_confirmed) {
            $invoice->load(['invoiceProductWarehouses.product.mainCategory', 'invoiceProductWarehouses.product.subCategory']);

            $this->applyStock($invoice);
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        //
    }

    private function applyStock(Invoice $invoice): void
    {
        foreach ($invoice->invoiceProductWarehouses as $item) {

            DB::transaction(function () use ($invoice, $item) {

                $productWarehouse = ProductWarehouse::where([
                    'warehouse_id' => $invoice->warehouse_id,
                    'product_id'   => $item->product_id,
                ])->lockForUpdate()->first();

                if ($productWarehouse) {
                    $productWarehouse->increment('quantity', $item->added_quantity);
                } else {
                    ProductWarehouse::create([
                        'warehouse_id' => $invoice->warehouse_id,
                        'product_id'   => $item->product_id,
                        'quantity'     => $item->added_quantity,
                    ]);
                }
            });
        }
    }
}
