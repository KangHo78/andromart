@extends('layouts.backend.default')
@section('title', __('pages.title').__(' | ')."$contentType->name")
@section('titleContent', "$contentType->name")
@section('breadcrumb', __('Data'))
@section('morebreadcrumb')
<div class="breadcrumb-item active">{{ $contentType->name }}</div>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('contentStores', Crypt::encryptString($contentType->id)) }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row">
                @if($contentType->column_1 == 1)
                <div class="form-group col-md-6 col-xs-12">
                    <div class="d-block">
                        <label for="title" class="control-label">{{ __('Judul') }}<code>*</code></label>
                    </div>
                    <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" required autofocus/>
                    @error('title')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                @endif
                @if($contentType->column_2 == 1)
                <div class="form-group col-md-6 col-xs-12">
                    <div class="d-block">
                        <label for="subtitle" class="control-label">{{ __('Sub Judul') }}<code>*</code></label>
                    </div>
                    <input id="subtitle" type="text" class="form-control @error('subtitle') is-invalid @enderror" name="subtitle" required/>
                    @error('subtitle')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                @endif
                @if($contentType->column_3 == 1)
                <div class="form-group col-md-6 col-xs-12">
                    <div class="d-block">
                        <label for="description" class="control-label">{{ __('Deskripsi') }}<code>*</code></label>
                    </div>
                    <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" required autofocus/>
                    @error('description')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                @endif
                @if($contentType->column_4 == 1)
                <div class="form-group col-md-6 col-xs-12">
                    <div class="d-block">
                        <label for="subtitle" class="control-label">{{ __('Gambar') }}<code>*</code></label>
                    </div>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="image" name="image" required>
                      <!-- <input type="file" name="photo" class="custom-file-input"> -->
                      <label class="custom-file-label">Pilih Gambar</label>
                    </div>
                    @error('image')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                @endif
                @if($contentType->column_5 == 1)
                <div class="form-group col-md-6 col-xs-12">
                    <div class="d-block">
                        <label for="icon" class="control-label">{{ __('Ikon') }}<code>*</code></label>
                    </div>
                    <input id="icon" type="text" class="form-control @error('icon') is-invalid @enderror" name="icon" required autofocus/>
                    @error('icon')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                @endif
                @if($contentType->column_6 == 1)
                <div class="form-group col-md-6 col-xs-12">
                    <div class="d-block">
                        <label for="url" class="control-label">{{ __('Url') }}<code>*</code></label>
                    </div>
                    <input id="url" type="text" class="form-control @error('url') is-invalid @enderror" name="url" required/>
                    @error('url')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                @endif
                @if($contentType->column_7 == 1)
                <div class="form-group col-md-6 col-xs-12">
                    <div class="d-block">
                        <label for="class" class="control-label">{{ __('Class') }}<code>*</code></label>
                    </div>
                    <input id="class" type="text" class="form-control @error('class') is-invalid @enderror" name="class" required autofocus/>
                    @error('class')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                @endif
                @if($contentType->column_8 == 1)
                <div class="form-group col-md-6 col-xs-12">
                    <div class="d-block">
                        <label for="position" class="control-label">{{ __('Posisi') }}<code>*</code></label>
                    </div>
                    <select class="form-control @error('position') is-invalid @enderror" name="position" id="position" required>
                        <option value="Left">Kiri</option>
                        <option value="Right">Kanan</option>
                    </select>
                    @error('position')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                @endif
            </div>
        </div>
        <div class="card-footer text-right">
            <a class="btn btn-outline" href="javascript:window.history.go(-1);">{{ __('Kembali') }}</a>
            <button class="btn btn-primary mr-1" type="submit">Tambah {{ $contentType->name }}</button>
        </div>
    </form>
</div>
@endsection