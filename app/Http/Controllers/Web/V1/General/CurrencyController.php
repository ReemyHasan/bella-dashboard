<?php

namespace App\Http\Controllers\Web\V1\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\General\CurrencyRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\DashUser\CurrencyResource;
use App\Models\Currency;
use App\Services\DashUser\CurrencyService;

class CurrencyController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_currencies', only: ['index']),
            new Middleware('permission:view_currency_by_id', only: ['show']),
            new Middleware('permission:create_currency', only: ['store']),
            new Middleware('permission:update_currency', only: ['update']),
            new Middleware('permission:delete_currency', only: ['destroy']),

        ];
    }

    public function __construct(private CurrencyService $currencyService) {}

    public function index(Request $request)
    {
        $currencies = $this->currencyService->list($request);
        return response()->format($this->returnPaginatedResponse($currencies, CurrencyResource::collection($currencies)), 'messages.success', 200);
    }

    public function store(CurrencyRequest $request)
    {
        $currency = $this->currencyService->create($request->validated());
        return response()->format(new CurrencyResource($currency),  __('messages.created_successfully',  ['item' => __('constants.currency')]), 201);
    }

    public function update(CurrencyRequest $request, Currency $currency)
    {
        $currency = $this->currencyService->update($currency, $request->validated());
        return response()->format(new CurrencyResource($currency),  __('messages.updated_successfully',  ['item' => __('constants.currency')]), 200);
    }
    public function show(Currency $currency)
    {
        $currency = $this->currencyService->show($currency);
        return response()->format(new CurrencyResource($currency), 'messages.success', 200);
    }
    public function destroy(Currency $currency)
    {
        $returned = $this->currencyService->delete($currency);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.currency')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.currency')]), 200);
    }

    public function selectAvailable()
    {
        $currencies = $this->currencyService->selectAvailable();

        $returnedData = $currencies->map(fn($currency) => [
            'key' => $currency?->id,
            'value' => $currency?->name . '(' . $currency?->symbol . ')'
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
