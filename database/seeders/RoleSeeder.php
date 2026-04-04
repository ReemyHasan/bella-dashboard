<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::updateOrCreate([
            'name' => 'Admin',

        ], [
            'name' => 'Admin',
            'name_ar' => 'مدير',
            'guard_name' => 'dash_user_guard',
            'is_protected' => true
        ]);

        $permissions = Permission::where('guard_name', 'dash_user_guard')->pluck('id');

        $admin->permissions()->sync($permissions);


        $data_entry = Role::updateOrCreate([
            'name' => 'Data Entry',

        ], [
            'name_ar' => 'مدخل بيانات',
            'guard_name' => 'dash_user_guard',
            'is_protected' => true
        ]);

        $permissions2 = Permission::where('guard_name', 'dash_user_guard')->pluck('id');

        $data_entry->permissions()->sync($permissions2);


        $accountant = Role::updateOrCreate([
            'name' => 'Accountant',
        ], [
            'name' => 'Accountant',
            'name_ar' => 'محاسب',
            'guard_name' => 'dash_user_guard',
            'is_protected' => true
        ]);

        $warehouseKeeper = Role::updateOrCreate([
            'name' => 'Warehouse Keeper',

        ], [
            'name' => 'Warehouse Keeper',
            'name_ar' => 'أمين مستودع',
            'guard_name' => 'dash_user_guard',
            'is_protected' => true
        ]);

        $warehouseKeeper->permissions()->sync($permissions2);

        // $permissions3 = Permission::where('guard_name', 'dash_user_guard')->pluck('id');

        // $accountant->permissions()->sync($permissions3);



        $marketer = Role::updateOrCreate([
            'name' => 'Marketer',

        ], [
            'name' => 'Marketer',
            'name_ar' => 'مسوق',
            'guard_name' => 'app_user_guard',
            'is_protected' => true
        ]);

        $permissions4 = Permission::where('guard_name', 'app_user_guard')->pluck('id');

        $marketer->permissions()->sync($permissions4);

        $teamManager = Role::updateOrCreate([
            'name' => 'Team Manager',
        ], [
            'name' => 'Team Manager',
            'name_ar' => 'مدير فريق رئيسي',
            'guard_name' => 'app_user_guard',
            'is_protected' => true
        ]);
        $teamManager->permissions()->sync($permissions4);

        $teamLeader = Role::updateOrCreate([
            'name' => 'Team Leader',

        ], [
            'name' => 'Team Leader',
            'name_ar' => 'قائد فريق فرعي',
            'guard_name' => 'app_user_guard',
            'is_protected' => true
        ]);
        $teamLeader->permissions()->sync($permissions4);
    }
}
