<?php

use App\Http\Controllers\ReportPeriodicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Payment routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "Payment" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'report'], function () {
    Route::group(['prefix' => 'report'], function () {
        Route::resource('report-periodic', ReportPeriodicController::class)
            ->except([
                'show',
            ]);

        Route::post(
            'search-report-periodic',
            [ReportPeriodicController::class, 'searchReportPeriodic']
        )->name('report-periodic.searchReportPeriodic');
    });
});
