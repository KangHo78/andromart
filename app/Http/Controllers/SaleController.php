<?php

namespace App\Http\Controllers;

use App\Models\AccountData;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Cash;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Carbon\carbon;
use GuzzleHttp\Promise\Create;

class SaleController extends Controller
{
    public function __construct(DashboardController $DashboardController)
    {
        $this->middleware('auth');
        $this->DashboardController = $DashboardController;
    }

    public function index(Request $req)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(5,'view');
        if($checkRoles == 'akses ditolak'){
            return view('forbidden');
        }

        if ($req->ajax()) {
            $branch = Auth::user()->employee->branch_id;
            $data = Sale::with(['SaleDetail', 'SaleDetail.Item'])->where('branch_id', $branch)->orderBy('id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->order(function ($query) {
                    if(request()->has('id')) {
                        $query->orderBy('id', 'desc');
                    }
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<div class="btn-group">';
                    $actionBtn .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                            data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>';
                    $actionBtn .= '<div class="dropdown-menu">';
                    $actionBtn .= '<a class="dropdown-item" href="' . route('sale.printSale', $row->id) . '" target="output"><i class="fas fa-print"></i> Nota Besar</a>';
                    $actionBtn .= '<a class="dropdown-item" href="' . route('sale.printSmallSale', $row->id) . '" target="output"><i class="fas fa-print"></i> Nota Kecil</a>';
                    $actionBtn .= '<a onclick="jurnal(' . "'" . $row->code . "'" . ')" class="dropdown-item" style="cursor:pointer;"><i class="fas fa-file-alt"></i> Jurnal</a>';
                    $actionBtn .= '<a class="dropdown-item" href="' . route('sale.edit', $row->id) . '" ><i class="fas fa-pencil-alt"></i> Edit</a>';
                    $actionBtn .= '<a onclick="del(' . $row->id . ')" class="dropdown-item" style="cursor:pointer;"><i class="fas fa-trash"></i> Hapus</a>';
                    // $actionBtn .= '<a onclick="" class="dropdown-item" style="cursor:pointer;"><i class="far fa-eye"></i> Lihat</a>';
                    $actionBtn .= '</div></div>';
                    return $actionBtn;
                })
                ->addColumn('dataCode', function($row) {
                    $htmlAdd = '<td><a href="' . route('sale.show', $row->id) . '">' . $row->code . '</a></td>';

                    return $htmlAdd;
                })
                ->addColumn('dataDateOperator', function ($row) {
                    $htmlAdd = '<table>';
                    $htmlAdd .=   '<tr>';
                    $htmlAdd .=      '<th>' . Carbon::parse($row->date)->locale('id')->isoFormat('LL') . '</th>';
                    $htmlAdd .=   '</tr>';
                    $htmlAdd .=   '<tr>';
                    $htmlAdd .=      '<th>' . $row->created_by . '</th>';
                    $htmlAdd .=   '</tr>';
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })
                ->addColumn('dataCustomer', function ($row) {
                    $htmlAdd = '<table>';
                    $htmlAdd .=   '<tr>';
                    $htmlAdd .=      '<th>' . $row->customer_name . '</th>';
                    $htmlAdd .=   '</tr>';
                    $htmlAdd .=   '<tr>';
                    $htmlAdd .=      '<th>' . $row->customer_address . '</th>';
                    $htmlAdd .=   '</tr>';
                    $htmlAdd .=   '<tr>';
                    $htmlAdd .=      '<th>' . $row->customer_phone . '</th>';
                    $htmlAdd .=   '</tr>';
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })
                ->addColumn('dataItem', function ($row) {
                    $htmlAdd = '<table>';
                    foreach ($row->SaleDetail as $key => $value) {
                        // $item = $value->Item()->withTrashed()->get('name');
                        $htmlAdd .=   '<tr>';
                        $htmlAdd .=      '<td>x' . $value->qty . '</td>';
                        $htmlAdd .=      '<th>' . $value->item->name . '</th>';
                        $htmlAdd .=      '<td>(' . $value->item->warranty->periode . $value->item->warranty->name . ')</td>';
                        $htmlAdd .=   '</tr>';
                    }
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })
                ->addColumn('finance', function ($row) {
                    $htmlAdd = '<table>';
                    $htmlAdd .=   '<tr>';
                    $htmlAdd .=      '<td>Barang</td>';
                    $htmlAdd .=      '<th>' . number_format($row->item_price, 0, ".", ",") . '</th>';
                    $htmlAdd .=      '<td>S.P Sales</td>';
                    $htmlAdd .=      '<th>' . number_format($row->total_profit_sales, 0, ".", ",") . '</th>';
                    $htmlAdd .=   '</tr>';
                    $htmlAdd .=   '<tr>';
                    $htmlAdd .=      '<td>Diskon</td>';
                    $htmlAdd .=      '<th>' . number_format($row->discount_price, 0, ".", ",") . '</th>';
                    $htmlAdd .=      '<td>S.P Buyer</td>';
                    $htmlAdd .=      '<th>' . number_format($row->total_profit_buyer, 0, ".", ",") . '</th>';
                    $htmlAdd .=   '</tr>';
                    $htmlAdd .=   '<tr>';
                    $htmlAdd .=      '<td>Total</td>';
                    $htmlAdd .=      '<th>' . number_format($row->total_price, 0, ".", ",") . '</th>';
                    $htmlAdd .=      '<td>S.P Toko</td>';
                    $htmlAdd .=      '<th>' . number_format($row->total_profit_store, 0, ".", ",") . '</th>';
                    $htmlAdd .=   '</tr>';
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })

