<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/run-storage-link', function () {
    Artisan::call('storage:link');
    return "Storage link created!";
});
Route::get('/clear-route', function () {
    Artisan::call('route:clear');
    return "Route Cleared Successfuly!";
});
