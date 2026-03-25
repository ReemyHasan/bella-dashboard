<?php

namespace App\Http\Controllers\Web\V1\Vaults;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Vaults\FinancialAdjustmentRequest;
use App\Http\Resources\DashUser\FinancialAdjustmentResource;
use App\Models\FinancialAdjustment;
use App\Services\DashUser\FinancialAdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FinancialAdjustmentController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_financial_adjustments', only: ['index']),
            new Middleware('permission:view_financial_adjustment_by_id', only: ['show']),
            new Middleware('permission:create_financial_adjustment', only: ['store']),
            new Middleware('permission:update_financial_adjustment', only: ['update']),
            new Middleware('permission:delete_financial_adjustment', only: ['destroy']),
            new Middleware('permission:handle_financial_adjustment', only: ['reject']),
            new Middleware('permission:handle_financial_adjustment', only: ['approve']),

        ];
    }

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

    public function update(FinancialAdjustmentRequest $request, FinancialAdjustment $financial_adjustment)
    {
        $financial_adjustment = $this->financialAdjustmentService->update($financial_adjustment, $request->validated());
        return response()->format(new FinancialAdjustmentResource($financial_adjustment),  __('messages.updated_successfully',  ['item' => __('constants.financial_adjustment')]), 200);
    }
    public function show(FinancialAdjustment $financial_adjustment)
    {
        $financial_adjustment = $this->financialAdjustmentService->show($financial_adjustment);
        return response()->format(new FinancialAdjustmentResource($financial_adjustment), 'messages.success', 200);
    }
    public function destroy(FinancialAdjustment $financial_adjustment)
    {
        $returned = $this->financialAdjustmentService->delete($financial_adjustment);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.financial_adjustment')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.financial_adjustment')]), 200);
    }

    public function handle(Request $request, FinancialAdjustment $financial_adjustment)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string']
        ]);

        $financial_adjustment = $this->financialAdjustmentService->handle(
            $financial_adjustment,
            $validated
        );

        return response()->format(
            new FinancialAdjustmentResource($financial_adjustment),
            __('messages.handled_successfully', ['item' => __('constants.financial_adjustment')]),
            200
        );
    }
}
