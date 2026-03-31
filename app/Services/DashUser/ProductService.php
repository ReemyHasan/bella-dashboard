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
            'zonePrices.zone.currency'
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
            $product->load(
                'mainCategory',
                'subCategory'
            );
            return $product;
        });
    }

    public function update(Product $product, array $data)
    {

        return DB::transaction(function () use ($product, $data) {
            $product->update($data);

            $product->tags()->sync($data['tags'] ?? []);

            $product->load(
                'mainCategory',
                'subCategory',
                'mainImage'
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
                'mainImage'
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
                'mainImage'
            );
            return $product;
        });
    }
    public function show(Product $product)
    {
        $product->load([
            'images',
            'tags',
            'zonePrices.zone',
            'mainCategory',
            'subCategory',
            'mainImage'
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
}
