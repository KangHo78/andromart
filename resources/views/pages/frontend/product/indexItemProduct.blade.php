@extends('layouts.frontend.default')
@section('title', 'Home')
@section('menu-active', 'product')
@section('content')
<div role="main" class="main">
    @include('pages.frontend.product.itemProduct')
</div>
@endsection
