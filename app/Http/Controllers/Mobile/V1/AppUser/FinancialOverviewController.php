<?php

namespace App\Http\Controllers\Mobile\V1\AppUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\AppUser\FinancialOverviewRequest;
use App\Services\Mobile\FinancialOverviewService;
use Illuminate\Http\Request;

class FinancialOverviewController extends Controller
{

    public function __construct(private FinancialOverviewService $service) {}

    public function userBalanceSummary(FinancialOverviewRequest $request)
    {
        $data = $this->service->userBalanceSummary($request->validated());
        return response()->format($data, 'messages.success', 200);
    }
    public function basePriceOverTime(FinancialOverviewRequest $request)
    {
        $data = $this->service->basePriceOverTime($request->validated());
        return response()->format($data, 'messages.success', 200);
    }

    public function topProducts(FinancialOverviewRequest $request)
    {
        $data = $this->service->topProducts($request->validated());
        return response()->format($data, 'messages.success', 200);
    }

    public function topCustomers(FinancialOverviewRequest $request)
    {
        $data = $this->service->topCustomers($request->validated());
        return response()->format($data, 'messages.success', 200);
    }
}
