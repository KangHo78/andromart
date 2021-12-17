@extends('layouts.backend.default')
@section('title', __('pages.title').__(' | Tambah Master Supplier'))
@section('titleContent', __('Tambah Master Supplier'))
@section('breadcrumb', __('Data'))
@section('morebreadcrumb')
<div class="breadcrumb-item active">{{ __('Master Supplier') }}</div>
<div class="breadcrumb-item active">{{ __('Tambah Master Supplier') }}</div>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('supplier.store') }}">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="name" class="control-label">{{ __('Nama') }}<code>*</code></label>
                    <input id="name" type="text" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" name="name"
                        required autofocus>
                    @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4 col-xs-12">
                    <label for="contact">{{ __('Kontak') }}<code>*</code></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                          <div class="input-group-text">
                            <i class="fas fa-phone"></i>
                          </div>
                        </div>
                        <input id="contact" type="text" class="form-control @error('contact') is-invalid @enderror"
                            name="contact" value="{{ old('contact') }}" required>
                        @error('contact')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group col-md-6 col-xs-12">
                    <label for="address">{{ __('Alamat') }}<code>*</code></label>
                    <input id="address" type="text" class="form-control @error('address') is-invalid @enderror"
                        name="address" value="{{ old('address') }}" required>
                    @error('address')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a class="btn btn-outline" href="javascript:window.history.go(-1);">{{ __('Kembali') }}</a>
            <button class="btn btn-primary mr-1" type="submit">{{ __('Tambah Data Master') }}</button>
        </div>
    </form>
</div>
@endsection
