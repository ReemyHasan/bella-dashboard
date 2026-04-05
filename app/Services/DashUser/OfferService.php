<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\Offer;
use App\Traits\HandlesImageUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfferService
{
    use HandlesImageUpload;

    public function list($request)
    {
        return Offer::with(
            'mainImage',
            'tags'
        )->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = Str::slug($data['name']);
            $offer = Offer::create($data);

            if (!empty($data['tags'])) {
                $offer->tags()->sync($data['tags']);
            }

            // Products
            if (!empty($data['products'])) {
                $offer->products()->sync(
                    $this->formatProducts($data['products'])
                );
            }

            // Warehouses
            if (!empty($data['warehouses'])) {
                $offer->warehouses()->sync(
                    $this->formatWarehouses($data['warehouses'])
                );
            }
            $offer->load(
                'tags'

            );
            return $offer;
        });
    }

    public function update(Offer $offer, array $data)
    {

        return DB::transaction(function () use ($offer, $data) {
            $offer->update($data);

            $offer->tags()->sync($data['tags'] ?? []);

            $offer->load(
                'mainImage',
                'tags'

            );

            // Products
            if (isset($data['products'])) {
                $offer->products()->sync(
                    $this->formatProducts($data['products'])
                );
            }

            // Warehouses
            if (isset($data['warehouses'])) {
                $offer->warehouses()->sync(
                    $this->formatWarehouses($data['warehouses'])
                );
            }
            return $offer;
        });
    }

    private function formatProducts(array $products): array
    {
        return collect($products)
            ->mapWithKeys(fn($item) => [
                $item['product_id'] => ['quantity' => $item['quantity']]
            ])
            ->toArray();
    }

    private function formatWarehouses(array $warehouses): array
    {
        return collect($warehouses)
            // ->mapWithKeys(fn($item) => [
            //     $item['warehouse_id'] => ['quantity' => $item['quantity']]
            // ])
            ->toArray();
    }
    public function syncImages($data, Offer $offer)
    {
        $existingIds = $offer->images()->pluck('id')->toArray();
        $incomingIds = collect($data['images'])
            ->pluck('id')
            ->filter()
            ->toArray();


        $toDelete = array_diff($existingIds, $incomingIds);
        return DB::transaction(function () use ($offer, $data, $toDelete) {

            $imagesToDelete = $offer->images()->whereIn('id', $toDelete)->get();
            foreach ($imagesToDelete as $image) {
                $this->deleteImage($image->path);
            }

            $offer->images()->whereIn('id', $toDelete)->delete();

            foreach ($data['images'] as $imageData) {

                if (!empty($imageData['id'])) {

                    $image = $offer->images()->find($imageData['id']);

                    $updateData = [
                        'is_main' => $imageData['is_main'],
                        'sort_order' => $imageData['sort_order'] ?? 0,
                    ];

                    if (!empty($imageData['file'])) {
                        $updateData['path'] = $this->uploadImage($imageData['file'], 'offers');
                    }

                    $image->update($updateData);
                } else {

                    $offer->images()->create([
                        'path' => $this->uploadImage($imageData['file'], 'offers'),
                        'is_main' => $imageData['is_main'],
                        'sort_order' => $imageData['sort_order'] ?? 0,
                    ]);
                }
            }
            $offer->load(
                'mainImage',
                'tags'

            );
            return $offer;
        });
    }

    public function syncZonePrices($data, Offer $offer)
    {
        $existingZoneIds = $offer->zonePrices()->pluck('zone_id')->toArray();
        $incomingZoneIds = collect($data['zones'])->pluck('zone_id')->toArray();
        $toDelete = array_diff($existingZoneIds, $incomingZoneIds);

        return DB::transaction(function () use ($offer, $data, $toDelete) {


            $offer->zonePrices()
                ->whereIn('zone_id', $toDelete)
                ->delete();

            foreach ($data['zones'] as $zone) {

                $offer->zonePrices()->updateOrCreate(
                    ['zone_id' => $zone['zone_id']],
                    [
                        'price' => $zone['price'],
                        'is_available' => $zone['is_available'],
                    ]
                );
            }
            $offer->load(
                'mainImage',
                'tags'
            );
            return $offer;
        });
    }
    public function show(Offer $offer)
    {
        $offer->load([
            'images',
            'tags',
            'zonePrices.zone.currency',
            'offerProducts.product.mainImage',
            'offerWarehouses.warehouse'
        ]);
        return $offer;
    }

    public function delete(Offer $offer)
    {
        foreach ($offer->images as $image) {
            $this->deleteImage($image->path);
        }

        $offer->images()->delete();
        //////////// if offer has orders --- later
        return $offer->delete();
    }
}
