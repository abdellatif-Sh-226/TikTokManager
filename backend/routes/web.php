<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/terms', 'pages.terms');
Route::view('/privacy', 'pages.privacy');
