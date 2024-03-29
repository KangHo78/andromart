<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\FrontEnd\FrontendController;

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

// Front End
// Route::get('/', function () {
//  return view('pages.frontend.index');
// });
Route::get('/', [FrontendController::class, 'home'])->name('frontendHome');
Route::get('/produk-detail/{id}', [FrontendController::class, 'productDetail'])->name('frontendProductDetail');
Route::get('/about', [FrontendController::class, 'about'])->name('frontendAbout');
Route::get('/services', [FrontendController::class, 'services'])->name('frontendServices');
Route::get('/work', [FrontendController::class, 'work'])->name('frontendWork');
Route::get('/contact', [FrontendController::class, 'contact'])->name('frontendContact');
Route::post('/message', [FrontendController::class, 'message'])->name('frontendMessage');
Route::get('/login', [FrontendController::class, 'login'])->name('frontendLogin');
Route::get('/trackingService/{id}', [FrontendController::class, 'tracking'])->name('frontendTracking');
Route::get('/product', [FrontendController::class, 'product'])->name('frontendProduct');
Route::get('/product-show/{id}/{sort?}', [FrontendController::class, 'productShow'])->name('frontendProductShow');
Route::get('/product-show-detail/{id}', [FrontendController::class, 'productShowDetail'])->name('frontendProductShowDetail');

// Route::get('image/{filename}', [FrontendController::class,'getPubliclyStorgeFile'])->name('image.displayImage');
// Route::get('/login', function () {
//     return view('home');
// });

// Backend
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
Route::get('/log', [DashboardController::class, 'log'])
    ->name('dashboard.log');
Route::get('/dashboard/filter-data-dashboard', [DashboardController::class, 'filterDataDashboard'])
    ->name('dashboard.filterDataDashboard');

Route::get('/dashboard/filter-data-statistic', [StatisticController::class, 'filterDataStatistic'])
    ->name('dashboard.filterDataStatistic');

Route::get('/dashboard/print-data-service-belum-diambil-dashboard', 
            [DashboardController::class, 'printDataServiceBelumDiambilDashboard'])
            ->name('dashboard.printDataServiceBelumDiambilDashboard');
Route::get('/dashboard/filter-kpi-dashboard', [DashboardController::class, 'filterKpiDashboard'])
    ->name('dashboard.filterKpiDashboard');
Route::get('/dashboard/filter-sharing-profit-dashboard', [DashboardController::class, 'checkSharingProfitEmployee'])
    ->name('dashboard.checkSharingProfitEmployee');

require __DIR__ . '/auth.php';

require __DIR__ . '/users.php';

require __DIR__ . '/master/areaRoute.php';
require __DIR__ . '/master/branchRoute.php';
require __DIR__ . '/master/brandRoute.php';
require __DIR__ . '/master/cashRoute.php';
require __DIR__ . '/master/categoryRoute.php';
require __DIR__ . '/master/costRoute.php';
require __DIR__ . '/master/customerRoute.php';
require __DIR__ . '/master/employeeRoute.php';
require __DIR__ . '/master/itemRoute.php';
require __DIR__ . '/master/presentaseRoute.php';
require __DIR__ . '/master/roleRoute.php';
require __DIR__ . '/master/supplierRoute.php';
require __DIR__ . '/master/typeRoute.php';
require __DIR__ . '/master/unitRoute.php';
require __DIR__ . '/master/warrantyRoute.php';
require __DIR__ . '/master/menuRoute.php';
require __DIR__ . '/master/accountRoute.php';
require __DIR__ . '/master/iconRoute.php';
require __DIR__ . '/master/assetRoute.php';
require __DIR__ . '/master/activaGroupRoute.php';

require __DIR__ . '/transaction/serviceRoute.php';
require __DIR__ . '/transaction/serviceReturnRoute.php';
require __DIR__ . '/transaction/saleRoute.php';
require __DIR__ . '/transaction/transactionIncomeRoute.php';
require __DIR__ . '/transaction/paymentRoute.php';
require __DIR__ . '/transaction/purchasingRoute.php';

require __DIR__ . '/finance/sharingProfitRoute.php';
require __DIR__ . '/finance/lossItemsRoute.php';
require __DIR__ . '/finance/ReportRoute.php';
require __DIR__ . '/finance/sharingProfitRoute.php';
require __DIR__ . '/finance/activaRoute.php';
require __DIR__ . '/finance/assetAdditionRoute.php';

require __DIR__ . '/report/reportServiceRoute.php';
require __DIR__ . '/report/reportSaleRoute.php';
require __DIR__ . '/report/reportSpendingRoute.php';
require __DIR__ . '/report/reportSummaryRoute.php';
require __DIR__ . '/report/reportServicePaymentRoute.php';
require __DIR__ . '/report/reportPurchaseRoute.php';
require __DIR__ . '/report/reportCashBalanceRoute.php';
require __DIR__ . '/report/reportPeriodicRoute.php';
require __DIR__ . '/report/reportIncomeStatementRoute.php';
require __DIR__ . '/report/reportNeracaRoute.php';

require __DIR__ . '/warehouse/stockRoute.php';
require __DIR__ . '/warehouse/stockTransactionRoute.php';
require __DIR__ . '/warehouse/stockMutationRoute.php';
require __DIR__ . '/warehouse/stockOpnameRoute.php';

// require __DIR__ . '/content/notes.php';
require __DIR__ . '/content/contentsRoute.php';
require __DIR__ . '/content/messageRoute.php';
require __DIR__ . '/content/productRoute.php';

require __DIR__ . '/office/regulationRoute.php';
require __DIR__ . '/office/notesRoute.php';

// require __DIR__ . '/office/selarasJurnalRoute.php';


// merubah 
Route::get('/rubahLinkMenu', [DashboardController::class, 'rubahLinkMenu'])
    ->name('rubahLinkMenu');

Route::get('/selarasJournals', [DashboardController::class, 'selarasJournals'])
    ->name('selarasJournals');


Route::get('/selarasJurnalLoss', [DashboardController::class, 'selarasJurnalLoss'])
    ->name('selarasJurnalLoss');


Route::get('/checkTotalJournals', [StatisticController::class, 'checkTotalJournals'])
    ->name('checkTotalJournals');