@extends('layouts.backend.default')
@section('title', __('pages.title').__(' | Tambah Notulensi'))
@section('titleContent', __('Tambah Notulensi'))
@section('breadcrumb', __('Data'))
@section('morebreadcrumb')
<div class="breadcrumb-item active">{{ __('Notulensi') }}</div>
<div class="breadcrumb-item active">{{ __('Tambah Notulensi') }}</div>
@endsection
@push('custom-css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
@endpush
@section('content')
<div class="card">
    <form method="POST" action="{{ route('notes.store') }}" enctype="multipart/form-data" class="form-data">
        @csrf
        <div class="card-body">
            <div class="form-group col-md-6 col-xs-12">
                <div class="d-block">
                    <label for="titles" class="control-label">{{ __('Judul') }}<code>*</code></label>
                </div>
                <input id="titles" type="text" class="form-control @error('titles') is-invalid @enderror" name="titles"
                    required autofocus>
                @error('titles')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group col-md-12 col-xs-12">
                <label for="description">{{ __('Deskripsi') }}<code>*</code></label>
                <textarea class="summernote @error('description') is-invalid @enderror" id="description" name="description"></textarea>
                @error('description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6 col-xs-12">
                <div class="d-block">
                    <label for="subtitle" class="control-label">{{ __('File') }}<code>*</code></label>
                </div>
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="file" name="file[]" multiple>
                  <!-- <input type="file" name="photo" class="custom-file-input"> -->
                  <label class="custom-file-label">Pilih File</label>
                </div>
                @error('file')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>
        <div class="card-footer text-right">
            <a class="btn btn-outline" href="javascript:window.history.go(-1);">{{ __('Kembali') }}</a>
            <button class="btn btn-primary mr-1" type="submit">{{ __('Tambah Notulensi') }}</button>
        </div>
    </form>
</div>
@endsection