                ->rawColumns(['action', 'dataCode', 'dataItem', 'dataCustomer', 'finance', 'dataDateOperator'])
                ->make(true);
        }
        return view('pages.backend.transaction.sale.indexSale');
    }

    public function code($type)
    {
        $getEmployee = Employee::with('branch')
            ->where('user_id', Auth::user()->id)
            ->first();
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('y');
        $co = Sale::where('branch_id', Auth::user()->employee->branch_id)
            ->whereMonth('date', now())
            ->get();
        $index = count($co) + 1;
        // $index = DB::table('Sale')->max('id') + 1;
        $index = str_pad($index, 3, '0', STR_PAD_LEFT);

        $code = $type . $getEmployee->Branch->code . $year . $month . $index;

        $checkCode = Sale::where('code', $code)
            ->count();

        if ($checkCode == 0 ) {
            return $code;
        }else{
            $code = $type . $getEmployee->Branch->code . $year . $month . ($index+1);

            $checkCode2 = Service::where('code', $code)
            ->count();
            if ($checkCode2 == 0) {
                return $code;
            }else{
                $index2 = count($co) + 1;
                $index2 = str_pad($index2, 5, '0', STR_PAD_LEFT);
                return $code = $type . $getEmployee->Branch->code . $year . $month . $index2;
            }
        }
    }

    public function codeJournals($type)
    {
        $getEmployee =  Employee::with('branch')->where('user_id', Auth::user()->id)->first();
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('y');
        // DB::table('service')->max('id')+1;
        $index = DB::table('journals')->max('id') + 1;

        $index = str_pad($index, 3, '0', STR_PAD_LEFT);
        return $code = $type . $getEmployee->Branch->code . $year . $month . $index;
    }

    public function create()
    {
        $checkRoles = $this->DashboardController->cekHakAkses(5,'create');
        if($checkRoles == 'akses ditolak'){
            return view('forbidden');
        }

        $code = $this->code('PJT');
        // return $code;
        $account  = AccountData::with('AccountMain', 'AccountMainDetail', 'Branch')->get();
        $userBranch = Auth::user()->employee->branch_id;
        $sales = Employee::where('id', '!=', '1')->where('branch_id', '=', $userBranch)->orderBy('name', 'asc')->where('status','aktif')->get();
        $buyer = Employee::where('id', '!=', '1')->where('branch_id', '=', $userBranch)->orderBy('name', 'asc')->where('status','aktif')->get();
        $cash = Cash::get();
        $customer = Customer::where('branch_id', '=', $userBranch)->orderBy('name', 'asc')->get();
        $stock = Stock::where('branch_id', '=', $userBranch)->where('item_id', '!=', 1)->get();

        return view('pages.backend.transaction.sale.createSale', compact('code', 'cash', 'buyer', 'sales', 'stock', 'customer', 'account'));
    }

    public function store(Request $req)
    {
        // return $req->all();
        DB::beginTransaction();
        try {
            $id = DB::table('sales')->max('id') + 1;
            $getEmployee =  Employee::where('user_id', Auth::user()->id)->first();
            $code = $this->code('PJT');
            // $dateConvert = $this->DashboardController->changeMonthIdToEn($req->date);
            // return($req);
            if ($req->customer_name != null) {
                $customerName = $req->customer_name;
                $customerPhone = $req->customer_phone;
                $customerAddress = $req->customer_address;
            } else {
                $customerName = 'Umum';
                $customerPhone = null;
                $customerAddress = null;
            }

            for ($i = 0; $i < count($req->itemsDetail); $i++) {
                $sharing_profit_store[$i] = (100 - ($req->profitSharingBuyer[$i] + $req->profitSharingSales[$i])) * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * (str_replace(",", '', $req->profitDetail[$i])))) / 100;
                $sharing_profit_sales[$i] = $req->profitSharingSales[$i] * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * (str_replace(",", '', $req->profitDetail[$i])))) / 100;
                $sharing_profit_buyer[$i] = $req->profitSharingBuyer[$i] * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * (str_replace(",", '', $req->profitDetail[$i])))) / 100;
                $total_profit_store = collect($sharing_profit_store)->sum();
                $total_profit_sales = collect($sharing_profit_sales)->sum();
                $total_profit_buyer = collect($sharing_profit_buyer)->sum();
                $hpp = str_replace(",", '', $req->profitDetail);
                $total_hpp = collect($hpp)->sum();
                // return $tot;
            }

            Sale::create([
                'id' => $id,
                'code' => $code,
                'user_id' => Auth::user()->id,
                'sales_id' => $req->sales_id,
                'account' => $req->account,
                'branch_id' => $getEmployee->branch_id,
                'customer_id' => $req->customer_id,
                'customer_name' => $customerName,
                'customer_address' => $customerAddress,
                'customer_phone' => $customerPhone,
                'payment_method' => $req->PaymentMethod,
                'date' => date('Y-m-d'),
                'discount_type' => $req->typeDiscount,
                'discount_price' => str_replace(",", '', $req->totalDiscountValue),
                'discount_percent' => str_replace(",", '', $req->totalDiscountPercent),
                'discount_sale' => str_replace(",", '', $req->totalSparePart),-str_replace(",", '', $req->totalDiscountValue),
                'item_price' => str_replace(",", '', $req->totalSparePart),
                'total_price' => str_replace(",", '', $req->totalPrice),
                'total_hpp' => str_replace(",", '', $total_hpp),
                'total_profit_store' => $total_profit_store,
                'total_profit_sales' => $total_profit_sales,
                'total_profit_buyer' => $total_profit_buyer,
                'description' => $req->description,
                'created_at' => date('Y-m-d h:i:s'),
                'created_by' => Auth::user()->name,
            ]);

            $checkStock = [];
            for ($i = 0; $i < count($req->itemsDetail); $i++) {
                $sharing_profit_store[$i] = (100 - ($req->profitSharingBuyer[$i] + $req->profitSharingSales[$i])) * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * (str_replace(",", '', $req->profitDetail[$i])))) / 100;
                $sharing_profit_sales[$i] = $req->profitSharingSales[$i] * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * (str_replace(",", '', $req->profitDetail[$i])))) / 100;
                $sharing_profit_buyer[$i] = $req->profitSharingBuyer[$i] * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * (str_replace(",", '', $req->profitDetail[$i])))) / 100;
                // $hpp[$i] = $req->profitDetail[$i];
                $tot_hpp[$i] = (str_replace(",", '', $req->profitDetail[$i])) * $req->qtyDetail[$i];

                SaleDetail::create([
                    'sale_id' => $id,
                    'item_id' => $req->itemsDetail[$i],
                    'sales_id' => $req->sales_id,
                    'buyer_id' => $req->buyerDetail[$i],
                    'sharing_profit_store' => $sharing_profit_store[$i],
                    'sharing_profit_sales' => $sharing_profit_sales[$i],
                    'sharing_profit_buyer' => $sharing_profit_buyer[$i],
                    'price' => str_replace(",", '', $req->priceDetail[$i]),
                    'qty' => $req->qtyDetail[$i],
                    'total' => str_replace(",", '', $req->totalPriceDetail[$i]),
                    'hpp' => str_replace(",", '', $req->profitDetail[$i]),
                    'total_hpp' => str_replace(",", '', $tot_hpp[$i]),
                    'description' => $req->descriptionDetail[$i],
                    'type' => $req->typeDetail[$i],
                    'created_by' => Auth::user()->name,
                    'created_at' => date('Y-m-d h:i:s'),
                ]);
                if ($req->typeDetail[$i] != 'Jasa') {
                    $checkStock[$i] = Stock::where('item_id', $req->itemsDetail[$i])
                        ->where('branch_id', $getEmployee->branch_id)
                        ->where('id', '!=', 1)
                        ->get();
                    if (count($checkStock[$i]) != null) {
                        if ($checkStock[$i][0]->stock < $req->qtyDetail[$i]) {
                            return Response::json([
                                'status' => 'fail',
                                'message' => 'Stock Item Ada yang 0. Harap Cek Kembali'
                            ]);
                        }
                        if ($req->typeDetail[$i] == 'SparePart') {
                            $desc[$i] = 'Pengeluaran Barang Pada Penjualan ' . $code;
                        } else {
                            $desc[$i] = 'Pengeluaran Barang Loss Pada Penjualan ' . $code;
                        }
                        Stock::where('item_id', $req->itemsDetail[$i])
                            ->where('branch_id', $getEmployee->branch_id)->update([
                                'stock'      => $checkStock[$i][0]->stock - $req->qtyDetail[$i],
                            ]);
                        StockMutation::create([
                            'item_id'    => $req->itemsDetail[$i],
                            'unit_id'    => $checkStock[$i][0]->unit_id,
                            'branch_id'  => $checkStock[$i][0]->branch_id,
                            'qty'        => $req->qtyDetail[$i],
                            'code'       => $code,
                            'type'       => 'Out',
                            'created_by' => Auth::user()->name,
                            'created_at' => date('Y-m-d h:i:s'),
                            'updated_by' => Auth::user()->name,
                            'updated_at' => date('Y-m-d h:i:s'),
                            'description' => $desc[$i],
                        ]);
                    } else {
                        return Response::json([
                            'status' => 'fail',
                            'message' => 'Item Tidak Ditemukan Di STOCK '
                        ]);
                    }
                }
            }

            // Jurnal
            $idJournal = DB::table('journals')->max('id') + 1;
            Journal::create([
                'id' => $idJournal,
                'code' => $this->code('DD'),
                'year' => date('Y'),
                'date' => date('Y-m-d'),
                'type' => 'Penjualan',
                'total' => str_replace(",", '', $req->totalPrice),
                'ref' => $code,
                'description' => 'Transaksi Penjualan ' .$code,
                'created_at' => date('Y-m-d h:i:s'),
            ]);

            if ($req->type == 'DownPayment') {
            } else {
                $accountData  = AccountData::where('branch_id', $getEmployee->branch_id)
                    ->where('active', 'Y')
                    ->where('main_id', 5)
                    ->where('main_detail_id', 27)
                    ->first();

                $accountDiskon  = AccountData::where('branch_id', $getEmployee->branch_id)
                    ->where('active', 'Y')
                    ->where('main_id', 8)
                    ->where('main_detail_id', 30)
                    ->first();

                $accountPembayaran  = AccountData::where('id', $req->account)
                    ->first();

                if (str_replace(",", '', $req->totalDiscountValue) == 0) {
                    $accountCode = [
                        $accountPembayaran->id,
                        $accountData->id,
                    ];
                    $description = [
                        'Kas Pendapatan Penjualan ' .$code,
                        'Pendapatan Penjualan ' .$code,
                    ];
                    $totalBayar = [
                        str_replace(",", '', $req->totalPrice),
                        str_replace(",", '', $req->totalPrice),
                    ];
                    $DK = [
                        'D',
                        'K',
                    ];
                } else {
                    $accountCode = [
                        $accountPembayaran->id,
                        $accountDiskon->id,
                        $accountData->id,
                    ];
                    $totalBayar = [
                        str_replace(",", '', $req->totalPrice),
                        str_replace(",", '', $req->totalDiscountValue),
                        str_replace(",", '', $req->totalSparePart),
                    ];
                    $description = [
                        'Kas Pendapatan Penjualan ' .$code,
                        'Diskon Penjualan ' .$code,
                        'Pendapatan Penjualan ' .$code,
                    ];
                    $DK = [
                        'D',
                        'D',
                        'K',
                    ];
                }

                for ($i = 0; $i < count($accountCode); $i++) {
                    $idDetail = DB::table('journal_details')->max('id') + 1;
                    JournalDetail::create([
                        'id' => $idDetail,
                        'journal_id' => $idJournal,
                        'account_id' => $accountCode[$i],
                        'total' => $totalBayar[$i],
                        'description' => $description[$i],
                        'debet_kredit' => $DK[$i],
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]);
                }

                //Jurnal HPP
                $idJournalHpp = DB::table('journals')->max('id') + 1;
                Journal::create([
                    'id' => $idJournalHpp,
                    'code' => $this->code('KK', $idJournalHpp),
                    'year' => date('Y'),
                    'date' => date('Y-m-d'),
                    'type' => 'Biaya',
                    'total' => str_replace(",", '', $total_hpp),
                    'ref' => $code,
                    'description' => 'HPP ' . $code,
                    'created_at' => date('Y-m-d h:i:s'),
                ]);

                $accountPersediaan  = AccountData::where('branch_id', $getEmployee->branch_id)
                    ->where('active', 'Y')
                    ->where('main_id', 3)
                    ->where('main_detail_id', 11)
                    ->first();

                $accountBiayaHpp  = AccountData::where('branch_id', $getEmployee->branch_id)
                    ->where('active', 'Y')
                    ->where('main_id', 7)
                    ->where('main_detail_id', 29)
                    ->first();
                // JURNAL HPP
                $accountCodeHpp = [
                    $accountBiayaHpp->id,
                    $accountPersediaan->id,
                ];
                $total_hpp = [
                    str_replace(",", '', $total_hpp),
                    str_replace(",", '', $total_hpp),
                ];
                $descriptionHpp = [
                    'Pengeluaran Harga Pokok Penjualan ' . $code,
                    'Biaya Harga Pokok Penjualan ' . $code,
                ];
                $DKHpp = [
                    'D',
                    'K',
                ];
                for ($i = 0; $i < count($accountCodeHpp); $i++) {
                    if ($total_hpp[$i] != 0) {
                        $idDetailhpp = DB::table('journal_details')->max('id') + 1;
                        JournalDetail::create([
                            'id' => $idDetailhpp,
                            'journal_id' => $idJournalHpp,
                            'account_id' => $accountCodeHpp[$i],
                            'total' => $total_hpp[$i],
                            'description' => $descriptionHpp[$i],
                            'debet_kredit' => $DKHpp[$i],
                            'created_at' => date('Y-m-d h:i:s'),
                            'updated_at' => date('Y-m-d h:i:s'),
                        ]);
                    }
                }
            }

            DB::commit();
            return Response::json(['status' => 'success', 'message' => 'Data Tersimpan', 'id' => $id] );
        } catch (\Throwable $th) {
            DB::rollback();

            return Response::json(['status' => 'error', 'message' => $th ]);
        }
    }

    public function show($id)
    {
        $sale = Sale::with(['SaleDetail', 'Customer', 'accountData'])->where('code',$id)->first();
        if (isset($sale) == 1) {
            $sale = $sale;
        } else {
            $sale = Sale::with(['SaleDetail', 'Customer', 'accountData'])->find($id);
        }
        

        return view('pages.backend.transaction.sale.showSale', compact('sale'));
    }

    public function edit($id)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(5,'edit');
        if($checkRoles == 'akses ditolak'){
            return view('forbidden');
        }

        $code = $this->code('PJT');
        $userBranch = Auth::user()->employee->branch_id;
        $sales = Employee::where('id', '!=', '1')->where('branch_id', '=', $userBranch)->orderBy('name', 'asc')->where('status','aktif')->get();
        $buyer = Employee::where('id', '!=', '1')->where('branch_id', '=', $userBranch)->orderBy('name', 'asc')->where('status','aktif')->get();
        $cash = Cash::get();
        $account  = AccountData::with('AccountMain', 'AccountMainDetail', 'Branch')->get();
        $customer = Customer::where('branch_id', '=', $userBranch)->orderBy('name', 'asc')->get();
        $stock = Stock::where('branch_id', '=', $userBranch)->where('item_id', '!=', 1)->get();
        $sale = Sale::with(['SaleDetail', 'Customer'])->find($id);
        $item = Item::with('stock')->where('name', '!=', 'Jasa Service')->get();
        $idJurnal = Journal::where('ref', $sale->code)->get();

        return view('pages.backend.transaction.sale.updateSale', compact('account', 'sale', 'cash', 'stock', 'buyer', 'customer', 'sales', 'idJurnal'));
    }

    public function update(Request $req, $id)
    {
        DB::beginTransaction();
        try{
            $getEmployee =  Employee::where('user_id', Auth::user()->id)->first();

        if ($req->customer_name != null) {
            $customerName = $req->customer_name;
            $customerPhone = $req->customer_phone;
            $customerAddress = $req->customer_address;
        } else {
            $customerName = 'Umum';
            $customerPhone = null;
            $customerAddress = null;
        }
        $sharing_profit_store = [];
        $sharing_profit_sales = [];
        $sharing_profit_buyer = [];
        $sharing_profit_storeOld = [];
        $sharing_profit_salesOld = [];
        $sharing_profit_buyerOld = [];

        if ($req->itemsDetail != null) {
            for ($i = 0; $i < count($req->itemsDetail); $i++) {
                $sharing_profit_store[$i] = (100 - ($req->profitSharingBuyer[$i] + $req->profitSharingSales[$i])) * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * (str_replace(",", '', $req->profitDetail[$i])))) / 100;
                $sharing_profit_sales[$i] = $req->profitSharingSales[$i] * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * (str_replace(",", '', $req->profitDetail[$i])))) / 100;
                $sharing_profit_buyer[$i] = $req->profitSharingBuyer[$i] * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * (str_replace(",", '', $req->profitDetail[$i])))) / 100;
                $hpp[$i] = collect((str_replace(",", '', $req->profitDetail[$i])))->sum();
            }
        } else {
            $hpp = '0';
        }
        if ($req->itemsDetailOld != null) {
            for ($i = 0; $i < count($req->itemsDetailOld); $i++) {
                $sharing_profit_storeOld[$i] = (100 - ($req->profitSharingBuyerOld[$i] + $req->profitSharingSalesOld[$i])) * ((str_replace(",", '', $req->totalPriceDetailOld[$i])) - ($req->qtyDetailOld[$i] * (str_replace(",", '', $req->profitDetailOld[$i])))) / 100;
                $sharing_profit_salesOld[$i] = $req->profitSharingSalesOld[$i] * ((str_replace(",", '', $req->totalPriceDetailOld[$i])) - ($req->qtyDetailOld[$i] * (str_replace(",", '', $req->profitDetailOld[$i])))) / 100;
                $sharing_profit_buyerOld[$i] = $req->profitSharingBuyerOld[$i] * ((str_replace(",", '', $req->totalPriceDetailOld[$i])) - ($req->qtyDetailOld[$i] * (str_replace(",", '', $req->profitDetailOld[$i])))) / 100;
                $hppOld[$i] = collect((str_replace(",", '', $req->profitDetailOld[$i])))->sum();
            }
        }

        $total_profit_store = collect($sharing_profit_store)->sum() + collect($sharing_profit_storeOld)->sum();
        $total_profit_sales = collect($sharing_profit_sales)->sum() + collect($sharing_profit_salesOld)->sum();
        $total_profit_buyer = collect($sharing_profit_buyer)->sum() + collect($sharing_profit_buyerOld)->sum();
        $total_hpp = collect($hpp)->sum() + collect($hppOld)->sum();

        Sale::where('id', $id)
            ->update([
                'user_id' => Auth::user()->id,
                'sales_id' => $req->sales_id,
                'account' => $req->account,
                'customer_id' => $req->customer_id,
                'customer_name' => $customerName,
                'customer_address' => $customerAddress,
                'customer_phone' => $customerPhone,
                'payment_method' => $req->PaymentMethod,
                'discount_type' => $req->typeDiscount,
                'discount_price' => str_replace(",", '', $req->totalDiscountValue),
                'discount_percent' => str_replace(",", '', $req->totalDiscountPercent),
                'discount_sale' => str_replace(",", '', $req->totalSparePart)-str_replace(",", '', $req->totalDiscountValue),
                'item_price' => str_replace(",", '', $req->totalSparePart),
                'total_price' => str_replace(",", '', $req->totalPrice),
                'total_hpp' => $total_hpp,
                'total_profit_store' => $total_profit_store,
                'total_profit_sales' => $total_profit_sales,
                'total_profit_buyer' => $total_profit_buyer,
                'description' => $req->description,
                'updated_at' => date('Y-m-d h:i:s'),
                'updated_by' => Auth::user()->name,
            ]);

        // check data yang dihapus dan mengembalikan stock terlebih dahulu
        if ($req->deletedExistingData != null) {
            $checkDataDeleted = SaleDetail::whereIn('id', $req->deletedExistingData)->get();
            $checkStockDeleted = [];
            for ($i = 0; $i < count($checkDataDeleted); $i++) {
                $checkStockDeleted[$i] = Stock::where('item_id', $checkDataDeleted[$i]->item_id)
                    ->where('branch_id', $getEmployee->branch_id)
                    ->where('id', '!=', 1)
                    ->get();
                if ($checkDataDeleted[$i]->type == 'SparePart') {
                    $desc[$i] = '(Update Penjualan) Pengembalian Barang Pada Penjualan ' . $req->code;
                } else {
                    $desc[$i] = '(Update Penjualan) Pengembalian Barang Pada Penjualan ' . $req->code;
                }

                Stock::where('item_id', $checkDataDeleted[$i]->item_id)
                    ->where('branch_id', $getEmployee->branch_id)->update([
                        'stock'      => $checkStockDeleted[$i][0]->stock + $checkDataDeleted[$i]->qty,
                    ]);
                StockMutation::create([
                    'item_id'    => $checkDataDeleted[$i]->item_id,
                    'unit_id'    => $checkStockDeleted[$i][0]->unit_id,
                    'branch_id'  => $checkStockDeleted[$i][0]->branch_id,
                    'qty'        => $checkDataDeleted[$i]->qty,
                    'code'       => $req->code,
                    'type'       => 'In',
                    'description' => $desc[$i],
                    'created_by' => Auth::user()->name,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_by' => Auth::user()->name,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);
            }
            $destroyExistingData = DB::table('sale_details')->whereIn('id', $req->deletedExistingData)->delete();
        }

        // menyimpan data baru dan memperbarui stock
        if ($req->itemsDetail != null) {
            $checkStock = [];
            for ($i = 0; $i < count($req->itemsDetail); $i++) {
                $sharing_profit_store[$i] = (100 - ($req->profitSharingBuyer[$i] + $req->profitSharingSales[$i])) * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * $req->profitDetail[$i])) / 100;
                $sharing_profit_sales[$i] = $req->profitSharingSales[$i] * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * $req->profitDetail[$i])) / 100;
                $sharing_profit_buyer[$i] = $req->profitSharingBuyer[$i] * ((str_replace(",", '', $req->totalPriceDetail[$i])) - ($req->qtyDetail[$i] * $req->profitDetail[$i])) / 100;
                $tot_hpp[$i] = (str_replace(",", '', $req->profitDetail[$i])) * $req->qtyDetail[$i];

                SaleDetail::create([
                    'sale_id' => $id,
                    'item_id' => $req->itemsDetail[$i],
                    'sales_id' => $req->sales_id,
                    'buyer_id' => $req->buyerDetail[$i],
                    'sharing_profit_store' => $sharing_profit_store[$i],
                    'sharing_profit_sales' => $sharing_profit_sales[$i],
                    'sharing_profit_buyer' => $sharing_profit_buyer[$i],
                    'price' => str_replace(",", '', $req->priceDetail[$i]),
                    'qty' => $req->qtyDetail[$i],
                    'total' => str_replace(",", '', $req->totalPriceDetail[$i]),
                    'hpp' => str_replace(",", '', $req->profitDetail[$i]),
                    'total_hpp' => str_replace(",", '', $tot_hpp[$i]),
                    'description' => $req->descriptionDetail[$i],
                    'created_by' => Auth::user()->name,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_by' => Auth::user()->name,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);
                if ($req->typeDetail[$i] != 'Jasa') {
                    $checkStock[$i] = Stock::where('item_id', $req->itemsDetail[$i])
                        ->where('branch_id', $getEmployee->branch_id)
                        ->where('id', '!=', 1)
                        ->get();
                    if ($checkStock[$i][0]->stock < $req->qtyDetail[$i]) {
                        return Response::json([
                            'status' => 'fail',
                            'message' => 'Stock Item Ada yang 0. Harap Cek Kembali'
                        ]);
                    }
                    if ($req->typeDetail[$i] == 'SparePart') {
                        $desc[$i] = '(Update Penjualan) Pengeluaran Barang Pada Penjualan ' . $req->code;
                    } else {
                        $desc[$i] = '(Update Penjualan) Pengeluaran Barang Loss Pada Penjualan ' . $req->code;
                    }
                    Stock::where('item_id', $req->itemsDetail[$i])
                        ->where('branch_id', $getEmployee->branch_id)->update([
                            'stock'      => $checkStock[$i][0]->stock - $req->qtyDetail[$i],
                        ]);
                    StockMutation::create([
                        'item_id'    => $req->itemsDetail[$i],
                        'unit_id'    => $checkStock[$i][0]->unit_id,
                        'branch_id'  => $checkStock[$i][0]->branch_id,
                        'qty'        => $req->qtyDetail[$i],
                        'code'       => $this->code('PJT'),
                        'type'       => 'Out',
                        'description' => $desc[$i],
                        'created_by' => Auth::user()->name,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_by' => Auth::user()->name,
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]);
                }
            }
        }

        if ($req->itemsDetailOld != null) {
            $checkDataOld = SaleDetail::whereIn('id', $req->idDetailOld)->get();
            $checkStockExisting = [];

            for ($i = 0; $i < count($checkDataOld); $i++) {
                // memfilter type kecuali jasa
                if ($checkDataOld[$i]->item_id != 1) {
                    // return$checkDataOld[$i];
                    if ($req->typeDetailOld[$i] != 'Jasa') {
                        $checkStockExisting[$i] = Stock::where('item_id', $req->itemsDetailOld[$i])
                            ->where('branch_id', $getEmployee->branch_id)
                            ->where('id', '!=', 1)
                            ->get();

                        $checkStockExistingOlder[$i] = Stock::where('item_id', $checkDataOld[$i]->item_id)
                            ->where('branch_id', $getEmployee->branch_id)
                            ->where('id', '!=', 1)
                            ->get();

                            // mengecek kembali jika data item sama dengan data yang ada di sale_detail
                        if ($checkDataOld[$i]->item_id == $req->itemsDetailOld[$i]) {
                            // return 'masuk 2.1';
                            // mengecek kembali jika data QTY sama dengan data yang ada di sale_detail
                            if ($checkDataOld[$i]->qty == $req->qtyDetailOld[$i]) {
                                // return 'masuk 3.1';
                                // jika qty di sale_detail sama dengan QTY yang akan di update
                                if ($checkDataOld[$i]->type == $req->typeDetailOld[$i]) {
                                    // return 'masuk 4.1';
                                    // Jika Type sama maka tidak perlu melakukan update stock mutasi
                                } else {
                                    // Jika Type berbeda maka perlu melakukan update stock mutasi dengan type yaitu MUTATION
                                    // return 'masuk 4.2';
                                    $desc[$i] = '(Update Penjualan) Perubahan Barang Pada Penjualan ' . $req->code;

                                    StockMutation::create([
                                        'item_id'    => $req->itemsDetailOld[$i],
                                        'unit_id'    => $checkStockExisting[$i][0]->unit_id,
                                        'branch_id'  => $checkStockExisting[$i][0]->branch_id,
                                        'qty'        => $req->qtyDetailOld[$i],
                                        'code'       => $req->code,
                                        'type'       => 'Mutation',
                                        'description' => $desc[$i],
                                        'created_by' => Auth::user()->name,
                                        'created_at' => date('Y-m-d h:i:s'),
                                        'updated_by' => Auth::user()->name,
                                        'updated_at' => date('Y-m-d h:i:s'),
                                    ]);
                                }
                                // mengupdate sale detail jika item sama + qty sama + perubahan tipe sparepart / loss
                                SaleDetail::where('id', $req->idDetailOld[$i])->update([
                                    // 'item_id'=>$req->itemsDetailOld[$i],
                                    'price' => str_replace(",", '', $req->priceDetailOld[$i]),
                                    'sales_id' => $req->sales_id,
                                    'buyer_id' => $req->buyerDetailOld[$i],
                                    'sharing_profit_store' => $sharing_profit_storeOld[$i],
                                    'sharing_profit_sales' => $sharing_profit_salesOld[$i],
                                    'sharing_profit_buyer' => $sharing_profit_buyerOld[$i],
                                    // 'qty'=>$req->qtyDetailOld[$i],
                                    'total' => str_replace(",", '', $req->totalPriceDetailOld[$i]),
                                    'hpp' => str_replace(",", '', $req->profitDetailOld[$i]),
                                    'total_hpp' => str_replace(",", '', $hppOld[$i]),
                                    'description' => $req->descriptionDetailOld[$i],
                                    // 'type' =>$req->typeDetailOld[$i],
                                    'updated_by' => Auth::user()->name,
                                    'updated_at' => date('Y-m-d h:i:s'),
                                ]);
                            } else {
                                // return 'masuk 3.2';
                                // jika qty di sale_detail berbeda dengan QTY yang akan di update
                                // return $checkDataOld;
                                if ($req->typeDetailOld[$i] == 'SparePart') {
                                    $descPengembalian[$i] = '(Update Penjualan) Pengembalian Barang Pada Penjualan ' . $req->code;
                                } else {
                                    $descPengembalian[$i] = '(Update Penjualan) Pengembalian Barang Loss Pada Penjualan ' . $req->code;
                                }
                                StockMutation::create([
                                    'item_id'    => $req->itemsDetailOld[$i],
                                    'unit_id'    => $checkStockExisting[$i][0]->unit_id,
                                    'branch_id'  => $checkStockExisting[$i][0]->branch_id,
                                    'qty'        => $checkDataOld[$i]->qty,
                                    'code'       => $req->code,
                                    'type'       => 'In',
                                    'description' => $descPengembalian[$i],
                                    'created_by' => Auth::user()->name,
                                    'created_at' => date('Y-m-d h:i:s'),
                                    'updated_by' => Auth::user()->name,
                                    'updated_at' => date('Y-m-d h:i:s'),
                                ]);

                                // Pegeluaran atas data item yang dirubah
                                if ($req->typeDetailOld[$i] == 'SparePart') {
                                    $descPengeluaran[$i] = '(Update Penjualan) Pengeluaran Barang Pada Penjualan ' . $req->code;
                                } else {
                                    $descPengeluaran[$i] = '(Update Penjualan) Pengeluaran Barang Loss Pada Penjualan ' . $req->code;
                                }
                                StockMutation::create([
                                    'item_id'    => $req->itemsDetailOld[$i],
                                    'unit_id'    => $checkStockExisting[$i][0]->unit_id,
                                    'branch_id'  => $checkStockExisting[$i][0]->branch_id,
                                    'qty'        => $req->qtyDetailOld[$i],
                                    'code'       => $req->code,
                                    'type'       => 'Out',
                                    'description' => $descPengeluaran[$i],
                                    'created_by' => Auth::user()->name,
                                    'created_at' => date('Y-m-d h:i:s'),
                                    'updated_by' => Auth::user()->name,
                                    'updated_at' => date('Y-m-d h:i:s'),
                                ]);

                                Stock::where('item_id', $checkDataOld[$i]->item_id)
                                    ->where('branch_id', $getEmployee->branch_id)->update([
                                        'stock'      => $checkStockExisting[$i][0]->stock + $checkDataOld[$i]->qty - $req->qtyDetailOld[$i],
                                    ]);

                                SaleDetail::where('id', $req->idDetailOld[$i])->update([
                                    // 'item_id'=>$req->itemsDetailOld[$i],
                                    'sales_id' => $req->sales_id,
                                    'buyer_id' => $req->buyerDetailOld[$i],
                                    'sharing_profit_store' => $sharing_profit_storeOld[$i],
                                    'sharing_profit_sales' => $sharing_profit_salesOld[$i],
                                    'sharing_profit_buyer' => $sharing_profit_buyerOld[$i],
                                    'price' => str_replace(",", '', $req->priceDetailOld[$i]),
                                    'qty' => $req->qtyDetailOld[$i],
                                    'total' => str_replace(",", '', $req->totalPriceDetailOld[$i]),
                                    'hpp' => str_replace(",", '', $req->profitDetailOld[$i]),
                                    'total_hpp' => str_replace(",", '', $hppOld[$i]),
                                    'description' => str_replace(",", '', $req->descriptionDetailOld[$i]),
                                    'updated_by' => Auth::user()->name,
                                    'updated_at' => date('Y-m-d h:i:s'),
                                ]);
                            }
                        } else {
                            // return 'masuk 2.2';
                            // pengembalian stock atas item sale_detail yang dirubah

                            if ($checkStockExistingOlder[$i][0]->item_id == $checkDataOld[$i]->item_id) {
                                Stock::where('item_id', $checkDataOld[$i]->item_id)
                                    ->where('branch_id', $getEmployee->branch_id)->update([
                                        'stock'      => $checkStockExistingOlder[$i][0]->stock + $checkDataOld[$i]->qty,
                                    ]);
                            }

                            if ($checkDataOld[$i]->type == 'SparePart') {
                                $descPengembalian[$i] = '(Update Penjualan) Pengembalian Barang Pada Penjualan ' . $req->code;
                            } else {
                                $descPengembalian[$i] = '(Update Penjualan) Pengembalian Barang Loss Pada Penjualan ' . $req->code;
                            }
                            StockMutation::create([
                                'item_id'    => $checkDataOld[$i]->item_id,
                                'unit_id'    => $checkStockExisting[$i][0]->unit_id,
                                'branch_id'  => $checkStockExisting[$i][0]->branch_id,
                                'qty'        => $checkDataOld[$i]->qty,
                                'code'       => $req->code,
                                'type'       => 'In',
                                'description' => $descPengembalian[$i],
                                'created_by' => Auth::user()->name,
                                'created_at' => date('Y-m-d h:i:s'),
                                'updated_by' => Auth::user()->name,
                                'updated_at' => date('Y-m-d h:i:s'),
                            ]);

                            // Pegeluaran atas data item yang dirubah
                            if ($req->typeDetailOld[$i] == 'SparePart') {
                                $descPengeluaran[$i] = '(Update Penjualan) Pengeluaran Barang Pada Penjualan ' . $req->code;
                            } else {
                                $descPengeluaran[$i] = '(Update Penjualan) Pengeluaran Barang Loss Pada Penjualan ' . $req->code;
                            }
                            Stock::where('item_id', $req->itemsDetailOld[$i])
                                ->where('branch_id', $getEmployee->branch_id)->update([
                                    'stock'      => $checkStockExisting[$i][0]->stock - $req->qtyDetailOld[$i],
                                ]);
                            StockMutation::create([
                                'item_id'    => $req->itemsDetailOld[$i],
                                'unit_id'    => $checkStockExisting[$i][0]->unit_id,
                                'branch_id'  => $checkStockExisting[$i][0]->branch_id,
                                'qty'        => $req->qtyDetailOld[$i],
                                'code'       => $req->code,
                                'type'       => 'Out',
                                'description' => $descPengeluaran[$i],
                                'created_by' => Auth::user()->name,
                                'created_at' => date('Y-m-d h:i:s'),
                                'updated_by' => Auth::user()->name,
                                'updated_at' => date('Y-m-d h:i:s'),
                            ]);

                            SaleDetail::where('id', $req->idDetailOld[$i])->update([
                                'item_id' => $req->itemsDetailOld[$i],
                                'sales_id' => $req->sales_id,
                                'buyer_id' => $req->buyerDetailOld[$i],
                                'sharing_profit_store' => $sharing_profit_storeOld[$i],
                                'sharing_profit_sales' => $sharing_profit_salesOld[$i],
                                'sharing_profit_buyer' => $sharing_profit_buyerOld[$i],
                                'price' => str_replace(",", '', $req->priceDetailOld[$i]),
                                'qty' => $req->qtyDetailOld[$i],
                                'total' => str_replace(",", '', $req->totalPriceDetailOld[$i]),
                                'hpp' => str_replace(",", '', $req->profitDetailOld[$i]),
                                'total_hpp' => str_replace(",", '', $hppOld[$i]),
                                'description' => str_replace(",", '', $req->descriptionDetailOld[$i]),
                                'updated_by' => Auth::user()->name,
                                'updated_at' => date('Y-m-d h:i:s'),
                            ]);
                        }
                    }
                }
            }
        }

        // Jurnal
        $idJ = Journal::where('ref', $req->code)->first('id');
        $idJournal = DB::table('journals')->max('id') + 1;
        // Delete Jurnal
        Journal::where('ref', $req->code)->delete();
        JournalDetail::where('journal_id', $idJ)->delete();

        Journal::create([
            'id' => $idJournal,
            'code' => $this->code('DD'),
            'year' => date('Y'),
            'date' => date('Y-m-d'),
            'type' => 'Penjualan',
            'total' => str_replace(",", '', $req->totalPrice),
            'ref' => $req->code,
            'description' => 'Transaksi Penjualan ' .$req->code,
            'created_at' => date('Y-m-d h:i:s'),
        ]);

        if ($req->type == 'DownPayment') {
        } else {
            $accountData  = AccountData::where('branch_id', $getEmployee->branch_id)
                ->where('active', 'Y')
                ->where('main_id', 5)
                ->where('main_detail_id', 27)
                ->first();

            $accountDiskon  = AccountData::where('branch_id', $getEmployee->branch_id)
                ->where('active', 'Y')
                ->where('main_id', 8)
                ->where('main_detail_id', 30)
                ->first();

            $accountPembayaran  = AccountData::where('id', $req->account)
                ->first();

            if (str_replace(",", '', $req->totalDiscountValue) == 0) {
                $accountCode = [
                    $accountPembayaran->id,
                    $accountData->id,
                ];
                $description = [
                    'Kas Pendapatan Penjualan ' .$req->code,
                    'Pendapatan Penjualan ' .$req->code,
                ];
                $totalBayar = [
                    str_replace(",", '', $req->totalPrice),
                    str_replace(",", '', $req->totalPrice),
                ];
                $DK = [
                    'D',
                    'K',
                ];
            } else {
                $accountCode = [
                    $accountPembayaran->id,
                    $accountDiskon->id,
                    $accountData->id,
                ];
                $totalBayar = [
                    str_replace(",", '', $req->totalPrice),
                    str_replace(",", '', $req->totalDiscountValue),
                    str_replace(",", '', $req->totalSparePart),
                ];
                $description = [
                    'Kas Pendapatan Penjualan ' .$req->code,
                    'Diskon Penjualan ' .$req->code,
                    'Pendapatan Penjualan ' .$req->code,
                ];
                $DK = [
                    'D',
                    'D',
                    'K',
                ];
            }

            for ($i = 0; $i < count($accountCode); $i++) {
                $idDetail = DB::table('journal_details')->max('id') + 1;
                JournalDetail::create([
                    'id' => $idDetail,
                    'journal_id' => $idJournal,
                    'account_id' => $accountCode[$i],
                    'total' => $totalBayar[$i],
                    'description' => $description[$i],
                    'debet_kredit' => $DK[$i],
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);
            }

            //Jurnal HPP
            $idJournalHpp = DB::table('journals')->max('id') + 1;
            Journal::create([
                'id' => $idJournalHpp,
                'code' => $this->code('KK', $idJournalHpp),
                'year' => date('Y'),
                'date' => date('Y-m-d'),
                'type' => 'Biaya',
                'total' => str_replace(",", '', $total_hpp),
                'ref' => $req->code,
                'description' => 'HPP ' . $req->code,
                'created_at' => date('Y-m-d h:i:s'),
            ]);

            $accountPersediaan  = AccountData::where('branch_id', $getEmployee->branch_id)
                ->where('active', 'Y')
                ->where('main_id', 3)
                ->where('main_detail_id', 11)
                ->first();

            $accountBiayaHpp  = AccountData::where('branch_id', $getEmployee->branch_id)
                ->where('active', 'Y')
                ->where('main_id', 7)
                ->where('main_detail_id', 29)
                ->first();

            // JURNAL HPP
            $accountCodeHpp = [
                $accountBiayaHpp->id,
                $accountPersediaan->id,
            ];
            // return $accountCodeHpp;
            $total_hpp = [
                str_replace(",", '', $total_hpp),
                str_replace(",", '', $total_hpp),
            ];
            $descriptionHpp = [
                'Pengeluaran Harga Pokok Penjualan ' . $req->code,
                'Biaya Harga Pokok Penjualan ' . $req->code,
            ];
            $DKHpp = [
                'D',
                'K',
            ];
            for ($i = 0; $i < count($accountCodeHpp); $i++) {
                if ($total_hpp[$i] != 0) {
                    $idDetailhpp = DB::table('journal_details')->max('id') + 1;
                    JournalDetail::create([
                        'id' => $idDetailhpp,
                        'journal_id' => $idJournalHpp,
                        'account_id' => $accountCodeHpp[$i],
                        'total' => $total_hpp[$i],
                        'description' => $descriptionHpp[$i],
                        'debet_kredit' => $DKHpp[$i],
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]);
                }
            }
        }

        DB::commit();
        return Response::json(['status' => 'success', 'message' => 'Data Tersimpan']);
        } catch (\Throwable $th) {
            throw $th;
            DB::rollback();
            return Response::json(['status' => 'error','message'=> $th->getMessage()]);
        }
    }

    public function destroy(Request $req, $id)
    {
        // stok, sale detail, jurnal, jurnal detail, sale.
        $checkRoles = $this->DashboardController->cekHakAkses(5,'delete');
        if($checkRoles == 'akses ditolak'){
            return view('forbidden');
        }
        DB::beginTransaction();
        try {
            $getEmployee = Employee::where('user_id', Auth::user()->id)->first();
            $sale = Sale::where('id', $id)->first();
            $branch = Sale::where('id', $id)->first('branch_id');
            $checkDataDeleted = SaleDetail::where('sale_id', $id)->get();
            $idJournal = Journal::where('ref', $sale->code)->first('id');
            $journalDetail = JournalDetail::where('journal_id', $idJournal->id)->get();
            $checkStockDeleted = [];
            for ($i = 0; $i < count($checkDataDeleted); $i++) {
                if ($checkDataDeleted[$i]->item_id != 1) {
                    $checkStockDeleted[$i] = Stock::where('item_id', $checkDataDeleted[$i]->item_id)
                        ->where('branch_id', $branch->branch_id)
                        ->where('id', '!=', 1)
                        ->get();

                    if ($checkDataDeleted[$i]->type == 'SparePart') {
                        $desc[$i] = '(Delete Penjualan) Pengembalian Barang Pada Penjualan ' . $sale->code;
                    } else {
                        $desc[$i] = '(Delete Penjualan) Pengembalian Barang Loss Pada Penjualan ' . $sale->code;
                    }
                    // return $desc;
                    Stock::where('item_id', $checkDataDeleted[$i]->item_id)
                        ->where('branch_id', $branch->branch_id)
                        ->update([
                            'stock' => $checkStockDeleted[$i][0]->stock + $checkDataDeleted[$i]->qty,
                        ]);
                    StockMutation::create([
                        'item_id' => $checkDataDeleted[$i]->item_id,
                        'unit_id' => $checkStockDeleted[$i][0]->unit_id,
                        'branch_id' => $checkStockDeleted[$i][0]->branch_id,
                        'qty' => $checkDataDeleted[$i]->qty,
                        'code' => $sale->code,
                        'type' => 'In',
                        'description' => $desc[$i],
                    ]);
                }
            }
            // return $branch;
            // return $checkDataDeleted;
            // return $checkStockDeleted;
            DB::table('sales')->where('id', $id)->delete();
            DB::table('sale_details')->where('sale_id', $id)->delete();
            DB::table('journal_details')->where('journal_id', $idJournal->id)->delete();
            DB::table('journals')->where('ref', $sale->code)->delete();
            DB::commit();

            return Response::json(['status' => 'success', 'message' => 'Data Tersimpan']);
        } catch (\Throwable $th) {
            DB::rollback();

            return $th;
            return Response::json(['status' => 'error', 'message' => $th]);
        }
    }

    public function printSale($id)
    {
        $sale = Sale::with('SaleDetail', 'Sales', 'SaleDetail.Item', 'SaleDetail.Item.Warranty', 'SaleDetail.Item.Brand', 'SaleDetail.Item.Brand.Category', 'CreatedByUser')->find($id);
        $member = User::get();

        return view('pages.backend.transaction.sale.printSales', ['sale' => $sale, 'member' => $member]);
    }

    public function printSmallSale($id)
    {
        $sale = Sale::with('SaleDetail', 'Sales', 'SaleDetail.Item', 'SaleDetail.Item.Brand', 'SaleDetail.Item.Brand.Category', 'CreatedByUser')->find($id);
        $member = User::get();

        return view('pages.backend.transaction.sale.printSmallSale', ['sale' => $sale, 'member' => $member]);
    }

    public function checkjournals(Request $req)
    {
        $data = Journal::with('JournalDetail.AccountData')
            ->where('ref',$req->id)
            ->get();

        return Response::json(['status' => 'success', 'jurnal'=>$data]);
    }

    public function getPaymentMethod()
    {
        $data = array();
        $branchID = Auth::user()->employee->branch_id;
        $cash = AccountData::with('AccountMainDetail')
            ->Where(function ($query, $branchID) {
                $query->AccountMainDetail->where('branch_id', $branchID);
            })
            ->orWhere(function ($query, $branchID) {
                $query->AccountMainDetail->where('branch_id', $branchID);
            })
            ->orWhere(function ($query, $branchID) {
                $query->AccountMainDetail->where('name', 'like', 'Kas Kecil%');
            })
            ->orWhere(function ($query, $branchID) {
                $query->AccountMainDetail->where('name', 'like', 'Kas Besar%');
            })
            ->get();

        foreach ($cash as $c) {
            array_push($data, $c->AccountMainDetail->name);
        }

        dd($data);
    }
}
