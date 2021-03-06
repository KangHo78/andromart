<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Role Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Role routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "Role" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'master'], function () {
    Route::group(['prefix' => 'roles'], function () {
        Route::resource('role', RoleController::class)
            ->except([
                'show',
            ]);

        Route::post(
            'search-roles-detail',
            [RoleController::class, 'rolesDetailSearch']
        )->name('role.rolesDetailSearch');

        Route::post(
            'save-roles-detail',
            [RoleController::class, 'rolesDetailSave']
        )->name('role.rolesDetailSave');
    });
});