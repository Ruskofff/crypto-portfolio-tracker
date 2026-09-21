<?php

use App\Http\Controllers\Api\CryptocurrencyController;
use App\Http\Controllers\Api\HoldingController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('portfolio', PortfolioController::class)->name('api.portfolio');

Route::apiResource('holdings', HoldingController::class)->names('api.holdings');

Route::get('cryptocurrencies', [CryptocurrencyController::class, 'index'])
    ->name('api.cryptocurrencies.index');

Route::get('platforms', [PlatformController::class, 'index'])
    ->name('api.platforms.index');
