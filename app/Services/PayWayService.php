<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class PayWayService
{
    protected string $merchantId;
    protected string $apiKey;

    public function __construct()
    {
        $this->merchantId = (string) Config::get('payway.merchant_id');
        $this->apiKey     = (string) Config::get('payway.api_key');
    }

    /** Build payload for browser POST → PayWay (no server HTTP call). */
    public function buildPurchasePayload(array $p): array
    {
        // NOTE: use '' for missing optional fields; include ALL in the hash
        $d = [
            'req_time'      => now('UTC')->format('YmdHis'),
            'merchant_id'   => $this->merchantId,
            'tran_id'       => $p['tran_id'],
            'amount'        => $this->formatAmount($p['amount'] ?? 0, $p['currency']),
            'items'         => $p['items']               ?? '',
            'shipping' => isset($p['shipping']) && $p['shipping'] !== ''
                ? (string)$p['shipping']    // already a number string
                : '0',
            'firstname'     => $p['firstname']           ?? '',
            'lastname'      => $p['lastname']            ?? '',
            'email'         => $p['email']               ?? 'sunwukhongking@gmail.com',
            'phone'         => $p['phone']               ?? '',
            'type'          => $p['type']                ?? '',
            'payment_option'=> $p['payment_option']      ?? 'abapay_khqr_deeplink',
            'return_url'    => (string) config('payway.return_url'),
            'cancel_url'    => (string) config('payway.cancel_url'),
            'continue_success_url' => $p['continue_success_url'] ?? '',
            'return_deeplink'      => $p['return_deeplink']      ?? '',
            'currency'      => $p['currency'],
            'custom_fields' => $p['custom_fields']       ?? '',
            'return_params' => $p['return_params']       ?? '',
            'payout'        => $p['payout']              ?? '',
            'lifetime'      => $p['lifetime']            ?? '',
            'additional_params' => $p['additional_params'] ?? '',
            'google_pay_token'   => $p['google_pay_token'] ?? '',
            'skip_success_page'  => $p['skip_success_page'] ?? '',
        ];

        $d['hash'] = $this->makeHashForPurchase($d);
        return $d;
    }

    /** Exact PayWay concat order + Base64(HMAC-SHA512(raw)) */
    protected function makeHashForPurchase(array $d): string
    {
        $order = [
            'req_time','merchant_id','tran_id','amount','items','shipping',
            'firstname','lastname','email','phone','type','payment_option',
            'return_url','cancel_url','continue_success_url','return_deeplink',
            'currency','custom_fields','return_params','payout','lifetime',
            'additional_params','google_pay_token','skip_success_page',
        ];

        $pieces = [];
        foreach ($order as $k) { $pieces[] = isset($d[$k]) ? (string)$d[$k] : ''; }
        $b4hash = implode('', $pieces);

        return base64_encode(hash_hmac('sha512', $b4hash, $this->apiKey, true));
    }

    public function purchaseEndpoint(): string
    {
        $mode = config('payway.mode', 'sandbox');
        $base = rtrim(config("payway.base_urls.$mode"), '/');
        return $base . config('payway.endpoints.purchase'); // …/api/payment-gateway/v1/payments/purchase
    }

    protected function formatAmount($amount, string $currency): string
    {
        return $currency === 'KHR'
            ? (string) intval(round($amount))
            : number_format((float)$amount, 2, '.', '');
    }

    public function purchaseKhqrDeeplink(array $payload): array
{
    $endpoint = $this->purchaseEndpoint();

    // Build the same ordered payload but force payment_option
    $p = array_merge($payload, [
        'payment_option' => 'abapay_khqr_deeplink',
    ]);

    // Important: shipping=0 if empty, amount formatting, etc.
    if (!isset($p['shipping']) || $p['shipping'] === '') {
        $p['shipping'] = '0';
    }

    // Build + hash (reusing your buildPurchasePayload + makeHashForPurchase)
    $data = $this->buildPurchasePayload($p);

    $res = (new \GuzzleHttp\Client(['timeout' => 20]))->post($endpoint, [
        'headers'     => ['Accept' => 'application/json'],
        'form_params' => $data,
        'http_errors' => false,
    ]);

    $body = json_decode((string) $res->getBody(), true);
    return is_array($body) ? $body : ['status' => ['code' => -1, 'message' => 'Gateway error']];
}

public function checkTransaction(string $tranId): array
{
    $endpoint = rtrim(config("payway.base_urls.".config('payway.mode','sandbox')), '/')
              . config('payway.endpoints.check_transaction');

    $json = [
        'merchant_id' => $this->merchantId,
        'tran_id'     => $tranId,
        'req_time'    => now('UTC')->format('YmdHis'),
    ];
    $json['hash'] = base64_encode(hash_hmac('sha512',
        $json['req_time'].$json['merchant_id'].$json['tran_id'],
        $this->apiKey, true
    ));

    $res = (new \GuzzleHttp\Client(['timeout' => 15]))->post($endpoint, [
        'headers' => ['Accept' => 'application/json'],
        'json'    => $json,
        'http_errors' => false,
    ]);
    return json_decode((string) $res->getBody(), true) ?: [];
}
}
