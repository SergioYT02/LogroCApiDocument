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
    Route::get('/auth/lista/provincias',[AuthController::class,'listaProvincias']);
    Route::get('/auth/lista/cantones',[AuthController::class,'Lista_cantones_provincias']);
    Route::get('/auth/lista/recintos',[AuthController::class,'Lista_recintos']);
    Route::put('/auth/update/recintos/{id}',[AuthController::class,'updateRecintosElectorales']);
    Route::put('/auth/delete/parroquias/{id}',[AuthController::class,'DeleteP']);
   
});

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);

