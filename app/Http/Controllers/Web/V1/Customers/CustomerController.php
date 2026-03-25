<?php

namespace App\Http\Controllers\Web\V1\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Customers\CustomerRequest;
use App\Http\Requests\DashUser\Users\UserPasswordRequest;
use App\Http\Resources\DashUser\CustomerResource;
use App\Models\Customer;
use App\Services\DashUser\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomerController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_customers', only: ['index']),
            new Middleware('permission:view_customer_by_id', only: ['show']),
            new Middleware('permission:create_customer', only: ['store']),
            new Middleware('permission:update_customer', only: ['update', 'changeStatus', 'setPassword']),
            new Middleware('permission:delete_customer', only: ['destroy']),

        ];
    }

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
    public function destroy(Customer $customer)
    {
        $returned = $this->customerService->delete($customer);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.customer')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.customer')]), 200);
    }

    public function setPassword(
        UserPasswordRequest $request,
        Customer $user
    ) {
        $this->customerService->updatePassword($user, $request->new_password);
        return response()->format(null, __('messages.password_reset_successfully',  ['item' => __('constants.customer')]), 200);
    }

    public function changeStatus(Request $request, Customer $user)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:ban,unban'],
            'blocked_reason' => 'nullable|required_if:action,ban'
        ]);

        $message = $this->customerService->handleStatusChange($user, $validated['action'], $validated['blocked_reason']);

        return response()->format(null, $message, 200);
    }

}
