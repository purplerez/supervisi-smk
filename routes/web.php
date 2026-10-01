<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Style Guide — hanya tersedia di lingkungan local
|--------------------------------------------------------------------------
*/
if (app()->isLocal()) {
    Route::get('/style-guide', function () {
        return view('style-guide');
    })->name('style-guide');
}
