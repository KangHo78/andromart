@extends('layouts.backend.default')
@section('title', __('pages.title').__(' | Tambah Master Kas'))
@section('titleContent', __('Tambah Master Kas'))
@section('breadcrumb', __('Data'))
@section('morebreadcrumb')
<div class="breadcrumb-item active">{{ __('Master Kas') }}</div>
<div class="breadcrumb-item active">{{ __('Tambah Master Kas') }}</div>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('cash.store') }}">
        @csrf
        <div class="card-body">
            <div class="form-group col-md-2 col-xs-12">
                <label for="code">{{ __('Kode') }}<code>*</code></label>
                <input id="code" type="text" class="form-control @error('code') is-invalid @enderror"
                    name="code" value="{{ old('code') }}" required autofocus>
                @error('code')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group col-md-3 col-xs-12">
                <div class="d-block">
                    <label for="name" class="control-label">{{ __('Nama') }}<code>*</code></label>
                </div>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                    required>
                @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            {{-- <div class="form-group col-md-3 col-xs-12">
                <label for="balance">{{ __('Saldo') }}<code>*</code></label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <div class="input-group-text">
                          Rp.
                        </div>
                      </div>
                      <input id="rupiah" type="text" class="form-control currency @error('balance') is-invalid @enderror"
                        name="balance" value="{{ old('balance') }}" required autocomplete="balance">
                      @error('balance')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
            </div> --}}
        </div>
        <div class="card-footer text-right">
            <a class="btn btn-outline" href="javascript:window.history.go(-1);">{{ __('Kembali') }}</a>
            <button class="btn btn-primary mr-1" type="submit">{{ __('Tambah Data Master') }}</button>
        </div>
    </form>
</div>
@endsection
