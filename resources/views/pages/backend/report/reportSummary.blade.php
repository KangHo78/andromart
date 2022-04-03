@extends('layouts.backend.default')
@section('title', __('pages.title') . __(' | Laporan Total Summary'))
@section('titleContent', __('Laporan Total Summary'))
@section('breadcrumb', __('Data'))
@section('morebreadcrumb')
    <div class="breadcrumb-item active">{{ __('Laporan Total Summary') }}</div>
@endsection

@section('content')
    {{-- @include('pages.backend.components.filterSearch') --}}
    @include('layouts.backend.components.notification')

    {{-- <style>
    .areaToPrint{
        font-size: 10px;
    }
</style> --}}

    <form class="form-data">
        @csrf
        <section class="section">
            <div class="section-body">
                 <div class="row">
                    <div class="col-12">
                        <h2 class="section-title">Search Data </h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-12 col-md-4 col-lg-4">
                                        <label for="startDate">{{ __('Tanggal Awal') }}<code>*</code></label>
                                        <input id="startDate" type="text" class="form-control datepicker" name="startDate">
                                    </div>
                                    <div class="form-group col-12 col-md-4 col-lg-4">
                                        <label for="endDate">{{ __('Tanggal Akhir') }}<code>*</code></label>
                                        <input id="endDate" type="text" class="form-control datepicker" name="endDate">
                                    </div>
                                    <div class="form-group col-12 col-md-4 col-lg-4">
                                        <label for="startDate">{{ __('cabang') }}<code>*</code></label>
                                        <select class="form-control cabang" name="cabang">
                                            <option value="">- select -</option>
                                            @foreach ($branch as $el)
                                                <option value="{{$el->id}}">{{$el->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-12 col-md-12 col-lg-12">
                                        <button class="btn btn-primary" style="margin-right: 20px" type="button"
                                            onclick="checkData()"><i class="fas fa-eye"></i> Cari</button>
                                        <button class="btn btn-warning" type="button" onclick="printDiv()"><i
                                                class="fas fa-print"></i> Cetak</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="col-12">
                    <h2 class="section-title">Total Summary</h2>
                    <div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-dollar-sign fa-2xl"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Pembelian </h4>
                                        </div>
                                        <div class="card-body">
                                            {{number_format($totalPembelian, 0, '.', ',') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-danger">
                                        <i class="fas fa-dollar-sign fa-2xl"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Pengeluaran</h4>
                                        </div>
                                        <div class="card-body">
                                            {{number_format($totalPengeluaran, 0, '.', ',') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-dollar-sign fa-2xl"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Penjualan</h4>
                                        </div>
                                        <div class="card-body">
                                            {{number_format($totalPenjualan-$totalDiskonPenjualan, 0, '.', ',') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-dollar-sign fa-2xl"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Pembayaran Service</h4>
                                        </div>
                                        <div class="card-body">
                                            {{number_format($totalService-$totalDiskonService, 0, '.', ',') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
        </section>
    </form>
@endsection
@section('script')

    <script>
        function checkData() {
            var startDate = $("#startDate").val();
            var endDate = $("#endDate").val();
            var cabang = $(".cabang").val();
            

            window.location.href = '{{ route('report-summary.reportSummary') }}?&startDate=' + startDate+'&endDate='+endDate+'&cabang='+cabang;
        }

    </script>
    {{-- <script src="{{ asset('assets/pages/finance/reportIncomeSpending.js') }}"></script> --}}

@endsection