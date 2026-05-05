<?php

namespace App\Services\Mobile;

use App\Enums\PaginationEnum;

use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\WarehouseReview;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WarehouseManReviewService
{
    public function list($request)
    {
        $user = auth()->user();

        if (!$user->is_warehouse_man) {
            throw new CustomException('فقط عامل المستودع يمكنه عرض التقييمات');
        }

        return WarehouseReview::with('reviewer')
            ->where('reviewed_user_id', $user->id)
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = auth()->user();

        return DB::transaction(function () use ($data, $user) {

            $reviewed = AppUser::findOrFail($data['reviewed_user_id']);


            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            $exists = WarehouseReview::where('reviewer_id', $user->id)
                ->where('reviewed_user_id', $reviewed->id)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->exists();

            if ($exists) {
                throw new CustomException('لا يمكنك تقييم هذا الموزع أكثر من مرة في الأسبوع');
            }

            return WarehouseReview::create([
                'reviewer_id' => $user->id,
                'reviewed_user_id' => $reviewed->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);
        });
    }
}
