{{-- resources/views/Backend/Orders/show.blade.php --}}
@extends('Backend.Layout.App')
@section('title','Order #'.$order->tran_id)

@push('styles')
<style>
  .panel{background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 8px 22px rgba(0,0,0,.04)}
  .panel .hd{padding:12px 14px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}
  .panel .bd{padding:14px}
  .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
  .card{border:1px solid var(--line);border-radius:14px;padding:12px;background:#fff}
  .label{font-size:12px;color:var(--muted)}
  .value{font-size:16px}
  .actions{display:flex;gap:8px;flex-wrap:wrap}
  .btn{padding:10px 12px;border:1px solid var(--line);border-radius:10px;background:#fff;cursor:pointer}
  .btn.primary{background:linear-gradient(120deg,var(--primary),var(--primary-600));color:#fff;border-color:transparent}
  .status{display:inline-flex;gap:6px;align-items:center;padding:4px 10px;border-radius:999px;border:1px solid var(--line);font-size:12px;background:#fff}
  .status.approved{background:#ecfdf5;border-color:#a7f3d0}
  .status.pending{background:#fff7ed;border-color:#fed7aa}
  .status.failed{background:#fef2f2;border-color:#fecaca}
</style>
@endpush

@section('content')
<div class="panel">
  <div class="hd">
    <h2 style="margin:0">Order #{{ $order->tran_id }}</h2>
    <div class="actions">
      <a class="btn" href="{{ route('orders.index') }}">← Back</a>
      <a class="btn" href="{{ route('payway.khqr', $order->tran_id) }}">Pay via KHQR (in-app)</a>


      {{-- Trigger PayWay checkout (posts the required fields) --}}
      @if($order->status === 'pending')
      <form action="{{ route('payway.checkout.khqr') }}" method="post" class="actions">
        @csrf
        <input type="hidden" name="tran_id"  value="{{ $order->tran_id }}">
        <input type="hidden" name="amount"   value="{{ $order->amount }}">
        <input type="hidden" name="currency" value="{{ $order->currency }}">
        <input type="hidden" name="firstname" value="{{ $order->firstname }}">
        <input type="hidden" name="lastname"  value="{{ $order->lastname }}">
        <input type="hidden" name="email"     value="{{ $order->email }}">
        <input type="hidden" name="phone"     value="{{ $order->phone }}">
        <button class="btn primary" type="submit"><i class="fa fa-credit-card"></i> Pay with ABA PayWay</button>
      </form>

    <form action="{{ route('payway.checkoutCards') }}" method="post">
        @csrf
        <input type="hidden" name="tran_id" value="{{ $order->tran_id }}">
        <input type="hidden" name="amount"  value="{{ number_format($order->amount,2,'.','') }}">
        <input type="hidden" name="currency" value="{{ $order->currency }}">
        <input type="hidden" name="email" value="{{ $order->email }}">
        <input type="hidden" name="phone" value="{{ $order->phone }}">
        <button class="btn primary" type="submit">Pay with Card (Sandbox)</button>
    </form>

      @endif
    </div>
  </div>

  <div class="bd">
    <div class="grid">
      <div class="card" style="grid-column:span 4">
        <div class="label">Status</div>
        <div class="value"><span class="status {{ $order->status }}">{{ ucfirst($order->status) }}</span></div>
      </div>
      <div class="card" style="grid-column:span 4">
        <div class="label">Amount</div>
        <div class="value">{{ number_format($order->amount,2) }} {{ $order->currency }}</div>
      </div>
      <div class="card" style="grid-column:span 4">
        <div class="label">Paid At</div>
        <div class="value">{{ $order->paid_at? $order->paid_at->timezone(config('app.timezone'))->format('Y-m-d H:i') : '—' }}</div>
      </div>

      <div class="card" style="grid-column:span 6">
        <div class="label">Customer</div>
        <div class="value">
          {{ trim(($order->firstname ?? '').' '.($order->lastname ?? '')) ?: '—' }}<br>
          {{ $order->email ?: '—' }}<br>
          {{ $order->phone ?: '—' }}
        </div>
      </div>

      <div class="card" style="grid-column:span 6">
        <div class="label">Gateway Response (last)</div>
        <pre style="margin:6px 0 0;white-space:pre-wrap">{{ json_encode($order->gateway_response, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
      </div>
    </div>
  </div>
</div>
@endsection
