<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            DashUserSeeder::class,

            CurrencySeeder::class,
            ZoneSeeder::class,
            CitySeeder::class,
            TagSeeder::class,
            MainCategorySeeder::class,
            SubCategorySeeder::class,
            WarehouseSeeder::class,
            RegionSeeder::class,
            VaultSeeder::class,
            SettingSeeder::class,

            ///////////////To Remove
            AppUserWithRequestsSeeder::class,
            CustomerAddressSeeder::class,
            ProductOfferSeeder::class,
            BalanceTransferRequestSeeder::class

        ]);
    }
}
