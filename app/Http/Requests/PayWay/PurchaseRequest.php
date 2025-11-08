<?php

namespace App\Http\Requests\PayWay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
              // Required
            'tran_id'   => ['required','string','max:20'],
            'amount'    => ['required','numeric','min:0.01'],
            'currency'  => ['required', Rule::in(['USD','KHR'])],

            // Optional buyer info
            'firstname' => ['nullable','string','max:20'],
            'lastname'  => ['nullable','string','max:20'],
            'email'     => ['nullable','email','max:50'],
            'phone'     => ['nullable','string','max:20'],

            // Optional fields used in the hash (keep them present as strings if you send them)
            'type'                => ['nullable','string','max:20'],          // 'purchase' | 'pre-auth'
            'payment_option'      => ['nullable','string','max:20'],          // cards | abapay_khqr | ...
            'return_url'          => ['nullable','url','max:255'],
            'cancel_url'          => ['nullable','url','max:255'],
            'continue_success_url'=> ['nullable','url','max:255'],
            'return_deeplink'     => ['nullable','string','max:2000'],        // base64 JSON if used
            'custom_fields'       => ['nullable','string','max:2000'],        // base64 JSON if used
            'return_params'       => ['nullable','string','max:1000'],
            'payout'              => ['nullable','string','max:2000'],        // base64 JSON if used
            'lifetime'            => ['nullable','integer','min:3'],          // minutes
            'additional_params'   => ['nullable','string','max:2000'],        // base64 JSON if used
            'google_pay_token'    => ['nullable','string','max:5000'],
            'skip_success_page'   => ['nullable', Rule::in([0,1])],

            // “Items”/“shipping” are optional; ship as string (items should be base64 JSON if used)
            'items'    => ['nullable','string','max:500'],
            'shipping' => ['nullable'], // number in docs, but we’ll ship as string to hash consistently
        ];
    }
}
