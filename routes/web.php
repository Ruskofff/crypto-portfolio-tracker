<?php

use App\Http\Controllers\HoldingController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', PortfolioController::class)->name('portfolio.index');

Route::resource('holdings', HoldingController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy']);
