<?php

namespace App\Http\Controllers\Mobile\V1\AppUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\AppUser\FinancialAdjustmentRequest;
use App\Http\Resources\Mobile\FinancialAdjustmentResource;
use App\Models\FinancialAdjustment;
use App\Services\Mobile\FinancialAdjustmentService;
use Illuminate\Http\Request;

class FinancialAdjustmentController extends Controller
{

    public function __construct(private FinancialAdjustmentService $financialAdjustmentService) {}

    public function index(Request $request)
    {
        $requests = $this->financialAdjustmentService->list($request);
        return response()->format($this->returnPaginatedResponse($requests, FinancialAdjustmentResource::collection($requests)), 'messages.success', 200);
    }

    public function store(FinancialAdjustmentRequest $request)
    {
        $financial_adjustment = $this->financialAdjustmentService->create($request->validated());
        return response()->format(new FinancialAdjustmentResource($financial_adjustment),  __('messages.created_successfully',  ['item' => __('constants.financial_adjustment')]), 201);
    }

    public function show(FinancialAdjustment $financial_adjustment)
    {
        $financial_adjustment = $this->financialAdjustmentService->show($financial_adjustment);
        return response()->format(new FinancialAdjustmentResource($financial_adjustment), 'messages.success', 200);
    }
}
