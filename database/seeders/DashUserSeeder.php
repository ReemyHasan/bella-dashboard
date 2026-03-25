<?php

namespace Database\Seeders;

use App\Models\DashUser;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DashUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Admin = Role::with('permissions')->find(1);
        $DataEntry = Role::with('permissions')->find(2);


        $user = DashUser::create([
            'first_name' => 'Marah',
            'last_name' => 'Mansour',
            'user_name' => 'Mhran',
            'birth_date' => '1998-01-16',
            'mobile' => '0935536380',
            'profile_link' => '',
            'password' => bcrypt('password123'),
        ]);

        $user->assignRole('Admin');
        $user->permissions()->sync(
            $Admin->permissions->pluck('id')->toArray()
        );


        $user2 = DashUser::create([
            'first_name' => 'Ali',
            'last_name' => 'Abbas',
            'user_name' => 'SARH15161718',
            'birth_date' => '1998-01-16',
            'mobile' => '0945098162',
            'profile_link' => '',
            'password' => bcrypt('password123'),
        ]);

        $user2->assignRole('Admin');
        $user2->permissions()->sync(
            $Admin->permissions->pluck('id')->toArray()
        );


        $user3 = DashUser::create([
            'first_name' => 'ميس',
            'last_name' => 'محمود',
            'user_name' => 'M.MHMOD',
            'birth_date' => '1992-05-05',
            'mobile' => '0930416057',
            'profile_link' => '',
            'password' => bcrypt('password123'),
        ]);

        $user3->assignRole('Data Entry');
        $user3->permissions()->sync(
            $DataEntry->permissions->pluck('id')->toArray()
        );


        $user4 = DashUser::create([
            'first_name' => 'لورين',
            'last_name' => 'البدور',
            'user_name' => 'M.LOREEN',
            'birth_date' => '1999-01-02',
            'mobile' => '0995785268',
            'profile_link' => '',
            'password' => bcrypt('password123'),
        ]);

        $user4->assignRole('Data Entry');
        $user4->permissions()->sync(
            $DataEntry->permissions->pluck('id')->toArray()
        );

        $user5 = DashUser::create([
            'first_name' => 'MHMOD',
            'last_name' => 'ALISS',
            'user_name' => 'M.MHMOD11',
            'birth_date' => '1992-05-05',
            'mobile' => '0991547495',
            'profile_link' => '',
            'password' => bcrypt('password123'),
        ]);

        $user5->assignRole('Data Entry');
        $user5->permissions()->sync(
            $DataEntry->permissions->pluck('id')->toArray()
        );

        $user6 = DashUser::create([
            'first_name' => 'wajd',
            'last_name' => 'alnmr',
            'user_name' => 'AL.ALAA',
            'birth_date' => '1995-05-05',
            'mobile' => '0991061806',
            'profile_link' => '',
            'password' => bcrypt('password123'),
        ]);

        $user6->assignRole('Admin');
        $user6->assignRole('Data Entry');
        $user6->permissions()->sync(
            $Admin->permissions->pluck('id')->toArray()
        );


        $user7 = DashUser::create([
            'first_name' => 'ادارة',
            'last_name' => 'الطلبات',
            'user_name' => 'm.miass',
            'birth_date' => '1980-05-05',
            'mobile' => '0934204665',
            'profile_link' => '',
            'password' => bcrypt('password123'),
        ]);

        $user7->assignRole('Admin');

        $user7->permissions()->sync(
            $Admin->permissions->pluck('id')->toArray()
        );
        $user8 = DashUser::create([
            'first_name' => 'Shahd',
            'last_name' => 'Shahd',
            'user_name' => 'Hh63694oodh',
            'birth_date' => '1978-05-05',
            'mobile' => '0966301324',
            'profile_link' => '',
            'password' => bcrypt('password123'),
        ]);

        $user8->assignRole('Data Entry');
        $user8->permissions()->sync(
            $DataEntry->permissions->pluck('id')->toArray()
        );

        $user9 = DashUser::create([
            'first_name' => 'alaa',
            'last_name' => 'alaa',
            'user_name' => '1559alaa',
            'birth_date' => '1998-05-05',
            'mobile' => '0932863452',
            'profile_link' => '',
            'password' => bcrypt('password123'),
        ]);

        $user9->assignRole('Data Entry');

        $user9->permissions()->sync(
            $DataEntry->permissions->pluck('id')->toArray()
        );
    }
}
