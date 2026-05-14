<?php

namespace App\Http\Controllers\Mobile\V1\AppUser;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\VaultTransactionResource;
use App\Http\Resources\Mobile\VaultTransferResource;
use App\Models\VaultTransfer;
use App\Services\Mobile\VaultService;
use Illuminate\Http\Request;

class VaultController extends Controller
{

    public function __construct(private VaultService $vaultService) {}

    public function vaultTransactions(Request $request)
    {
        $data = $this->vaultService->vaultTransactions($request);
        $transactions = $data['transactions'];
        $balance = $data['balance'];

        return response()->format([
            'balance' => $balance,
            "transactions" => $this->returnPaginatedResponse($transactions, VaultTransactionResource::collection($transactions))
        ], 'messages.success', 200);
    }

    public function vaultTransfers(Request $request)
    {
        $vaultTransfers = $this->vaultService->vaultTransfers($request);
        return response()->format($this->returnPaginatedResponse($vaultTransfers, VaultTransferResource::collection($vaultTransfers)), 'messages.success', 200);
    }

    public function showVaultTransfer(VaultTransfer $vaultTransfer)
    {
        $vaultTransfer = $this->vaultService->showVaultTransfer($vaultTransfer);
        return response()->format(new VaultTransferResource($vaultTransfer), 'messages.success', 200);
    }
}
