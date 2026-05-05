<?php

namespace App\Http\Controllers\Mobile\V1\AppUser;

use App\Http\Controllers\Controller;
use App\Services\Mobile\WarehouseManReviewService;
use Illuminate\Http\Request;
use App\Http\Requests\Mobile\AppUser\WarehouseReviewRequest;
use App\Http\Resources\Mobile\WarehouseReviewResource;

class WarehouseManReviewController extends Controller
{

    public function __construct(private WarehouseManReviewService $service) {}

    public function index(Request $request)
    {
        $reviews = $this->service->list($request);
        return response()->format($this->returnPaginatedResponse($reviews, WarehouseReviewResource::collection($reviews)), 'messages.success', 200);
    }

    public function store(WarehouseReviewRequest $request)
    {
        $customer = $this->service->create($request->validated());
        return response()->format(null,  __('messages.created_successfully',  ['item' => __('constants.review')]), 201);
    }
}
