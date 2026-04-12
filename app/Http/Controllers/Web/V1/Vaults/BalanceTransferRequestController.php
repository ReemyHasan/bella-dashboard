<?php

namespace App\Http\Controllers\Web\V1\Vaults;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashUser\BalanceTransferRequestResource;
use App\Models\BalanceTransferRequest;
use App\Models\CashRequest;
use App\Services\DashUser\BalanceTransferRequestService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BalanceTransferRequestController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_balance_transfer_requests', only: ['index']),
            new Middleware('permission:view_balance_transfer_request_by_id', only: ['show']),
            new Middleware('permission:handle_balance_transfer_request', only: ['handle']),
        ];
    }

    public function __construct(private BalanceTransferRequestService $balance_transfer_request_service) {}

    public function index(Request $request)
    {
        $requests = $this->balance_transfer_request_service->list($request);
        return response()->format($this->returnPaginatedResponse($requests, BalanceTransferRequestResource::collection($requests)), 'messages.success', 200);
    }

    public function show(BalanceTransferRequest $balance_transfer_request)
    {
        $balance_transfer_request = $this->balance_transfer_request_service->show($balance_transfer_request);
        return response()->format(new BalanceTransferRequestResource($balance_transfer_request), 'messages.success', 200);
    }

    public function handle(Request $request, BalanceTransferRequest $balance_transfer_request)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],

            'notes' => ['nullable', 'string']
        ]);

        $balance_transfer_request = $this->balance_transfer_request_service->handle(
            $balance_transfer_request,
            $validated
        );

        return response()->format(
            new BalanceTransferRequestResource($balance_transfer_request),
            __('messages.handled_successfully', ['item' => __('constants.balance_transfer_request')]),
            200
        );
    }
}
