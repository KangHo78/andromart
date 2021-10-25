<?php

use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sale Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Sale routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "Sale" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'transaction'], function () {
    Route::group(['prefix' => 'sale'], function () {
        Route::resource('sale', SaleController::class)
            ->except([
                'show',
            ]);

        Route::resource('sale-return', SaleReturnController::class);

        Route::get(
            'sale/{id}',
            [SaleController::class, 'printSale']
        )->name('sale.printSale');

        Route::get(
            'sale-print/{id}',
            [SaleController::class, 'printSmallSale']
        )->name('sale.printSmallSale');
    });
});