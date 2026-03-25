<?php

namespace App\Http\Controllers\Web\V1\Vaults;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Vaults\CashRequestRequest;
use App\Http\Resources\DashUser\CashRequestResource;
use App\Models\CashRequest;
use App\Services\DashUser\CashRequestService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CashRequestController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_cash_requests', only: ['index']),
            new Middleware('permission:view_cash_request_by_id', only: ['show']),
            new Middleware('permission:create_cash_request', only: ['store']),
            new Middleware('permission:update_cash_request', only: ['update']),
            new Middleware('permission:delete_cash_request', only: ['destroy']),
            new Middleware('permission:handle_cash_request', only: ['handle']),
        ];
    }

    public function __construct(private CashRequestService $cashRequestService) {}

    public function index(Request $request)
    {
        $requests = $this->cashRequestService->list($request);
        return response()->format($this->returnPaginatedResponse($requests, CashRequestResource::collection($requests)), 'messages.success', 200);
    }

    public function store(CashRequestRequest $request)
    {
        $cash_request = $this->cashRequestService->create($request->validated());
        return response()->format(new CashRequestResource($cash_request),  __('messages.created_successfully',  ['item' => __('constants.cash_request')]), 201);
    }

    public function update(CashRequestRequest $request, CashRequest $cash_request)
    {
        $cash_request = $this->cashRequestService->update($cash_request, $request->validated());
        return response()->format(new CashRequestResource($cash_request),  __('messages.updated_successfully',  ['item' => __('constants.cash_request')]), 200);
    }
    public function show(CashRequest $cash_request)
    {
        $cash_request = $this->cashRequestService->show($cash_request);
        return response()->format(new CashRequestResource($cash_request), 'messages.success', 200);
    }
    public function destroy(CashRequest $cash_request)
    {
        $returned = $this->cashRequestService->delete($cash_request);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.cash_request')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.cash_request')]), 200);
    }

    public function handle(Request $request, CashRequest $cash_request)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,in_transit,delivered,not_delivered,waiting_delivery_approve,completed'],

            'approved_amount' => ['required_if:status,approved', 'numeric', 'min:1'],

            'delivered_by' => ['nullable', 'exists:app_users,id'],

            'notes' => ['nullable', 'string']
        ]);

        $cash_request = $this->cashRequestService->handle(
            $cash_request,
            $validated
        );

        return response()->format(
            new CashRequestResource($cash_request),
            __('messages.handled_successfully', ['item' => __('constants.cash_request')]),
            200
        );
    }
}
