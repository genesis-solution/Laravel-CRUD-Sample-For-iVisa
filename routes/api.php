<?php

// /////////////////////////////////////////////////////////////////////////////
// PLEASE DO NOT RENAME OR REMOVE ANY OF THE CODE BELOW. 
// YOU CAN ADD YOUR CODE TO THIS FILE TO EXTEND THE FEATURES TO USE THEM IN YOUR WORK.
// /////////////////////////////////////////////////////////////////////////////

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'player'], function () {
    $idInThePath = '/{id}';
    Route::get('/', [PlayerController::class, 'index']);
    Route::get($idInThePath, [PlayerController::class, 'show']);
    Route::post('/', [PlayerController::class, 'store']);
    Route::put($idInThePath, [PlayerController::class, 'update']);
    Route::delete($idInThePath, [PlayerController::class, 'destroy'])->middleware('check.token');
});

Route::post('team/process', [PlayerController::class, 'teamProcess']);
