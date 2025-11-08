@extends('Backend.Layout.App')
@section('title','PayWay Checkout')

@section('content')
<div class="panel">
  <div class="hd"><h2 style="margin:0">Pay with ABA PayWay</h2></div>
  <div class="bd">
    {!! $html !!} {{-- PayWay’s returned HTML --}}
  </div>
</div>
@endsection
