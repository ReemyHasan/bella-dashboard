<?php

namespace App\Http\Controllers\Web\V1\Warehouses;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Warehouses\InvoiceRequest;
use App\Http\Resources\DashUser\InvoiceResource;
use App\Models\Invoice;
use App\Services\DashUser\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InvoiceController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_invoices', only: ['index']),
            new Middleware('permission:view_invoice_by_id', only: ['show']),
            new Middleware('permission:create_invoice', only: ['store']),
            new Middleware('permission:update_invoice', only: ['update']),
            new Middleware('permission:delete_invoice', only: ['destroy']),

        ];
    }

    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $invoices = $this->invoiceService->list($request);
        return response()->format($this->returnPaginatedResponse($invoices, InvoiceResource::collection($invoices)), 'messages.success', 200);
    }

    public function store(InvoiceRequest $request)
    {
        $invoice = $this->invoiceService->create($request->validated());
        return response()->format(new InvoiceResource($invoice),  __('messages.created_successfully',  ['item' => __('constants.invoice')]), 201);
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        if ($invoice->is_confirmed) {
            return response()->format(null,  __('messages.update_prohibited',  ['item' => __('constants.invoice')]), 403);
        }
        $invoice = $this->invoiceService->update($invoice, $request->validated());
        return response()->format(new InvoiceResource($invoice),  __('messages.updated_successfully',  ['item' => __('constants.invoice')]), 200);
    }


    public function confirmInvoice(Invoice $invoice)
    {
        if ($invoice->is_confirmed) {
            return response()->format(null,  __('messages.update_prohibited',  ['item' => __('constants.invoice')]), 403);
        }
        $invoice = $this->invoiceService->confirmInvoice($invoice);
        return response()->format(new InvoiceResource($invoice),  __('messages.confirmed_successfully',  ['item' => __('constants.invoice')]), 200);
    }

    public function show(Invoice $invoice)
    {
        $invoice = $this->invoiceService->show($invoice);
        return response()->format(new InvoiceResource($invoice), 'messages.success', 200);
    }
    public function destroy(Invoice $invoice)
    {
        $returned = $this->invoiceService->delete($invoice);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.invoice')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.invoice')]), 200);
    }

    public function selectAvailable(Request $request)
    {
        $zone = $request->input('zone');
        $is_main = $request->input('is_main');

        $invoices = $this->invoiceService->selectAvailable($zone, $is_main);

        $returnedData = $invoices->map(fn($invoice) => [
            'key' => $invoice?->id,
            'value' => $invoice?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
