<?php

namespace App\Http\Controllers\Web\V1\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\General\AddressRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\DashUser\AddressResource;
use App\Models\Address;
use App\Services\DashUser\AddressService;

class AddressController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_addresses', only: ['index']),
            new Middleware('permission:view_address_by_id', only: ['show']),
            new Middleware('permission:create_address', only: ['store']),
            new Middleware('permission:update_address', only: ['update']),
            new Middleware('permission:delete_address', only: ['destroy']),

        ];
    }

    public function __construct(private AddressService $addressService) {}

    public function index(Request $request)
    {
        $addresses = $this->addressService->list($request);
        return response()->format($this->returnPaginatedResponse($addresses, AddressResource::collection($addresses)), 'messages.success', 200);
    }

    public function store(AddressRequest $request)
    {
        $address = $this->addressService->create($request->validated());
        return response()->format(new AddressResource($address),  __('messages.created_successfully',  ['item' => __('constants.address')]), 201);
    }

    public function update(AddressRequest $request, Address $address)
    {
        $address = $this->addressService->update($address, $request->validated());
        return response()->format(new AddressResource($address),  __('messages.updated_successfully',  ['item' => __('constants.address')]), 200);
    }
    public function show(Address $address)
    {
        $address = $this->addressService->show($address);
        return response()->format(new AddressResource($address), 'messages.success', 200);
    }
    public function destroy(Address $address)
    {
        $returned = $this->addressService->delete($address);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.address')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.address')]), 200);
    }

    public function selectAvailable(Request $request)
    {
        $region = $request->input('region');
        $addresses = $this->addressService->selectAvailable($region);

        $returnedData = $addresses->map(fn($address) => [
            'key' => $address?->id,
            'value' => $address?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }

    public function marketerAddresses($marketerId)
    {

        $returned = $this->addressService->marketerAddresses($marketerId);
        return response()->format($returned, 'messages.success', 200);
    }
}
