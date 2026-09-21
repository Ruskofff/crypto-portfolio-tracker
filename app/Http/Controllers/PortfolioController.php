<?php

namespace App\Http\Controllers;

use App\Services\PortfolioService;
use Illuminate\Contracts\View\View;

class PortfolioController extends Controller
{
    /**
     * Show the portfolio, valued at the prices of this request.
     */
    public function __invoke(PortfolioService $portfolio): View
    {
        return view('portfolio.index', [
            'summary' => $portfolio->summary(),
        ]);
    }
}
