@extends('layouts.backend.default')
@section('title', __('pages.title') . __(' | Tambah Aktiva / Penyusutan'))
@section('titleContent', __('Tambah Aktiva / Penyusutan'))
@section('breadcrumb', __('Data'))
@section('morebreadcrumb')
    <div class="breadcrumb-item active">{{ __(' Aktiva / Penyusutan') }}</div>
    <div class="breadcrumb-item active">{{ __('Tambah  Aktiva / Penyusutan') }}</div>
@endsection

@section('content')
    <form method="POST" action="{{ route('activa.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="email" class="control-label">{{ __('code') }}</label><code>*</code>
                                <input placeholder="OTOMATIS KETIKA MENYIMPAN" type="text" class="form-control" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="address" class="control-label">{{ __('Cabang') }}<code>*</code></label>
                                <select name="branch_id" id="branch_id" class="form-control select2" required>
                                    <option value=""> - Select - </option>
                                    @foreach ($Branch as $el)
                                        <option value="{{ $el->id }}"> {{ $el->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="email"
                                    class="control-label">{{ __('Lokasi Barang') }}</label><code>*</code>
                                <input id="location" type="text" class="form-control" name="location" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email"
                                    class="control-label">{{ __('Deksripsi') }}</label><code>*</code>
                                <input id="description" type="text" class="form-control" name="description">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="address" class="control-label">{{ __('Jenis Asset') }}<code>*</code></label>
                                <select name="asset_id" id="asset_id" class="form-control select2" required >
                                    <option value=""> - Select - </option>
                                    @foreach ($Asset as $el)
                                        <option value="{{ $el->id }}"> {{ $el->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="address"
                                    class="control-label">{{ __('Golongan Aktiva') }}<code>*</code></label>
                                <select name="activa_group_id" id="activa_group_id" class="form-control select2" required onchange="appendValue()">
                                    <option value=""> - Select - </option>
                                    @foreach ($ActivaGroup as $el)
                                        <option value="{{ $el->id }}" data-estimate="{{ $el->estimate_age }}" data-depreciation="{{ $el->depreciation_rate }}"> {{ $el->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            {{-- <div class="form-group col-md-6">
                                <label for="email"
                                    class="control-label">{{ __('Barang Baru ?') }}</label><code>*</code>
                                <select name="with_items" id="with_items"
                                    class="form-control">
                                    <option> - Select - </option>
                                    <option value="Y">YA</option>
                                    <option value="N">TIDAK</option>
                                </select>
                            </div> --}}
                            <div class="form-group col-md-12">
                                <label for="email"
                                    class="control-label">{{ __('Ambil Dari Master ?') }}</label><code>*</code>
                                <select onchange="changeSelectItems()" name="with_items" id="with_items"
                                    class="form-control">
                                    <option> - Select - </option>
                                    <option value="Y">YA</option>
                                    <option value="N">TIDAK</option>
                                </select>
                            </div>
                        </div>
                        <div class="row hiddenItems" style="display: none">
                            <div class="form-group col-md-12">
                                <label for="email" class="control-label">{{ __('Nama Barang') }}</label><code>*</code>
                                <input name="items" id="items" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row hiddenItemsId" style="display: none">
                            <div class="form-group col-md-12">
                                <label for="email"
                                    class="control-label">{{ __('Ambil Dari Master / Tidak') }}</label><code>*</code>
                                <select name="items_id" id="items_id" class="form-control select2" >
                                    <option value=""> - Select - </option>
                                    @foreach ($Item as $el)
                                        <option value="{{ $el->id }}"> {{ $el->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="email"
                                    class="control-label">{{ __('Tanggal Perolehan') }}</label><code>*</code>
                                <input id="date_acquisition" type="text" class="datepicker form-control" name="date_acquisition" onchange="dateAcquisition()" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="email"
                                    class="control-label">{{ __('Bulan Selesai') }}</label><code>*</code>
                                <input id="date_finished" type="text" class="form-control" name="date_finished"
                                    readonly placeholder="OTOMATIS KETIKA MENYIMPAN">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="email"
                                    class="control-label">{{ __('QTY') }}</label><code>*</code>
                                <input id="qty" type="text" class="form-control" name="qty">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="email"
                                    class="control-label">{{ __('Nilai Perolehan') }}</label><code>*</code>
                                <input id="total_acquisition" type="text" class="form-control cleaveNumeral" name="total_acquisition" required
                                    onkeyup="calc()">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email"
                                    class="control-label">{{ __('Nilai Penyusutan Awal') }}</label><code>*</code>
                                <input id="total_early_depreciation" type="text" class="form-control cleaveNumeral" name="total_early_depreciation"
                                    >
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Perkiraan Penyusutan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="email" class="control-label">{{ __('Estimasi Tahun Penyusutan') }}</label><code>*Tahun</code>
                                <input id="estimate_age" name="estimate_age" type="text" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="email" class="control-label">{{ __('Presentase Penyusutan') }}</label><code>*%</code>
                                <input id="depreciation_rate" name="depreciation_rate" type="text" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="email" class="control-label">{{ __('Nilai Penyusutan Perbulan') }}</label><code>*</code>
                                <input name="total_depreciation" id="total_depreciation" type="text" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a class="btn btn-outline" href="javascript:window.history.go(-1);">{{ __('Kembali') }}</a>
                        <button class="btn btn-primary mr-1" type="submit">{{ __('Tambah Data Activa') }}</button>
                    </div>
                </div>
            </div>
    </form>

    
@endsection
@section('script')
    <script>
        function changeSelectItems(params) {
            var with_items = $('#with_items').val();

            if (with_items == 'Y') {
                $('.hiddenItems').css('display', 'none');
                $('.hiddenItemsId').css('display', 'block');
            } else {
                $('.hiddenItems').css('display', 'block');
                $('.hiddenItemsId').css('display', 'none');
            }
        }

        function appendValue(params) {
            $('#estimate_age').val($('#activa_group_id').find(':selected').data('estimate'));
            $('#depreciation_rate').val($('#activa_group_id').find(':selected').data('depreciation'));

            calc();
        }
        
    </script>
    <script src="{{ asset('assets/pages/master/activaScript.js') }}"></script>
@endsection
