<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHoldingRequest;
use App\Http\Requests\UpdateHoldingRequest;
use App\Http\Resources\HoldingResource;
use App\Models\Holding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class HoldingController extends Controller
{
    /**
     * List every stored holding.
     */
    public function index(): AnonymousResourceCollection
    {
        $holdings = Holding::query()
            ->with(['cryptocurrency', 'platform'])
            ->orderBy('id')
            ->get();

        return HoldingResource::collection($holdings);
    }

    /**
     * Store a newly created holding.
     */
    public function store(StoreHoldingRequest $request): JsonResponse
    {
        $holding = Holding::create($request->validated());

        return HoldingResource::make($holding->load(['cryptocurrency', 'platform']))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display a single holding.
     */
    public function show(Holding $holding): HoldingResource
    {
        return HoldingResource::make($holding->load(['cryptocurrency', 'platform']));
    }

    /**
     * Update an existing holding.
     */
    public function update(UpdateHoldingRequest $request, Holding $holding): HoldingResource
    {
        $holding->update($request->validated());

        return HoldingResource::make($holding->load(['cryptocurrency', 'platform']));
    }

    /**
     * Remove a holding from the portfolio.
     */
    public function destroy(Holding $holding): Response
    {
        $holding->delete();

        return response()->noContent();
    }
}
