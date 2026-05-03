<?php

namespace App\Http\Controllers\Mobile\V1\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Customers\CustomerRequest;
use App\Http\Resources\Mobile\CustomerResource;
use App\Models\Customer;
use App\Services\Mobile\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller 
{
    public function __construct(private CustomerService $customerService) {}

    public function index(Request $request)
    {
        $customers = $this->customerService->list($request);
        return response()->format($this->returnPaginatedResponse($customers, CustomerResource::collection($customers)), 'messages.success', 200);
    }

    public function store(CustomerRequest $request)
    {
        $customer = $this->customerService->create($request->validated());
        return response()->format(new CustomerResource($customer),  __('messages.created_successfully',  ['item' => __('constants.customer')]), 201);
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $customer = $this->customerService->update($customer, $request->validated());
        return response()->format(new CustomerResource($customer),  __('messages.updated_successfully',  ['item' => __('constants.customer')]), 200);
    }
    public function show($id)
    {
        $customer = $this->customerService->show($id);
        return response()->format(new CustomerResource($customer), 'messages.success', 200);
    }
}
