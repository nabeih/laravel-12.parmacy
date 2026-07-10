<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/phar', function () {
    return view('login');
});
Route::get('/catalog', function () {
    return view('admin.catalog');
});
Route::get('/master', function () {
    return view('masterpage');
});
