@extends('Backend.Layout.App')
@section('title','Pay with ABA — '.$order->tran_id)

@php
  // Prefer official fields if present (names vary by profile)
  $qrImage   = data_get($res, 'qrImage')        // data:image/png;base64,...
            ?: data_get($res, 'qr_image')
            ?: null;

  $qrString  = data_get($res, 'qrString')       // raw EMV string
            ?: data_get($res, 'qr_string')
            ?: null;

  $qrUrl     = data_get($res, 'checkout_qr_url') // hosted QR image
            ?: data_get($res, 'qrUrl')
            ?: null;

  $deeplink  = data_get($res, 'abapay_deeplink')
            ?: data_get($res, 'abapayDeeplink')
            ?: null;
@endphp

@section('content')
<div class="panel">
  <div class="hd"><h2 style="margin:0">Pay {{ number_format($order->amount,2) }} {{ $order->currency }}</h2></div>
  <div class="bd" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div>
      <div class="card" style="text-align:center;padding:16px">
        @if($qrImage)
          <img alt="KHQR" src="{{ $qrImage }}" style="max-width:260px;width:100%;height:auto;border-radius:12px;border:1px solid var(--line)" />
        @elseif($qrUrl)
          <img alt="KHQR" src="{{ $qrUrl }}" style="max-width:260px;width:100%;height:auto;border-radius:12px;border:1px solid var(--line)" />
        @else
          <p>No QR image returned.</p>
          @if($qrString)
            <textarea rows="5" style="width:100%">{{ $qrString }}</textarea>
          @endif
        @endif
        <div style="margin-top:10px">
          @if($deeplink)
            <a class="btn primary" href="{{ $deeplink }}">Open in ABA Mobile</a>
          @endif
        </div>
        <p class="dim" style="margin-top:8px">Scan with ABA Mobile or any KHQR-supported app.</p>
      </div>
    </div>

    <div>
      <div class="card" style="padding:16px">
        <div>Status: <b id="status">{{ ucfirst($order->status) }}</b></div>
        <div id="paid_at">{{ $order->paid_at? $order->paid_at->format('Y-m-d H:i') : '' }}</div>
        <div id="msg" class="dim" style="margin-top:8px">Waiting for payment...</div>
      </div>
      <div class="card" style="padding:16px;margin-top:12px">
        <pre style="white-space:pre-wrap;margin:0">{{ json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const tranId = @json($order->tran_id);
  let tries = 0, stop = false;

  function poll(){
    if(stop) return;
    fetch(@json(route('payway.check', $order->tran_id)), {cache: 'no-store'})
      .then(r=>r.json()).then(j=>{
        const st = j.order?.status || 'pending';
        document.getElementById('status').textContent = st.charAt(0).toUpperCase()+st.slice(1);
        if (st === 'approved') {
          stop = true;
          document.getElementById('msg').textContent = 'Payment received ✅';
        } else if (st === 'failed' || st === 'cancelled') {
          stop = true;
          document.getElementById('msg').textContent = 'Payment not completed ❌';
        } else {
          tries++;
          if (tries < 180) setTimeout(poll, 3000); // poll up to 9 minutes
        }
      }).catch(()=> setTimeout(poll, 4000));
  }

  setTimeout(poll, 2000);
})();
</script>
@endpush
@endsection
