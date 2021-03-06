<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountData;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function GuzzleHttp\Promise\all;

class ReportSaleController extends Controller
{
    public function __construct(DashboardController $dashboardController)
    {
        $this->middleware('auth');
        $this->DashboardController = $dashboardController;
    }

    public function reportSale()
    {
        $branchUser = Auth::user()->employee->branch_id;
        $stock = Stock::where('branch_id', $branchUser)->where('id', '!=', 1)->get();
        $sales = Employee::where('branch_id', $branchUser)->where('id', '!=', 1)->get();
        $kas = AccountData::where('branch_id', $branchUser)->where('active', 'Y')->where('name', 'LIKE', '%'.'Kas'.'%')->get();
        $customer = Customer::where('branch_id', $branchUser)->get();
        if (Auth::user()->role_id == 1) {
            $branch = Branch::get();
        } elseif (Auth::user()->role_id == 2) {
            $branch = Branch::where('area_id', Auth::user()->employee->branch->area_id)->get();
        } else {
            $branch = Branch::where('id', Auth::user()->employee->branch_id)->get();
        }

        return view('pages.backend.report.reportSale', compact(['stock', 'sales', 'kas', 'customer', 'branch']));
    }

    public function dataLoad(Request $req)
    {
        $branchUser = Auth::user()->employee->branch_id;
        $startDate = $req->startDate1;
        $endDate = $req->endDate1;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('branch_id', $branchUser)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.reportSaleLoad', compact('data', 'tr', 'sumKotor', 'sumBersih'));
    }

    public function itemLoad(Request $req)
    {
        $startDate = $req->startDate2;
        $endDate = $req->endDate2;
        $item = $req->item;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->leftJoin('sale_details','sale_details.sale_id','=','sales.id')
        ->select('sales.id', 'sales.date', 'sales.code', 'sales.account', 'sales.total_price', 'sales.total_profit_store')
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('sale_details.item_id', '=', $item)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.reportSaleLoad', compact('data', 'tr', 'sumKotor', 'sumBersih'));
    }

    public function salesLoad(Request $req)
    {
        $startDate = $req->startDate3;
        $endDate = $req->endDate3;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('sales_id', $req->sales)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.reportSaleLoad', compact('data', 'tr', 'sumKotor', 'sumBersih'));
    }

    public function kasLoad(Request $req)
    {
        $startDate = $req->startDate4;
        $endDate = $req->endDate4;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('account', $req->kas)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.reportSaleLoad', compact('data', 'tr', 'sumKotor', 'sumBersih'));
    }

    public function customerLoad(Request $req)
    {
        $startDate = $req->startDate5;
        $endDate = $req->endDate5;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('customer_id', $req->customer)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.reportSaleLoad', compact('data', 'tr', 'sumKotor', 'sumBersih'));
    }

    public function branchLoad(Request $req)
    {
        $startDate = $req->startDate6;
        $endDate = $req->endDate6;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('branch_id', $req->branch)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.reportSaleLoad', compact('data', 'tr', 'sumKotor', 'sumBersih'));
    }

    public function printPeriode(Request $req)
    {
        $title = 'Laporan Penjualan per Periode';
        $subtitle = ' ';
        $periode = $req->startDate1. ' - ' .$req->endDate1;
        $val = ' ';
        $branchUser = Auth::user()->employee->branch_id;
        $startDate = $req->startDate1;
        $endDate = $req->endDate1;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('branch_id', $branchUser)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.printReportSale', compact('data', 'tr', 'sumKotor', 'sumBersih', 'title', 'subtitle', 'val', 'periode'));
    }

    public function printItem(Request $req)
    {
        $item = Item::where('id', $req->item)->first();
        $brand = Brand::where('id', $item->brand_id)->first();
        $title = 'Laporan Penjualan per Item';
        $subtitle = 'Item';
        $periode = $req->startDate2. ' - ' .$req->endDate2;
        $val = $brand->name. ' ' .$item->name;
        $branchUser = Auth::user()->employee->branch_id;
        $startDate = $req->startDate2;
        $endDate = $req->endDate2;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->leftJoin('sale_details','sale_details.sale_id','=','sales.id')
        ->select('sales.id', 'sales.date', 'sales.code', 'sales.account', 'sales.total_price', 'sales.total_profit_store')
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('sale_details.item_id', '=', $req->item)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.printReportSale', compact('data', 'tr', 'sumKotor', 'sumBersih', 'title', 'subtitle', 'val', 'periode'));
    }

    public function printSales(Request $req)
    {
        $sales = Employee::where('id', $req->sales)->first();
        $title = 'Laporan Penjualan per Sales';
        $subtitle = 'Sales';
        $periode = $req->startDate3. ' - ' .$req->endDate3;
        $val = $sales->name;
        $branchUser = Auth::user()->employee->branch_id;
        $startDate = $req->startDate3;
        $endDate = $req->endDate3;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('branch_id', $branchUser)
        ->where('sales_id', $req->sales)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.printReportSale', compact('data', 'tr', 'sumKotor', 'sumBersih', 'title', 'subtitle', 'val', 'periode'));
    }

    public function printKas(Request $req)
    {
        $account = AccountData::where('id', $req->kas)->first();
        $title = 'Laporan Penjualan per Akun Kas';
        $subtitle = 'Akun Kas';
        $periode = $req->startDate4. ' - ' .$req->endDate4;
        $val = $account->name;
        $branchUser = Auth::user()->employee->branch_id;
        $startDate = $req->startDate4;
        $endDate = $req->endDate4;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('branch_id', $branchUser)
        ->where('account', $req->kas)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.printReportSale', compact('data', 'tr', 'sumKotor', 'sumBersih', 'title', 'subtitle', 'val', 'periode'));
    }

    public function printCustomer(Request $req)
    {
        $customer = Customer::where('id', $req->customer)->first();
        $title = 'Laporan Penjualan per Customer';
        $subtitle = 'Customer';
        $periode = $req->startDate5. ' - ' .$req->endDate5;
        $val = $customer->name;
        $branchUser = Auth::user()->employee->branch_id;
        $startDate = $req->startDate5;
        $endDate = $req->endDate5;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('branch_id', $branchUser)
        ->where('customer_id', $req->customer)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.printReportSale', compact('data', 'tr', 'sumKotor', 'sumBersih', 'title', 'subtitle', 'val', 'periode'));
    }

    public function printBranch(Request $req)
    {
        $branch = Branch::where('id', $req->branch)->first();
        $title = 'Laporan Penjualan per Cabang';
        $subtitle = 'Cabang';
        $periode = $req->startDate6. ' - ' .$req->endDate6;
        $val = $branch->name;
        $branchUser = Auth::user()->employee->branch_id;
        $startDate = $req->startDate6;
        $endDate = $req->endDate6;
        $data = Sale::with(['SaleDetail', 'SaleDetail.Item', 'accountData'])
        ->where('date','>=',$this->DashboardController->changeMonthIdToEn($startDate))
        ->where('date','<=',$this->DashboardController->changeMonthIdToEn($endDate))
        ->where('branch_id', $req->branch)
        ->orderBy('id', 'desc')->get();

        $sumKotor = $data->sum('total_price');
        $sumBersih = $data->sum('total_profit_store');
        $tr = count($data);

        return view('pages.backend.report.printReportSale', compact('data', 'tr', 'sumKotor', 'sumBersih', 'title', 'subtitle', 'val', 'periode'));
    }
}
