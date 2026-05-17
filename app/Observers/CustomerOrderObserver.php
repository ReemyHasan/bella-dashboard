<?php

namespace App\Observers;

use App\Enums\CompetitionStatus;
use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\VaultTransactionType;
use App\Events\NotificationEvent;
use App\Models\AppUser;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\CustomerOrder;
use App\Models\VaultTransaction;
use Illuminate\Support\Facades\DB;

class CustomerOrderObserver
{
    /**
     * Handle the CustomerOrder "created" event.
     */
    public function created(CustomerOrder $order): void {}

    /**
     * Handle the CustomerOrder "updated" event.
     */
    public function updated(CustomerOrder $order): void
    {

        if (
            !$order->is_target ||
            !$order->competition_id ||
            !$order->wasChanged('order_status') ||
            $order->getOriginal('order_status') == OrderStatus::completed->value ||
            $order->order_status != OrderStatus::completed->value
        ) {
            return;
        }
        $order->load([
            'competition.teams.subteams',
            'competition.subteams',
            'competition.marketers',
            'competition.products',
            'competition.offers',
            'products',
            'offers'
        ]);
        $competition = $order->competition;
        if (!$competition) return;

        // 🔹 Check competition is active and within date
        if (!$this->isCompetitionActive($competition, $order)) {
            return;
        }

        // 🔹 Check marketer eligibility
        if (!$this->isMarketerEligible($order, $competition)) {
            return;
        }


        // 🔹 Handle tracking based on type
        $this->handleCompetitionTracking($order, $competition);
    }

    /**
     * Handle the CustomerOrder "deleted" event.
     */
    public function deleted(CustomerOrder $customerOrder): void
    {
        //
    }

    private function isCompetitionActive($competition, $order): bool
    {
        return $competition->status == CompetitionStatus::active->value
            && $order->created_at->between($competition->start_at, $competition->end_at);
    }

    private function isMarketerEligible(CustomerOrder $order, $competition): bool
    {
        $marketerId = $order->app_user_id;
        return match ($competition->target) {

            'all' => true,

            'teams' => $competition->teams
                ->pluck('id')
                ->contains($order->team_id),

            'subteams' => $competition->subteams
                ->pluck('id')
                ->contains($order->sub_team_id),

            'marketers' => $competition->marketers
                ->pluck('id')
                ->contains($marketerId),

            default => false,
        };
    }

    private function handleCompetitionTracking(CustomerOrder $order, $competition)
    {
        match ($competition->type) {

            'financial_amount' => $this->handleFinancial($order, $competition),

            'orders_count' => $this->handleOrdersCount($order, $competition),

            'product_sales' => $this->handleProductSales($order, $competition),

            'offer_sales' => $this->handleOfferSales($order, $competition),

            'general_product_sales' => $this->handleGeneralProductSales($order, $competition),

            default => null,
        };
    }
    private function handleFinancial($order, $competition)
    {
        $amount = $order->final_total_price;

        $this->increaseScore($competition, $order, $amount);
    }
    private function handleOrdersCount($order, $competition)
    {
        $this->increaseScore($competition, $order, 1);
    }

    private function handleProductSales($order, $competition)
    {
        $products = $competition->products->pluck('id');

        foreach ($order->products as $orderProduct) {
            if ($products->contains($orderProduct->product_id)) {
                $this->increaseScore(
                    $competition,
                    $order,
                    $orderProduct->quantity
                );
            }
        }
    }

    private function handleOfferSales($order, $competition)
    {
        $offers = $competition->offers->pluck('id');

        foreach ($order->offers as $orderOffer) {
            if ($offers->contains($orderOffer->offer_id)) {
                $this->increaseScore(
                    $competition,
                    $order,
                    $orderOffer->quantity
                );
            }
        }
    }

    private function handleGeneralProductSales($order, $competition)
    {
        foreach ($order->products as $orderProduct) {
            $this->increaseScore(
                $competition,
                $order,
                $orderProduct->quantity
            );
        }
    }

    private function increaseScore($competition, $order, $value)
    {
        [$participantId, $participantType] = $this->resolveParticipant($competition, $order);

        if (!$participantId || !$participantType) {
            return;
        }
        $participant = CompetitionParticipant::firstOrCreate(
            [
                'competition_id' => $competition->id,
                'participant_id' => $participantId,
                'participant_type' => $participantType,
            ],
            [
                'score' => 0,
                'progress' => 0
            ]
        );

        $participant->increment('score', $value);
        $participant->progress = $this->calculateProgress($participant->score, $competition);
        $participant->save();

        $participant->refresh();

        $this->checkIfWinner($participant, $competition);
    }

