<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
     protected $fillable = [
        'tran_id','amount','currency','status',
        'firstname','lastname','email','phone',
        'paid_at','meta','gateway_response'
    ];

    protected $casts = [
        'paid_at'          => 'datetime',
        'meta'             => 'array',
        'gateway_response' => 'array',
    ];

    public function markApproved(array $gateway = []): void
    {
        $this->status = 'approved';
        $this->paid_at = now();
        if ($gateway) {
            $this->gateway_response = array_merge($this->gateway_response ?? [], $gateway);
        }
        $this->save();
    }

    public function markFailed(array $gateway = []): void
    {
        $this->status = 'failed';
        if ($gateway) {
            $this->gateway_response = array_merge($this->gateway_response ?? [], $gateway);
        }
        $this->save();
    }
}
