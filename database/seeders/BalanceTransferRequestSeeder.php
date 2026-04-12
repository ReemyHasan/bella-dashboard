<?php

namespace Database\Seeders;

use App\Models\AppUser;
use App\Models\BalanceTransferRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BalanceTransferRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            $users = AppUser::inRandomOrder()->take(4)->get();

            if ($users->count() < 2) {
                return;
            }

            foreach (range(1, 10) as $i) {

                $from = $users->random();
                $to = $users->where('id', '!=', $from->id)->random();

                BalanceTransferRequest::create([
                    'from_user_id' => $from->id,
                    'to_user_id' => $to->id,
                    'amount' => rand(10, 500),

                    'status' => 'pending',

                    'notes' => 'طلب تحويل تجريبي #' . $i,

                    'review_notes' => null,

                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]);
            }
        });
    }
}
