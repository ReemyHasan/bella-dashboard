<?php

namespace App\Http\Controllers\Web\V1\Vaults;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Vaults\VaultRequest;
use App\Http\Resources\DashUser\VaultResource;
use App\Http\Resources\DashUser\VaultTransactionResource;
use App\Models\Vault;
use App\Services\DashUser\VaultService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class VaultController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_vaults', only: ['index']),
            new Middleware('permission:view_vault_by_id', only: ['show']),
            new Middleware('permission:create_vault', only: ['store']),
            new Middleware('permission:update_vault', only: ['update']),
            new Middleware('permission:delete_vault', only: ['destroy']),

        ];
    }

    public function __construct(private VaultService $vaultService) {}

    public function index(Request $request)
    {
        $vaults = $this->vaultService->list($request);
        return response()->format($this->returnPaginatedResponse($vaults, VaultResource::collection($vaults)), 'messages.success', 200);
    }
    public function transactions(Request $request, Vault $vault)
    {
        $request->vault_id = $vault->id;
        $vaults = $this->vaultService->vaultTransactions($vault, $request);
        return response()->format($this->returnPaginatedResponse($vaults, VaultTransactionResource::collection($vaults)), 'messages.success', 200);
    }

    public function store(VaultRequest $request)
    {
        $vault = $this->vaultService->create($request->validated());
        return response()->format(new VaultResource($vault),  __('messages.created_successfully',  ['item' => __('constants.vault')]), 201);
    }

    public function updateCompanyBalance(Request $request)
    {
        $validated = $request->validate([
            'balance' => 'required|numeric',
        ]);

        $vault = $this->vaultService->update($validated['balance']);


        return response()->format(new VaultResource($vault),  __('messages.updated_successfully',  ['item' => __('constants.vault')]), 200);
    }
    public function show(Vault $vault)
    {
        $vault = $this->vaultService->show($vault);
        return response()->format(new VaultResource($vault), 'messages.success', 200);
    }
    public function destroy(Vault $vault)
    {
        $returned = $this->vaultService->delete($vault);
        if (!$returned) {
            return response()->format(null,  'الرصيد أكبر من الصفر, يتعذر الحذف', 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.vault')]), 200);
    }

    public function selectAvailable(Request $request)
    {
        $vaults = $this->vaultService->selectAvailable();

        $returnedData = $vaults->map(fn($vault) => [
            'key' => $vault?->id,
            'value' => $vault->id == 1 ? 'خزنة الشركة' : $vault->owner?->first_name . ' ' . $vault->owner?->last_name . ' (' . $vault->balance . ')',
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
