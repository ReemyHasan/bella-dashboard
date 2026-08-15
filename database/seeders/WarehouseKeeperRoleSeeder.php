<?php

namespace Database\Seeders;

use App\Models\AppUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseKeeperRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AppUser::query()
            ->where('is_warehouse_man', true)
            ->chunkById(100, function ($keepers) {
                foreach ($keepers as $keeper) {
                    $keeper->roles()->sync([]);
                    $keeper->permissions()->sync([]);

                    $keeper->assignRole('Warehouse Keeper');
                }
            });
    }
}
