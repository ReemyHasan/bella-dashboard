<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\AppUserRequest;
use App\Models\SubTeam;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppUserRequestService
{
    public function list($request)
    {
        return AppUserRequest::with('appUser', 'userRequestType')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function show(AppUserRequest $request)
    {
        $request->load('appUser', 'userRequestType', 'requestedBy', 'reviewedBy');

        return $request;
    }

    public function review(AppUserRequest $request)
    {
        if ($request->read_at) {
            throw new CustomException(__('messages.already_reviewed'));
        }

        $request->update([
            'read_at' => now(),
        ]);

        return $request;
    }
    public function handle(AppUserRequest $request, array $data)
    {
        // if ($request->handled_at) {
        //     throw new CustomException(__('messages.already_handled'));
        // }
        $status = $data['status'];

        return match ($status) {
            'approved' =>
            $this->approve(
                $request,
                $data['notes'] ?? null
            ),

            'rejected' =>
            $this->reject(
                $request,
                $data['notes'] ?? null
            ),

            default => throw new CustomException('يوجد مشكلة بالمعلومات المدخلة.')
        };
    }


    public function approve(AppUserRequest $request, ?string $notes = null)
    {
        if ($request->status !== 'pending') {
            throw new CustomException('لا يمكن الموافقة على الطلب, لقد تم معالجته بالفعل.');
        }

        return DB::transaction(function () use ($request, $notes) {

            $appUser = $request->appUser;
            $requester = $request->requestedBy;

            if ($request->user_request_type_id == 3) {
                $this->handlePromotion($appUser, $requester);
            }

            $readAt = $request->read_at ?? now();
            $request->update([
                'read_at' => $readAt,
                'handled_at' => now(),
                'notes' => $notes ?? null,
                'reviewed_by' => Auth::id(),
                'status' => 'approved',
            ]);
            $request->refresh();
            $request->load('appUser', 'userRequestType', 'requestedBy', 'reviewedBy');

            return $request;
        });
    }
    public function reject(AppUserRequest $request, ?string $notes = null)
    {
        if ($request->status !== 'pending') {
            throw new CustomException('لا يمكن رفض على الطلب, لقد تم معالجته بالفعل.');
        }

        return DB::transaction(function () use ($request, $notes) {

            $readAt = $request->read_at ?? now();
            $request->update([
                'read_at' => $readAt,
                'handled_at' => now(),
                'notes' => $notes ?? null,
                'reviewed_by' => Auth::id(),
                'status' => 'rejected',
            ]);
            $request->refresh();
            $request->load('appUser', 'userRequestType', 'requestedBy', 'reviewedBy');

            return $request;
        });
    }

    private function handlePromotion(AppUser $appUser, $requester)
    {
        if ($appUser->hasRole('Team Manager') || $appUser->hasRole('Team Leader')) {
            throw new CustomException('المستخدم بالفعل مدير لفريق رئيسي أو فرعي.');
        }

        if (!$requester->hasRole('Team Manager')) {
            throw new CustomException('منشئ الطلب ليس مدير لفريق رئيسي.');
        }

        $team = Team::where('manager_id', $requester->id)->first();

        if (!$team) {
            throw new CustomException('لا يوجد فريق مرتبط بالمدير.');
        }

        SubTeam::create([
            'team_id' => $team->id,
            'name' => "{$appUser->user_name} Team",
            'team_leader_id' => $appUser->id,
            'active' => true,
            'is_direct' => false,
        ]);
    }
}
