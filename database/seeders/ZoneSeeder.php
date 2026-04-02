<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Tip;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zone1 = Zone::create([
            'currency_id' => 1,
            'name' => 'سوريا [مدن]',
            'symbol' => 'SYP',
            // 'marketer_percentage' => 30,
            // 'team_leader_percentage' => 10,
            // 'manager_percentage' => 5,
            // 'direct_manager_percentage' => 10,
            // 'delivery_man_percentage' => 0,
            // 'warehouse_man_percentage' => 0,
            // 'delivery_cost' => 20000,
        ]);
        Tip::insert(
            [
                [
                    'zone_id' =>  $zone1->id,
                    'amount' => 5000
                ],

                [
                    'zone_id' =>  $zone1->id,
                    'amount' => 10000
                ],
                [
                    'zone_id' =>  $zone1->id,
                    'amount' => 15000
                ],
                [
                    'zone_id' =>  $zone1->id,
                    'amount' => 20000
                ],
                [
                    'zone_id' =>  $zone1->id,
                    'amount' => 25000
                ],
                [
                    'zone_id' =>  $zone1->id,
                    'amount' => 30000
                ]
            ]
        );
        ////////////////////

        $zone2 = Zone::create([
            'currency_id' => 1,
            'name' => 'سوريا [ارياف]',
            'symbol' => 'SR/CO',
            // 'marketer_percentage' => 30,
            // 'team_leader_percentage' => 10,
            // 'manager_percentage' => 5,
            // 'direct_manager_percentage' => 10,
            // 'delivery_man_percentage' => 0,
            // 'warehouse_man_percentage' => 0,
            // 'delivery_cost' => 20000,
        ]);
        Tip::insert(
            [
                [
                    'zone_id' =>  $zone2->id,
                    'amount' => 5000
                ],

                [
                    'zone_id' =>  $zone2->id,
                    'amount' => 10000
                ],
                [
                    'zone_id' =>  $zone2->id,
                    'amount' => 15000
                ],
                [
                    'zone_id' =>  $zone2->id,
                    'amount' => 20000
                ],
                [
                    'zone_id' =>  $zone2->id,
                    'amount' => 25000
                ],
                [
                    'zone_id' =>  $zone2->id,
                    'amount' => 30000
                ]
            ]
        );

        ////////////////////


        $zone3 = Zone::create([
            'currency_id' => 2,
            'name' => 'ادلب [المدينة]',
            'symbol' => 'ED',
            // 'marketer_percentage' => 30,
            // 'team_leader_percentage' => 10,
            // 'manager_percentage' => 5,
            // 'direct_manager_percentage' => 10,
            // 'delivery_man_percentage' => 0,
            // 'warehouse_man_percentage' => 0,
            // 'delivery_cost' => 3,
        ]);
        Tip::insert(
            [
                [
                    'zone_id' =>  $zone3->id,
                    'amount' => 1
                ],

                [
                    'zone_id' =>  $zone3->id,
                    'amount' => 2
                ],
                [
                    'zone_id' =>  $zone3->id,
                    'amount' => 3
                ],
                [
                    'zone_id' =>  $zone3->id,
                    'amount' => 4
                ],
                [
                    'zone_id' =>  $zone3->id,
                    'amount' => 5
                ]
            ]
        );

        ////////////////////


        $zone4 = Zone::create([
            'currency_id' => 2,
            'name' => 'ادلب [الريف]',
            'symbol' => 'ED/CO',
            // 'marketer_percentage' => 30,
            // 'team_leader_percentage' => 10,
            // 'manager_percentage' => 5,
            // 'direct_manager_percentage' => 10,
            // 'delivery_man_percentage' => 0,
            // 'warehouse_man_percentage' => 0,
            // 'delivery_cost' => 4,
        ]);
        Tip::insert(
            [
                [
                    'zone_id' =>  $zone4->id,
                    'amount' => 1
                ],

                [
                    'zone_id' =>  $zone4->id,
                    'amount' => 2
                ],
                [
                    'zone_id' =>  $zone4->id,
                    'amount' => 3
                ],
                [
                    'zone_id' =>  $zone4->id,
                    'amount' => 4
                ],
                [
                    'zone_id' =>  $zone4->id,
                    'amount' => 5
                ]
            ]
        );


        ////////////////////


        $zone5 = Zone::create([
            'currency_id' => 2,
            'name' => 'لبنان',
            'symbol' => 'LB',
            // 'marketer_percentage' => 30,
            // 'team_leader_percentage' => 10,
            // 'manager_percentage' => 5,
            // 'direct_manager_percentage' => 10,
            // 'delivery_man_percentage' => 0,
            // 'warehouse_man_percentage' => 0,
            // 'delivery_cost' => 6,
        ]);
        Tip::insert(
            [
                [
                    'zone_id' =>  $zone5->id,
                    'amount' => 1
                ],

                [
                    'zone_id' =>  $zone5->id,
                    'amount' => 2
                ],
                [
                    'zone_id' =>  $zone5->id,
                    'amount' => 3
                ],
                [
                    'zone_id' =>  $zone5->id,
                    'amount' => 4
                ],
                [
                    'zone_id' =>  $zone5->id,
                    'amount' => 5
                ]
            ]
        );


        ////////////////////


        $zone6 = Zone::create([
            'currency_id' => 4,
            'name' => 'الأردن',
            'symbol' => 'JOR',
            // 'marketer_percentage' => 37,
            // 'team_leader_percentage' => 10,
            // 'manager_percentage' => 8,
            // 'direct_manager_percentage' => 18,
            // 'delivery_man_percentage' => 0,
            // 'warehouse_man_percentage' => 0,
            // 'delivery_cost' => 4,
        ]);
        Tip::insert(
            [
                [
                    'zone_id' =>  $zone6->id,
                    'amount' => 1
                ],

                [
                    'zone_id' =>  $zone6->id,
                    'amount' => 2
                ],
                [
                    'zone_id' =>  $zone6->id,
                    'amount' => 3
                ],
                [
                    'zone_id' =>  $zone6->id,
                    'amount' => 4
                ],
                [
                    'zone_id' =>  $zone6->id,
                    'amount' => 5
                ]
            ]
        );
    }
}
