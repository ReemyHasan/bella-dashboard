<?php

namespace App\Services\DashUser\Orders;

use App\Enums\CompetitionStatus;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\Competition;
use Illuminate\Support\Facades\DB;

class CompetitionService
{
    public function list($request)
    {
        return Competition::with([
            'createdBy',
            'coCreatedBy',
            'zones',
        ])->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $CompData = [
                'co_created_by_type' => $data["co_created_by_id"] ? AppUser::class : null,
                'created_by_id' => auth()->id(),
                'created_by_type' => get_class(auth()->user()),
                'status' => CompetitionStatus::draft->value
            ];
            $competition = Competition::create(array_merge($CompData, $data));

            $competition->zones()->sync($data['zones']);

            if ($data['target'] === 'teams') {
                $competition->teams()->sync($data['teams']);
            }

            if ($data['target'] === 'subteams') {
                $competition->subteams()->sync($data['subteams']);
            }

            if ($data['target'] === 'marketers') {
                $competition->marketers()->sync($data['marketers']);
            }

            if ($data['type'] === 'product_sales') {
                $competition->products()->sync(
                    collect($data['products'])->mapWithKeys(fn($item) => [
                        $item['product_id'] => ['target_quantity' => $item['target_quantity']]
                    ])
                );
            }

            if ($data['type'] === 'offer_sales') {
                $competition->offers()->sync(
                    collect($data['offers'])->mapWithKeys(fn($item) => [
                        $item['offer_id'] => ['target_quantity' => $item['target_quantity']]
                    ])
                );
            }

            $competition->load([
                'createdBy',
                'coCreatedBy',
                'zones',
            ]);

            return $competition;
        });
    }

    public function update(Competition $competition, array $data)
    {
        return DB::transaction(function () use ($competition, $data) {

            $competition->update($data);

            $competition->zones()->sync($data['zones']);

            $competition->teams()->sync([]);
            $competition->subteams()->sync([]);
            $competition->marketers()->sync([]);

            if ($data['target'] === 'teams') {
                $competition->teams()->sync($data['teams'] ?? []);
            }

            if ($data['target'] === 'subteams') {
                $competition->subteams()->sync($data['subteams'] ?? []);
            }

            if ($data['target'] === 'marketers') {
                $competition->marketers()->sync($data['marketers'] ?? []);
            }

            if ($data['type'] === 'product_sales') {
                $competition->products()->sync(
                    collect($data['products'])->mapWithKeys(fn($item) => [
                        $item['product_id'] => ['target_quantity' => $item['target_quantity']]
                    ])
                );
            }

            if ($data['type'] === 'offer_sales') {
                $competition->offers()->sync(
                    collect($data['offers'])->mapWithKeys(fn($item) => [
                        $item['offer_id'] => ['target_quantity' => $item['target_quantity']]
                    ])
                );
            }

            $competition->load([
                'createdBy',
                'coCreatedBy',
                'zones',
            ]);

            return $competition;
        });
    }
    public function show(Competition $competition)
    {
        $competition->load(
            [
                'createdBy',
                'coCreatedBy',
                'zones',
                'teams',
                'subteams',
                'marketers',
                'products',
                'offers',
                'winners.winner'
            ]
        );
        return $competition;
    }

    public function delete(Competition $competition)
    {

        if (in_array($competition->status, [
            CompetitionStatus::active->value,
            CompetitionStatus::ended->value
        ])) {
            throw new CustomException('لا يمكنك حذف المسابقة, أنها حاليا منتهية او نشطة.');
        }

        return $competition->delete();
    }


    public function selectAvailable($status = null)
    {

        $competitions = Competition::when(!is_null($status), function ($query) use ($status) {
            $query->where('status', $status);
        })->orderBy('id')->get([
            'id',
            'name',
            'status'
        ]);

        return $competitions;
    }


    public function activate(Competition $competition)
    {


        if (in_array($competition->status, [
            CompetitionStatus::active->value,
            CompetitionStatus::ended->value
        ])) {
            throw new CustomException('لا يمكنك تفعيل المسابقة, أنها حاليا منتهية او نشطة.');
        }
        $competition->update([
            'status' => CompetitionStatus::active->value,
        ]);
        return $competition;
    }
}
