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
        Route::post(
            'refresh-report-service',
            [ReportServiceController::class, 'dataLoad']
        )->name('report-service.dataLoad');
        Route::post(
            'refresh-report-service-type',
            [ReportServiceController::class, 'typeLoad']
        )->name('report-service.typeLoad');
        Route::post(
            'refresh-report-service-technician',
            [ReportServiceController::class, 'technicianLoad']
        )->name('report-service.technicianLoad');
        Route::post(
            'refresh-report-service-branch',
            [ReportServiceController::class, 'branchLoad']
        )->name('report-service.branchLoad');
        Route::post(
            'refresh-report-service-status',
            [ReportServiceController::class, 'statusLoad']
        )->name('report-service.statusLoad');
        Route::post(
            'refresh-employee-report-service',
            [ReportServiceController::class, 'dataEmployeeLoad']
        )->name('report-employee-service.dataLoad');

        //print report
        Route::get(
            'print-report-service-periode',
            [ReportServiceController::class, 'printPeriode']
        )->name('print-report-service.periode');
        Route::get(
            'print-report-service-series',
            [ReportServiceController::class, 'printSeries']
        )->name('print-report-service.series');
        Route::get(
            'print-report-service-technician',
            [ReportServiceController::class, 'printTechnician']
        )->name('print-report-service.technician');
        Route::get(
            'print-report-service-branch',
            [ReportServiceController::class, 'printBranch']
        )->name('print-report-service.branch');
        Route::get(
            'print-report-service-status',
            [ReportServiceController::class, 'printStatus']
        )->name('print-report-service.status');
    });

});