    private function checkIfWinner($participant, $competition): void
    {
        if ($participant->is_winner) {
            return;
        }

        $isWinner = match ($competition->type) {

            // 🔹 Simple target types
            'financial_amount',
            'orders_count',
            'general_product_sales'
            => $participant->score >= $competition->target_value,

            // 🔹 Advanced types
            'product_sales'
            => $this->checkProductTarget($participant, $competition),

            'offer_sales'
            => $this->checkOfferTarget($participant, $competition),

            default => false,
        };

        if ($isWinner) {
            DB::transaction(function () use ($participant, $competition) {

                $participant->update([
                    'is_winner' => true,
                ]);

                $this->rewardWinner($participant, $competition);
            });


            event(new NotificationEvent(
                type: NotificationType::COMPETITION_GOAL_ACHIEVEMENT,
                data: [
                    'participant' => $participant->fresh(['participant']),
                    'competition' => $competition,
                ]
            ));
        }
    }
    private function checkProductTarget($participant, $competition): bool
    {
        $required = $competition->products->sum('pivot.target_quantity');

        return $participant->score >= $required;
    }
    private function checkOfferTarget($participant, $competition): bool
    {
        $required = $competition->offers->sum('pivot.target_quantity');

        return $participant->score >= $required;
    }

    private function resolveParticipant($competition, $order): array
    {
        return match ($competition->target) {

            // 🔹 Individual competitions
            'all',
            'marketers'
            => [$order->app_user_id, \App\Models\AppUser::class],

            // 🔹 Team competitions
            'teams'
            => [$order->team_id, \App\Models\Team::class],

            // 🔹 SubTeam competitions
            'subteams'
            => [$order->sub_team_id, \App\Models\SubTeam::class],

            default => [null, null],
        };
    }
    private function calculateProgress($score, $competition): float
    {
        $target = match ($competition->type) {
            'product_sales'
            => $competition->products->sum('pivot.target_quantity'),
            'offer_sales'
            => $competition->offers->sum('pivot.target_quantity'),
            default
            => $competition->target_value,
        };

        if (!$target || $target == 0) {
            return 0;
        }

        return min(100, ($score / $target) * 100);
    }
    private function rewardWinner($participant, $competition): void
    {
        $receiver = $this->resolveRewardReceiver($participant, $competition);

        if (!$receiver) {
            return;
        }
        DB::transaction(function () use ($receiver, $competition) {

            $fromBefore = $receiver->balance;
            $receiver->increment('balance', $competition->prize);
            $receiver->refresh();
            $fromAfter = $receiver->balance;


            if ($competition->co_created_by_id != null) {
                $coCreatedBy = $competition->coCreatedBy;
                $coCreatedByToBefore = $coCreatedBy->balance;
                $coCreatedBy->decrement('balance', $competition->prize);
                $coCreatedBy->refresh();

                $coCreatedByToAfter = $coCreatedBy->balance;

                VaultTransaction::create([
                    'balance_user_type' => AppUser::class,
                    'balance_user_id' => $coCreatedBy->id,
                    'type' => VaultTransactionType::SOURCE_COMPETITION_PRIZE_DEDUCT->value,
                    'amount' => $competition->prize,
                    'transaction_date' => now(),

                    'reference_type' => Competition::class,
                    'reference_id' => $competition->id,

                    'from_vault_balance_before' => $coCreatedByToBefore,
                    'from_vault_balance_after' => $coCreatedByToAfter,

                    'action_by_type' => null,
                    'action_by_id' => null,
                    'reason' => 'خصم جائزة المسابقة المالية من المدير المنشئ.',
                    'notes' => 'تمت عملية النقل بشكل تلقائي من النظام'
                ]);
            }

            VaultTransaction::create([
                'balance_user_type' => AppUser::class,
                'balance_user_id' => $receiver->id,
                'type' => VaultTransactionType::COMPETITION_PRIZE->value,
                'amount' => $competition->prize,
                'transaction_date' => now(),

                'reference_type' => Competition::class,
                'reference_id' => $competition->id,

                'to_vault_balance_before' => $fromBefore,
                'to_vault_balance_after' => $fromAfter,

                'action_by_type' => null,
                'action_by_id' => null,
                'reason' => 'جائزة المسابقة المالية',
                'notes' => 'تمت عملية النقل بشكل تلقائي من النظام'
            ]);
        });
    }
    private function resolveRewardReceiver($participant, $competition)
    {
        return match ($competition->target) {

            'all',
            'marketers'
            => $participant->participant,

            'teams'
            => optional($participant->participant)->manager,

            'subteams'
            => optional($participant->participant)->teamLeader,

            default => null,
        };
    }
}
