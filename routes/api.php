<?php

use App\Http\Controllers\ApiMapConfigController;
use App\Http\Controllers\ApiPanoramaController;
use App\Http\Controllers\PanoramaImageController;
use App\Http\Controllers\PanoramaOriginalImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::apiResource('panorama', class_basename(ApiPanoramaController::class));
Route::get('/map-config', class_basename(ApiMapConfigController::class));
Route::get('/panorama-original-image/{panorama}', class_basename(PanoramaOriginalImageController::class));
Route::get('/panorama-image/{panorama}/{zoom}/{tile_x}/{tile_y}', class_basename(PanoramaImageController::class));
