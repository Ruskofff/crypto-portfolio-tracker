<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortfolioResource;
use App\Services\PortfolioService;

class PortfolioController extends Controller
{
    /**
     * Return the whole portfolio, valued at the prices of this request.
     */
    public function __invoke(PortfolioService $portfolio): PortfolioResource
    {
        return new PortfolioResource($portfolio->summary());
    }
}
