<?php

namespace App\Observers;

use App\Enums\CompetitionStatus;
use App\Enums\OrderStatus;
use App\Models\CompetitionParticipant;
use App\Models\CustomerOrder;
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

        // 'teams' => $competition->teams->contains(function ($team) use ($order) {
        //     return $team->id === $order->team_id
        //         || $team->subteams->pluck('id')->contains($order->sub_team_id);
        // }),
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

        $this->increaseScore($competition, $order->app_user_id, $amount);
    }
    private function handleOrdersCount($order, $competition)
    {
        $this->increaseScore($competition, $order->app_user_id, 1);
    }

    private function handleProductSales($order, $competition)
    {
        $products = $competition->products->pluck('id');

        foreach ($order->products as $orderProduct) {
            if ($products->contains($orderProduct->product_id)) {
                $this->increaseScore(
                    $competition,
                    $order->app_user_id,
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
                    $order->app_user_id,
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
                $order->app_user_id,
                $orderProduct->quantity
            );
        }
    }

    private function increaseScore($competition, $userId, $value)
    {
        $participant = CompetitionParticipant::firstOrCreate(
            [
                'competition_id' => $competition->id,
                'user_id' => $userId,
            ],
            [
                'score' => 0
            ]
        );

        $participant->increment('score', $value);

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
            $participant->update([
                'is_winner' => true,
            ]);

            // event(new CompetitionWon($participant));
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
}
