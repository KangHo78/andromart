<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Item;
use App\Models\Asset;
use App\Models\Activa;
use App\Models\ActivaDetail;
use App\Models\ActivaGroup;
use App\Models\AccountData;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Carbon\carbon;

class ActivaController extends Controller
{
    public function __construct(DashboardController $DashboardController)
    {
        $this->middleware('auth');
        $this->DashboardController = $DashboardController;
    }

    public function index(Request $req)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(54, 'view');
        if ($checkRoles == 'akses ditolak') {
            return view('forbidden');
        }

        if ($req->ajax()) {
            $data = Activa::with('ItemsRel', 'Branch', 'AccountDepreciation', 'AccountAccumulation', 'Asset', 'ActivaGroup','ActivaDetail')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<div class="btn-">';
                    $actionBtn .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                            data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>';
                    $actionBtn .=
                        '<div class="dropdown-menu">
                           ';
                    if (count($row->ActivaDetail) == 0) {
                        $actionBtn .= '<a class="dropdown-item" href="' .
                        route('activa.edit', $row->id) .
                        '">Edit</a><a onclick="del(' . $row->id . ')" class="dropdown-item" style="cursor:pointer;">Hapus</a>';
                    }else{
                        if ($row->status == 'ACTIVE') {
                            // $actionBtn .= '<a onclick="changeStatus(' . $row->id . ')" class="dropdown-item" style="cursor:pointer;">Hentikan Penyusutan</a>';
                            $actionBtn .= '<a class="dropdown-item" href="' .
                            route('activa.stop-activa', ['id'=>$row->id]) .'">Hentikan Aktiva</a>';
                        }
                    }
                    $actionBtn .= '<a class="dropdown-item" href="' . route('activa.detail', ['id' => $row->id]) . '">Tabel Akumulasi</a>';
                    $actionBtn .= '</div></div>';
                    return $actionBtn;
                })
                ->addColumn('itemsReal', function ($row) {
                    if ($row->with_items == 'Y') {
                        return $row->ItemsRel->name;
                    } else {
                        return $row->items;
                    }
                })
                ->addColumn('val', function ($row) {
                    $htmlAdd = '<table>';
                        $htmlAdd .= '<tr>';
                            $htmlAdd .= '<th>Nilai Per.</th>';
                            $htmlAdd .= '<th>' . number_format($row->total_acquisition, 0, '.', ',') . '</th>';
                            $htmlAdd .= '<th>Tgl Per.</th>';
                            $htmlAdd .= '<th>' . date('d F Y',strtotime($row->date_acquisition)) . '</th>';
                        $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                        $htmlAdd .= '<th>Awal Peny.</th>';
                            $htmlAdd .= '<th>' . number_format($row->total_early_depreciation, 0, '.', ',') . '</th>';
                            $htmlAdd .= '<th>Nilai Peny.</th>';
                            $htmlAdd .= '<th>' . number_format($row->total_depreciation, 0, '.', ',') . '</th>';
                        $htmlAdd .= '</tr>';
                    $htmlAdd .= '<tr>';
                        $htmlAdd .= '<th>akumulasi Peny.</th>';
                        $htmlAdd .= '<th>' . number_format($row->accumulation_depreciation, 0, '.', ',') . '</th>';
                        $htmlAdd .= '<th>Sisa Peny.</th>';
                        $htmlAdd .= '<th>' . number_format($row->remaining_depreciation, 0, '.', ',') . '</th>';
                    $htmlAdd .= '</tr>';
                    $htmlAdd .= '<table>';

                    return $htmlAdd;
                })
                ->addColumn('account', function ($row) {
                    return $row->AccountDepreciation->code . '<br>' . $row->AccountDepreciation->name . '<br><br>' . $row->AccountAccumulation->code . '<br>' . $row->AccountAccumulation->name;
                })
                ->addColumn('stat', function ($row) {
                    if ($row->status == 'ACTIVE') {
                       return '<div class="badge badge-success">ACTIVE</div>';
                    } elseif ($row->status == 'NON ACTIVE') {
                       return '<div class="badge badge-danger">TIDAK AKTIF</div>';
                    }
                })
                ->rawColumns(['action', 'itemsReal', 'account', 'val','stat'])
                ->make(true);
        }
        return view('pages.backend.finance.activa.indexActiva');
    }

    public function create()
    {
        $checkRoles = $this->DashboardController->cekHakAkses(54, 'create');
        if ($checkRoles == 'akses ditolak') {
            return view('forbidden');
        }
        $Item = Item::get();
        $Branch = Branch::get();
        $Asset = Asset::get();
        $ActivaGroup = ActivaGroup::get();
        $Employee = Employee::get();
        return view('pages.backend.finance.activa.createActiva', compact('Branch', 'Asset', 'ActivaGroup', 'Item','Employee'));
    }

    public function store(Request $req)
    {
        DB::beginTransaction();
        try {
            // return $req->all();

            $dateConvert = $this->DashboardController->changeMonthIdToEn($req->date_acquisition);
            $futureDate = date('Y-m', strtotime('+' . $req->estimate_age . ' year', strtotime($dateConvert)));

            $checkAsset = Asset::where('id', $req->asset_id)->first();

            $accDepreciation = AccountData::where('branch_id', $req->branch_id)
                ->where('main_detail_id', $checkAsset->account_depreciation_id)
                ->first();

            $accAccumulation = AccountData::where('branch_id', $req->branch_id)
                ->where('main_detail_id', $checkAsset->account_accumulation_id)
                ->first();
            // return $req->all();
            // return [$checkAsset,$accDepreciation,$accAccumulation];
            for ($i = 0; $i < $req->qty; $i++) {
                $code = $this->code($req->branch_id);
                Activa::create([
                    'id' => $req->id,
                    'code' => $code,
                    'name' => $req->name,
                    'location' => $req->location,
                    'responsible' => $req->responsible,
                    'branch_id' => $req->branch_id,
                    'items_id' => $req->items_id,
                    'items' => $req->items,
                    'asset_id' => $req->asset_id,
                    'activa_group_id' => $req->activa_group_id,
                    'account_depreciation_id' => $accDepreciation->id,
                    'account_accumulation_id' => $accAccumulation->id,
                    'total_acquisition' => str_replace(',', '', $req->total_acquisition),
                    'date_acquisition' => $dateConvert,
                    'date_finished' => $futureDate,
                    'total_depreciation' => str_replace(',', '', $req->total_depreciation),
                    'total_early_depreciation' => str_replace(',', '', $req->total_early_depreciation),
                    'accumulation_depreciation' => 0,
                    'remaining_depreciation' => str_replace(',', '', $req->total_early_depreciation),
                    'description' => $req->description,
                    'with_items' => $req->with_items,
                    'status' => 'ACTIVE',
                    'created_by' => Auth::user()->name,
                ]);
            }

            $this->DashboardController->createLog($req->header('user-agent'), $req->ip(), 'Membuat aktiva baru');

            DB::commit();
            return Redirect::route('activa.index')->with([
                'status' => 'Berhasil membuat aktiva',
                'type' => 'success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
        }
    }

    // public function show(Area $area)
    // {
    //     //
    // }

    public function edit($id)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(54, 'edit');
        if ($checkRoles == 'akses ditolak') {
            return view('forbidden');
        }

        $data = Activa::find($id);
        $Item = Item::get();
        $Branch = Branch::get();
        $Asset = Asset::get();
        $ActivaGroup = ActivaGroup::get();
        $Employee = Employee::get();
        return view('pages.backend.finance.activa.updateActiva', compact('Branch', 'Asset', 'ActivaGroup', 'Item', 'data','Employee'));
    }

    public function update(Request $req, $id)
    {
        // return $req->all();
        DB::beginTransaction();
        try {

            $dateConvert = $this->DashboardController->changeMonthIdToEn($req->date_acquisition);
            $futureDate = date('Y-m', strtotime('+' . $req->estimate_age . ' year', strtotime($dateConvert)));

            $checkAsset = Asset::where('id', $req->asset_id)->first();

            $accDepreciation = AccountData::where('branch_id', $req->branch_id)
                ->where('main_detail_id', $checkAsset->account_depreciation_id)
                ->first();

            $accAccumulation = AccountData::where('branch_id', $req->branch_id)
                ->where('main_detail_id', $checkAsset->account_accumulation_id)
                ->first();
            // return $req->all();
            // return [$checkAsset,$accDepreciation,$accAccumulation];
            for ($i = 0; $i < $req->qty; $i++) {
                $code = $this->code($req->branch_id);
                Activa::where('id',$req->id)->update([
                    // 'id' => $req->id,
                    // 'code' => $code,
                    // 'name' => $req->name,
                    'location' => $req->location,
                    'branch_id' => $req->branch_id,
                    'responsible' => $req->responsible,
                    'items_id' => $req->items_id,
                    'items' => $req->items,
                    'asset_id' => $req->asset_id,
                    'activa_group_id' => $req->activa_group_id,
                    'account_depreciation_id' => $accDepreciation->id,
                    'account_accumulation_id' => $accAccumulation->id,
                    'total_acquisition' => str_replace(',', '', $req->total_acquisition),
                    'date_acquisition' => $dateConvert,
                    'date_finished' => $futureDate,
                    'total_depreciation' => str_replace(',', '', $req->total_depreciation),
                    'total_early_depreciation' => str_replace(',', '', $req->total_early_depreciation),
                    'accumulation_depreciation' => 0,
                    'remaining_depreciation' => str_replace(',', '', $req->total_early_depreciation),
                    'description' => $req->description,
                    'with_items' => $req->with_items,
                    // 'status' => 'ACTIVE',
                    'updated_by' => Auth::user()->name,
                ]);
            }

            $this->DashboardController->createLog($req->header('user-agent'), $req->ip(), 'Mengubah aktiva / Penyusutan');

            DB::commit();
            return Redirect::route('activa.index')->with([
                'status' => 'Berhasil Mengubah aktiva / Penyusutan',
                'type' => 'success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
        }
    }

    public function destroy(Request $req, $id)
    {
        DB::beginTransaction();
        try {
            $checkRoles = $this->DashboardController->cekHakAkses(54, 'delete');
            if ($checkRoles == 'akses ditolak') {
                return view('forbidden');
            }

            $data = Activa::find($id);

            $this->DashboardController->createLog($req->header('user-agent'), $req->ip(), 'Menghapus Activa / Penyusutan ' . Activa::find($id)->name);

            ActivaDetail::where('activa_id',$id)->delete();
            Activa::destroy($id);

            DB::commit();
            return Response::json([
                'status' => 'success',
                'message' => 'Data berhasil dihapus !',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
        }
    }
    public function code($cabang)
    {
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('y');
        $co = Activa::where('branch_id', $cabang)
            ->whereMonth('created_at', now())
            ->get();
        $index = count($co) + 1;
        // $index = DB::table('service')->max('id') + 1;
        $index = str_pad($index, 3, '0', STR_PAD_LEFT);

        return 'ACT' . $cabang . $year . $month . $index;
    }
    public function codeJournals($type, $id,$branch)
    {
        $branch = Branch::where('id',$branch)->first();
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('y');
        $index = str_pad($id, 3, '0', STR_PAD_LEFT);
        return $type . $branch->code . $year . $month . $index;
    }
    public function detail(Request $req)
    {
        $data = Activa::with('ItemsRel', 'Branch', 'AccountDepreciation', 'AccountAccumulation', 'Asset', 'ActivaGroup','ActivaDetail','ActivaDetail.Journals')
            ->where('id', $req->id)
            ->first();
        // return $data;
        return view('pages.backend.finance.activa.detailActiva', compact('data'));
    }
    public function depreciation()
    {
        $data = Activa::with('ItemsRel', 'Branch', 'AccountDepreciation', 'AccountAccumulation', 'Asset', 'ActivaGroup')
                      ->where('date_acquisition','<',date('Y-m-01'))
                      ->whereDoesntHave('ActivaDetail', function($q){
                            $q->where('period_depreciation', '=', date('Y-m'));
                        })
                      ->get();
        // return $data;
        
        return view('pages.backend.finance.activa.depreciationActiva', compact('data'));
    }
    public function changeStatus(Request $req)
    {
        DB::beginTransaction();
        try {
            Activa::where('id', $req->id)->update([
                'status'=>'NON ACTIVE',
            ]);
            DB::commit();
            return Response::json([
                'status' => 'success',
                'message' => 'Data master berhasil dihapus !',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
        }
    }
    public function storeDepreciation(Request $req)
    {
        // return $req->all();
        DB::beginTransaction();
        try {
            for ($i = 0; $i < count($req->code); $i++) {
                Activa::where('id', $req->id[$i])->where('status','ACTIVE')->update([
                    'remaining_depreciation' => 
                    DB::raw('remaining_depreciation - ' . $req->total_depreciation[$i]),
                    'accumulation_depreciation' => 
                    DB::raw('accumulation_depreciation + ' . $req->total_depreciation[$i]),
                    'updated_by' => Auth::user()->name,
                ]);

                $idJournal = DB::table('journals')->max('id') + 1;
                // DB::rollback();
                $kode = $this->codeJournals('KAC',$idJournal,$req->branch_id[$i]);

                Journal::create([
                    'id' => $idJournal,
                    'code' => $kode,
                    'year' => date('Y'),
                    'date' => date('Y-m-t'),
                    'type' => 'Beban Penyusutan',
                    'total' => str_replace(',', '', $req->total_depreciation[$i]),
                    'ref' => $req->code[$i],
                    'description' => 'Beban Penyusutan ' . $req->code[$i],
                    'created_at' => date('Y-m-d h:i:s'),
                ]);

                $AccountDepreciation = AccountData::where('branch_id', $req->branch_id[$i])
                    ->where('active', 'Y')
                    ->where('id', $req->account_depreciation_id[$i])
                    ->first();

                $accountAccumulation = AccountData::where('branch_id', $req->branch_id[$i])
                    ->where('active', 'Y')
                    ->where('id', $req->account_accumulation_id[$i])
                    ->first();

                $idDetail = DB::table('journal_details')->max('id') + 1;
                JournalDetail::create([
                    'id' => $idDetail,
                    'journal_id' => $idJournal,
                    'account_id' => $AccountDepreciation->id,
                    'total' => $req->total_depreciation[$i],
                    'description' => 'Beban Penyusutan',
                    'debet_kredit' => 'D',
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);
                $idDetail2 = DB::table('journal_details')->max('id') + 1;
                JournalDetail::create([
                    'id' => $idDetail2,
                    'journal_id' => $idJournal,
                    'account_id' => $accountAccumulation->id,
                    'total' => $req->total_depreciation[$i],
                    'description' => 'Beban Akumulasi Penyusutan',
                    'debet_kredit' => 'K',
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);
                
                ActivaDetail::create([
                    'activa_id' => $req->id[$i],
                    'branch_id' => $req->branch_id[$i],
                    'account_depreciation_id' => $req->account_depreciation_id[$i],
                    'account_accumulation_id' => $req->account_accumulation_id[$i],
                    'total_depreciation' => $req->total_depreciation[$i],
                    'period_depreciation' => $req->period_depreciation[$i],
                    'ref_journals'=>$kode,
                    'created_by' => Auth::user()->name,
                ]);
            }

            DB::commit();
            return Response::json([
                'status' => 'success',
                'message' => 'Data master berhasil dihapus !',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
        }
    }
    public function activaCheckJournals(Request $req)
    {
        $data = Journal::with('JournalDetail.AccountData')
            ->where('code', $req->id)
            ->first();
        return Response::json(['status' => 'success', 'jurnal' => $data]);
    }
    public function excelView(Request $req)
    {
        $branch = Branch::get();
        
        $checkBranch = Branch::where(function ($q) use ($req) {
            if ($req->branch_id == '') {
                
            } else {
                $q->where('id', $req->branch_id);
            }
        })->first();

        $ActivaGroup = Asset::get();
        $location = Activa::select('location')->groupBy('location')->get();
        $Employee = Employee::get();
        $data = Activa::with('ItemsRel', 'Branch', 'AccountDepreciation', 'AccountAccumulation', 'Asset', 'ActivaGroup','ActivaDetail','UserResponsible')->where(function ($q) use ($req) {
            if ($req->branch_id == '') {
            } else {
                $q->where('branch_id', $req->branch_id);
            }
            if ($req->asset_id == '') {
            } else {
                $q->where('asset_id', $req->asset_id);
            }
            if ($req->responsible_id == '') {
            } else {
                $q->where('responsible', $req->responsible_id);
            }
            if ($req->location_id == '') {
            } else {
                $q->where('location', $req->location_id);
            }
        })
        ->get();
        return view('pages.backend.finance.activa.excelActiva',compact('data','branch','ActivaGroup','Employee','checkBranch','location'));
    }
    public function stopActiva(Request $req)
    {
        $checkRoles = $this->DashboardController->cekHakAkses(54, 'edit');
        if ($checkRoles == 'akses ditolak') {
            return view('forbidden');
        }

        $data = Activa::find($req->id);
        $Item = Item::get();
        $Branch = Branch::get();
        $Asset = Asset::get();
        $ActivaGroup = ActivaGroup::get();
        $Employee = Employee::get();
        return view('pages.backend.finance.activa.stopActiva', compact('Branch', 'Asset', 'ActivaGroup', 'Item', 'data','Employee'));
    }
    public function stopStoreActiva(Request $req)
    {
        DB::beginTransaction();
        try {
            $checkData = Activa::where('id', $req->id)->first();
            
            if ($req->reason == 'Broken') {
                $data = [
                    'status'=>'NON ACTIVE',
                    'reason'=>$req->reason,
                ];
            } elseif($req->reason == 'Mutasi') {
                $data = [
                    'status'=>'NON ACTIVE',
                    'branch_id'=>$req->branch_id,
                    'reason'=>$req->reason,
                ];
            }else{
                $data = [
                    'status'=>'NON ACTIVE',
                    'reason'=>$req->reason,
                    'sell_price'=>str_replace(',', '', $req->sell_price),
                ];
                $idJournal = DB::table('journals')->max('id') + 1;
                // DB::rollback();
                $kode = $this->codeJournals('KAC',$idJournal,$req->branch_id[$i]);

                Journal::create([
                    'id' => $idJournal,
                    'code' => $kode,
                    'year' => date('Y'),
                    'date' => date('Y-m-t'),
                    'type' => 'Beban Penyusutan',
                    'total' => str_replace(',', '', $req->total_depreciation),
                    'ref' => $req->code,
                    'description' => 'Beban Penyusutan ' . $req->code,
                    'created_at' => date('Y-m-d h:i:s'),
                ]);

                $AccountDepreciation = AccountData::where('branch_id', $req->branch_id)
                    ->where('active', 'Y')
                    ->where('id', $req->account_depreciation_id)
                    ->first();

                $accountAccumulation = AccountData::where('branch_id', $req->branch_id)
                    ->where('active', 'Y')
                    ->where('id', $req->account_accumulation_id)
                    ->first();

                $idDetail = DB::table('journal_details')->max('id') + 1;
                JournalDetail::create([
                    'id' => $idDetail,
                    'journal_id' => $idJournal,
                    'account_id' => $AccountDepreciation->id,
                    'total' => $req->total_depreciation,
                    'description' => 'Beban Penyusutan',
                    'debet_kredit' => 'D',
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);
            }

            Activa::where('id', $req->id)->update(
                $data
            );
            
            DB::commit();
            return Response::json([
                'status' => 'success',
                'message' => 'Data master berhasil dihapus !',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
        }
    }
}
