<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\UserRequestType;
use Illuminate\Support\Facades\DB;

class UserRequestTypeService
{
    public function list($request)
    {
        return UserRequestType::filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $userRequestType = UserRequestType::create([
                'name' => $data['name']
            ]);
            return $userRequestType;
        });
    }

    public function update(UserRequestType $userRequestType, array $data)
    {
        return DB::transaction(function () use ($userRequestType, $data) {
            $userRequestType->update([
                'name' => $data['name']
            ]);

            return $userRequestType;
        });
    }
    public function show(UserRequestType $userRequestType)
    {
        return $userRequestType;
    }

    public function delete(UserRequestType $userRequestType)
    {
        if ($userRequestType->requests()->exists())
            throw new CustomException("هذا النوع مستخدم في طلبات موجودة, يتعذر حذفه.");

        return $userRequestType->delete();
    }


    public function selectAvailable()
    {

        $userRequestTypes = UserRequestType::orderBy('id')->get([
            'id',
            'name'
        ]);

        return $userRequestTypes;
    }
}
