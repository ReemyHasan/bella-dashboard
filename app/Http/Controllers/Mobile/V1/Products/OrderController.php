<?php

namespace App\Http\Controllers\Mobile\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Orders\CustomerOrderRequest;
use App\Http\Requests\Mobile\Orders\HandleOrderRequest;
use App\Http\Resources\Mobile\CustomerOrderDetailsResource;
use App\Http\Resources\Mobile\CustomerOrderListResource;
use App\Models\CustomerOrder;
use App\Services\Mobile\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $requests = $this->orderService->list($request);
        return response()->format($this->returnPaginatedResponse($requests, CustomerOrderListResource::collection($requests)), 'messages.success', 200);
    }


    public function managedOrders(Request $request)
    {
        $requests = $this->orderService->managedOrders($request);
        return response()->format($this->returnPaginatedResponse($requests, CustomerOrderListResource::collection($requests)), 'messages.success', 200);
    }

    public function store(CustomerOrderRequest $request)
    {
        $customer_order = $this->orderService->create($request->validated());
        return response()->format(null,  __('messages.created_successfully',  ['item' => __('constants.customer_order')]), 201);
    }

    public function update(CustomerOrderRequest $request, CustomerOrder $customer_order)
    {
        $customer_order = $this->orderService->update($customer_order, $request->validated());
        return response()->format(null,  __('messages.updated_successfully',  ['item' => __('constants.customer_order')]), 200);
    }
    public function show(CustomerOrder $customer_order)
    {
        $customer_order = $this->orderService->show($customer_order);
        return response()->format(new CustomerOrderDetailsResource($customer_order), 'messages.success', 200);
    }

    public function handle(HandleOrderRequest $request, CustomerOrder $customer_order)
    {

        $customer_order = $this->orderService->handle(
            $customer_order,
            $request->validated()
        );

        return response()->format(
            null,
            __('messages.handled_successfully', ['item' => __('constants.customer_order')]),
            200
        );
    }
    public function addNotes(Request $request, CustomerOrder $customer_order)
    {
        $validated = $request->validate([
            'notes' => ['required', 'string'],
        ]);
        $this->orderService->addNotes(
            $customer_order,
            $validated
        );

        return response()->format(
            null,
            __('messages.note_added_successfully', ['item' => __('constants.customer_order')]),
            200
        );
    }
}
