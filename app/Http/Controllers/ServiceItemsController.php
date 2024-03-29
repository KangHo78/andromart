<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Item;
use App\Models\Type;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Brand;
use App\Models\StockMutation;
use App\Models\Employee;
use App\Models\Service;
use App\Models\ServiceEquipment;
use App\Models\ServiceCondition;
use App\Models\Journal;
use App\Models\AccountData;
use App\Models\JournalDetail;
use App\Models\Warranty;
use App\Models\SettingPresentase;
use App\Models\ServiceDetail;
use App\Models\ServiceStatusMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Carbon\carbon;

class ServiceItemsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DashboardController $DashboardController)
    {
        $this->middleware('auth');
        $this->DashboardController = $DashboardController;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $req)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(51, 'view');
        if ($checkRoles == 'akses ditolak') {
            return view('forbidden');
        }
        // return new User::akses();

        if ($req->ajax()) {
            $data = Service::with(['Employee1', 'Employee2', 'CreatedByUser', 'Type', 'Brand','Items','Buyer'])
                ->whereNotNull('group_service')
                ->orderBy('id', 'DESC')
                // ->where('technician_id',Auth::user()->id)
                ->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->order(function ($query) {
                    if (request()->has('id')) {
                        $query->orderBy('id', 'desc');
                    }
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<div class="btn-group">';
                    $actionBtn .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                            data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button><div class="dropdown-menu">';
                    $actionBtn .= '<a class="dropdown-item" href="' . route('service-items.edit', $row->id) . '"><i class="far fa-edit"></i> Edit</a>';
                    $actionBtn .= '<a onclick="del(' . $row->id . ')" class="dropdown-item" style="cursor:pointer;"><i class="far fa-trash-alt"></i> Hapus</a>';
                    $actionBtn .= '<a onclick="jurnal('."'$row->code'".')" class="dropdown-item" style="cursor:pointer;"><i class="far fa-file"></i> Jurnal</a>';
                  
                    $actionBtn .= '<a class="dropdown-item" href="' . route('service-items.printService', $row->id) . '"><i class="fas fa-print"></i> Cetak</a>';
                    $actionBtn .= '</div></div>';
                    return $actionBtn;
                })
                ->addColumn('Informasi', function ($row) {
                    $htmlAdd = '<table>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Kode</td>';
                    $htmlAdd .= '<th>';
                    $htmlAdd .= '<a href="' . route('service.show', $row->id) . '">' . $row->code . '</a>';
                    $htmlAdd .= '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Tgl Dibuat</td>';
                    $htmlAdd .=
                        '<th>' .
                        Carbon::parse($row->created_at)
                            ->locale('id')
                            ->isoFormat('LL') .
                        '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Tgl </td>';
                    $htmlAdd .=
                        '<th>' .
                        Carbon::parse($row->date)
                            ->locale('id')
                            ->isoFormat('LL') .
                        '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Di Input </td>';
                    $htmlAdd .= '<th>' . $row->created_by . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Teknisi 1</td>';
                    $htmlAdd .= '<th>' . $row->Employee1->name . '</th>';
                    $htmlAdd .= '</tr>';
                    if ($row->technician_replacement_id != null) {
                        $htmlAdd .= '<tr>';
                        $htmlAdd .= '<td>Teknisi 2</td>';
                        $htmlAdd .= '<th>' . $row->Employee2->name . '</th>';
                        $htmlAdd .= '</tr>';
                    }
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })
                ->addColumn('dataBuyer', function ($row) {
                    $htmlAdd = '<table>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<th>' . $row->Buyer->name . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })
                ->addColumn('dataItem', function ($row) {
                    $htmlAdd = '<table>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Merk</td>';
                    $htmlAdd .= '<th>' . $row->Brand->name . '</th>';
                    $htmlAdd .= '<td>Harga Beli</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_price_buy, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Items</td>';
                    $htmlAdd .= '<th>' . $row->Items->name . '</th>';
                    $htmlAdd .= '<td>Total Service/Jasa</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_price, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Kategori</td>';
                    $htmlAdd .= '<th>' . $row->Brand->Category->name . '</th>';
                    $htmlAdd .= '<td>Harga Jual</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_price_sell, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>IMEI</td>';
                    $htmlAdd .= '<th>' . $row->no_imei . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Keluhan</td>';
                    $htmlAdd .= '<th>' . $row->complaint . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })
                ->addColumn('finance', function ($row) {
                    $htmlAdd = '<table>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Service</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_service, 0, '.', ',') . '</th>';
                    $htmlAdd .= '<td>S.P Toko</td>';
                    $htmlAdd .= '<th>' . number_format($row->sharing_profit_store, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Part</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_part, 0, '.', ',') . '</th>';
                    $htmlAdd .= '<td>S.P Teknisi</td>';
                    $htmlAdd .= '<th>' . number_format($row->sharing_profit_technician_1, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Lalai</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_loss, 0, '.', ',') . '</th>';
                    $htmlAdd .= '<td>S.P Teknisi 2</td>';
                    $htmlAdd .= '<th>' . number_format($row->sharing_profit_technician_2, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Diskon</td>';
                    $htmlAdd .= '<th>' . number_format($row->discount_price, 0, '.', ',') . '</th>';
                    $htmlAdd .= '<td>Loss Store</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_loss_store, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Total</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_price, 0, '.', ',') . '</th>';
                    $htmlAdd .= '<td>Loss Teknisi</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_loss_technician_1, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<td></td>';
                    $htmlAdd .= '<th></th>';
                    $htmlAdd .= '<td>Loss Teknisi 2</td>';
                    $htmlAdd .= '<th>' . number_format($row->total_loss_technician_2, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })
                ->addColumn('currentStatus', function ($row) {
                    $workStatus = '<div class="badge badge-success">Status Tidak Diketahui</div>';
                    if ($row->work_status == 'Proses') {
                        $workStatus = '<div class="badge badge-warning">Proses Pengerjaan</div>';
                    } elseif ($row->work_status == 'Mutasi') {
                        $workStatus = '<div class="badge badge-warning">Perpindahan Teknisi</div>';
                    } elseif ($row->work_status == 'Selesai') {
                        $workStatus = '<div class="badge badge-success">Selesai</div>';
                    } elseif ($row->work_status == 'Cancel') {
                        $workStatus = '<div class="badge badge-danger">Service Batal</div>';
                    } elseif ($row->work_status == 'Manifest') {
                        $workStatus = '<div class="badge badge-primary">Barang Diterima</div>';
                    } elseif ($row->work_status == 'Diambil') {
                        $workStatus = '<div class="badge badge-success">Sudah Diambil</div>';
                    } elseif ($row->work_status == 'Return') {
                        $workStatus = '<div class="badge badge-success">Sudah Diambil</div>';
                    }
                    // if (isset($workStatus)) {
                        // $workStatus = '<div class="badge badge-success">Status Tidak Diketahui</div>';
                    // }

                
                    $htmlAdd = '<table>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<td>Status Pekerjaan</td>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<th>' . $workStatus . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })

                ->rawColumns(['action', 'dataItem', 'dataBuyer', 'finance', 'Informasi', 'currentStatus'])
                ->make(true);
        }
        return view('pages.backend.transaction.service_items.indexServiceItems');
    }
    public function code($type)
    {
        $getEmployee = Employee::with('branch')
            ->where('user_id', Auth::user()->id)
            ->first();
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('y');
        $co = Service::where('branch_id', Auth::user()->employee->branch_id)
            ->whereMonth('date', now())
            ->whereNotNull('group_service')
            ->get();
        $index = count($co) + 1;
        // $index = DB::table('service')->max('id') + 1;
        $index = str_pad($index, 3, '0', STR_PAD_LEFT);

        $code = $type . $getEmployee->Branch->code . $year . $month . $index;

        $checkCode = Service::where('code', $code)
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
    public function create()
    {
        $checkRoles = $this->DashboardController->cekHakAkses(51, 'create');
        if ($checkRoles == 'akses ditolak') {
            return view('forbidden');
        }
        $code = $this->code('SRVU');
        $employee = Employee::orderBy('name', 'ASC')->get();
        $item = Item::with('stock', 'supplier')
            ->where('name', '!=', 'Jasa Service')
            ->orderBy('name', 'ASC')
            ->get();
        $brand = Brand::orderBy('name', 'ASC')->get();
        $type = Type::orderBy('name', 'ASC')->get();
        $category = Category::orderBy('name', 'ASC')->get();
        $warranty = Warranty::orderBy('name', 'ASC')->get();
        $customer = Customer::orderBy('name', 'ASC')->get();

        return view('pages.backend.transaction.service_items.createServiceItems', compact('employee', 'code', 'item', 'brand', 'type', 'warranty', 'category', 'customer'));
    }

    public function store(Request $req)
    {

        // return $req->all();
        DB::beginTransaction();
        try {
            // return Items::where('id',$req->items)->get();
            $tech1 = Service::where('technician_id', $req->technicianId)
                ->where('work_status', '!=', 'Selesai')
                ->where('work_status', '!=', 'Cancel')
                ->where('work_status', '!=', 'Return')
                ->where('work_status', '!=', 'Diambil')
                ->where('work_status', '!=', 'Mutasi')
                ->count();
            $tech2 = Service::where('technician_replacement_id', $req->technicianId)
                ->where('work_status', '!=', 'Selesai')
                ->where('work_status', '!=', 'Cancel')
                ->where('work_status', '!=', 'Return')
                ->where('work_status', '!=', 'Diambil')
                ->count();

            $getEmployee = Employee::where('user_id', Auth::user()->id)->first();
            $settingPresentase = SettingPresentase::get();

            for ($i = 0; $i < count($settingPresentase); $i++) {
                if ($settingPresentase[$i]->name == 'Presentase Sharing Profit Toko') {
                    $sharingProfitStore = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Sharing Profit Teknisi') {
                    $sharingProfitTechnician = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Loss Toko') {
                    $lossStore = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Loss Teknisi') {
                    $lossTechnician = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Batasan Maximum Handle Customer Pada Teknisi') {
                    $MaxHandle = $settingPresentase[$i]->total;
                }
            }

            if ($req->technicianId != 1) {
                if ($tech1 + $tech2 >= $MaxHandle) {
                    return Response::json([
                        'status' => 'fail',
                        'message' => 'Teknisi Memiliki ' . $MaxHandle . ' Pekerjaan Belum Selesai',
                    ]);
                }
            }
        
            $codeNota = $this->code('SRVU');
            $id = DB::table('service')->max('id') + 1;
            
            // mengecek apakah total menjadi 100%
            if ($req->presentaseTechnician1+$req->presentaseTechnician2+$req->presentaseStore < 100 && $req->presentaseTechnician1+$req->presentaseTechnician2+$req->presentaseStore != 0) {
                return Response::json([
                        'status' => 'fail',
                        'message' => 'Total sharing Profit harus bernilai 100',
                    ]);
            }

            // mengecek sharing profit manual
            if ($req->presentaseTechnician1 != 0 && $req->presentaseTechnician2 != 0 && $req->presentaseStore != 0) {
                $sharingProfitStore = $req->presentaseStore;
                $sharingProfitTechnician = $req->presentaseTechnician1+$req->presentaseTechnician2;
                // presentase yang disimpan di database
                $presentaseSharingProfitTechnician1 = $req->presentaseTechnician1;
                $presentaseSharingProfitTechnician2 = $req->presentaseTechnician2;

            }else{
                $sharingProfitStore = $sharingProfitStore;
                $sharingProfitTechnician =  $sharingProfitTechnician;
                // presentase yang disimpan di database
                $presentaseSharingProfitTechnician1 = $sharingProfitTechnician;
                $presentaseSharingProfitTechnician2 = 0;
            }

            $sharing_profit_store = ((str_replace(',', '', $req->totalService)) / 100) * $sharingProfitStore + str_replace(',', '', $req->totalSparePart);
            $sharing_profit_technician_1 = ((str_replace(',', '', $req->totalService)) / 100) * $sharingProfitTechnician;

            $total_loss_technician_1 = (str_replace(',', '', $req->totalLoss) / 100) * $lossTechnician;
            $total_loss_store = (str_replace(',', '', $req->totalLoss) / 100) * $lossStore;

            $estimateDate = $this->DashboardController->changeMonthIdToEn($req->date);

            $image = $req->image;
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = base64_decode($image);
            if ($image != null) {
                $fileSave = 'public/Service_' . $this->code('SRVU') . '.' . 'png';
                $fileName = 'Service_' . $this->code('SRVU') . '.' . 'png';
                Storage::put($fileSave, $image);
            } else {
                $fileName = null;
            }
            // return 'asd';
            Service::create([
                'id' => $id,
                'code' => $codeNota,
                'user_id' => Auth::user()->id,
                'branch_id' => $getEmployee->branch_id,
                'customer_id' => $req->customerId,
                // 'customer_name' => $req->customerName,
                // 'customer_address' => $req->customerAdress,
                // 'customer_phone' => $req->customerPhone,
                'date' => $estimateDate,
                // 'estimate_date' => $estimateDate,
                // 'estimate_day' => $req->estimateDay,
                'brand' => $req->brand,
                'series' => $req->items,
                'type' => $req->type,
                'no_imei' => $req->noImei,
                'complaint' => $req->complaint,
                'clock' => date('h:i'),
                'total_service' => str_replace(',', '', $req->totalService),
                'total_part' => str_replace(',', '', $req->totalSparePart),
                'total_payment' => 0,
                'total_downpayment' => 0,
                'total_loss' => str_replace(',', '', $req->totalLoss),
                'total_loss_technician_1' => $total_loss_technician_1,
                'total_loss_technician_2' => 0,
                'total_loss_store' => $total_loss_store,
                'image' => $fileName,
                'discount_type' => '-',
                'discount_price' => 0,
                'discount_service' => 0,
                'discount_percent' => 0,
                'total_hpp' => str_replace(',', '', $req->totalHppAtas),
                'total_price' => str_replace(',', '', $req->totalPrice),
                'total_price_buy' => str_replace(',', '', $req->totalPriceBuy),
                'total_price_sell' => str_replace(',', '', $req->totalPriceSell),
                'work_status' => 'Manifest',
                'group_service' => 'Upgrade',
                // 'equipment' => $req->equipment,
                'description' => '-',
                // 'warranty_id' => $req->warranty,
                // 'verification_price' => $req->verificationPrice,
                'technician_id' => $req->technicianId,
                'sharing_profit_store' => str_replace(',', '', $sharing_profit_store),
                'sharing_profit_technician_1' => str_replace(',', '', $sharing_profit_technician_1),
                'sharing_profit_technician_2' => str_replace(',', '', 0),
                'presentase_sharing_profit_store'=>$sharingProfitStore,
                'presentase_sharing_profit_technician_1'=>$presentaseSharingProfitTechnician1,
                'presentase_sharing_profit_technician_2'=>$presentaseSharingProfitTechnician2,
                'created_at' => date('Y-m-d h:i:s'),
                'created_by' => Auth::user()->name,
            ]);
            $checkStock = [];
            for ($i = 0; $i < count($req->itemsDetail); $i++) {
                ServiceDetail::create([
                    'service_id' => $id,
                    'item_id' => $req->itemsDetail[$i],
                    'price' => str_replace(',', '', $req->priceDetail[$i]),
                    'hpp' => str_replace(',', '', $req->priceHpp[$i]),
                    'qty' => $req->qtyDetail[$i],
                    'total_price' => str_replace(',', '', $req->totalPriceDetail[$i]),
                    'total_hpp' => str_replace(',', '', $req->totalPriceHpp[$i]),
                    'description' => str_replace(',', '', $req->descriptionDetail[$i]),
                    'type' => $req->typeDetail[$i],
                    'created_by' => Auth::user()->name,
                    'created_at' => date('Y-m-d h:i:s'),
                ]);
                if ($req->typeDetail[$i] != 'Jasa') {
                    $checkStock[$i] = Stock::where('item_id', $req->itemsDetail[$i])
                        ->where('branch_id', $getEmployee->branch_id)
                        ->where('id', '!=', 1)
                        ->get();
                    if ($checkStock[$i][0]->stock < $req->qtyDetail[$i]) {
                        return Response::json([
                            'status' => 'fail',
                            'message' => 'Stock Item Ada yang 0. Harap Cek Kembali',
                        ]);
                    }
                    if ($req->typeDetail[$i] == 'SparePart') {
                        $desc[$i] = 'Pengeluaran Barang Pada Service ' . $codeNota;
                    } else {
                        $desc[$i] = 'Pengeluaran Barang Loss Pada Service ' . $codeNota;
                    }
                    Stock::where('item_id', $req->itemsDetail[$i])
                        ->where('branch_id', $getEmployee->branch_id)
                        ->update([
                            'stock' => $checkStock[$i][0]->stock - $req->qtyDetail[$i],
                        ]);
                    StockMutation::create([
                        'item_id' => $req->itemsDetail[$i],
                        'unit_id' => $checkStock[$i][0]->unit_id,
                        'branch_id' => $checkStock[$i][0]->branch_id,
                        'qty' => $req->qtyDetail[$i],
                        'code' => $codeNota,
                        'type' => 'Out',
                        'description' => $desc[$i],
                    ]);
                }
            }

            ServiceStatusMutation::create([
                'service_id' => $id,
                'technician_id' => $req->technicianId,
                'index' => 1,
                'status' => 'Manifest',
                'image' => $fileName,
                'description' => 'Barang Sedang Dicek & Diterima oleh ' . Auth::user()->name,
                'created_by' => Auth::user()->name,
                'created_at' => date('Y-m-d h:i:s'),
            ]);

            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'LCD',
                'status' => $req->LcdCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'WIFI',
                'status' => $req->wifiCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Camera Depan',
                'status' => $req->cameraDepanCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Camera Belakang',
                'status' => $req->cameraBelakangCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Speaker',
                'status' => $req->speakerCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Charging',
                'status' => $req->chargingCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Mic',
                'status' => $req->micCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Speaker',
                'status' => $req->speakerCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Touch Screen',
                'status' => $req->touchScreenCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Vibrator',
                'status' => $req->vibratorCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Soket Audio',
                'status' => $req->soketAudioCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Usb',
                'status' => $req->usbCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Sinyal',
                'status' => $req->sinyalCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Tombol Tombol',
                'status' => $req->tombolCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Keyboard',
                'status' => $req->keyboardCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Touchpad',
                'status' => $req->touchpadCondition,
            ]);
            $dataEquipment = [];

            if ($req->chargerEquipment == 'on') {
                $dataEquipment[0] = 'Y';
                $dataEquipmentName[0] = 'Charger';
                $dataEquipmentDesc[0] = $req->chargerEquipmentDesc;
            } else {
                $dataEquipment[0] = 'N';
                $dataEquipmentName[0] = 'Charger';
                $dataEquipmentDesc[0] = $req->chargerEquipmentDesc;
            }
            if ($req->bateraiEquipment == 'on') {
                $dataEquipment[1] = 'Y';
                $dataEquipmentName[1] = 'Baterai';
                $dataEquipmentDesc[1] = $req->bateraiEquipmentDesc;
            } else {
                $dataEquipment[1] = 'N';
                $dataEquipmentName[1] = 'Baterai';
                $dataEquipmentDesc[1] = $req->bateraiEquipmentDesc;
            }
            if ($req->hardiskSsdEquipment == 'on') {
                $dataEquipment[2] = 'Y';
                $dataEquipmentName[2] = 'Hardisk / SSD';
                $dataEquipmentDesc[2] = $req->hardiskSsdEquipmentDesc;
            } else {
                $dataEquipment[2] = 'N';
                $dataEquipmentName[2] = 'Hardisk / SSD';
                $dataEquipmentDesc[2] = $req->hardiskSsdEquipmentDesc;
            }
            if ($req->RamEquipment == 'on') {
                $dataEquipment[3] = 'Y';
                $dataEquipmentName[3] = 'Ram';
                $dataEquipmentDesc[3] = $req->RamEquipmentDesc;
            } else {
                $dataEquipment[3] = 'N';
                $dataEquipmentName[3] = 'Ram';
                $dataEquipmentDesc[3] = $req->RamEquipmentDesc;
            }
            if ($req->kabelEquipment == 'on') {
                $dataEquipment[4] = 'Y';
                $dataEquipmentName[4] = 'Kabel';
                $dataEquipmentDesc[4] = $req->kabelEquipmentDesc;
            } else {
                $dataEquipment[4] = 'N';
                $dataEquipmentName[4] = 'Kabel';
                $dataEquipmentDesc[4] = $req->kabelEquipmentDesc;
            }
            if ($req->tasLaptopEquipment == 'on') {
                $dataEquipment[5] = 'Y';
                $dataEquipmentName[5] = 'Tas Laptop';
                $dataEquipmentDesc[5] = $req->tasLaptopEquipmentDesc;
            } else {
                $dataEquipment[5] = 'N';
                $dataEquipmentName[5] = 'Tas Laptop';
                $dataEquipmentDesc[5] = $req->tasLaptopEquipmentDesc;
            }
            if ($req->aksesorisEquipment == 'on') {
                $dataEquipment[6] = 'Y';
                $dataEquipmentName[6] = 'Aksesoris';
                $dataEquipmentDesc[6] = $req->aksesorisEquipmentDesc;
            } else {
                $dataEquipment[6] = 'N';
                $dataEquipmentName[6] = 'Aksesoris';
                $dataEquipmentDesc[6] = $req->aksesorisEquipmentDesc;
            }

            if ($req->lainnyaEquipment == 'on') {
                $dataEquipment[7] = 'Y';
                $dataEquipmentName[7] = 'Lainnya';
                $dataEquipmentDesc[7] = $req->lainnyaEquipmentDesc;
            } else {
                $dataEquipment[7] = 'N';
                $dataEquipmentName[7] = 'Lainnya';
                $dataEquipmentDesc[7] = $req->lainnyaEquipmentDesc;
            }
            for ($i = 0; $i < count($dataEquipment); $i++) {
                ServiceEquipment::create([
                    'service_id' => $id,
                    'name' => $dataEquipmentName[$i],
                    'status' => $dataEquipment[$i],
                    'description' => $dataEquipmentDesc[$i],
                ]);
            }

            // fungsi update harga barang
            $upgrade = Item::with('Stock')->where('id',$req->items)->update([
                'sell'=> str_replace(',', '', $req->totalPriceSell),
            ]);
            $desc = 'Biaya Barang Upgrade atas sparepart yang dikeluarkan pada transaksi '.$codeNota;
            $idJournal = DB::table('journals')->max('id') + 1;
            Journal::create([
                'id' => $idJournal,
                'code' => $this->codeJournals('KK', $idJournal),
                'year' => date('Y'),
                'date' => date('Y-m-d'),
                'type' => 'Biaya',
                'total' => str_replace(',', '', $req->totalSparePart),
                'ref' => $codeNota,
                'description' => $desc,
                'created_at' => date('Y-m-d h:i:s'),
            ]);

            $accountPersediaan = AccountData::where('branch_id', $getEmployee->branch_id)
                ->where('active', 'Y')
                ->where('main_id', 3)
                ->where('main_detail_id', 11)
                ->first();

            $accountBiayaHilang = AccountData::where('branch_id', $getEmployee->branch_id)
                ->where('active', 'Y')
                ->where('main_id', 6)
                ->where('main_detail_id', 49)
                ->first();
            
            $accountCode = [$accountPersediaan->id,$accountBiayaHilang->id];
            $totalBayar = [str_replace(',', '', $req->totalSparePart),str_replace(',', '', $req->totalSparePart)];
            $description = [$desc,$desc];
            $DK = ['D','K'];

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

            
            DB::commit();
            return Response::json(['status' => 'success', 'message' => 'Data Tersimpan', 'id' => $id]);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
            return Response::json(['status' => 'error', 'message' => $th]);
        }
    }

    public function edit($id)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(51, 'edit');
        if ($checkRoles == 'akses ditolak') {
            return view('forbidden');
        }
        $service = Service::with('ServiceDetail', 'serviceCondition', 'serviceEquipment')->find($id);
        $member = User::orderBy('name', 'ASC')->get();
        $employee = Employee::orderBy('name', 'ASC')->get();
        $category = Category::orderBy('name', 'ASC')->get();
        $brand = Brand::orderBy('name', 'ASC')->get();
        $type = Type::orderBy('name', 'ASC')->get();
        $warranty = Warranty::orderBy('name', 'ASC')->get();
        $item = Item::with('stock')
            ->where('name', '!=', 'Jasa Service')
            ->orderBy('name', 'ASC')
            ->get();
        $customer = Customer::orderBy('name', 'ASC')->get();

        return view('pages.backend.transaction.service_items.editServiceItems', compact('employee', 'item', 'brand', 'type', 'warranty', 'service', 'category', 'customer'));
    }
    public function show($id)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(51, 'edit');
        if ($checkRoles == 'akses ditolak') {
            return view('forbidden');
        }
        $service = Service::with('ServiceDetail', 'serviceCondition', 'serviceEquipment')->find($id);
        $member = User::orderBy('name', 'ASC')->get();
        $employee = Employee::orderBy('name', 'ASC')->get();
        $category = Category::orderBy('name', 'ASC')->get();
        $brand = Brand::orderBy('name', 'ASC')->get();
        $type = Type::orderBy('name', 'ASC')->get();
        $warranty = Warranty::orderBy('name', 'ASC')->get();
        $item = Item::with('stock')
            ->where('name', '!=', 'Jasa Service')
            ->orderBy('name', 'ASC')
            ->get();
        $customer = Customer::orderBy('name', 'ASC')->get();

        return view('pages.backend.transaction.service.showService', compact('employee', 'item', 'brand', 'type', 'warranty', 'service', 'category', 'customer'));
    }
    public function printService($id)
    {
        $Service = Service::with('ServiceDetail', 'ServiceDetail.Items', 'Employee1', 'Employee2', 'CreatedByUser', 'Type', 'Brand', 'Brand.Category', 'ServiceEquipment', 'ServiceCondition')->find($id);
        $member = User::get();

        return view('pages.backend.transaction.service.printService', ['service' => $Service, 'member' => $member]);
    }

    public function update($id, Request $req)
    {
        DB::beginTransaction();
        try {
            $tech1 = Service::where('technician_id', $req->technicianId)
                ->where('work_status', '!=', 'Cancel')
                ->where('work_status', '!=', 'Return')
                ->where('work_status', '!=', 'Diambil')
                ->where('work_status', '!=', 'Selesai')
                ->where('work_status', '!=', 'Mutasi')
                ->count();
            $tech2 = Service::where('technician_replacement_id', $req->technicianId)
                ->where('work_status', '!=', 'Selesai')
                ->where('work_status', '!=', 'Cancel')
                ->where('work_status', '!=', 'Return')
                ->where('work_status', '!=', 'Diambil')
                ->count();
            $checkData = Service::where('id', $id)->first();

            $getEmployee = Employee::where('user_id', Auth::user()->id)->first();
            $settingPresentase = SettingPresentase::get();
            for ($i = 0; $i < count($settingPresentase); $i++) {
                if ($settingPresentase[$i]->name == 'Presentase Sharing Profit Toko') {
                    $sharingProfitStore = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Sharing Profit Teknisi') {
                    $sharingProfitTechnician = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Sharing Profit Teknisi 1') {
                    $sharingProfitTechnician1 = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Sharing Profit Teknisi 2') {
                    $sharingProfitTechnician2 = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Loss Toko') {
                    $lossStore = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Loss Teknisi') {
                    $lossTechnician = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Loss Teknisi 1') {
                    $lossTechnician1 = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Presentase Loss Teknisi 2') {
                    $lossTechnician2 = $settingPresentase[$i]->total;
                }
                if ($settingPresentase[$i]->name == 'Batasan Maximum Handle Customer Pada Teknisi') {
                    $MaxHandle = $settingPresentase[$i]->total;
                }
            }

            if ($req->technicianId != 1) {
                if ($checkData->technician_id != $req->technicianId) {
                    $totalHandledNow = $tech1 + $tech2;
                    if ($totalHandledNow >= $MaxHandle) {
                        return Response::json([
                            'status' => 'fail',
                            'message' => 'Teknisi Memiliki ' . $MaxHandle . ' Pekerjaan Belum Selesai',
                        ]);
                    }
                }
            }

      

            if (str_replace(',', '', $req->totalLoss) == 0) {
                $total_loss_store = 0;
                $total_loss_technician_1 = 0;
                $total_loss_technician_2 = 0;
            } else {
                $total_loss_store = (str_replace(',', '', $req->totalLoss) / 100) * $lossStore;
                if ($checkData->technician_replacement_id != null) {
                    $total_loss_technician_1 = ($lossTechnician1 / 100) * str_replace(',', '', $req->totalLoss);
                    $total_loss_technician_2 = ($lossTechnician2 / 100) * str_replace(',', '', $req->totalLoss);
                } else {
                    $total_loss_technician_1 = ($lossTechnician / 100) * str_replace(',', '', $req->totalLoss);
                    $total_loss_technician_2 = 0;
                }
            }


            // mengecek sharing profit jika kurang dari 100
            if ($req->presentaseTechnician1+$req->presentaseTechnician2+$req->presentaseStore < 100 && $req->presentaseTechnician1+$req->presentaseTechnician2+$req->presentaseStore != 0) {
                return Response::json([
                    'status' => 'fail',
                    'message' => 'Total sharing Profit harus bernilai 100',
                ]);
            }
            
            // mengecek sharing profit manual
            if ($req->presentaseTechnician1 != 0 && $req->presentaseTechnician2 != 0 && $req->presentaseStore != 0) {
                $sharingProfitStore = $req->presentaseStore;
                $sharingProfitTechnician = $req->presentaseTechnician1+$req->presentaseTechnician2;
                // presentase yang disimpan di database
                $presentaseSharingProfitTechnician1 = $req->presentaseTechnician1;
                $presentaseSharingProfitTechnician2 = $req->presentaseTechnician2;
                
                $sharingProfitTechnician1 = $req->presentaseTechnician1;
                $sharingProfitTechnician2 = $req->presentaseTechnician2;
            }else{
                $sharingProfitStore = $sharingProfitStore;
                $sharingProfitTechnician =  $sharingProfitTechnician;
                // presentase yang disimpan di database
                $presentaseSharingProfitTechnician1 = $sharingProfitTechnician;
                $presentaseSharingProfitTechnician2 = 0;

                $sharingProfitTechnician1 = $sharingProfitTechnician1;
                $sharingProfitTechnician2 = $sharingProfitTechnician2;
            }


            $sharing_profit_store = ((str_replace(',', '', $req->totalService) - str_replace(',', '', $req->totalDiscountValue)) / 100) * $sharingProfitStore + str_replace(',', '', $req->totalSparePart);

            if ($checkData->technician_replacement_id != null) {
                $sharing_profit_technician_1 = ($sharingProfitTechnician1 / 100) * (str_replace(',', '', $req->totalService) - str_replace(',', '', $req->totalDiscountValue));
                $sharing_profit_technician_2 = ($sharingProfitTechnician2 / 100) * (str_replace(',', '', $req->totalService) - str_replace(',', '', $req->totalDiscountValue));
            } else {
                $sharing_profit_technician_1 = ($sharingProfitTechnician / 100) * (str_replace(',', '', $req->totalService) - str_replace(',', '', $req->totalDiscountValue));
                $sharing_profit_technician_2 = 0;
            }
           

            $estimateDate = $this->DashboardController->changeMonthIdToEn($req->estimateDate);

            $image = $req->image;
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = base64_decode($image);
            if ($image != null) {
                $fileSave = 'public/Service_' . $checkData->code . '.' . 'png';
                $fileName = 'Service_' . $checkData->code . '.' . 'png';
                Storage::put($fileSave, $image);
            } else {
                $fileName = $checkData->image;
            }

            Service::where('id', $id)->update([
                'id' => $id,
                'code' => $codeNota,
                'user_id' => Auth::user()->id,
                'branch_id' => $getEmployee->branch_id,
                'customer_id' => $req->customerId,
                // 'customer_name' => $req->customerName,
                // 'customer_address' => $req->customerAdress,
                // 'customer_phone' => $req->customerPhone,
                'date' => $estimateDate,
                // 'estimate_date' => $estimateDate,
                // 'estimate_day' => $req->estimateDay,
                'brand' => $req->brand,
                'series' => $req->items,
                'type' => $req->type,
                'no_imei' => $req->noImei,
                'complaint' => $req->complaint,
                'clock' => date('h:i'),
                'total_service' => str_replace(',', '', $req->totalService),
                'total_part' => str_replace(',', '', $req->totalSparePart),
                'total_payment' => 0,
                'total_downpayment' => 0,
                'total_loss' => str_replace(',', '', $req->totalLoss),
                'total_loss_technician_1' => $total_loss_technician_1,
                'total_loss_technician_2' => 0,
                'total_loss_store' => $total_loss_store,
                'image' => $fileName,
                'discount_type' => '-',
                'discount_price' => 0,
                'discount_service' => 0,
                'discount_percent' => 0,
                'total_hpp' => str_replace(',', '', $req->totalHppAtas),
                'total_price' => str_replace(',', '', $req->totalPrice),
                'total_price_buy' => str_replace(',', '', $req->totalPriceBuy),
                'total_price_sell' => str_replace(',', '', $req->totalPriceSell),
                'work_status' => 'Manifest',
                'group_service' => 'Upgrade',
                // 'equipment' => $req->equipment,
                'description' => '-',
                // 'warranty_id' => $req->warranty,
                // 'verification_price' => $req->verificationPrice,
                'technician_id' => $req->technicianId,
                'sharing_profit_store' => str_replace(',', '', $sharing_profit_store),
                'sharing_profit_technician_1' => str_replace(',', '', $sharing_profit_technician_1),
                'sharing_profit_technician_2' => str_replace(',', '', 0),
                'presentase_sharing_profit_store'=>$sharingProfitStore,
                'presentase_sharing_profit_technician_1'=>$presentaseSharingProfitTechnician1,
                'presentase_sharing_profit_technician_2'=>$presentaseSharingProfitTechnician2,
                'updated_at' => date('Y-m-d h:i:s'),
                'updated_by' => Auth::user()->name,
            ]);

           
            // check data yang dihapus dan mengembalikan stock terlebih dahulu
            if ($req->deletedExistingData != null) {
                $checkDataDeleted = ServiceDetail::whereIn('id', $req->deletedExistingData)->get();
                $checkStockDeleted = [];
                for ($i = 0; $i < count($checkDataDeleted); $i++) {
                    $checkStockDeleted[$i] = Stock::where('item_id', $checkDataDeleted[$i]->item_id)
                        ->where('branch_id', $checkData->branch_id)
                        ->where('id', '!=', 1)
                        ->get();

                    if ($checkDataDeleted[$i]->type == 'SparePart') {
                        $desc[$i] = '(Update Service) Pengembalian Barang Pada Service ' . $req->code;
                    } else {
                        $desc[$i] = '(Update Service) Pengembalian Barang Loss Pada Service ' . $req->code;
                    }
                    // return $desc;
                    Stock::where('item_id', $checkDataDeleted[$i]->item_id)
                        ->where('branch_id', $checkData->branch_id)
                        ->update([
                            'stock' => $checkStockDeleted[$i][0]->stock + $checkDataDeleted[$i]->qty,
                        ]);
                    StockMutation::create([
                        'item_id' => $checkDataDeleted[$i]->item_id,
                        'unit_id' => $checkStockDeleted[$i][0]->unit_id,
                        'branch_id' => $checkStockDeleted[$i][0]->branch_id,
                        'qty' => $checkDataDeleted[$i]->qty,
                        'code' => $req->code,
                        'type' => 'In',
                        'description' => $desc[$i],
                    ]);
                }
                $destroyExistingData = DB::table('service_detail')
                    ->whereIn('id', $req->deletedExistingData)
                    ->delete();
            }
        
            // menyimpan data baru dan memperbaru stock
            if ($req->itemsDetail != null) {
                $checkStock = [];
                for ($i = 0; $i < count($req->itemsDetail); $i++) {
                    if ($req->typeDetail[$i] != 'Jasa') {
                        ServiceDetail::create([
                            'service_id' => $id,
                            'item_id' => $req->itemsDetail[$i],
                            'price' => str_replace(',', '', $req->priceDetail[$i]),
                            'hpp' => str_replace(',', '', $req->priceHpp[$i]),
                            'qty' => $req->qtyDetail[$i],
                            'total_price' => str_replace(',', '', $req->totalPriceDetail[$i]),
                            'total_hpp' => str_replace(',', '', $req->totalPriceHpp[$i]),
                            'description' => str_replace(',', '', $req->descriptionDetail[$i]),
                            'type' => $req->typeDetail[$i],
                            'created_by' => Auth::user()->name,
                            'created_at' => date('Y-m-d h:i:s'),
                        ]);
                        $checkStock[$i] = Stock::where('item_id', $req->itemsDetail[$i])
                            ->where('branch_id', $checkData->branch_id)
                            ->where('id', '!=', 1)
                            ->get();
                        if ($checkStock[$i][0]->stock < $req->qtyDetail[$i]) {
                            return Response::json([
                                'status' => 'fail',
                                'message' => 'Stock Item Ada yang 0. Harap Cek Kembali',
                            ]);
                        }
                        if ($req->typeDetail[$i] == 'SparePart') {
                            $desc[$i] = '(Update Service) Pengeluaran Barang Pada Service ' . $req->code;
                        } else {
                            $desc[$i] = '(Update Service) Pengeluaran Barang Loss Pada Service ' . $req->code;
                        }
                        Stock::where('item_id', $req->itemsDetail[$i])
                            ->where('branch_id', $checkData->branch_id)
                            ->update([
                                'stock' => $checkStock[$i][0]->stock - $req->qtyDetail[$i],
                            ]);
                        StockMutation::create([
                            'item_id' => $req->itemsDetail[$i],
                            'unit_id' => $checkStock[$i][0]->unit_id,
                            'branch_id' => $checkStock[$i][0]->branch_id,
                            'qty' => $req->qtyDetail[$i],
                            'code' => $req->code,
                            'type' => 'Out',
                            'description' => $desc[$i],
                        ]);
                    }
                }
            }
         
            // DB::rollback();
            // return [$req->idDetailOld,
            // $req->priceDetailOld,
            // $req->priceHppOld,
            // $req->totalPriceHppOld];
            // mengecek data existing
            if ($req->itemsDetailOld != null) {
                // return $req->all();
                // mengecek data existing
                $checkDataOld = ServiceDetail::whereIn('id', $req->idDetailOld)->get();
                $checkStockExisting = [];
                 
                for ($i = 0; $i < count($checkDataOld); $i++) {
                    // memfilter type kecuali jasa
                    if ($checkDataOld[$i]->item_id != 1) {
                        // return$checkDataOld[$i];
                        if ($req->typeDetailOld[$i] != 'Jasa') {
                            $checkStockExisting[$i] = Stock::where('item_id', $req->itemsDetailOld[$i])
                                ->where('branch_id', $checkData->branch_id)
                                ->where('id', '!=', 1)
                                ->get();

                            $checkStockExistingOlder[$i] = Stock::where('item_id', $checkDataOld[$i]->item_id)
                                ->where('branch_id', $checkData->branch_id)
                                ->where('id', '!=', 1)
                                ->get();
                            // if($checkStockExisting[$i][0]->stock < ($req->qtyDetailOld[$i])){
                            //     return Response::json(['status' => 'fail',
                            //                     'message'=>'Stock Item Ada yang 0. Harap Cek Kembali']);
                            // }
                            // return 'masuk 1';
                            // mengecek kembali jika data item sama dengan data yang ada di service_detail
                            if ($checkDataOld[$i]->item_id == $req->itemsDetailOld[$i]) {
                                // return 'masuk 2.1';
                                // mengecek kembali jika data QTY sama dengan data yang ada di service_detail
                                if ($checkDataOld[$i]->qty == $req->qtyDetailOld[$i]) {
                                    // return 'masuk 3.1';
                                    // jika qty di service_detail sama dengan QTY yang akan di update
                                    if ($checkDataOld[$i]->type == $req->typeDetailOld[$i]) {
                                        // return 'masuk 4.1';
                                        // Jika Type sama maka tidak perlu melakukan update stock mutasi
                                    } else {
                                        // Jika Type berbeda maka perlu melakukan update stock mutasi dengan type yaitu MUTATION
                                        // return 'masuk 4.2';
                                        $desc[$i] = '(Update Service) Perubahan Barang dari ' . $checkDataOld[$i]->type . ' Menjadi ' . $req->typeDetailOld[$i] . ' Pada Service ' . $req->code;

                                        StockMutation::create([
                                            'item_id' => $req->itemsDetailOld[$i],
                                            'unit_id' => $checkStockExisting[$i][0]->unit_id,
                                            'branch_id' => $checkStockExisting[$i][0]->branch_id,
                                            'qty' => $req->qtyDetailOld[$i],
                                            'code' => $req->code,
                                            'type' => 'Mutation',
                                            'description' => $desc[$i],
                                        ]);
                                    }
                                    // mengupdate service detail jika item sama + qty sama + perubahan tipe sparepart / loss
                                    ServiceDetail::where('id', $req->idDetailOld[$i])->update([
                                        // 'service_id'=>$id,
                                        // 'item_id'=>$req->itemsDetailOld[$i],
                                        'price' => str_replace(',', '', $req->priceDetailOld[$i]),
                                        'hpp' => str_replace(',', '', $req->priceHppOld[$i]),
                                        'total_hpp' => str_replace(',', '', $req->totalPriceHppOld[$i]),
                                        // 'qty'=>$req->qtyDetailOld[$i],
                                        'total_price' => str_replace(',', '', $req->totalPriceDetailOld[$i]),
                                        'description' => str_replace(',', '', $req->descriptionDetailOld[$i]),
                                        'type' => $req->typeDetailOld[$i],
                                        'updated_by' => Auth::user()->name,
                                        'updated_at' => date('Y-m-d h:i:s'),
                                    ]);
                                } else {
                                    // return 'masuk 3.2';
                                    // jika qty di service_detail berbeda dengan QTY yang akan di update
                                    // return $checkDataOld;
                                    if ($req->typeDetailOld[$i] == 'SparePart') {
                                        $descPengembalian[$i] = '(Update Service) Pengembalian Barang Pada Service ' . $req->code;
                                    } else {
                                        $descPengembalian[$i] = '(Update Service) Pengembalian Barang Loss Pada Service ' . $req->code;
                                    }
                                    StockMutation::create([
                                        'item_id' => $req->itemsDetailOld[$i],
                                        'unit_id' => $checkStockExisting[$i][0]->unit_id,
                                        'branch_id' => $checkStockExisting[$i][0]->branch_id,
                                        'qty' => $checkDataOld[$i]->qty,
                                        'code' => $req->code,
                                        'type' => 'In',
                                        'description' => $descPengembalian[$i],
                                    ]);

                                    // Pegeluaran atas data item yang dirubah
                                    if ($req->typeDetailOld[$i] == 'SparePart') {
                                        $descPengeluaran[$i] = '(Update Service) Pengeluaran Barang Pada Service ' . $req->code;
                                    } else {
                                        $descPengeluaran[$i] = '(Update Service) Pengeluaran Barang Loss Pada Service ' . $req->code;
                                    }
                                    StockMutation::create([
                                        'item_id' => $req->itemsDetailOld[$i],
                                        'unit_id' => $checkStockExisting[$i][0]->unit_id,
                                        'branch_id' => $checkStockExisting[$i][0]->branch_id,
                                        'qty' => $req->qtyDetailOld[$i],
                                        'code' => $req->code,
                                        'type' => 'Out',
                                        'description' => $descPengeluaran[$i],
                                    ]);

                                    Stock::where('item_id', $checkDataOld[$i]->item_id)
                                        ->where('branch_id', $checkData->branch_id)
                                        ->update([
                                            'stock' => $checkStockExisting[$i][0]->stock + $checkDataOld[$i]->qty - $req->qtyDetailOld[$i],
                                        ]);

                                    ServiceDetail::where('id', $req->idDetailOld[$i])->update([
                                        // 'service_id'=>$id,
                                        // 'item_id'=>$req->itemsDetailOld[$i],
                                        'price' => str_replace(',', '', $req->priceDetailOld[$i]),
                                        'qty' => $req->qtyDetailOld[$i],
                                        'hpp' => str_replace(',', '', $req->priceDetailOld[$i]),
                                        'total_hpp' => str_replace(',', '', $req->totalPriceHppOld[$i]),
                                        'total_price' => str_replace(',', '', $req->totalPriceDetailOld[$i]),
                                        'description' => str_replace(',', '', $req->descriptionDetailOld[$i]),
                                        'type' => $req->typeDetailOld[$i],
                                        'updated_by' => Auth::user()->name,
                                        'updated_at' => date('Y-m-d h:i:s'),
                                    ]);
                                }
                            } else {
                                // return 'masuk 2.2';
                                // pengembalian stock atas item service_detail yang dirubah

                                if ($checkStockExistingOlder[$i][0]->item_id == $checkDataOld[$i]->item_id) {
                                    Stock::where('item_id', $checkDataOld[$i]->item_id)
                                        ->where('branch_id', $checkData->branch_id)
                                        ->update([
                                            'stock' => $checkStockExistingOlder[$i][0]->stock + $checkDataOld[$i]->qty,
                                        ]);
                                }

                                if ($checkDataOld[$i]->type == 'SparePart') {
                                    $descPengembalian[$i] = '(Update Service) Pengembalian Barang Pada Service ' . $req->code;
                                } else {
                                    $descPengembalian[$i] = '(Update Service) Pengembalian Barang Loss Pada Service ' . $req->code;
                                }
                                StockMutation::create([
                                    'item_id' => $checkDataOld[$i]->item_id,
                                    'unit_id' => $checkStockExisting[$i][0]->unit_id,
                                    'branch_id' => $checkStockExisting[$i][0]->branch_id,
                                    'qty' => $checkDataOld[$i]->qty,
                                    'code' => $req->code,
                                    'type' => 'In',
                                    'description' => $descPengembalian[$i],
                                ]);

                                // Pegeluaran atas data item yang dirubah
                                if ($req->typeDetailOld[$i] == 'SparePart') {
                                    $descPengeluaran[$i] = '(Update Service) Pengeluaran Barang Pada Service ' . $req->code;
                                } else {
                                    $descPengeluaran[$i] = '(Update Service) Pengeluaran Barang Loss Pada Service ' . $req->code;
                                }
                                Stock::where('item_id', $req->itemsDetailOld[$i])
                                    ->where('branch_id', $checkData->branch_id)
                                    ->update([
                                        'stock' => $checkStockExisting[$i][0]->stock - $req->qtyDetailOld[$i],
                                    ]);
                                StockMutation::create([
                                    'item_id' => $req->itemsDetailOld[$i],
                                    'unit_id' => $checkStockExisting[$i][0]->unit_id,
                                    'branch_id' => $checkStockExisting[$i][0]->branch_id,
                                    'qty' => $req->qtyDetailOld[$i],
                                    'code' => $req->code,
                                    'type' => 'Out',
                                    'description' => $descPengeluaran[$i],
                                ]);

                                ServiceDetail::where('id', $req->idDetailOld[$i])->update([
                                    // 'service_id'=>$id,
                                    'item_id' => $req->itemsDetailOld[$i],
                                    'price' => str_replace(',', '', $req->priceDetailOld[$i]),
                                    'qty' => $req->qtyDetailOld[$i],
                                    'hpp' => str_replace(',', '', $req->priceDetailOld[$i]),
                                    'total_hpp' => str_replace(',', '', $req->totalPriceHppOld[$i]),
                                    'total_price' => str_replace(',', '', $req->totalPriceDetailOld[$i]),
                                    'description' => str_replace(',', '', $req->descriptionDetailOld[$i]),
                                    'type' => $req->typeDetailOld[$i],
                                    'updated_by' => Auth::user()->name,
                                    'updated_at' => date('Y-m-d h:i:s'),
                                ]);
                            }
                        }
                    }
                }
                ServiceDetail::where('id', $req->idDetailOld[0])->update([
                    // 'service_id'=>$id,
                    'item_id' => $req->itemsDetailOld[0],
                    'price' => str_replace(',', '', $req->priceDetailOld[0]),
                    'qty' => $req->qtyDetailOld[0],
                    'total_price' => str_replace(',', '', $req->totalPriceDetailOld[0]),
                    'description' => str_replace(',', '', $req->descriptionDetailOld[0]),
                    'type' => $req->typeDetailOld[0],
                    'updated_by' => Auth::user()->name,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);
            }
            
//             DB::rollback();
//             return [$req->idDetailOld,
// $req->priceDetailOld,
// $req->priceHppOld,
// $req->totalPriceHppOld];
//             return 'as';
            DB::table('service_condition')
                ->where('service_id', $id)
                ->delete();
            DB::table('service_equipment')
                ->where('service_id', $id)
                ->delete();

            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'LCD',
                'status' => $req->LcdCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'WIFI',
                'status' => $req->wifiCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Camera Depan',
                'status' => $req->cameraDepanCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Camera Belakang',
                'status' => $req->cameraBelakangCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Speaker',
                'status' => $req->speakerCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Charging',
                'status' => $req->chargingCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Mic',
                'status' => $req->micCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Speaker',
                'status' => $req->speakerCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Touch Screen',
                'status' => $req->touchScreenCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Vibrator',
                'status' => $req->vibratorCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Soket Audio',
                'status' => $req->soketAudioCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Usb',
                'status' => $req->usbCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Sinyal',
                'status' => $req->sinyalCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Tombol Tombol',
                'status' => $req->tombolCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Keyboard',
                'status' => $req->keyboardCondition,
            ]);
            ServiceCondition::create([
                'service_id' => $id,
                'name' => 'Touchpad',
                'status' => $req->touchpadCondition,
            ]);
            $dataEquipment = [];

            if ($req->chargerEquipment == 'on') {
                $dataEquipment[0] = 'Y';
                $dataEquipmentName[0] = 'Charger';
                $dataEquipmentDesc[0] = $req->chargerEquipmentDesc;
            } else {
                $dataEquipment[0] = 'N';
                $dataEquipmentName[0] = 'Charger';
                $dataEquipmentDesc[0] = $req->chargerEquipmentDesc;
            }
            if ($req->bateraiEquipment == 'on') {
                $dataEquipment[1] = 'Y';
                $dataEquipmentName[1] = 'Baterai';
                $dataEquipmentDesc[1] = $req->bateraiEquipmentDesc;
            } else {
                $dataEquipment[1] = 'N';
                $dataEquipmentName[1] = 'Baterai';
                $dataEquipmentDesc[1] = $req->bateraiEquipmentDesc;
            }
            if ($req->hardiskSsdEquipment == 'on') {
                $dataEquipment[2] = 'Y';
                $dataEquipmentName[2] = 'Hardisk / SSD';
                $dataEquipmentDesc[2] = $req->hardiskSsdEquipmentDesc;
            } else {
                $dataEquipment[2] = 'N';
                $dataEquipmentName[2] = 'Hardisk / SSD';
                $dataEquipmentDesc[2] = $req->hardiskSsdEquipmentDesc;
            }
            if ($req->RamEquipment == 'on') {
                $dataEquipment[3] = 'Y';
                $dataEquipmentName[3] = 'Ram';
                $dataEquipmentDesc[3] = $req->RamEquipmentDesc;
            } else {
                $dataEquipment[3] = 'N';
                $dataEquipmentName[3] = 'Ram';
                $dataEquipmentDesc[3] = $req->RamEquipmentDesc;
            }
            if ($req->kabelEquipment == 'on') {
                $dataEquipment[4] = 'Y';
                $dataEquipmentName[4] = 'Kabel';
                $dataEquipmentDesc[4] = $req->kabelEquipmentDesc;
            } else {
                $dataEquipment[4] = 'N';
                $dataEquipmentName[4] = 'Kabel';
                $dataEquipmentDesc[4] = $req->kabelEquipmentDesc;
            }
            if ($req->tasLaptopEquipment == 'on') {
                $dataEquipment[5] = 'Y';
                $dataEquipmentName[5] = 'Tas Laptop';
                $dataEquipmentDesc[5] = $req->tasLaptopEquipmentDesc;
            } else {
                $dataEquipment[5] = 'N';
                $dataEquipmentName[5] = 'Tas Laptop';
                $dataEquipmentDesc[5] = $req->tasLaptopEquipmentDesc;
            }
            if ($req->aksesorisEquipment == 'on') {
                $dataEquipment[6] = 'Y';
                $dataEquipmentName[6] = 'Aksesoris';
                $dataEquipmentDesc[6] = $req->aksesorisEquipmentDesc;
            } else {
                $dataEquipment[6] = 'N';
                $dataEquipmentName[6] = 'Aksesoris';
                $dataEquipmentDesc[6] = $req->aksesorisEquipmentDesc;
            }

            if ($req->lainnyaEquipment == 'on') {
                $dataEquipment[7] = 'Y';
                $dataEquipmentName[7] = 'Lainnya';
                $dataEquipmentDesc[7] = $req->lainnyaEquipmentDesc;
            } else {
                $dataEquipment[7] = 'N';
                $dataEquipmentName[7] = 'Lainnya';
                $dataEquipmentDesc[7] = $req->lainnyaEquipmentDesc;
            }
            // return [$dataEquipment,$dataEquipmentName];
            for ($i = 0; $i < count($dataEquipment); $i++) {
                ServiceEquipment::create([
                    'service_id' => $id,
                    'name' => $dataEquipmentName[$i],
                    'status' => $dataEquipment[$i],
                    'description' => $dataEquipmentDesc[$i],
                ]);
            }

            DB::commit();
            return Response::json(['status' => 'success', 'message' => 'Data Tersimpan']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
            return Response::json(['status' => 'error', 'message' => $th]);
        }
    }

    public function destroy(Request $req, $id)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(51, 'delete');
        if ($checkRoles == 'akses ditolak') {
            return Response::json(['status' => 'restricted', 'message' => 'Kamu Tidak Boleh Mengakses Fitur Ini :)']);
        }
        DB::beginTransaction();
        try {
            $getEmployee = Employee::where('user_id', Auth::user()->id)->first();
            
            $checkData = Service::where('id', $id)->first();
            $checkDataDeleted = ServiceDetail::where('service_id', $id)->get();
            $checkStockDeleted = [];
            for ($i = 0; $i < count($checkDataDeleted); $i++) {
                if ($checkDataDeleted[$i]->item_id != 1) {
                    $checkStockDeleted[$i] = Stock::where('item_id', $checkDataDeleted[$i]->item_id)
                        ->where('branch_id', $checkDataDeleted->branch_id)
                        ->where('id', '!=', 1)
                        ->get();

                    if ($checkDataDeleted[$i]->type == 'SparePart') {
                        $desc[$i] = '(Delete Service) Pengembalian Barang Pada Service ' . $req->code;
                    } else {
                        $desc[$i] = '(Delete Service) Pengembalian Barang Loss Pada Service ' . $req->code;
                    }
                    Stock::where('item_id', $checkDataDeleted[$i]->item_id)
                        ->where('branch_id', $checkDataDeleted->branch_id)
                        ->update([
                            'stock' => $checkStockDeleted[$i][0]->stock + $checkDataDeleted[$i]->qty,
                        ]);

                    // return $desc;
                    Stock::where('item_id', $checkDataDeleted[$i]->item_id)
                        ->where('branch_id', $checkDataDeleted->branch_id)
                        ->update([
                            'stock' => $checkStockDeleted[$i][0]->stock + $checkDataDeleted[$i]->qty,
                        ]);
                    StockMutation::create([
                        'item_id' => $checkDataDeleted[$i]->item_id,
                        'unit_id' => $checkStockDeleted[$i][0]->unit_id,
                        'branch_id' => $checkStockDeleted[$i][0]->branch_id,
                        'qty' => $checkDataDeleted[$i]->qty,
                        'code' => $req->code,
                        'type' => 'In',
                        'description' => $desc[$i],
                    ]);
                }
            }

            $upgrade = Item::with('Stock')->where('id',$checkData->type)->update([
                'sell'=> str_replace('.', '', $checkData->total_price_buy),
            ]);

            DB::table('service')
                ->where('id', $id)
                ->delete();
            DB::table('service_detail')
                ->where('service_id', $id)
                ->delete();
            DB::table('service_payment')
                ->where('service_id', $id)
                ->delete();
            DB::table('service_status_mutation')
                ->where('service_id', $id)
                ->delete();
            DB::table('service_condition')
                ->where('service_id', $id)
                ->delete();
            DB::table('service_equipment')
                ->where('service_id', $id)
                ->delete();
            DB::commit();
            return Response::json(['status' => 'success', 'message' => 'Data Tersimpan']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
            return Response::json(['status' => 'error', 'message' => $th]);
        }
    }
    public function codeJournals($type, $id)
    {
        $getEmployee = Employee::with('branch')
            ->where('user_id', Auth::user()->id)
            ->first();
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('y');
        $index = str_pad($id, 3, '0', STR_PAD_LEFT);
        return $code = $type . $getEmployee->Branch->code . $year . $month . $index;
    }
    public function checkJournals(Request $req)
    {
        $data = Journal::with('JournalDetail.AccountData')
            ->where('ref', $req->id)
            ->get();
        return Response::json(['status' => 'success', 'jurnal' => $data]);
    }
    public function checkStock(Request $req)
    {
        $getEmployee = Employee::with('branch')
                        ->where('user_id', Auth::user()->id)
                        ->first();

        $checkStock = Stock::where('item_id', $req->id)
                        ->where('branch_id', $getEmployee->branch_id)
                        // ->where('id', '!=', 1)
                        ->get();
        if (count($checkStock) == 0) {
            return Response::json([ 
                'status' => 'fail',
                'message' => 'Stock Item Ada yang 0. Harap Cek Kembali',
            ]);
        }
        if ($checkStock[0]->stock <= 0) {
            return Response::json([
                'status' => 'fail',
                'message' => 'Stock Item Ada yang 0. Harap Cek Kembali',
            ]);
        }else{
            return Response::json([
                'status' => 'success',
                'message' => 'Stock Item/Barang Tersedia'
            ]);
        }
    }
  

    
}
