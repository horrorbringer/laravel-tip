{{-- resources/views/Backend/Orders/payway-redirect.blade.php --}}
@extends('Backend.Layout.App')

@section('head')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  {{-- Only this plugin URL works --}}
  <script src="https://checkout.payway.com.kh/plugins/checkout2-0.js"></script>
@endsection

@section('title','Pay with ABA PayWay')

@section('content')
<div class="panel">
  <div class="hd"><h2 style="margin:0">Pay with ABA PayWay</h2></div>
  <div class="bd">
    <p class="dim">A secure PayWay popup will open. If it doesn’t, use the fallback button.</p>

    {{-- Required hidden iframe for the plugin --}}
    <iframe name="aba_webservice" style="display:none;width:0;height:0;border:0"></iframe>

    <form id="aba_merchant_request"
          action="{{ $action }}"
          method="post"
          target="aba_webservice"
          enctype="multipart/form-data">
      @foreach($data as $k => $v)
        @continue($k === 'is_plugin_js')
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
      @endforeach
      <input type="hidden" name="view_type" value="popup">
      <input type="hidden" name="shipping"  value="{{ $data['shipping'] ?? '0' }}">
    </form>

    <div style="display:flex;gap:10px;margin-top:10px">
      <button id="checkout_button" class="btn primary">
        <i class="fa fa-credit-card"></i> Checkout Now
      </button>
      <button id="fallback_button" class="btn">Open in New Tab</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const form   = document.getElementById('aba_merchant_request');
  const iframe = document.querySelector('iframe[name="aba_webservice"]');

  function fallback() {
    // switch to hosted page so it always works
    let vt = form.querySelector('input[name="view_type"]');
    if (vt) vt.value = 'hosted_view'; else {
      vt = document.createElement('input');
      vt.type = 'hidden'; vt.name = 'view_type'; vt.value = 'hosted_view';
      form.appendChild(vt);
    }
    form.removeAttribute('target');
    form.setAttribute('target', '_blank');
    form.submit();
  }

  function openPopup() {
    try {
      if (typeof AbaPayway !== 'object') throw new Error('plugin not ready');
      AbaPayway.checkout();
      // watchdog: if the checkout HTML doesn’t land in 4s (blocked cookies), fallback
      setTimeout(() => {
        // We can’t peek inside (cross-origin), so fallback if still no network activity in frame
        // Heuristic: if the iframe has no src yet, or still empty DOM
        if (!iframe || !iframe.contentWindow || !iframe.contentDocument) return; // ignore
        try {
          // if blocked, accessing contentDocument.body usually throws → fallback
          void iframe.contentDocument.body; // attempt
        } catch (_) {
          fallback();
        }
      }, 4000);
      return true;
    } catch (e) {
      console.warn('Popup failed, using fallback:', e);
      fallback();
      return false;
    }
  }

  // auto try once
  $(function(){ setTimeout(openPopup, 300); });

  // manual triggers
  $('#checkout_button').on('click', function(e){ e.preventDefault(); openPopup(); });
  $('#fallback_button').on('click', function(e){ e.preventDefault(); fallback(); });
})();
</script>
@endpush
@endsection
