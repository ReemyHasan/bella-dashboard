<?php

namespace App\Http\Controllers\Web\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Offers\OfferImageRequest;
use App\Http\Requests\DashUser\Offers\OfferRequest;
use App\Http\Requests\DashUser\Offers\OfferZonePriceSyncRequest;
use App\Http\Resources\DashUser\OfferResource;
use App\Models\Offer;
use App\Services\DashUser\OfferService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OfferController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_offers', only: ['index']),
            new Middleware('permission:view_offer_by_id', only: ['show']),
            new Middleware('permission:create_offer', only: ['store']),
            new Middleware('permission:update_offer', only: ['update', 'syncImages', 'syncZonePrices']),
            new Middleware('permission:delete_offer', only: ['destroy']),

        ];
    }

    public function __construct(private OfferService $offerService) {}

    public function index(Request $request)
    {
        $offers = $this->offerService->list($request);
        return response()->format($this->returnPaginatedResponse($offers, OfferResource::collection($offers)), 'messages.success', 200);
    }

    public function store(OfferRequest $request)
    {
        $offer = $this->offerService->create($request->validated());
        return response()->format(new OfferResource($offer),  __('messages.created_successfully',  ['item' => __('constants.offer')]), 201);
    }

    public function update(OfferRequest $request, Offer $offer)
    {
        $offer = $this->offerService->update($offer, $request->validated());
        return response()->format(new OfferResource($offer),  __('messages.updated_successfully',  ['item' => __('constants.offer')]), 200);
    }

    public function syncImages(OfferImageRequest $request, Offer $offer)
    {
        $offer = $this->offerService->syncImages($request->validated(), $offer);
        return response()->format(new OfferResource($offer),  __('messages.updated_successfully',  ['item' => __('constants.offer')]), 200);
    }

    public function syncZonePrices(OfferZonePriceSyncRequest $request, Offer $offer)
    {
        $offer = $this->offerService->syncZonePrices($request->validated(), $offer);
        return response()->format(new OfferResource($offer),  __('messages.updated_successfully',  ['item' => __('constants.offer')]), 200);
    }
    public function show(Offer $offer)
    {
        $offer = $this->offerService->show($offer);
        return response()->format(new OfferResource($offer), 'messages.success', 200);
    }
    public function destroy(Offer $offer)
    {
        $returned = $this->offerService->delete($offer);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.offer')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.offer')]), 200);
    }

}
