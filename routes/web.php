<?php

use App\Http\Controllers\PanoramaController;
use App\Http\Controllers\PanoramaImageController;
use App\Http\Controllers\PanoramaLinkController;
use App\Http\Controllers\PanoramaOriginalImageController;
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

Route::get('/', function () {
    return redirect()->route('panorama.index');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::resource('/panorama', class_basename(PanoramaController::class));
Route::resource('/panorama-link', class_basename(PanoramaLinkController::class));

Route::get('/panorama-image/{panorama}/{zoom}/{tile_x}/{tile_y}', class_basename(PanoramaImageController::class))->name('panorama-image');
Route::get('/panorama-original-image/{panorama}', class_basename(PanoramaOriginalImageController::class))->name('panorama-original-image');
