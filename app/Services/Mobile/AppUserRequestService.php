<?php

namespace App\Services\Mobile;

use App\Enums\PaginationEnum;
use App\Enums\AppUserRequestStatus;

use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\AppUserRequest;
use Illuminate\Support\Facades\DB;

class AppUserRequestService
{
    public function list($request)
    {
        $user = auth()->user();
        return AppUserRequest::visibleTo(auth()->user())->with('appUser', 'userRequestType')
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function show($id)
    {
        return AppUserRequest::visibleTo(auth()->user())->with('appUser', 'userRequestType', 'requestedBy', 'reviewedBy')->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $appRequest = AppUserRequest::create([

                'app_user_id' => isset($data['app_user_id']) ? $data['app_user_id'] : auth()->user()->id,
                'user_request_type_id' => $data['user_request_type_id'],
                'content' => $data['content'],
                'status' => AppUserRequestStatus::pending->value,
                'requested_by_id' => auth()->user()->id,
                'requested_by_type' => AppUser::class,
            ]);

            return $appRequest;
        });
    }
}
