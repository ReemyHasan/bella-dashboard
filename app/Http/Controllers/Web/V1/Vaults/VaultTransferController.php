<?php

namespace App\Http\Controllers\Web\V1\Vaults;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Vaults\VaultTransferRequest;
use App\Models\VaultTransfer;
use App\Services\DashUser\VaultTransferService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\DashUser\VaultTransferResource;

class VaultTransferController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_vault_transfers', only: ['index']),
            new Middleware('permission:view_vault_transfer_by_id', only: ['show']),
            new Middleware('permission:create_vault_transfer', only: ['store']),
            new Middleware('permission:update_vault_transfer', only: ['update']),
            new Middleware('permission:delete_vault_transfer', only: ['destroy']),
            new Middleware('permission:handle_vault_transfer', only: ['confirm', 'cancel']),

        ];
    }

    public function __construct(private VaultTransferService $vaultTransferService) {}

    public function index(Request $request)
    {
        $vaultTransfers = $this->vaultTransferService->list($request);
        return response()->format($this->returnPaginatedResponse($vaultTransfers, VaultTransferResource::collection($vaultTransfers)), 'messages.success', 200);
    }

    public function store(VaultTransferRequest $request)
    {
        $vaultTransfer = $this->vaultTransferService->create($request->validated());
        return response()->format(new VaultTransferResource($vaultTransfer),  __('messages.created_successfully',  ['item' => __('constants.vault_transfer')]), 201);
    }

    public function update(VaultTransferRequest $request, VaultTransfer $vaultTransfer)
    {
        $vaultTransfer = $this->vaultTransferService->update($vaultTransfer, $request->validated());
        if (!$vaultTransfer) {
            return response()->format(null,  __('messages.update_prohibited',  ['item' => __('constants.vault_transfer')]), 403);
        }
        return response()->format(new VaultTransferResource($vaultTransfer),  __('messages.updated_successfully',  ['item' => __('constants.vault_transfer')]), 200);
    }
    public function show(VaultTransfer $vaultTransfer)
    {
        $vaultTransfer = $this->vaultTransferService->show($vaultTransfer);
        return response()->format(new VaultTransferResource($vaultTransfer), 'messages.success', 200);
    }

    public function confirm(VaultTransfer $vaultTransfer)
    {
        $vaultTransfer = $this->vaultTransferService->confirm($vaultTransfer);
        return response()->format(new VaultTransferResource($vaultTransfer), __('messages.specific_confirmed_successfully',  ['item' => __('constants.vault_transfer')]), 200);
    }

    public function cancel(VaultTransfer $vaultTransfer)
    {
        $vaultTransfer = $this->vaultTransferService->cancel($vaultTransfer);
        return response()->format(new VaultTransferResource($vaultTransfer), __('messages.specific_cancelled_successfully',  ['item' => __('constants.vault_transfer')]), 200);
    }

    public function destroy(VaultTransfer $vaultTransfer)
    {
        $returned = $this->vaultTransferService->delete($vaultTransfer);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.vault_transfer')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.vault_transfer')]), 200);
    }
}
