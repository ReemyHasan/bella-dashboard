<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Traits\HandlesImageUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    use HandlesImageUpload;

    public function list($request)
    {
        return Product::with([
            'mainCategory',
            'subCategory',
            'mainImage',
            'zonePrices.zone.currency',
            'brand'
        ])->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = Str::slug($data['name']);
            $product = Product::create($data);

            if (!empty($data['tags'])) {
                $product->tags()->sync($data['tags']);
            }

            if (!empty($data['warehouses'])) {
                foreach ($data['warehouses'] as $warehouse) {
                    ProductWarehouse::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse['warehouse_id'],
                        'quantity' => $warehouse['quantity'],
                        'reserved_quantity' => 0,
                    ]);
                }
            }

            $product->load(
                'mainCategory',
                'subCategory',
                'brand'
            );
            return $product;
        });
    }

    public function update(Product $product, array $data)
    {

        return DB::transaction(function () use ($product, $data) {
            $product->update($data);

            $product->tags()->sync($data['tags'] ?? []);

            if (isset($data['warehouses'])) {

                $warehouseIds = [];

                foreach ($data['warehouses'] as $warehouse) {

                    $warehouseIds[] = $warehouse['warehouse_id'];

                    ProductWarehouse::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouse['warehouse_id'],
                        ],
                        [
                            'quantity' => $warehouse['quantity'],
                        ]
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Remove warehouses not sent
            |--------------------------------------------------------------------------
            */
                ProductWarehouse::where('product_id', $product->id)
                    ->whereNotIn('warehouse_id', $warehouseIds)
                    ->delete();
            }
            $product->load(
                'mainCategory',
                'subCategory',
                'mainImage',
                'brand'
            );
            return $product;
        });
    }

    public function syncImages($data, Product $product)
    {
        $existingIds = $product->images()->pluck('id')->toArray();
        $incomingIds = collect($data['images'])
            ->pluck('id')
            ->filter()
            ->toArray();


        $toDelete = array_diff($existingIds, $incomingIds);
        return DB::transaction(function () use ($product, $data, $toDelete) {

            $imagesToDelete = $product->images()->whereIn('id', $toDelete)->get();
            foreach ($imagesToDelete as $image) {
                $this->deleteImage($image->path);
            }

            $product->images()->whereIn('id', $toDelete)->delete();

            foreach ($data['images'] as $imageData) {

                if (!empty($imageData['id'])) {

                    $image = $product->images()->find($imageData['id']);

                    $updateData = [
                        'is_main' => $imageData['is_main'],
                        'sort_order' => $imageData['sort_order'] ?? 0,
                    ];

                    if (!empty($imageData['file'])) {
                        $updateData['path'] = $this->uploadImage($imageData['file'], 'products');
                    }

                    $image->update($updateData);
                } else {

                    $product->images()->create([
                        'path' => $this->uploadImage($imageData['file'], 'products'),
                        'is_main' => $imageData['is_main'],
                        'sort_order' => $imageData['sort_order'] ?? 0,
                    ]);
                }
            }
            $product->load(
                'mainCategory',
                'subCategory',
                'mainImage',
                'brand'
            );
            return $product;
        });
    }

    public function syncZonePrices($data, Product $product)
    {
        $existingZoneIds = $product->zonePrices()->pluck('zone_id')->toArray();
        $incomingZoneIds = collect($data['zones'])->pluck('zone_id')->toArray();
        $toDelete = array_diff($existingZoneIds, $incomingZoneIds);

        return DB::transaction(function () use ($product, $data, $toDelete) {


            $product->zonePrices()
                ->whereIn('zone_id', $toDelete)
                ->delete();

            foreach ($data['zones'] as $zone) {

                $product->zonePrices()->updateOrCreate(
                    ['zone_id' => $zone['zone_id']],
                    [
                        'price' => $zone['price'],
                        'is_available' => $zone['is_available'],
                    ]
                );
            }
            $product->load(
                'mainCategory',
                'subCategory',
                'mainImage',
                'brand'
            );
            return $product;
        });
    }
    public function show(Product $product)
    {
        $product->load([
            'images',
            'tags',
            'zonePrices.zone.currency',
            'mainCategory',
            'subCategory',
            'mainImage',
            'brand'
        ]);
        return $product;
    }

    public function delete(Product $product)
    {
        foreach ($product->images as $image) {
            $this->deleteImage($image->path);
        }

        $product->images()->delete();
        //////////// if product has orders --- later
        return $product->delete();
    }


    public function selectAvailable($mainCategory = null, $subCategory = null)
    {

        $products = Product::with(
            'mainCategory',
            'subCategory',
        )->when(!is_null($mainCategory), function ($query) use ($mainCategory) {
            $query->where('main_category_id', $mainCategory);
        })->when(!is_null($subCategory), function ($query) use ($subCategory) {
            $query->where('sub_category_id', $subCategory);
        })->orderBy('id')->where('active', true)->get([
            'id',
            'name',
            'main_category_id',
            'sub_category_id',

            'size',
            'country_of_origin',
            'active',
        ]);

        return $products;
    }

    public function productWarehouses($request, Product $product)
    {
        return ProductWarehouse::with([
            'warehouse'
        ])
            ->where('product_id', $product->id)

            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->paginate($request->input('per_page') ?? PaginationEnum::GeneralPagination->value);
    }


    public function applyAdjustment(array $data)
    {
        return DB::transaction(function () use ($data) {

            $products = isset($data['product_ids'])
                ? Product::with('zonePrices')->whereIn('id', $data['product_ids'])->get()
                : Product::with('zonePrices')->get();

            foreach ($products as $product) {

                $product->update([
                    'adjustment_type' => $data['type'],
                    'adjustment_value' => $data['value'],
                    'adjustment_operation' => $data['operation'],
                ]);

                foreach ($product->zonePrices as $zonePrice) {

                    $basePrice = $zonePrice->price;

                    if ($data['type'] === 'percentage') {
                        $amount = ($basePrice * $data['value']) / 100;
                    } else {
                        $amount = $data['value'];
                    }

                    $newPrice = $data['operation'] === 'increase'
                        ? $basePrice + $amount
                        : $basePrice - $amount;

                    $zonePrice->update([
                        'price_after_adjustment' => max(0, $newPrice)
                    ]);
                }
            }
        });
    }

    public function removeAdjustment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $productIds = $data['product_ids'];
            $products = $productIds
                ? Product::whereIn('id', $productIds)->get()
                : Product::all();

            foreach ($products as $product) {

                $product->update([
                    'adjustment_type' => null,
                    'adjustment_value' => null,
                    'adjustment_operation' => null,
                ]);

                foreach ($product->zonePrices as $zonePrice) {
                    $zonePrice->update([
                        'price_after_adjustment' => null
                    ]);
                }
            }
        });
    }
}
