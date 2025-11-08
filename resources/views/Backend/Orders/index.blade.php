{{-- resources/views/Backend/Orders/index.blade.php --}}
@extends('Backend.Layout.App')
@section('title','Orders — Tracking Session System')

@push('styles')
<style>
  .panel{background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 8px 22px rgba(0,0,0,.04)}
  .panel .hd{padding:12px 14px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:10px}
  .panel .bd{padding:14px}
  .actions{display:flex;gap:8px;flex-wrap:wrap}
  .input{padding:8px 10px;border:1px solid var(--line);border-radius:10px;background:#fff}
  .btn{padding:8px 12px;border:1px solid var(--line);border-radius:10px;background:#fff;cursor:pointer}
  .btn.primary{background:linear-gradient(120deg,var(--primary),var(--primary-600));color:#fff;border-color:transparent}
  table{width:100%;border-collapse:collapse}
  th,td{padding:10px;border-bottom:1px solid var(--line);text-align:left;white-space:nowrap}
  .status{display:inline-flex;gap:6px;align-items:center;padding:4px 10px;border-radius:999px;border:1px solid var(--line);font-size:12px;background:#fff}
  .status.approved{background:#ecfdf5;border-color:#a7f3d0}
  .status.pending{background:#fff7ed;border-color:#fed7aa}
  .status.failed{background:#fef2f2;border-color:#fecaca}
</style>
@endpush

@section('content')
<div class="panel">
  <div class="hd">
    <h2 style="margin:0">Orders</h2>
    <form method="get" class="actions">
      <input class="input" type="text" name="q" value="{{ $q }}" placeholder="Search tran_id / email / phone">
      <select name="status" class="input">
        <option value="">All statuses</option>
        @foreach(['pending','approved','failed','cancelled'] as $s)
          <option value="{{ $s }}" @selected($status===$s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
      <select name="per_page" class="input">
        @foreach([10,20,50,100] as $n)
          <option value="{{ $n }}" @selected((int)request('per_page',20)===$n)>Show {{ $n }}</option>
        @endforeach
      </select>
      <button class="btn">Filter</button>
    </form>
  </div>
  <div class="bd">
    <div class="actions" style="margin-bottom:10px">
      <form action="{{ route('orders.store') }}" method="post" class="actions">
        @csrf
        <input class="input" name="tran_id" placeholder="ORD-00123" required>
        <input class="input" name="amount" type="number" step="0.01" placeholder="10.00" required>
        <select name="currency" class="input">
          <option value="USD">USD</option>
          <option value="KHR">KHR</option>
        </select>
        <input class="input" name="email" placeholder="customer@example.com">
        <input class="input" name="phone" placeholder="012345678">
        <button class="btn primary" type="submit"><i class="fa fa-plus"></i> Create Order</button>
      </form>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Tran ID</th>
            <th>Amount</th>
            <th>Currency</th>
            <th>Status</th>
            <th>Paid At</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $o)
            <tr>
              <td>{{ $o->tran_id }}</td>
              <td>{{ number_format($o->amount,2) }}</td>
              <td>{{ $o->currency }}</td>
              <td><span class="status {{ $o->status }}">{{ ucfirst($o->status) }}</span></td>
              <td>{{ $o->paid_at? $o->paid_at->timezone(config('app.timezone'))->format('Y-m-d H:i') : '—' }}</td>
              <td><a class="btn" href="{{ route('orders.show', $o->tran_id) }}">View</a></td>
            </tr>
          @empty
            <tr><td colspan="6">No orders yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:10px">{{ $orders->links() }}</div>
  </div>
</div>
@endsection
