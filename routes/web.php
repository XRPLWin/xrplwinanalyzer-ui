<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\AppController::class, 'front'])->name('front');
Route::get('/account/{account}', [App\Http\Controllers\AppController::class, 'account_index'])->name('account.index');
Route::get('/account/{account}/ancestry', [App\Http\Controllers\AppController::class, 'account_index'])->name('account.ancestry');
