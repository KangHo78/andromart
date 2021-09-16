@extends('layouts.backend.default')
@section('title', __('pages.title').__('Tambah Service'))
@section('titleContent', __('Tambah Service'))
@section('breadcrumb', __('Data'))
@section('morebreadcrumb')
<div class="breadcrumb-item active">{{ __('Service') }}</div>
<div class="breadcrumb-item active">{{ __('Tambah Service') }}</div>
@endsection

@section('content')
<form method="POST" class="form-data">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4>Form Data</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-12 col-md-6 col-lg-6">
                            <label for="code">{{ __('Kode Faktur') }}<code>*</code></label>
                            <input id="code" type="text" class="form-control" readonly="" value="{{$code}}" name="code">
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-6">
                            <div class="d-block">
                                <label for="serviceId"
                                    class="control-label">{{ __('Service Faktur') }}<code>*</code></label>
                            </div>
                            <select class="select2 serviceId" name="serviceId" onchange="choseService()">
                                <option value="">- Select -</option>
                                @foreach ($data as $element)
                                    <option value="{{$element->id}}">[{{$element->code}}] {{$element->customer_name}} - {{$element->brand}} {{$element->series}} <span><strong>( {{$element->work_status}} )</span></strong></option>
                                @endforeach
                            </select>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="form-group col-12 col-md-6 col-lg-6">
                            <label for="date">{{ __('Tanggal') }}<code>*</code></label>
                            <input id="date" type="text" class="form-control datepicker"
                                name="date">
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-6">
                            <label for="type">{{ __('Tipe') }}<code>*</code></label>
                            <select class="select2 type" name="type">
                                <option value="">- Select -</option>
                                <option value="Lunas">Lunas</option>
                                <option value="DownPayment">Down Payment</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-12 col-md-12 col-lg-12">
                            <label for="type">{{ __('Keterangan') }}<code>*</code></label>
                            <textarea name="description" class="form-control" id="description"></textarea>
                        </div>
                    </div>

                    <h6 style="color: #6777ef">Data Service</h6>
                    <br>
                    <div class="row">
                        <div class="form-group col-12 col-md-12 col-lg-12">
                          
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4>Harga</h4>

                </div>
                <input id="totalPriceHidden" type="hidden" value="0" class="form-control cleaveNumeral"
                name="totalPriceHidden" style="text-align: right">

                <input id="checkDpData" type="hidden" class="form-control"
                name="checkDpData" style="text-align: right">

                <div class="card-body">
                    <div class="form-group">
                        <label for="totalService">{{ __('Jasa') }}<code>*</code></label>
                        <input readonly id="totalService" onchange="sumTotal()" type="text" value="0"
                            class="form-control cleaveNumeral" name="totalService" style="text-align: right">
                    </div>
                    <div class="form-group">
                        <label for="totalSparePart">{{ __('Spare Part') }}<code>*</code></label>
                        <input readonly id="totalSparePart" onchange="sumTotal()" type="text" value="0"
                            class="form-control cleaveNumeral" name="totalSparePart" style="text-align: right">
                    </div>
                    <div class="form-group" style="display: none">
                        <label for="totalLoss" >{{ __('Total Loss') }}<code>*</code></label>
                        <input readonly id="totalLoss" onchange="sumTotal()" type="text" value="0"
                            class="form-control cleaveNumeral" name="totalLoss" style="text-align: right">
                    </div>
                    <div class="form-group DownPaymentHidden"  style="display: none">
                        <label for="totalDownPayment">{{ __('Down Payment') }}<code>*</code></label>
                        <input readonly id="totalDownPayment" onchange="sumTotal()" type="text" value="0"
                            class="form-control cleaveNumeral" name="totalDownPayment" style="text-align: right">
                    </div>
                    <div class="row">
                        <div class="form-group col-12 col-md-6 col-lg-6">
                            <label for="totalDiscountPercent">{{ __('Diskon %') }}<code>*</code></label>
                            <input readonly id="totalDiscountPercent" type="text" value="0" class="form-control cleaveNumeral"
                                name="totalDiscountPercent" onkeyup="sumTotal(),sumDiscont()" style="text-align: right">
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-6">
                            <label for="totalDiscountValue">{{ __('Diskon') }}<code>*</code></label>
                            <input readonly id="totalDiscountValue" type="text" value="0" class="form-control cleaveNumeral"
                                name="totalDiscountValue" onkeyup="sumTotal(),sumDiscont()" style="text-align: right">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="totalPayment">{{ __('Total Bayar') }}<code>*</code></label>
                        <input id="totalPayment" type="text" value="0" class="form-control cleaveNumeral"
                            name="totalPayment" style="text-align: right" onkeyup="sumTotal()">
                    </div>
                    
                    <div class="form-group">
                        <label for="totalPrice">{{ __('Total Harga') }}<code>*</code></label>
                        <input id="totalPrice" type="text" value="0" class="form-control cleaveNumeral"
                            name="totalPrice" onchange="sumTotal()" style="text-align: right">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Data Detail</h4>
            <div class="card-header-action">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 20%">Barang / Jasa</th>
                            <th>Harga</th>
                            <th style="width: 9%">qty</th>
                            <th>Total</th>
                            <th>Deskripsi</th>
                            <th style="width: 15%">tipe</th>
                        </tr>
                    </thead>
                    <tbody class="dropHereItem" style="border: none !important">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary mr-1" type="button" onclick="save()"><i class="far fa-save"></i>
                {{ __('Simpan Pelunasan Data') }}</button>
        </div>
    </div>
</form>
@endsection

@section('script')
<script src="{{ asset('assets/pages/transaction/servicePaymentScript.js') }}"></script>
@endsection