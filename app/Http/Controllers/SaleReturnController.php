<?php

namespace App\Http\Controllers;

use App\Models\AccountData;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;


class SaleReturnController extends Controller
{
    public function __construct(DashboardController $DashboardController)
    {
        $this->middleware('auth');
        $this->DashboardController = $DashboardController;
    }

    public function index(Request $req)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(6,'view');

        if($checkRoles == 'akses ditolak'){
            return Response::json(['status' => 'restricted', 'message' => 'Kamu Tidak Boleh Mengakses Fitur Ini :)']);
        }

        if ($req->ajax()) {
            $data = SaleReturn::with('Sale', 'SaleReturnDetail')->get();
            return Datatables::of($data)
                ->addColumn('code', function ($row) {
                    return $row->code;
                })
                ->addColumn('faktur', function ($row) {
                    return $row->Sale->code;
                })
                ->addColumn('name', function ($row) {
                    $html = '<table>';
                    foreach ($row->SaleReturnDetail as $i) {
                        $html .= '<tr><th>';
                        $html .= Item::find($i->item_id)->name;
                        $html .= '</th></tr>';
                    }
                    $html .= '</table>';

                    return $html;
                })
                ->addColumn('type', function ($row) {
                    $html = '<table>';
                    foreach ($row->SaleReturnDetail as $i) {
                        switch ($i->type) {
                            case 1:
                                $data = "Diservice";
                            case 2:
                                $data = "Diganti Baru";
                            case 3:
                                $data = "Tukar Tambah";
                            case 4:
                                $data = "Diganti Uang";
                            case 5:
                                $data = "Diganti Barang Lain";
                        }
                        $html .= '<tr><th>';
                        $html .= $data;
                        $html .= '</th></tr>';
                    }
                    $html .= '</table>';

                    return $html;
                })
                ->addColumn('desc', function ($row) {
                    return $row->desc;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a class="btn btn-primary btn-block" target="_blank" href="' . route('sale.return.print', $row->id) . '">';
                    $actionBtn .= '<i class="fas fa-print"></i> Nota Besar</a>';
                    $actionBtn .= '<a class="btn btn-primary btn-block" target="_blank" href="' . route('sale.return.printSmall', $row->id) . '">';
                    $actionBtn .= '<i class="fas fa-print"></i> Nota Kecil</a>';
                    return $actionBtn;
                })

                ->rawColumns(['code', 'faktur', 'name', 'type', 'desc', 'action'])
                ->make(true);
        }
        return view('pages.backend.transaction.sale.return.indexReturn');
    }

    public function code($type)
    {
        $getEmployee =  Employee::with('branch')->where('user_id', Auth::user()->id)->first();
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('y');
        $index = DB::table('sale_return')->max('id') + 1;

        $index = str_pad($index, 3, '0', STR_PAD_LEFT);
        return $code = $type . $getEmployee->Branch->code . $year . $month . $index;
    }

    public function create()
    {
        $checkRoles = $this->DashboardController->cekHakAkses(6,'create');
        if($checkRoles == 'akses ditolak'){
            return view('forbidden');
        }

        $code = $this->code('RTP');
        $item = SaleDetail::with('Sale', 'Item')->get();
        $sale = Sale::with('SaleDetail')->get();
        $account  = AccountData::with('AccountMain', 'AccountMainDetail', 'Branch')->get();
        $userBranch = Auth::user()->employee->branch_id;
        // $sales = Employee::where('id', '!=', '1')->where('branch_id', '=', $userBranch)->orderBy('name', 'asc')->get();
        // $buyer = Employee::where('id', '!=', '1')->where('branch_id', '=', $userBranch)->orderBy('name', 'asc')->get();
        // $cash = Cash::get();
        // $customer = Customer::where('branch_id', '=', $userBranch)->orderBy('name', 'asc')->get();
        $stock = Stock::where('branch_id', '=', $userBranch)->where('item_id', '!=', 1)->get();
        $actionDetail = null;
        // $barang = Sale::where('id', 1)->with('SaleDetail')->get();
        $barang = Sale::rightJoin('sale_details', 'sales.id', 'sale_details.sale_id')
        ->join('items', 'sale_details.item_id', 'items.id')
        ->join('brands', 'items.brand_id', 'brands.id')
        ->select('*', 'brands.name as brand_name', 'items.name as item_name')
        ->get();

        return view('pages.backend.transaction.sale.return.createReturn', [
            'code' => $code,
            'item' => $item,
            'sale' => $sale,
            'account' => $account,
            'stock' => $stock,
            'actionDetail' => $actionDetail,
            'barang' => $barang,
        ]);
    }

    public function loadDataItem(Request $req)
    {
        $id = $req->saleId;
        $query = Sale::where('id', $id)->with('SaleDetail')->first();
        // echo '<option value="'.$value->item_id.'">' . $value->item->brand->name .' '. $value->item->name . '</option>';
        echo '<option value="">- Select -</option>';
        foreach ($query->saleDetail as $key => $value) {
            echo '<option value="'.$value->id.'">' . $value->item->brand->name .' '. $value->item->name . '</option>';
        }
    }
    public function loadDataItemAll(Request $req)
    {
        $output = SaleDetail::find($req->item);
        echo '<label for="">Qty</label><code>*</code><input class="form-control" type="text" value="'.$output->qty.'">';
        // echo '<div class="form-group col-md-4 col-xs-12" id="qtyForm"><label for="">Qty</label><code>*</code><input class="form-control" type="text" value="'.$output->qty.'"></div>';
        // $output = Sale::where('code', 'PJT0112112002')->first();
        // $item = Item::find($req->item);
        // dd("masuk");
    }

    public function loadDataQty()
    {

    }
    public function loadAction()
    {

    }

    public function store(Request $req)
    {
        // Validator
        foreach ($req->items as $d) {
            if ($d == null) {
                return Response::json([
                    'status' => 'error',
                    'data' => array("Ada data yang kosong, pilih terlebih dahulu!")
                ]);
            }
        }

        foreach ($req->type as $t) {
            if ($t == null) {
                return Response::json([
                    'status' => 'error',
                    'data' => array("Ada perlakuan barang yang kosong, pilih terlebih dahulu!")
                ]);
            }
        }

        if (count(array_unique($req->items)) < count($req->items)) {
            // Array has duplicates
            return Response::json([
                'status' => 'error',
                'data' => array("Data Barang Ada Yang Sama")
            ]);
        }

        foreach ($req->items as $d) {
            // Initialization
            $warranty = Item::with('warranty')
                ->find($d)->warranty;
            // Mengambil Tanggal Faktur Dikeluarkan
            $date = Carbon::parse(Sale::find($req->item)->date);
            // Mengambil Jarak Garansi
            $dayWarranty = $this->getDayWarranty($warranty->name, $warranty->periode);
            // Mengambil Tanggal Garansi
            $warranty = $date->addDays($dayWarranty);
            // Check Garansi
            if (Carbon::now()->diffAsCarbonInterval($warranty)->d > $dayWarranty) {
                return Response::json([
                    'status' => 'error',
                    'data' => array(
                        "Barang " . Item::find($d)->name . " tidak bisa di return, karena melewati masa garansi"
                    )
                ]);
            }
        }

        $data = array();
        // Check Return
        foreach ($req->type as $index => $t) {
            switch ($t) {
                    // Service
                case 1:
                    // return Response::json([
                    //     'status' => 'loss',
                    //     'data' => "Barang akan diservice dan barang yang digantikan akan dijadikan barang loss sales!"
                    // ]);
                    break;
                case 2:
                    // Ganti Baru
                    // Sedangkan ssd rusak iku maeng akan di return ng supplier. Dadi mutasi barang ssd dengan keterangan barang direturn ng supplier.
                    // return Response::json([
                    //     'status' => 'new',
                    //     'data' => "Barang akan diganti baru dan barang lama akan di return ke supplier!"
                    // ]);
                    break;
                case 3:
                    // Tukar Tambah
                    break;
                case 4:
                    // Diganti Uang
                    // return Response::json([
                    //     'status' => 'money',
                    //     'data' => "Barang akan direturn menggunakan uang!"
                    // ]);
                    break;
                case 5:
                    // Diganti Barang Lain
                    // return Response::json([
                    //     'status' => 'att',
                    //     'data' => "Barang akan diganti sesuai keinginan dan barang lama akan dibeli toko dan masuk ke dalam stok!"
                    // ]);
                    break;
            }
        }
        // array_push($data, (object)[
        //     'id_item' => $i->id,
        //     'name_item' => $i->name,
        //     'qty' => $s->qty,
        //     'price' => $s->total,
        //     'sales_id' => $s->sales_id,
        //     'buyer_id' => $s->buyer_id,
        //     'sp_buyer' => $s->sharing_profit_sales,
        //     'sp_sales' => $s->sharing_profit_sales,
        //     'dsc' => $s->description,
        //     'sale' => $req->sale
        // ]);

        // $id = DB::table('sale_return')->max('id') + 1;
        // SaleReturn::create([
        //     'id' => $id,
        //     'code' => $this->DashboardController->createCode('RTNP', 'sale_return'),
        //     'sale_id' => $req->item,
        //     'branch_id' => Employee::where('user_id', Auth::user()->id)->first()->branch_id,
        //     'desc' => $req->description,
        //     'created_at' => date('Y-m-d h:i:s'),
        //     'created_by' => Auth::user()->name,
        // ]);

        // foreach ($req->data_item as $index => $d) {
        //     SaleReturnDetail::create([
        //         'sale_return_id' => $id,
        //         'item_id' => $d,
        //         'type' => $req->type[$index],
        //         'created_at' => date('Y-m-d h:i:s'),
        //         'created_by' => Auth::user()->name,
        //     ]);
        // }

        return Response::json([
            'status' => 'success',
            'result' => $data
        ]);
    }

    public function show()
    {
    }

    public function edit()
    {
    }

    public function update()
    {
    }

    public function destroy()
    {
    }

    public function getData(Request $req)
    {
        $sale = Sale::with('SaleDetail')->find($req->item_id);
        $data = array();
        $discountType = $sale->discount_type;
        $discount = $discountType == "percent" ? $sale->discount_percent
            : $sale->discount_price;

        $customer = '<div class="row"><div class="form-group col-12 col-md-6 col-lg-6"><label>Nama Customer</label>';
        $customer .= '<p>' . $sale->customer_name . '</p>';
        $customer .= '</div><div class="form-group col-12 col-md-6 col-lg-6"><label for="type">Alamat & No Telepon</label>';
        $customer .= '<p>' . $sale->customer_address . ' <br> ' . $sale->customer_phone . ' </p></div></div></div>';

        foreach ($sale->SaleDetail as $s) {
            foreach (Item::all() as $i) {
                if ($s->item_id == $i->id) {
                    array_push($data, (object)[
                        'id_item' => $i->id,
                        'name_item' => $i->name,
                        'qty' => $s->qty,
                        'price' => $s->total,
                        'sales_id' => $s->sales_id,
                        'buyer_id' => $s->buyer_id,
                        'sp_buyer' => $s->sharing_profit_sales,
                        'sp_sales' => $s->sharing_profit_sales,
                        'dsc' => $s->description,
                    ]);
                }
            }
        }

        $data = [
            'date' => Carbon::parse($sale->date)->format('d F Y'),
            'total' => number_format($sale->total_price),
            'operator' => User::find($sale->user_id)->name,
            'sale' => $sale->id,
            'discount_type' => $discountType,
            'discount' => $discount,
            'customer' => $customer,
            'data' => $data
        ];

        return Response::json([
            'status' => 'success',
            'result' => $data
        ]);
    }

    public function add(Request $req)
    {
        $sale = Sale::with('SaleDetail')->find($req->sale);
        $data = array();

        foreach ($sale->SaleDetail as $s) {
            foreach (Item::all() as $i) {
                if ($s->item_id == $i->id) {
                    array_push($data, (object)[
                        'id_item' => $i->id,
                        'name_item' => $i->name,
                        'qty' => $s->qty,
                        'price' => $s->total,
                        'sales_id' => $s->sales_id,
                        'buyer_id' => $s->buyer_id,
                        'sp_buyer' => $s->sharing_profit_sales,
                        'sp_sales' => $s->sharing_profit_sales,
                        'dsc' => $s->description,
                        'sale' => $req->sale
                    ]);
                }
            }
        }

        return Response::json([
            'status' => 'success',
            'result' => $data
        ]);
    }

    public function getDetail(Request $req)
    {
        $saleDetail = SaleDetail::where('sale_id', $req->sale)
            ->where('item_id', $req->item_id)
            ->first();

        $data = [
            'qty' => $saleDetail->qty,
            'total' => number_format($saleDetail->total),
            'taker' => User::find($saleDetail->sales_id)->name,
            'seller' => User::find($saleDetail->buyer_id)->name,
            'sp_taker' => $saleDetail->sharing_profit_sales,
            'sp_seller' => $saleDetail->sharing_profit_buyer,
            'desc' => $saleDetail->description,
        ];

        return Response::json([
            'status' => 'success',
            'result' => $data
        ]);
    }

    function getDayWarranty($type, $periode)
    {
        if ($type == 'Minggu') {
            $day = 7;
        } elseif ($type == 'Bulan') {
            $day = 30;
        }
        return $day + $periode;
    }

    public function getType($type)
    {
        switch ($type) {
                // Service
            case 1:
                return Response::json([
                    'status' => 'loss',
                    'data' => "Barang akan diservice dan barang yang digantikan akan dijadikan barang loss sales!"
                ]);
                break;
            case 2:
                // Ganti Baru
                // Sedangkan ssd rusak iku maeng akan di return ng supplier. Dadi mutasi barang ssd dengan keterangan barang direturn ng supplier.
                return Response::json([
                    'status' => 'new',
                    'data' => "Barang akan diganti baru dan barang lama akan di return ke supplier!"
                ]);
                break;
            case 3:
                // Tukar Tambah
                break;
            case 4:
                // Diganti Uang
                return Response::json([
                    'status' => 'money',
                    'data' => "Barang akan direturn menggunakan uang!"
                ]);
                break;
            case 5:
                // Diganti Barang Lain
                return Response::json([
                    'status' => 'att',
                    'data' => "Barang akan diganti sesuai keinginan dan barang lama akan dibeli toko dan masuk ke dalam stok!"
                ]);
                break;
        }
    }

    function storedReturn($sale, $item, $type, $dsc)
    {
        SaleReturn::create([
            'sale_id' => $sale,
            'item_id' => $item,
            'type' => $type,
            'desc' => $dsc,
            'created_by' => Auth::user()->name,
            'updated_by' => '',
            'deleted_by' => ''
        ]);
    }

    // Tindakan Ketika Return
    public function dataLoss()
    {
    }

    public function toSupplier()
    {
    }

    public function toStock()
    {
    }

    public function printReturn($id)
    {
        $return = SaleReturn::with('Sale', 'SaleReturnDetail')
            ->find($id);
        return view('pages.backend.transaction.sale.return.printReturn', [
            'return' => $return
        ]);
    }

    public function printSmallReturn($id)
    {
        $return = SaleReturn::with('Sale', 'SaleReturnDetail')
            ->find($id);
        return view('pages.backend.transaction.sale.return.printSmallReturn', [
            'return' => $return
        ]);
    }
}
