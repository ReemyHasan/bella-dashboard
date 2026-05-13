<?php

namespace App\Http\Controllers\Mobile\V1\AppUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\AppUser\CashRequestRequest;
use App\Http\Resources\Mobile\CashRequestResource;
use App\Models\CashRequest;
use Illuminate\Http\Request;
use App\Services\Mobile\CashRequestService;

class CashRequestController extends Controller
{

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

    public function show(CashRequest $cash_request)
    {
        $cash_request = $this->cashRequestService->show($cash_request);
        return response()->format(new CashRequestResource($cash_request), 'messages.success', 200);
    }

    public function handle(Request $request, CashRequest $cash_request)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:in_transit,delivered,not_delivered,waiting_delivery_approve,completed'],
            'notes' => ['nullable', 'string']
        ]);

        $cash_request = $this->cashRequestService->handle(
            $cash_request,
            $validated
        );

        return response()->format(
            null,
            __('messages.handled_successfully', ['item' => __('constants.cash_request')]),
            200
        );
    }
}
