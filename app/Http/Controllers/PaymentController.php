<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Branch;
use App\Models\Cash;
use App\Models\AccountData;
use App\Models\Cost;
use App\Models\Type;
use App\Models\Journal;
use App\Models\Employee;
use App\Models\JournalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Carbon\carbon;

class PaymentController extends Controller
{
    public function __construct(DashboardController $DashboardController)
    {
        $this->middleware('auth');
        $this->DashboardController = $DashboardController;
    }

    public function index(Request $req)
    {
        if ($req->ajax()) {
            $data = Payment::with('cost', 'branch', 'cash')->orderBy('id','DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    $htmlAdd = '<tr>';
                    $htmlAdd .=
                        '<td>' .
                        Carbon::parse($row->date)
                            ->locale('id')
                            ->isoFormat('LL') .
                        '</td>';
                    $htmlAdd .= '</tr>';

                    return $htmlAdd;
                })
                ->addColumn('price', function ($row) {
                    $htmlAdd = '<tr>';
                    $htmlAdd .= '<td>' . number_format($row->price, 0, '.', ',') . '</td>';
                    $htmlAdd .= '</tr>';

                    return $htmlAdd;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<div class="btn-group">';
                    $actionBtn .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                            data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>';
                    $actionBtn .= '<div class="dropdown-menu">';
                    $actionBtn .= '<a class="dropdown-item" href="' . route('payment.edit', $row->id) . '"><i class="fas fa-pencil-alt"></i> Edit</a>';
                    $actionBtn .= '<a onclick="jurnal(' . "'" . $row->code . "'" . ')" class="dropdown-item" style="cursor:pointer;"><i class="fas fa-file-alt"></i> Jurnal</a>';
                    $actionBtn .= '<a onclick="del(' . $row->id . ')" class="dropdown-item" style="cursor:pointer;"><i class="far fa-trash-alt"></i> Hapus</a>';
                    $actionBtn .= '<a href="' . route('payment.show', $row->id) . '" class="dropdown-item" style="cursor:pointer;"><i class="far fa-eye"></i> Lihat</a>';
                    $actionBtn .= '</div></div>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'date', 'price'])
                ->make(true);
        }
        return view('pages.backend.transaction.payment.indexPayment');
    }

    public function code($type)
    {
        $getEmployee = Employee::with('branch')
            ->where('user_id', Auth::user()->id)
            ->first();
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('y');
        $index = DB::table('payments')->max('id') + 1;

        $index = str_pad($index, 3, '0', STR_PAD_LEFT);
        return $code = $type . $getEmployee->Branch->code . $year . $month . $index;
    }

    public function codeJournals($type)
    {
        $getEmployee = Employee::with('branch')
            ->where('user_id', Auth::user()->id)
            ->first();
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('y');
        $index = DB::table('journals')->max('id') + 1;

        $index = str_pad($index, 3, '0', STR_PAD_LEFT);
        return $code = $type . $getEmployee->Branch->code . $year . $month . $index;
    }
    
    public function create()
    {
        $checkBranch = Employee::with('branch')
            ->where('user_id', Auth::user()->id)
            ->first();
        $code = $this->code('SPND');
        if ($checkBranch->branch_id == 1) {
            $branch = Branch::get();
            $cash = AccountData::get();
            $cash_transfer = AccountData::get();
        } else {
            $branch = Branch::where('id', $checkBranch->branch_id)->get();
            $cash = AccountData::where('branch_id', $checkBranch->branch_id)->get();
            $cash_transfer = AccountData::get();
        }

        return view('pages.backend.transaction.payment.createPayment', compact('cash', 'code', 'branch', 'cash_transfer'));
    }

    public function store(Request $req)
    {
     
        // return 'asd';
        // return $req->all();
        // return $this->checkSaldoKas($req->date));
        DB::beginTransaction();
        try {
            $date = $this->DashboardController->changeMonthIdToEn($req->date);

            Payment::create([
                'code' => $req->code,
                'date' => $date,
                'cost_id' => $req->cost_id,
                'branch_id' => $req->branch_id,
                'type' => $req->type_id,
                'transfer_to' => $req->cash_tranfer_id,
                'cash_id' => $req->cash_id,
                'price' => str_replace(',', '', $req->price),
                'description' => $req->description,
                'created_by' => Auth::user()->name,
            ]);

            if ($req->type_id == 'Transfer') {
                $idJournal = DB::table('journals')->max('id') + 1;
                Journal::create([
                    'id' => $idJournal,
                    'code' => $this->codeJournals('KK', $idJournal),
                    'year' => date('Y', strtotime($date)),
                    'date' => $date,
                    'type' => 'Transfer Keluar',
                    'total' => str_replace(',', '', $req->price),
                    'ref' => $req->code,
                    'description' => $req->description,
                    'created_at' => date('Y-m-d h:i:s'),
                    // 'updated_at'=>date('Y-m-d h:i:s'),
                ]);

                $accountPembayaran = AccountData::where('id', $req->account)->first();
                $accountCode = [$req->cost_id, $req->cash_id];
                $totalBayar = [str_replace(',', '', $req->price), str_replace(',', '', $req->price)];
                $description = [$req->description, $req->description];
                $DK = ['D', 'K'];

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

                $idJournalMasuk = DB::table('journals')->max('id') + 1;
                Journal::create([
                    'id' => $idJournalMasuk,
                    'code' => $this->codeJournals('DD', $idJournalMasuk),
                    'year' => date('Y', strtotime($date)),
                    'date' => $date,
                    'type' => 'Transfer Masuk',
                    'total' => str_replace(',', '', $req->price),
                    'ref' => $req->code,
                    'description' => $req->description,
                    'created_at' => date('Y-m-d h:i:s'),
                    // 'updated_at'=>date('Y-m-d h:i:s'),
                ]);

                $accountPembayaran = AccountData::where('id', $req->cash_tranfer_id)->first();
                $accountMutasi = AccountData::where('id', $req->cost_id)->first();
                $accountCabangLawan = AccountData::where('main_detail_id', $accountMutasi->main_detail_id)->where('branch_id',$accountPembayaran->branch_id)->first();

                $accountCode = [$accountPembayaran->id, $accountCabangLawan->id];
                $totalBayar = [str_replace(',', '', $req->price), str_replace(',', '', $req->price)];
                $description = [$req->description, $req->description];
                $DK = ['D', 'K'];

                for ($i = 0; $i < count($accountCode); $i++) {
                    $idDetailMasuk = DB::table('journal_details')->max('id') + 1;
                    JournalDetail::create([
                        'id' => $idDetailMasuk,
                        'journal_id' => $idJournalMasuk,
                        'account_id' => $accountCode[$i],
                        'total' => $totalBayar[$i],
                        'description' => $description[$i],
                        'debet_kredit' => $DK[$i],
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]);
                }
            } else {
                $idJournal = DB::table('journals')->max('id') + 1;
                Journal::create([
                    'id' => $idJournal,
                    'code' => $this->codeJournals('KK', $idJournal),
                    'year' => date('Y', strtotime($date)),
                    'date' => $date,
                    'type' => 'Biaya',
                    'total' => str_replace(',', '', $req->price),
                    'ref' => $req->code,
                    'description' => $req->description,
                    'created_at' => date('Y-m-d h:i:s'),
                    // 'updated_at'=>date('Y-m-d h:i:s'),
                ]);

                $accountPembayaran = AccountData::where('id', $req->account)->first();
                $accountCode = [$req->cost_id, $req->cash_id];
                $totalBayar = [str_replace(',', '', $req->price), str_replace(',', '', $req->price)];
                $description = [$req->description, $req->description];
                $DK = ['D', 'K'];

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
            }

            $this->DashboardController->createLog($req->header('user-agent'), $req->ip(), 'Membuat transaksi pembayaran baru');

            DB::commit();
            return Response::json([
                'status' => 'success',
                'message' => 'Berhasil Menyimpan Data',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function show($id)
    {
        
        $payment = Payment::where('code',$id)->first();
        if (isset($payment) == 1) {
            $payment = $payment;
        }else{
            $payment = Payment::find($id);
        }

        $checkBranch = Employee::with('branch')
            ->where('user_id', Auth::user()->id)
            ->first();
        $code = $this->code('SPND');
        if ($checkBranch->branch_id == 1) {
            $branch = Branch::get();
            $cash = AccountData::get();
            $cash_transfer = AccountData::get();
        } else {
            $branch = Branch::where('id', $checkBranch->branch_id)->get();
            $cash = AccountData::where('branch_id', $checkBranch->branch_id)->get();
            $cash_transfer = AccountData::get();
        }

        return view('pages.backend.transaction.payment.showPayment', ['payment' => $payment, 'branch' => $branch, 'cash' => $cash, 'cash_transfer' => $cash_transfer]);
    }

    public function edit($id)
    {
        // $branch = Branch::where('id', '!=', Payment::find($id)->branch_id)->get();
        // $cost = Cost::where('id', '!=', Payment::find($id)->cost_id)->get();
        // $cash = Cash::where('id', '!=', Payment::find($id)->cash_id)->get();
        $payment = Payment::find($id);

        $checkBranch = Employee::with('branch')
            ->where('user_id', Auth::user()->id)
            ->first();
        $code = $this->code('SPND');
        if ($checkBranch->branch_id == 1) {
            $branch = Branch::get();
            $cash = AccountData::get();
            $cash_transfer = AccountData::get();
        } else {
            $branch = Branch::where('id', $checkBranch->branch_id)->get();
            $cash = AccountData::where('branch_id', $checkBranch->branch_id)->get();
            $cash_transfer = AccountData::get();
        }

        return view('pages.backend.transaction.payment.updatePayment', ['payment' => $payment, 'branch' => $branch, 'cash' => $cash, 'cash_transfer' => $cash_transfer]);
    }

    public function update(Request $req, $id)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::find($req->id);
            $checkJurnal = DB::table('journals')
                ->where('ref', $payment->code)
                ->get();

            for ($i = 0; $i < count($checkJurnal); $i++) {
                DB::table('journal_details')
                    ->where('journal_id', $checkJurnal[$i]->id)
                    ->delete();
                DB::table('journals')
                    ->where('id', $checkJurnal[$i]->id)
                    ->delete();
            }

            DB::table('payments')
                ->where('id', $req->id)
                ->delete();

            $date = $this->DashboardController->changeMonthIdToEn($req->date);

            Payment::create([
                // 'id'=>$req->id
                'code' => $req->code,
                'date' => $date,
                'cost_id' => $req->cost_id,
                'branch_id' => $req->branch_id,
                'type' => $req->type_id,
                'transfer_to' => $req->cash_tranfer_id,
                'cash_id' => $req->cash_id,
                'price' => str_replace(',', '', $req->price),
                'description' => $req->description,
                'created_by' => Auth::user()->name,
            ]);

            if ($req->type_id == 'Transfer') {
                $idJournal = DB::table('journals')->max('id') + 1;
                Journal::create([
                    'id' => $idJournal,
                    'code' => $this->codeJournals('KK', $idJournal),
                    'year' => date('Y', strtotime($date)),
                    'date' => $date,
                    'type' => 'Transfer Keluar',
                    'total' => str_replace(',', '', $req->price),
                    'ref' => $req->code,
                    'description' => $req->description,
                    'created_at' => date('Y-m-d h:i:s'),
                    // 'updated_at'=>date('Y-m-d h:i:s'),
                ]);

                $accountPembayaran = AccountData::where('id', $req->account)->first();
                $accountCode = [$req->cost_id, $req->cash_id];
                $totalBayar = [str_replace(',', '', $req->price), str_replace(',', '', $req->price)];
                $description = [$req->description, $req->description];
                $DK = ['D', 'K'];

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

                $idJournalMasuk = DB::table('journals')->max('id') + 1;
                Journal::create([
                    'id' => $idJournalMasuk,
                    'code' => $this->codeJournals('DD', $idJournalMasuk),
                    'year' => date('Y', strtotime($date)),
                    'date' => $date,
                    'type' => 'Transfer Masuk',
                    'total' => str_replace(',', '', $req->price),
                    'ref' => $req->code,
                    'description' => $req->description,
                    'created_at' => date('Y-m-d h:i:s'),
                    // 'updated_at'=>date('Y-m-d h:i:s'),
                ]);

                $accountPembayaran = AccountData::where('id', $req->cash_tranfer_id)->first();

                $accountCode = [$accountPembayaran->id, $req->cost_id];
                $totalBayar = [str_replace(',', '', $req->price), str_replace(',', '', $req->price)];
                $description = [$req->description, $req->description];
                $DK = ['D', 'K'];

                for ($i = 0; $i < count($accountCode); $i++) {
                    $idDetailMasuk = DB::table('journal_details')->max('id') + 1;
                    JournalDetail::create([
                        'id' => $idDetailMasuk,
                        'journal_id' => $idJournalMasuk,
                        'account_id' => $accountCode[$i],
                        'total' => $totalBayar[$i],
                        'description' => $description[$i],
                        'debet_kredit' => $DK[$i],
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]);
                }
            } else {
                $idJournal = DB::table('journals')->max('id') + 1;
                Journal::create([
                    'id' => $idJournal,
                    'code' => $this->codeJournals('KK', $idJournal),
                    'year' => date('Y', strtotime($date)),
                    'date' => $date,
                    'type' => 'Biaya',
                    'total' => str_replace(',', '', $req->price),
                    'ref' => $req->code,
                    'description' => $req->description,
                    'created_at' => date('Y-m-d h:i:s'),
                    // 'updated_at'=>date('Y-m-d h:i:s'),
                ]);

                $accountPembayaran = AccountData::where('id', $req->account)->first();
                $accountCode = [$req->cost_id, $req->cash_id];
                $totalBayar = [str_replace(',', '', $req->price), str_replace(',', '', $req->price)];
                $description = [$req->description, $req->description];
                $DK = ['D', 'K'];

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
            } +

            $this->DashboardController->createLog($req->header('user-agent'), $req->ip(), 'Membuat transaksi pembayaran baru');

            DB::commit();
            return Response::json([
                'status' => 'success',
                'message' => 'Berhasil Menyimpan Data',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function destroy(Request $req, $id)
    {
        $this->DashboardController->createLog($req->header('user-agent'), $req->ip(), 'Menghapus Data Pengeluaran');
        $payment = Payment::find($id);
        $checkJurnal = DB::table('journals')
            ->where('ref', $payment->code)
            ->get();
        for ($i = 0; $i < count($checkJurnal); $i++) {
            DB::table('journal_details')
                ->where('journal_id', $checkJurnal[$i]->id)
                ->delete();
            DB::table('journals')
                ->where('id', $checkJurnal[$i]->id)
                ->delete();
        }

        DB::table('payments')
            ->where('id', $id)
            ->delete();
        return Response::json(['status' => 'success']);
    }

    function paymentCheckJournals(Request $req)
    {
        $data = Journal::with('JournalDetail.AccountData')
            ->where('ref', $req->id)
            ->get();
        return Response::json(['status' => 'success', 'jurnal' => $data]);
    }
}
