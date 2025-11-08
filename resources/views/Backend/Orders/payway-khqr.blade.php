{{-- resources/views/Backend/Orders/payway-khqr.blade.php --}}
@extends('Backend.Layout.App')
@section('title','Scan to pay — '.$order->tran_id)

@push('styles')
<style>
  .panel{background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 8px 22px rgba(0,0,0,.04)}
  .panel .hd{padding:12px 14px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}
  .panel .bd{padding:14px}
  .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
  .card{border:1px solid var(--line);border-radius:14px;padding:12px;background:#fff}
  .btn{padding:10px 12px;border:1px solid var(--line);border-radius:10px;background:#fff;cursor:pointer}
  .btn.primary{background:linear-gradient(120deg,var(--primary),var(--primary-600));color:#fff;border-color:transparent}
  .muted{color:#666}
  .qr{display:flex;align-items:center;justify-content:center;padding:12px;border:1px dashed var(--line);border-radius:12px;background:#fafafa}
</style>
@endpush

@section('content')
<div class="panel">
  <div class="hd"><h2 style="margin:0">Pay {{ number_format($order->amount,2) }} {{ $order->currency }}</h2></div>
  <div class="bd">
    <div class="grid">
      <div class="card" style="grid-column:span 6">
        <div class="qr">
          @if($qrImage)
            <img src="{{ $qrImage }}" alt="KHQR" style="width:260px;height:260px;object-fit:contain">
          @else
            <p class="muted">QR image not provided.</p>
          @endif
        </div>
        <p class="muted" style="margin-top:8px">Scan with ABA Mobile or any KHQR-supported app (LIVE ONLY).</p>
        @if($deeplink)
          <p><a class="btn primary" href="{{ $deeplink }}">Open ABA app</a></p>
        @endif
        @if($appStore || $playStore)
          <p class="muted">Need the app?
            @if($appStore)<a href="{{ $appStore }}" target="_blank">iOS</a>@endif
            @if($playStore) · <a href="{{ $playStore }}" target="_blank">Android</a>@endif
          </p>
        @endif
      </div>

      <div class="card" style="grid-column:span 6">
        <div class="muted">Transaction</div>
        <div><strong>{{ $order->tran_id }}</strong></div>
        <div style="margin-top:10px">
          <div>Status: <span id="status_text" class="muted">Waiting for payment…</span></div>
          <div id="status_raw" class="muted" style="margin-top:6px;font-size:12px"></div>
        </div>
        <div style="margin-top:12px">
          <a class="btn" href="{{ route('orders.show',$order->tran_id) }}">Back to order</a>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const tranId = @json($order->tran_id);
  const checkUrl = @json(route('payway.check', ['tran_id' => $order->tran_id]));
  const statusText = document.getElementById('status_text');
  const statusRaw  = document.getElementById('status_raw');

  let ticks = 0, timer = null;

  async function poll(){
    try{
      const r = await fetch(checkUrl, {credentials:'same-origin'});
      const j = await r.json();
      statusRaw.textContent = JSON.stringify(j.status || j.data || {}, null, 2);

      // Interpret your checkTransaction response shape:
      // Typical approved indicators: status == '0' or code == '00' or text 'APPROVED'
      const code = (j.data && (j.data.code || j.data.status_code)) || (j.status && (j.status.code || j.status.status_code));
      const msg  = (j.data && (j.data.message || j.data.status_text)) || (j.status && (j.status.message || j.status.status_text));
      if (code === '00' || code === 0 || msg === 'APPROVED' || msg === 'Success') {
        statusText.textContent = 'Paid — redirecting…';
        clearInterval(timer);
        // Hard-refresh order page so your server marks it approved (or do it here after verifying)
        window.location = @json(route('orders.show', $order->tran_id));
        return;
      }

      // stop after ~3 minutes to avoid infinite polling in sandbox
      if (++ticks > 45) { clearInterval(timer); statusText.textContent = 'Timed out. You can refresh to retry.'; }
    }catch(e){
      // ignore transient errors; keep polling
    }
  }

  timer = setInterval(poll, 4000);
  poll();
})();
</script>
@endpush
@endsection
