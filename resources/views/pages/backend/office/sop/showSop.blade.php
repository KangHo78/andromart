@extends('layouts.backend.default')
@section('title', __('pages.title').__(' | Peraturan'))
@section('titleContent', __('Peraturan'))
@section('breadcrumb', __('Data'))
@section('morebreadcrumb')
<div class="breadcrumb-item active">{{ __('Peraturan') }}</div>
@endsection

@section('content')
<div class="section-body">
  <h2 class="section-title">Peraturan</h2>
  <!-- <p class="section-lead">
    Some customers need your help.
  </p> -->

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-1"></div>
            <div class="col-10">
              <h4>{{ $model->title }}</h4>
              <div class="font-weight-600">{{ $model->roleName }}</div>
              <div class="bullet"></div>
              <div class="font-weight-600">{{ $model->roleBranch }}</div>
              <?php echo $model->description ?>
            </div>
            <div class="col-1"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('script')
<script src="{{ asset('assets/pages/office/regulationScript.js') }}"></script>
@endsection
