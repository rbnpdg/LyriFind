<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\laguController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/upload', [LaguController::class, 'upload'])->name('lagu-upload');
Route::get('/search', [LaguController::class, 'search'])->name('lagu-search');