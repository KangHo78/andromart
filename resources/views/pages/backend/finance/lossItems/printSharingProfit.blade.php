@include('layouts.components.header')
<style>
    @media print {
        body {
            -webkit-print-color-adjust: exact !important;
        }
    }

    @media print {
        .table th {
            background-color: #1d98d4 !important;
            color: white !important;
        }
    }

    @media print {
        .table th.thred {
            background-color: red !important;
            color: white !important;
        }
    }

    .invoice-number {
        margin-top: -230px !important;
    }

    .table.table-md td,
    .table.table-md th {
        padding: 5px !important;
    }

</style>

<div class="invoice">
    <div class="invoice-print">
        <div class="row">
            <div class="col-lg-12">
                <div class="invoice-title">
                    {{-- <h2>Invoice</h2> --}}
                    <h2><img alt="Porto" height="150" src="{{ asset('assetsfrontend/img/andromart.png') }}"
                            style="margin-top: 10px;"></h2>
                    <div style="width: 400px">
                        <p style="font-size: 15px">{{ Auth::user()->employee->branch->address }} <b> Tlp :
                                {{ Auth::user()->employee->branch->phone }}</b> </p>
                    </div>
                    <div class="invoice-number">
                        <h3>Job Order :</h3>
                        <h1 style="font-size: 50px;color:#eb2390">{{ $service->Service->code }}</h1>
                        <br>
                        <p style="font-size: 19px;font-weight:lighter">Lacak Perkembangan Service Kamu di : <br>
                            <b>www.andromartindonesia.com</b>
                            {{-- <br> <b> AM care : 0851-5646-2356 --}}
                            {{-- <br>Konsultasi Service --}}
                        </p>
                        {{-- </b> --}}
                    </div>

                </div>
                {{-- <div style="border: 1px solid #1d98d4"></div> --}}

                {{-- <br> --}}
                {{-- <hr> --}}
                <div class="row">
                    <div class="col-md-4">
                        <address>
                            <strong>
                                <p style="font-size: 25px"
                                    style="background-color:#eb2390;color:white;padding:5px;text-align:center">Teknisi
                                </p>
                            </strong>
                            <p style="font-size: 26px">{{ $service->Service->employee1->name }}</p>
                            {{-- <p style="font-size: 26px">{{$service->Service->employee1->contact}}</p> --}}
                        </address>
                    </div>
                    <div class="col-md-8 text-md-right">
                        <address>
                            <strong>
                                <p style="font-size: 25px"
                                    style="background-color:#eb2390;color:white;padding:5px;text-align:center">Customer
                                </p>
                            </strong>
                            <p style="font-size: 26px"><b>{{ $service->Service->customer_name }}</b></p>
                            <p style="font-size: 26px">{{ $service->Service->customer_phone }}</p>
                            <p style="font-size: 26px;margin: 10px auto;">{{ $service->Service->customer_address }}
                            </p>
                        </address>
                    </div>
                </div>
                <div style="border: 1px solid #1d98d4"></div>
                <div class="row">
                    <div class="col-md-6">

                        <address>
                            <br>
                            <p style="font-size: 30px"><strong>
                                    <o>Bayar :
                                </strong></o>
                                @if ($service->Service->payment_status == null)
                                    @if ($service->Service->verification_price == 'Y')
                                        <o style="font-size:30px"> Perlu Konfirmasi</o>
                                    @else
                                        <o style="font-size:30px"> Belum Bayar</o>
                                    @endif
                                @else
                                    {{ $service->Service->payment_status }}
                                @endif
                            </p>
                            {{-- <strong><h3 style="color:#28a745"> </h3></strong>s --}}
                        </address>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <address>
                            <br>
                            <strong>
                                <p style="font-size: 30px">Tanggal :
                                    {{ date('d F Y', strtotime($service->date)) }}</p>
                            </strong>
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div style="border: 1px solid gray"></div>


        <div class="row mt-4" style="margin-top: 0px !important">
            <div class="col-md-12">
                {{-- <div class="section-title"><h3>Service Detail</h3></div> --}}
                <div>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                            </tr>
                            {{-- <th class="text-center" style="font-weight:700;font-size: 25px;padding:0px !important" width="40%">Service Detail</th> --}}
                            </tr>
                            <tr>
                                {{-- <td style="border-right: 1px solid #1d98d4"  style="font-size: 17px">{{$service->Service->Brand->Category->name}} : --}}
                                {{-- <b>{{$service->Service->Brand->name}} {{$service->Service->Type->name}} [ {{$service->Service->no_imei}} ] </b> </td> --}}
                                {{-- <td>Pembayaran</td> --}}
                            </tr>
                            <tr>
                                {{-- <td style="font-size: 17px">Imei : {{$service->Service->no_imei}}</td> --}}
                                {{-- <td style="border-right: 1px solid #1d98d4" style="font-size: 17px">Analisa : <b>{{$service->Service->estimate_day}}</b> --}}
                            </tr>
                            {{-- <tr>
                <td style="font-size: 20px">Merk</td>
                <td style="border-right: 1px solid #1d98d4" style="font-size: 20px">{{$service->Service->Brand->name}}</td>
              </tr>
              <tr>
                <td style="font-size: 20px">Series</td>
                <td style="border-right: 1px solid #1d98d4" style="font-size: 20px">{{$service->Service->Type->name}}</td>
              </tr>
              <tr>
                <td  style="font-size: 20px">Estimasi Analisa</td>
                <td style="border-right: 1px solid #1d98d4" style="font-size: 20px">{{$service->Service->estimate_day}}</td>
              </tr> --}}
                        </tbody>
                    </table>
                </div>
                {{-- <div style="border: 1px solid gray"></div> --}}
                <div>
                    <table class="table table-striped  table-md">
                        <tbody>
                            <tr>
                                <th data-width="40" style="width: 40px;" style="font-size: 25px">#</th>
                                <th style="font-size: 25px">Item</th>
                                {{-- <th class="text-center" style="font-size: 25px">Garansi</th> --}}
                                <th class="text-center" style="font-size: 25px">Qty</th>
                                {{-- <th class="text-right" style="font-size: 25px">total</th> --}}
                            </tr>
                            @php
                                $price = 0;
                                $qty = 0;
                                $totalPrice = 0;
                            @endphp
                            @foreach ($service->Service->ServiceDetail as $i => $el)
                                @if ($el->Items->id == '1')
                                    <tr>
                                        <td style="font-size: 20px">{{ $i + 1 }}</td>
                                        <td style="font-size: 20px">{{ $el->Items->name }}</td>
                                        {{-- <td style="font-size: 20px">{{$service->Service->warranty_id}}</td> --}}
                                        {{-- <td style="font-size: 20px" class="text-center">{{number_format($el->price,0,".",",")}}</td> --}}
                                        <td style="font-size: 20px" class="text-center">
                                            {{ number_format($el->qty, 0, '.', ',') }}</td>
                                        {{-- <td style="font-size: 20px" class="text-right">{{number_format($el->total_price,0,".",",")}}</td> --}}
                                    </tr>
                                @else
                                    @php
                                        $price += $el->price;
                                        $qty += 0;
                                        $totalPrice += $el->total_price;
                                    @endphp
                                @endif
                            @endforeach

                            <tr>
                                <td style="font-size: 20px">2</td>
                                <td style="font-size: 20px">Spare Part</td>
                                {{-- <td style="font-size: 20px" class="text-center">{{number_format($price,0,".",",")}}</td> --}}
                                {{-- <td style="font-size: 20px">-</td> --}}
                                <td style="font-size: 20px" class="text-center">1</td>
                                {{-- <td style="font-size: 20px" class="text-right">{{number_format($totalPrice,0,".",",")}}</td> --}}
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="row mt-4">
                    <div class="col-lg-8 col-md-8 col-sm-8">
                        <div class="section-title" style="font-size: 20px">
                            Pembayaran <b>{{ $service->payment_method }} [ {{ $service->code }} ] </b>
                            <br>
                            {{-- {{$service->Service}} --}}
                            @isset($service->Service->Warrantys)
                                Garansi <b>{{ $service->Service->Warrantys->periode }}
                                    {{ $service->Service->Warrantys->name }}</b>
                            @endisset
                        </div>

                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4  text-right">
                            <table class="table table-striped  table-md">
                                <tbody>
                                    {{-- <tr>
                  <td class="text-right" style="font-size: 20px">Jasa</td>
                  <td class="text-right" style="font-size: 20px"><b>{{number_format($service->Service->total_service,0,".",",")}}</b></td>
                </tr>
                <tr>
                  <td class="text-right" style="font-size: 20px">Spare Part</td>
                  <td class="text-right" style="font-size: 20px"><b>{{number_format($service->Service->total_part,0,".",",")}}</b></td>
                </tr> --}}
                                    <tr>
                                        <td class="text-right" style="font-size: 20px">Discount</td>
                                        <td class="text-right" style="font-size: 20px">
                                            <b>{{ number_format($service->Service->discount_price, 0, '.', ',') }}</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-right" style="font-size: 20px">Total Service</td>
                                        <td class="text-right" style="font-size: 20px">
                                            <b>{{ number_format($service->Service->total_price, 0, '.', ',') }}</b>
                                        </td>
                                    </tr>
                                    @if ($service->Service->payment_status == 'DownPayment')
                                        <tr>
                                            <td class="text-right" style="font-size: 20px">Total Bayar</td>
                                            <td class="text-right" style="font-size: 20px">
                                                <b>{{ number_format($service->total, 0, '.', ',') }}</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" style="font-size: 20px">Sisa</td>
                                            <td class="text-right" style="font-size: 20px">
                                                <b>{{ number_format($service->Service->total_price - $service->total, 0, '.', ',') }}</b>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="text-right" style="font-size: 20px">Total DownPayment</td>
                                            <td class="text-right" style="font-size: 20px">
                                                <b>{{ number_format($service->Service->total_downpayment, 0, '.', ',') }}</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" style="font-size: 20px">Total Bayar</td>
                                            <td class="text-right" style="font-size: 20px">
                                                <b>{{ number_format($service->Service->total_payment, 0, '.', ',') }}</b>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-9 text-md-right">
                            <table class="table table-md" style="border: 1px solid red">
                                <tr>
                                    <th class="text-center thred" colspan="2" style="font-size: 20px"><b> HOT LINE </b>
                                    </th>
                                </tr>
                                <tr>
                                    <td class="text-right" style="font-size: 30px" width="50%"><b> AM CARE : </b>
                                    </td>
                                    <td class="text-left" style="font-size: 30px" width="50%"><b>
                                            0851-5646-2356</b>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-3 text-md-right">
                            <table class="table table-md" style="border: 1px solid #1d98d4">
                                <tr>
                                    <th class="text-center">Customer Service</th>
                                </tr>
                                <tr>
                                    <td style="height: 100px"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script>
        window.print();
    </script>
