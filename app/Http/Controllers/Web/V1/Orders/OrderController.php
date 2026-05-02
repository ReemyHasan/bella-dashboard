<?php

namespace App\Http\Controllers\Web\V1\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Orders\CustomerOrderRequest;
use App\Http\Requests\DashUser\Orders\HandleOrderRequest;
use App\Http\Resources\DashUser\Orders\CustomerOrderResource;
use App\Http\Resources\DashUser\Orders\OrderTransactionResource;
use App\Models\CustomerOrder;
use App\Services\DashUser\Orders\OrderService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OrderController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_orders', only: ['index']),
            new Middleware('permission:view_order_by_id', only: ['show', 'transactions']),
            new Middleware('permission:create_order', only: ['store']),
            new Middleware('permission:update_order', only: ['update']),
            new Middleware('permission:delete_order', only: ['destroy']),
            new Middleware('permission:handle_order', only: ['handle']),
        ];
    }

    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $requests = $this->orderService->list($request);
        return response()->format($this->returnPaginatedResponse($requests, CustomerOrderResource::collection($requests)), 'messages.success', 200);
    }
    public function transactions(Request $request, CustomerOrder $customer_order)
    {
        $request->customer_order = $customer_order->id;
        $customer_orders = $this->orderService->vaultTransactions($customer_order, $request);
        return response()->format($this->returnPaginatedResponse($customer_orders, OrderTransactionResource::collection($customer_orders)), 'messages.success', 200);
    }
    public function store(CustomerOrderRequest $request)
    {
        $customer_order = $this->orderService->create($request->validated());
        return response()->format(new CustomerOrderResource($customer_order),  __('messages.created_successfully',  ['item' => __('constants.customer_order')]), 201);
    }

    public function update(CustomerOrderRequest $request, CustomerOrder $customer_order)
    {
        $customer_order = $this->orderService->update($customer_order, $request->validated());
        return response()->format(new CustomerOrderResource($customer_order),  __('messages.updated_successfully',  ['item' => __('constants.customer_order')]), 200);
    }
    public function show(CustomerOrder $customer_order)
    {
        $customer_order = $this->orderService->show($customer_order);
        return response()->format(new CustomerOrderResource($customer_order), 'messages.success', 200);
    }
    public function destroy(CustomerOrder $customer_order)
    {
        $returned = $this->orderService->delete($customer_order);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.customer_order')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.customer_order')]), 200);
    }

    public function handle(HandleOrderRequest $request, CustomerOrder $customer_order)
    {

        $customer_order = $this->orderService->handle(
            $customer_order,
            $request->validated()
        );

        return response()->format(
            new CustomerOrderResource($customer_order),
            __('messages.handled_successfully', ['item' => __('constants.customer_order')]),
            200
        );
    }

    public function handleFinancialProcess(CustomerOrder $customer_order)
    {

        $customer_order = $this->orderService->handleFinancialProcess(
            $customer_order
        );

        return response()->format(
            new CustomerOrderResource($customer_order),
            __('messages.handled_successfully', ['item' => __('constants.customer_order')]),
            200
        );
    }


}
