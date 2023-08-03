<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Personas;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => ["auth:sanctum"]], function () {
    Route::get('/auth/mostrar', [AuthController::class, 'mostrar']);
    Route::get('/auth/cantonesP', [AuthController::class, 'cantonesProvincia']);
    Route::get('/auth/recintosE', [AuthController::class, 'recintosElectoralesPC
    ']);


});

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);

