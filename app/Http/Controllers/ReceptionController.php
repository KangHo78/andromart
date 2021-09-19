<?php

namespace App\Http\Controllers;

use App\Models\Purchasing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\DataTables;
use Carbon\carbon;

class ReceptionController extends Controller
{
    public function __construct(DashboardController $DashboardController)
    {
        $this->middleware('auth');
        $this->DashboardController = $DashboardController;
    }

    public function index(Request $req)
    {
        if ($req->ajax()) {
            $data = Purchasing::with('supplier')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<div class="btn-group">';
                    $actionBtn .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                            data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>';
                    $actionBtn .= '<div class="dropdown-menu">
                        <a class="dropdown-item" href="' . route('reception.edit', Crypt::encryptString($row->id)) . '">Ubah</a>';
                    $actionBtn .= '<a onclick="del(' . $row->id . ')" class="dropdown-item" style="cursor:pointer;">Hapus</a>';
                    $actionBtn .= '</div></div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('pages.backend.transaction.reception.indexReception');
    }

    public function create()
    {
        return view('pages.backend.office.notes.createNotes');
    }

    public function store(Request $req)
    {
    }

    public function show(Notes $notes, $id)
    {
        $id = Crypt::decryptString($id);
        $models = Notes::where('id', $id)->first();
        // $models = Notes::where('notes.id', $id)
        // ->join('users', 'notes.users_id', '=', 'users.id')
        // ->select('notes.id as notes_id', 'notes.date as date', 'users.name as name', 'users.id as users_id', 'notes.title as title', 'notes.description as description')
        // ->first();
        $modelsFile = NotesPhoto::where('notes_id', $id)->get();
        // dd($modelsFile);
        return view('pages.backend.office.notes.showNotes', compact('models', 'modelsFile'));
    }

    public function edit($id)
    {
        $id = Crypt::decryptString($id);
        $model = Purchasing::where('purchasings.id', $id)
        // ->join('employees', 'purchasings.employee_id', 'employees.id')
        ->first();
        $models = Purchasing::where('purchasings.id', $id)
        ->join('purchasing_details', 'purchasings.id', 'purchasing_details.purchasing_id')
        ->join('items', 'purchasing_details.item_id', 'items.id')
        ->join('stocks', 'items.id', 'stocks.item_id')
        ->join('units', 'stocks.unit_id', 'units.id')
        ->join('branches', 'stocks.branch_id', 'branches.id')
        ->where('purchasing_details.qty', '>', 0)
        ->select('purchasing_details.id as id','qty', 'items.name as itemName', 'branches.name as branchName', 'units.name as unitName')
        ->get();
        return view('pages.backend.transaction.reception.editReception', compact('model', 'models', 'id'));
    }

    public function update(Request $req, $id)
    {
        if($req->code == Area::find($id)->code){
            Validator::make($req->all(), [
                'name' => ['required', 'string', 'max:255'],
            ])->validate();
        }
        else{
            Validator::make($req->all(), [
                'code' => ['required', 'string', 'max:255', 'unique:areas'],
                'name' => ['required', 'string', 'max:255'],
            ])->validate();
        }

        Area::where('id', $id)
            ->update([
                'code' => $req->code,
                'name' => $req->name,
                'updated_by' => Auth::user()->name,
            ]);

        $area = Area::find($id);
        $this->DashboardController->createLog(
            $req->header('user-agent'),
            $req->ip(),
            'Mengubah masrter area ' . Area::find($id)->name
        );

        $area->save();

        return Redirect::route('area.index')
            ->with([
                'status' => 'Berhasil merubah master area ',
                'type' => 'success'
            ]);
    }

    public function destroy(Request $req, $id)
    {
        $this->DashboardController->createLog(
            $req->header('user-agent'),
            $req->ip(),
            'Menghapus master area ' . Area::find($id)->name
        );

        Area::destroy($id);

        return Response::json(['status' => 'success']);
    }
    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function index()
    // {
    //     //
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function create()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  \App\Models\Purchasing  $purchasing
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show(Purchasing $purchasing)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \App\Models\Purchasing  $purchasing
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit(Purchasing $purchasing)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Models\Purchasing  $purchasing
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, Purchasing $purchasing)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \App\Models\Purchasing  $purchasing
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy(Purchasing $purchasing)
    // {
    //     //
    // }
}