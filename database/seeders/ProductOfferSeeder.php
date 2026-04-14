<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\OfferWarehouse;
use App\Models\OfferZonePrice;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\ProductZonePrice;
use App\Models\Warehouse;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Shampoo', 'slug' => 'shampoo'],
            ['name' => 'Soap', 'slug' => 'soap'],
            ['name' => 'Toothpaste', 'slug' => 'toothpaste'],
        ];

        foreach ($products as $product) {
            Product::create([
                'main_category_id' => 1,
                'sub_category_id' => 1,
                'name' => $product['name'],
                'slug' => $product['slug'],
                'size' => '250ml',
                'description' => 'Sample description',
                'how_to_use' => 'Use daily',
                'precautions' => 'Keep away from eyes',
                'country_of_origin' => 'Germany',
                'active' => true,
            ]);
        }



        // =========================
        // ✅ OFFERS
        // =========================
        $offers = [
            ['name' => 'Shampoo Offer', 'symbol' => 'SH-OFF'],
            ['name' => 'Hygiene Bundle', 'symbol' => 'HY-BUN'],
        ];

        foreach ($offers as $offer) {
            $offerItem = Offer::create([
                'name' => $offer['name'],
                'symbol' => $offer['symbol'],
                'description' => 'Offer description',
                'summary' => 'Short summary',
                'marketing_description' => 'Best deal',
                'active' => true,
            ]);
            $offerItem->products()->sync(
                [
                    [
                        'product_id' => 1,
                        'quantity' => 1
                    ],
                    [
                        'product_id' => 2,
                        'quantity' => 2
                    ]
                ]
            );
        }

        $zones = Zone::all();
        $products = Product::all();
        $offers = Offer::all();

        foreach ($zones as $zone) {

            foreach ($products as $product) {
                ProductZonePrice::create([
                    'product_id' => $product->id,
                    'zone_id' => $zone->id,
                    'price' => rand(10, 100),
                    'is_available' => true,
                ]);
            }

            foreach ($offers as $offer) {
                OfferZonePrice::create([
                    'offer_id' => $offer->id,
                    'zone_id' => $zone->id,
                    'price' => rand(50, 200),
                    'is_available' => true,
                ]);
            }
        }


        $warehouses = Warehouse::all();

        foreach ($warehouses as $warehouse) {
            foreach ($products as $product) {
                ProductWarehouse::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'quantity' => rand(20, 100),
                    'reserved_quantity' => 0,
                ]);
            }
            foreach ($offers as $offer) {
                OfferWarehouse::create([
                    'warehouse_id' => $warehouse->id,
                    'offer_id' => $offer->id,

                ]);
            }
        }
    }
}
