<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q',''));
        $status = $request->input('status');

        $orders = Order::query()
            ->when($q, fn($qr) => $qr->where('tran_id','like',"%{$q}%")
                ->orWhere('email','like',"%{$q}%")->orWhere('phone','like',"%{$q}%"))
            ->when($status, fn($qr) => $qr->where('status', $status))
            ->orderByDesc('id')
            ->paginate((int)$request->input('per_page', 20))
            ->withQueryString();

        return view('Backend.Orders.index', compact('orders','q','status'));
    }

    public function show(string $tran_id)
    {
        $order = Order::where('tran_id', $tran_id)->firstOrFail();
        return view('Backend.Orders.show', compact('order'));
    }

    /**
     * Minimal create endpoint to make an order before calling /payway/checkout
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tran_id'   => ['required','string','max:40', Rule::unique('orders','tran_id')],
            'amount'    => ['required','numeric','min:0.01'],
            'currency'  => ['required','in:USD,KHR'],
            'firstname' => ['nullable','string','max:50'],
            'lastname'  => ['nullable','string','max:50'],
            'email'     => ['nullable','email','max:120'],
            'phone'     => ['nullable','string','max:30'],
            'meta'      => ['nullable','array'],
        ]);

        $order = Order::create($data);

        return redirect()
            ->route('orders.show', $order->tran_id)
            ->with('success','Order created. You can proceed to payment.');
    }
}
