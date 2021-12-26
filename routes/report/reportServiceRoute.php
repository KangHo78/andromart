<?php

use App\Http\Controllers\ReportServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Users Routes
|--------------------------------------------------------------------------
|
| Here is where you can register users routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "users" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'report'], function () {
    Route::group(['prefix' => 'report'], function () {
        Route::get(
            'report-service',
            [ReportServiceController::class, 'reportService']
        )->name('report-service.reportService');
        Route::post(
            'search-report-service',
            [ReportServiceController::class, 'searchReportSale']
        )->name('report-service.searchReportIncomeSpending');
        Route::post('refresh-report-service', [ReportServiceController::class, 'dataLoad'])->name('report-service.dataLoad');

    });

});