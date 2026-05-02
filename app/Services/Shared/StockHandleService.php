<?php

namespace App\Services\Shared;

use App\Exceptions\CustomException;
use App\Models\CustomerOrder;
use App\Models\Offer;
use App\Models\OfferWarehouse;
use App\Models\ProductWarehouse;

class StockHandleService
{
    public function releaseStock(CustomerOrder $order)
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

    public function reserveStock(CustomerOrder $order)
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

         

            $offer = $offers->get($item->offer_id);

            if (!$offer) {
                throw new CustomException('العرض غير موجود');
            }

           
            foreach ($offer->products as $product) {

                $requiredQty = $product->pivot->quantity * $item->quantity;

                $warehouseProduct = $warehouseProducts->get($product->id);

                if (!$warehouseProduct) {
                    throw new CustomException("منتج داخل العرض غير موجود في المستودع");
                }

            }

            foreach ($offer->products as $product) {

                $requiredQty = $product->pivot->quantity * $item->quantity;

                $warehouseProducts
                    ->get($product->id)
                    ->increment('reserved_quantity', $requiredQty);
            }
        }
    }

    public function removeFromStock(CustomerOrder $order)
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
            if (!$warehouseProduct) {
                throw new CustomException("المنتج غير موجود في المخزن");
            }
          
            if ($warehouseProduct->quantity < $item->quantity) {
                throw new CustomException("الكمية غير متوفرة للمنتج {$item->product_id}");
            }
            $warehouseProduct->decrement('reserved_quantity', $item->quantity);
            $warehouseProduct->decrement('quantity', $item->quantity);
        }

        $offerIds = $order->offers->pluck('offer_id');

        $offers = Offer::with('products')
            ->whereIn('id', $offerIds)
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

        foreach ($order->offers as $item) {

            $offer = $offers->get($item->offer_id);

            if (!$offer) continue;

            foreach ($offer->products as $product) {

                $requiredQty = $product->pivot->quantity * $item->quantity;

                $warehouseProduct = $warehouseProducts->get($product->id);

                if (!$warehouseProduct) {
                    throw new CustomException("منتج داخل العرض غير موجود");
                }

                if ($warehouseProduct->quantity < $requiredQty) {
                    throw new CustomException("الكمية الفعلية غير كافية لمنتج داخل العرض {$product->id}");
                }

                $warehouseProduct->decrement('reserved_quantity', $requiredQty);
                $warehouseProduct->decrement('quantity', $requiredQty);
            }
        }
    }


    public function returnToStock(CustomerOrder $order)
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
      
        $offerIds = $order->offers->pluck('offer_id');

        $offers = Offer::with('products')
            ->whereIn('id', $offerIds)
            ->get()
            ->keyBy('id');

        foreach ($order->offers as $item) {

           
            $offer = $offers->get($item->offer_id);

            if (!$offer) continue;

            foreach ($offer->products as $product) {

                $restoreQty = $product->pivot->quantity * $item->quantity;

                ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->increment('quantity', $restoreQty);
            }
        }
    }
}
