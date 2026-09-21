<?php

use Illuminate\Support\Facades\Route;

/*
 * The whole interface is a React single page application. Laravel only serves
 * the shell here; every piece of data it renders comes from routes/api.php.
 */
Route::view('/', 'app')->name('home');
