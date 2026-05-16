<?php

namespace Database\Seeders;

use App\Enums\DashUserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\AppUser;
use App\Models\AppUserRequest;
use App\Models\SubTeam;
use App\Models\Team;
use App\Models\Warehouse;

class AppUserWithRequestsSeeder extends Seeder
{
    public function run(): void
    {

        $teams = [
            [
                'name' => 'Team 1',
                'active' => 1,
                'marketer_percentage' => 30,
                'team_leader_percentage' => 10,
                'manager_percentage' => 5,
                'direct_manager_percentage' => 10,
                // 'warehouse_man_percentage' => 0
            ],
            [
                'name' => 'Team 2',
                'active' => 1,
                'marketer_percentage' => 30,
                'team_leader_percentage' => 10,
                'manager_percentage' => 5,
                'direct_manager_percentage' => 10,
                // 'warehouse_man_percentage' => 0
            ],
            [
                'name' => 'Team 3',
                'active' => 1,
                'marketer_percentage' => 30,
                'team_leader_percentage' => 10,
                'manager_percentage' => 5,
                'direct_manager_percentage' => 10,
                // 'warehouse_man_percentage' => 0
            ]
        ];



        $subTeams = [
            [
                'name' => 'Sub Team 1',
                'active' => 1,
                'is_direct' => 1,
                'team_id' => 1,
            ],

            [
                'name' => 'Sub Team 2',
                'active' => 1,
                'is_direct' => 0,
                'team_id' => 1,
            ],
            [
                'name' => 'Sub Team 3',
                'active' => 1,
                'is_direct' => 0,
                'team_id' => 1,
            ],



            [
                'name' => 'Sub Team 1',
                'active' => 1,
                'is_direct' => 1,
                'team_id' => 2,
            ],

            [
                'name' => 'Sub Team 2',
                'active' => 1,
                'is_direct' => 0,
                'team_id' => 2,
            ],


            [
                'name' => 'Sub Team 1',
                'active' => 1,
                'is_direct' => 1,
                'team_id' => 3,
            ],

            [
                'name' => 'Sub Team 2',
                'active' => 1,
                'is_direct' => 0,
                'team_id' => 3,
            ],
        ];

        foreach ($teams as $team) {

            Team::updateOrCreate(["name" => $team['name']], $team);
        }

        foreach ($subTeams as $subTeam) {

            SubTeam::updateOrCreate(["name" => $subTeam['name']], $subTeam);
        }
        $users = [
            [
                'first_name' => 'أحمد',
                'last_name' => 'محمد',
                'user_name' => 'ahmad1',
                'mobile' => '0991111111',
                'password' => Hash::make('password'),
                'join_date' => now(),
                'status' => DashUserStatus::ACTIVE->value,
                'team_id' => 1,
                'subteam_id' => 1,

            ],

            [
                'first_name' => 'خالد',
                'last_name' => 'علي',
                'user_name' => 'khaled1',
                'mobile' => '0992222222',
                'password' => Hash::make('password'),
                'join_date' => now(),
                'status' => DashUserStatus::ACTIVE->value,
                'team_id' => 1,
                'subteam_id' => 1,

            ],

            [
                'first_name' => 'رامي',
                'last_name' => 'يوسف',
                'user_name' => 'rami1',
                'mobile' => '0993333333',
                'password' => Hash::make('password'),
                'join_date' => now(),
                'status' => DashUserStatus::ACTIVE->value,
                'team_id' => 1,
                'subteam_id' => 1,


            ],

            [
                'first_name' => 'محمد',
                'last_name' => 'علي',
                'user_name' => 'ali.mon',
                'mobile' => '0993333333',
                'password' => Hash::make('password'),
                'join_date' => now(),
                'status' => DashUserStatus::ACTIVE->value,
                'team_id' => 1,
                'subteam_id' => 1,


            ],
            ////////////////////////

            [
                'first_name' => 'أسامة',
                'last_name' => 'موزع',
                'user_name' => 'Osama.dist',
                'mobile' => '0993333365',
                'password' => Hash::make('password'),
                'join_date' => now(),
                'is_warehouse_man' => true,
                'status' => DashUserStatus::ACTIVE->value,
                'warehouse_id' => 1
            ],

            [
                'first_name' => 'عبد الرحمن',
                'last_name' => 'علي',
                'user_name' => 'abd.ali',
                'mobile' => '09933335433',
                'password' => Hash::make('password'),
                'join_date' => now(),
                'is_warehouse_man' => true,
                'status' => DashUserStatus::ACTIVE->value,
                'warehouse_id' => 2


            ],

            [
                'first_name' => 'أمجد',
                'last_name' => 'سمير',
                'user_name' => 'Amjad.Smeer',
                'mobile' => '09933338765',
                'password' => Hash::make('password'),
                'join_date' => now(),
                'is_warehouse_man' => true,
                'status' => DashUserStatus::ACTIVE->value,
                'warehouse_id' => 3


            ]
        ];

        foreach ($users as $userData) {

            $user = AppUser::updateOrCreate([
                "user_name" => $userData['user_name']
            ], $userData);

            AppUserRequest::create([
                'app_user_id' => $user->id,
                'user_request_type_id' => 1,
                'content' => 'طلب سلفة مالية بقيمة 200000 ليرة.',
                'requested_by_id' => 1,
                'requested_by_type' => AppUser::class,
            ]);

            AppUserRequest::create([
                'app_user_id' => $user->id,
                'user_request_type_id' => 2,
                'content' => 'طلب إجازة لمدة ثلاثة أيام.',
                'read_at' => now(),
                'requested_by_id' => 1,
                'requested_by_type' => AppUser::class,
            ]);

            AppUserRequest::create([
                'app_user_id' => $user->id,
                'user_request_type_id' => 3,
                'content' => 'طلب تعديل رقم الهاتف.',
                'read_at' => now(),
                'handled_at' => now(),
                'notes' => 'تم تعديل الرقم بنجاح',
                'requested_by_id' => 1,
                'requested_by_type' => AppUser::class,
            ]);
        }

        $teams = Team::limit(1)->get();

        foreach ($teams as $team) {

            $team->update([
                'manager_id' => 1
            ]);
            $manager = AppUser::findOrFail(1);

            $manager->assignRole('Team Manager');
        }

        $subTeams = SubTeam::limit(1)->get();

        foreach ($subTeams as $subTeam) {

            $subTeam->update([
                'team_leader_id' => 2
            ]);
            $manager = AppUser::findOrFail(2);

            $manager->assignRole('Team Leader');
        }

        $warehouses = Warehouse::orderBy('id')->limit(3)->get();
        $i = 0;
        foreach ($warehouses as $warehouse) {

            $warehouse->update([
                'keeper_id' => 5 + $i
            ]);

            $keeper = AppUser::findOrFail((5 + $i));

            $keeper->assignRole('Warehouse Keeper');

            $i++;
        }
    }
}
