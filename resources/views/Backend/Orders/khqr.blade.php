{{-- resources/views/Backend/Orders/khqr.blade.php --}}
@extends('Backend.Layout.App')
@section('title','KHQR — '.$order->tran_id)

@section('content')
<div class="panel">
  <div class="hd"><h2 style="margin:0">Scan & Pay — {{ $order->tran_id }}</h2></div>
  <div class="bd">
    <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap">
      <div>
        @if($qrImage)
          <img src="{{ $qrImage }}" alt="KHQR" style="width:280px;height:280px;border-radius:12px;border:1px solid var(--line)" />
        @endif
        <div style="margin-top:10px">
          <button class="btn" onclick="copyText(`{{ $qrString }}`)">Copy QR String</button>
          @if($abapayDeeplink)
            <a class="btn primary" href="{{ $abapayDeeplink }}">Open in ABA</a>
          @endif
        </div>
      </div>

      <div>
        <div><b>Amount:</b> {{ number_format($order->amount, 2) }} {{ $order->currency }}</div>
        <div style="margin-top:8px"><b>Status:</b> <span id="p-status" class="status pending">Pending</span></div>
        <div id="p-note" class="dim" style="margin-top:6px">Waiting for payment…</div>
        <div style="margin-top:16px">
          <a class="btn" href="{{ route('orders.show', $order->tran_id) }}">Back to Order</a>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
let done = false;
function copyText(t){ navigator.clipboard.writeText(t||''); alert('QR string copied'); }
async function poll(){
  if(done) return;
  try{
    const r = await fetch('{{ route('payway.poll', $order->tran_id) }}', { cache: 'no-store' });
    const j = await r.json();
    if(j.ok){
      document.getElementById('p-status').className='status approved';
      document.getElementById('p-status').textContent='Approved';
      document.getElementById('p-note').textContent='Payment approved. Redirecting…';
      done = true;
      setTimeout(()=> location.href='{{ route('orders.show', $order->tran_id) }}', 1200);
      return;
    }
  }catch(e){}
  setTimeout(poll, 3000);
}
poll();
</script>
@endpush
@endsection
