<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayWay\PurchaseRequest;
use App\Models\Order;
use App\Services\PayWayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayWayController extends Controller
{
     public function __construct(private PayWayService $payway) {}

    /**
     * Create checkout and render the hosted PayWay page.
     */
    public function createCheckout(PurchaseRequest $request)
    {
        $data = $this->payway->buildPurchasePayload($request->validated());
        $action = $this->payway->purchaseEndpoint();

        // Return a small Blade that auto-posts to PayWay
        return view('Backend.Orders.payway-redirect', compact('action','data'));
    }

    /**
     * PayWay return URL (user browser redirects here after payment).
     * Use Check Transaction / or rely on pushback to finalize the order.
     */
    public function webhook(Request $request)
    {
        // Validate source via IP allowlist or HMAC you agree with ABA.
        $tranId = $request->input('tran_id');
        $status = (int) $request->input('status');
        $payload = $request->all();

        if ($tranId) {
            if ($order = \App\Models\Order::where('tran_id',$tranId)->first()) {
                if ($status === 0) $order->markApproved($payload);
                else               $order->markFailed($payload);
            }
        }
        return response()->json(['ok' => true]);
    }

    public function cancel()
    {
        return redirect()->route('orders.index')->with('warning', 'Payment cancelled.');
    }
    public function khqr(string $tranId)
{
    $order = Order::where('tran_id', $tranId)->firstOrFail();

    // Build payload similar to your purchase (minimal fields are fine)
    $payload = [
        'tran_id'   => $order->tran_id,
        'amount'    => $order->amount,
        'currency'  => $order->currency,   // USD: "X.00", KHR: integer string
        'email'     => (string) ($order->email ?? ''),
        'phone'     => (string) ($order->phone ?? ''),
        'shipping'  => '0',
    ];

    $resp = $this->payway->purchaseKhqrDeeplink($payload);

    if (($resp['status']['code'] ?? '') !== '00') {
        return back()->with('error', $resp['status']['message'] ?? 'KHQR error');
    }

    // Optionally persist last gateway response to the order
    $order->gateway_response = array_merge($order->gateway_response ?? [], ['khqr' => $resp]);
    $order->save();

    return view('Backend.Orders.khqr', [
        'order'           => $order,
        'qrImage'         => $resp['qrImage'] ?? null,        // data:image/png;base64,…
        'qrString'        => $resp['qrString'] ?? null,
        'abapayDeeplink'  => $resp['abapay_deeplink'] ?? null,
    ]);
}

    public function return(Request $request)
    {
        // Ensure JSON
        $data = $request->json()->all() ?: $request->all();

        // Example payload: tran_id, apv, status("0"=OK), return_params
        $tranId = $data['tran_id'] ?? null;

        if ($tranId && ($order = \App\Models\Order::where('tran_id',$tranId)->first())) {
            // Store raw callback
            $order->gateway_response = array_merge($order->gateway_response ?? [], ['callback' => $data]);
            $order->save();
        }

        // Always respond 200 quickly
        return response()->json(['ok' => true]);
    }
    public function khqrCheckout(Request $r)
    {
        $mode       = config('payway.mode', 'sandbox');
    $base       = config("payway.base_urls.$mode");
    $merchantId = config('payway.merchant_id');
    $apiKey     = config('payway.api_key');

        $reqTime  = now('UTC')->format('YmdHis');
$merchant = config('payway.merchant_id');
$apiKey   = config('payway.api_key');

$tranId   = $r->tran_id ?? 'ORD-' . strtoupper(Str::random(8));
$amount   = number_format((float)$r->amount, 2, '.', '');
$currency = $r->currency ?? 'USD';
$returnUrl = route('payway.checkTransaction'); // whitelisted domain
$paymentOpt = 'abapay_khqr_deeplink';

// Required field order — fill unused with empty strings!
$items = '';
$shipping = '';
$firstname = '';
$lastname = '';
$email = $r->email ?? '';
$phone = $r->phone ?? '';
$type = '';
$cancelUrl = '';
$continueUrl = '';
$returnDeeplink = '';
$custom = '';
$returnParams = '';
$payout = '';
$lifetime = '';
$additional = '';
$googlePayToken = '';
$skipSuccess = '';

// 💡 Build hash string exactly in this order:
$beforeHash = $reqTime
    .$merchant
    .$tranId
    .$amount
    .$items
    .$shipping
    .$firstname
    .$lastname
    .$email
    .$phone
    .$type
    .$paymentOpt
    .$returnUrl
    .$cancelUrl
    .$continueUrl
    .$returnDeeplink
    .$currency
    .$custom
    .$returnParams
    .$payout
    .$lifetime
    .$additional
    .$googlePayToken
    .$skipSuccess;

    // ✅ Generate HMAC-SHA512 and base64 encode it:
    $hash = base64_encode(hash_hmac('sha512', $beforeHash, $apiKey, true));

    $payload = [
        'req_time'       => $reqTime,
        'merchant_id'    => $merchantId,
        'tran_id'        => $tranId,
        'amount'         => $amount,
        'currency'       => $currency,
        'payment_option' => $paymentOpt,
        'return_url'     => $returnUrl,
        'hash'           => $hash,
        // do NOT send nulls; omit or send empty strings only if used
    ];

    try {
        // form-url-encoded works for PayWay; no need for multipart
        $res = Http::asForm()
            ->timeout(30)
            ->post("$base/api/payment-gateway/v1/payments/purchase", $payload);

        // Log everything useful for debugging
        Log::info('[PayWay purchase] status='.$res->status(), [
            'mode' => $mode,
            'tran_id' => $tranId,
            'payload_sent' => $payload,
            'body' => $res->body(),
            'headers' => $res->headers(),
        ]);

        // Expected for deeplink: 200 JSON with qr fields
        if ($res->ok() && $json = $res->json()) {
            // Common success shape:
            // { "status":{"code":"00"},"qrString": "...", "qrImage": "data:image/png...", "abapay_deeplink": "...", "checkout_qr_url": "..." }
            $qrUrl    = $json['checkout_qr_url'] ?? null;
            $deeplink = $json['abapay_deeplink'] ?? null;
            $qrString = $json['qrString'] ?? $json['qr_string'] ?? null;

            if ($qrUrl || $qrString || $deeplink) {
                return view('Backend.Orders.payway-khqr', [
                    'order_id'        => $tranId,
                    'checkout_qr_url' => $qrUrl,     // preferred image url
                    'qr_string'       => $qrString,  // optional; for your own QR generator
                    'deeplink'        => $deeplink,
                ]);
            }
        }

        // If PayWay returns 403/400 with JSON error, show it:
        if ($res->status() >= 400) {
            return back()->withErrors([
                'payway' => 'PayWay purchase failed: '.$res->status().' '.substr($res->body(), 0, 800),
            ]);
        }

        // Unexpected shape → show a readable error, not 502
        return back()->withErrors([
            'payway' => 'Unexpected PayWay response. See laravel.log for details.',
        ]);

    } catch (\Throwable $e) {
        Log::error('[PayWay purchase EXCEPTION]', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
        return back()->withErrors([
            'payway' => 'Could not contact PayWay: '.$e->getMessage(),
        ]);
    }
    }

    public function checkTransaction(Request $r)
    {
        // Here you call PayWay “Check Transaction API”
        // to verify payment after scan/deeplink

        $tranId = $r->tran_id;
        $merchantId = config('payway.merchant_id');
        $apiKey = config('payway.api_key');

        $reqTime = now('UTC')->format('YmdHis');
        $beforeHash = $reqTime.$merchantId.$tranId;
        $hash = base64_encode(hash_hmac('sha512', $beforeHash, $apiKey, true));

        $res = Http::asForm()->post(
            'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/check-transaction',
            [
                'req_time' => $reqTime,
                'merchant_id' => $merchantId,
                'tran_id' => $tranId,
                'hash' => $hash,
            ]
        );

        // in checkTransaction(), after $res = Http::asForm()->post(...)

        $data = $res->json();
        $code = $data['status']['code'] ?? $data['code'] ?? null;

        if ($code == 0 || $code == '0' || $code == '00') {
            \App\Models\Order::where('tran_id', $tranId)->update([
                'status'   => 'approved',
                'paid_at'  => now(),
                'gateway_response' => $data,
            ]);
        } else {
            \App\Models\Order::where('tran_id', $tranId)->update([
                'gateway_response' => $data,
            ]);
        }


        return $data;
    }

    public function checkoutDeeplink(PurchaseRequest $r, PayWayService $svc)
    {
        // 1) Load the order
        $order = Order::where('tran_id', $r->tran_id)->firstOrFail();

        // 2) Build payload for KHQR deeplink JSON response (no plugin)
        $payload = [
            'tran_id'   => $order->tran_id,
            'amount'    => $order->amount,
            'currency'  => $order->currency,         // USD or KHR
            'email'     => $order->email ?? '',
            'phone'     => $order->phone ?? '',
            // optional but recommended:
            'return_params' => $order->tran_id,      // echoed back to you
            // 'continue_success_url' => route('orders.show', $order->tran_id),
        ];

        // 3) Call PayWay (server-to-server)
        $res = $svc->purchaseKhqrDeeplink($payload);

        // 4) Handle gateway errors early
        $code = data_get($res, 'status.code') ?? data_get($res, 'status.status') ?? null;
        if (!in_array($code, ['00', 0, '0'], true)) {
            Log::warning('PayWay purchase failed', ['resp' => $res, 'tran' => $order->tran_id]);
            return back()->withErrors(['payway' => 'PayWay purchase failed: '.json_encode($res)]);
        }

        // 5) Persist last gateway response (optional)
        $order->gateway_response = $res;
        $order->save();

        // 6) Render a page that shows QR + Deeplink and polls status
        return view('Backend.Orders.payway-deeplink', [
            'order' => $order,
            'res'   => $res,
        ]);
    }

    // PayWay pushback (return_url) — they POST JSON here (optional but recommended)
    public function callback(Request $r)
    {
        // Expect JSON: { tran_id, apv, status, return_params }
        $data  = $r->all();
        Log::info('PayWay callback', $data);

        $order = Order::where('tran_id', $data['tran_id'] ?? '')->first();
        if ($order) {
            $order->gateway_response = array_merge((array)$order->gateway_response, ['callback' => $data]);
            // Map PayWay status to your statuses; PayWay "0" usually means success
            if (($data['status'] ?? null) === '0') {
                $order->status  = 'approved';
                $order->paid_at = now();
            }
            $order->save();
        }

        return response()->json(['ok' => true]);
    }

    // Polling endpoint the UI calls every few seconds
    public function check(string $tran_id, PayWayService $svc)
    {
        $res   = $svc->checkTransaction($tran_id);
        $order = Order::where('tran_id', $tran_id)->first();
        return response()->json([
            'gateway' => $res,
            'order'   => $order?->only(['tran_id','status','paid_at']),
        ]);
    }
    public function checkoutCards(Request $r, PayWayService $svc)
    {
            $order = Order::where('tran_id', $r->tran_id)->firstOrFail();

    $payload = [
        'tran_id'        => $order->tran_id,
        'amount'         => $order->amount,      // "1.00"
        'currency'       => $order->currency,    // USD or KHR
        'email'          => $order->email ?? '',
        'phone'          => $order->phone ?? '',
        'payment_option' => 'cards',
        'shipping'       => '0',
        'view_type'      => 'hosted',            // 👈 forces hosted redirect
        'return_params'  => $order->tran_id,
    ];

    $data = $svc->buildPurchasePayload($payload);

    // Ask PayWay to start hosted checkout; DO NOT follow redirects here
    $res = (new \GuzzleHttp\Client(['allow_redirects' => false, 'timeout' => 20]))
        ->post($svc->purchaseEndpoint(), ['form_params' => $data, 'http_errors' => false]);

    // PayWay normally replies with 302 Location to their hosted page
    if ($res->getStatusCode() === 302) {
        $url = $res->getHeaderLine('Location');
        if ($url) return redirect()->away($url);      // 👈 let browser follow it
    }

    // Some tenants return JSON instead of 302
    $body = json_decode((string) $res->getBody(), true);
    if (isset($body['redirect'])) {
        return redirect()->away($body['redirect']);
    }

    return back()->withErrors(['payway' => 'Unable to start card checkout']);

    }
    public function startKhqrDeeplink(Request $r, PayWayService $svc)
    {
        $order = Order::where('tran_id', $r->input('tran_id'))->firstOrFail();

        // Build the minimal payload; the service will format + hash it
        $payload = [
            'tran_id'   => $order->tran_id,
            'amount'    => $order->amount,       // numeric (e.g. 1.00)
            'currency'  => $order->currency,     // 'USD' or 'KHR'
            'email'     => $order->email ?? '',
            'phone'     => $order->phone ?? '',
            'items'     => base64_encode(json_encode([
                ['name' => 'Order '.$order->tran_id, 'quantity' => 1, 'price' => (float)$order->amount],
            ])),
            'shipping'  => '0',                   // IMPORTANT: '0' as string
            // optional UX (for this deeplink flow usually leave view_type empty)
            // 'return_params' => $order->tran_id,
        ];

        $resp = $svc->purchaseKhqrDeeplink($payload);

        // Example success body for deeplink flow:
        // {
        //   "status":{"code":"00","message":"Success!"},
        //   "qrString":"0002010102....",
        //   "qrImage":"data:image/png;base64,...",
        //   "abapay_deeplink":"abamobilebank://ababank.com?...",
        //   "app_store":"https://itunes.apple.com/...",
        //   "play_store":"https://play.google.com/..."
        // }

        if (data_get($resp, 'status.code') === '00') {
            // store last response for reference (optional)
            $order->gateway_response = $resp;
            $order->save();

            // Show QR/deeplink page that also polls check-transaction
            return view('Backend.Orders.payway-khqr', [
                'order'    => $order,
                'qrImage'  => data_get($resp, 'qrImage'),
                'qrString' => data_get($resp, 'qrString'),
                'deeplink' => data_get($resp, 'abapay_deeplink'),
                'appStore' => data_get($resp, 'app_store'),
                'playStore'=> data_get($resp, 'play_store'),
            ]);
        }

        // Show error from gateway
        $msg = json_encode($resp, JSON_UNESCAPED_UNICODE);
        return back()->withErrors(['payway' => "PayWay start failed: $msg"]);
    }

    // AJAX poller: /payway/check?tran_id=...
    public function poll(Request $r, PayWayService $svc)
    {
        $tranId = $r->query('tran_id');
        $resp   = $svc->checkTransaction($tranId);

        // Normalize a tiny shape for the front-end
        return response()->json([
            'ok'     => (bool) $resp,
            'status' => data_get($resp, 'status'),     // raw status text/code
            'data'   => $resp,
        ]);
    }

}
