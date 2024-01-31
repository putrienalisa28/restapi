<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntreanController;

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

Route::get('/hello', function () {
    return "Hello World!";
  });

Route::get('/auth', [AntreanController::class, 'generateToken'])->name('antrean.generateToken');
Route::get('/antrean/status/{kode_poli}/{tanggalperiksa}', [AntreanController::class, 'status'])->name('antrean.status');
Route::match(['get', 'post'], '/antrean', [AntreanController::class, 'ambilantrean'])->name('antrean.ambilantrean');
Route::get('/antrean/sisapeserta/{nomorkartu_jkn}/{kode_poli}/{tanggalperiksa}', [AntreanController::class, 'sisapeserta'])->name('antrean.sisapeserta');
Route::put('/antrean/batal', [AntreanController::class, 'batal'])->name('antrean.batal');

