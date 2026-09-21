<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CryptocurrencyResource;
use App\Models\Cryptocurrency;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CryptocurrencyController extends Controller
{
    /**
     * List the cryptocurrencies a holding can reference.
     */
    public function index(): AnonymousResourceCollection
    {
        $cryptocurrencies = Cryptocurrency::query()
            ->orderBy('name')
            ->get();

        return CryptocurrencyResource::collection($cryptocurrencies);
    }
}
