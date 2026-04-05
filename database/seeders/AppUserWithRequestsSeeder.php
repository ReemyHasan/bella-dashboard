<?php

namespace Database\Seeders;

use App\Enums\DashUserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\AppUser;
use App\Models\AppUserRequest;
use App\Models\SubTeam;
use App\Models\Team;

class AppUserWithRequestsSeeder extends Seeder
{
    public function run(): void
    {

        $fillable = [
            'manager_id',
            'team_leader_id',

        ];

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

            Team::create($team);
        }

        foreach ($subTeams as $subTeam) {

            SubTeam::create($subTeam);
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


            ]
        ];

        foreach ($users as $userData) {

            $user = AppUser::create($userData);

            AppUserRequest::create([
                'app_user_id' => $user->id,
                'user_request_type_id' => 1,
                'content' => 'طلب سلفة مالية بقيمة 200000 ليرة.',
            ]);

            AppUserRequest::create([
                'app_user_id' => $user->id,
                'user_request_type_id' => 2,
                'content' => 'طلب إجازة لمدة ثلاثة أيام.',
                'read_at' => now()
            ]);

            AppUserRequest::create([
                'app_user_id' => $user->id,
                'user_request_type_id' => 3,
                'content' => 'طلب تعديل رقم الهاتف.',
                'read_at' => now(),
                'handled_at' => now(),
                'notes' => 'تم تعديل الرقم بنجاح'
            ]);
        }
    }
}
