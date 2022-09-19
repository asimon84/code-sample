<?php

use App\Http\Controllers\CustomerController;
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


Route::get('/',  [CustomerController::class, 'index']);
Route::get('/customers',  [CustomerController::class, 'index']);
Route::get('/customer/{customer}',  [CustomerController::class, 'show']);
//Route::get('/customer/{id}',  [CustomerController::class, 'show']);
Route::post('/customer',  [CustomerController::class, 'create']);
Route::put('/customer/{id}',  [CustomerController::class, 'update']);
Route::delete('/customer/{id}',  [CustomerController::class, 'delete']);