@extends('layouts.backend.default')
@section('title', __('pages.title').__(' | Tambah Stock Transaksi'))
@section('titleContent', __('Tambah Stock Transaksi'))
@section('breadcrumb', __('Data'))
@section('morebreadcrumb')
<div class="breadcrumb-item active">{{ __('Service') }}</div>
<div class="breadcrumb-item active">{{ __('Tambah Stock Transaksi') }}</div>
@endsection

@section('content')
<form method="POST" class="form-data">
    @csrf
            <div class="card">
                <div class="card-header">
                    <h4>Form Data</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-12 col-md-4 col-lg-4">
                            <label for="type">{{ __('Kategori') }}<code>*</code></label>
                            <select class="select2 type validation" name="type" onchange="category()" data-name="Kategori">
                                <option value="">- Select -</option>
                                @foreach ($category as $element)
                                    <option value="{{$element->id}}">{{$element->name}}</option>
                                @endforeach
                                {{-- <option value="Handphone">Handphone</option>
                                <option value="Laptop">Laptop</option> --}}
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-4 col-lg-4">
                            <label for="brand">{{ __('Merk') }}<code>*</code></label>
                            <select class="select2 brand validation" name="brand" data-name="Merk">
                                <option value="">- Select -</option>
                                {{-- @foreach ($brand as $element)
                                <option value="{{$element->id}}">{{$element->name}}</option>
                                @endforeach --}}
                            </select>
                            {{-- <input id="brand" type="text" class="form-control" name="brand"> --}}
                        </div>
                        <div class="form-group col-12 col-md-4 col-lg-4">
                            <label for="itemId">{{ __('Nama Item') }}<code>*</code></label>
                            <select class="select2 item validation" onchange="checkStock()" name="item" data-name="Item">
                                <option value="">- Select -</option>
                                {{-- @foreach ($type as $element)
                                <option value="{{$element->id}}">{{$element->name}}</option>
                                @endforeach --}}
                            </select>
                            {{-- <input id="series" type="text" class="form-control" name="series"> --}}
                        </div>

                        @foreach ($brand as $el)
                            <input class="brandData" type="hidden"
                            data-category="{{$el->category_id}}"
                            data-name="{{$el->name}}"
                            value="{{$el->id}}">
                        @endforeach

                        @foreach ($item as $el)
                            <input class="itemData" type="hidden"
                            data-brand="{{$el->brand_id}}"
                            data-name="{{$el->name}}"
                            data-supplier="{{$el->supplier->name}}"
                            value="{{$el->id}}">
                        @endforeach

                    </div>
                    {{-- <div class="row">
                       
                    </div> --}}
                    <div class="row">
                        <div class="form-group col-12 col-md-4 col-lg-4">
                            <label for="type">{{ __('Tipe') }}<code>*</code></label>
                            <select class="select2 type checkType validation" name="type" data-name="Tipe">
                                <option value="">- Select -</option>
                                <option value="In">In (Masuk)</option>
                                <option value="Out">Out (Keluar)</option>
                                <option value="Mutation">Mutation (Pindah Cabang) </option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-4 col-lg-4">
                            <label for="date">{{ __('Tanggal') }}<code>*</code></label>
                            <input id="date" type="text" class="form-control datepicker"
                                name="date">
                        </div>
                        <div class="form-group col-12 col-md-2 col-lg-2">
                            <label>{{ __('Stock') }}<code>*</code></label>
                            <input type="text" class="form-control stockSaatIni" readonly>
                        </div>
                        <div class="form-group col-12 col-md-2 col-lg-2">
                            <label for="qty">{{ __('Qty') }}<code>*</code></label>
                            <input id="qty" type="text" class="form-control qty validation"
                                name="qty" data-name="Qty" value="0" onkeyup="sum()">
                        </div>
                    </div>
                    <div class="row hiddenReason" style="display: none">
                        <div class="form-group col-12 col-md-12 col-lg-12">
                            <label for="reason">{{ __('Alasan') }}<code>*</code></label>
                            <select class="select2 reason" name="reason">
                                <option value="">- Select -</option>
                            </select>
                        </div>
                    </div>
                    <div class="row hiddenBranch" style="display: none">
                        <div class="form-group col-12 col-md-6 col-lg-6">
                            <label for="origin">{{ __('Cabang Asal') }}<code>*</code></label>
                            <select class="form-control origin" name="origin" readonly style="pointer-events: none;">
                                <option value="">- Select -</option>
                                @foreach ($branch as $element)
                                    <option value="{{$element->id}}"
                                    @if (Auth::user()->employee->branch_id == $element->id) selected @endif>{{$element->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-6">
                            <label for="destination">{{ __('Cabang Tujuan') }}<code>*</code></label>
                            <select class="select2 destination" name="destination">
                                <option value="">- Select -</option>
                                @foreach ($branch as $element)
                                    <option value="{{$element->id}}">{{$element->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group col-12 col-md-6 col-lg-8">
                            <label for="type">{{ __('Keterangan') }}<code>*</code></label>
                            <textarea name="description" class="form-control validation" id="description" data-name="Keterangan"></textarea>
                        </div>
                       
                        <div class="form-group col-12 col-md-6 col-lg-2">
                            <label for="price">{{ __('Harga') }}<code>*</code></label>
                            <input id="price" type="text" readonly class="form-control price"
                                name="price" data-name="price">
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-2">
                            <label for="total">{{ __('Total') }}<code>*</code></label>
                            <input id="total" type="text" readonly class="form-control total"
                                name="total" data-name="total">
                        </div>
                        
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button class="btn btn-primary mr-1" type="button" onclick="save()"><i class="far fa-save"></i>
                        {{ __('Simpan Data') }}</button>
                </div>
            </div>


</form>
@endsection

@section('script')
<script src="{{ asset('assets/pages/warehouse/stockTransactionScript.js') }}"></script>
@endsection
