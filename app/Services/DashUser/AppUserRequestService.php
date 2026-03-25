<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUserRequest;

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
        $request->load('appUser', 'userRequestType');
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
    public function handle(AppUserRequest $request,  $notes = null)
    {

        if ($request->handled_at) {
            throw new CustomException(__('messages.already_handled'));
        }

        $readAt = $request->read_at ?? now();
        $request->update([
            'read_at' => $readAt,
            'handled_at' => now(),
            'notes' => $notes ?? null,
        ]);

        return $request;
    }
}
