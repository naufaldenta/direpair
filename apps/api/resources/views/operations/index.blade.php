@extends('layouts.operations')
@section('title', 'Repair requests')
@section('content')
<div class="page-head">
    <div><span class="route-code">WORK QUEUE / ALL LINES</span><h1>Repair requests</h1><span class="muted">{{ $requests->total() }} request tercatat</span></div>
    <form method="get" action="{{ route('operations.index') }}" class="actions filter-bar">
        <select name="status" aria-label="Filter status"><option value="">Semua status</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>@endforeach</select>
        <button class="secondary" type="submit">Filter</button>
    </form>
</div>
<div class="panel table-panel table-scroll"><table>
    <thead><tr><th>Request</th><th>Pelanggan</th><th>Perangkat</th><th>Status</th><th>Masuk</th></tr></thead>
    <tbody>@forelse ($requests as $repair)<tr>
        <td><a href="{{ route('operations.requests.show', $repair) }}"><strong>{{ $repair->public_number }}</strong></a>@if($repair->is_demo) <span class="badge demo">demo</span>@endif<br><span class="muted">{{ $repair->preferred_service_method }}</span></td>
        <td>{{ $repair->customer->name }}<br><span class="muted">{{ $repair->customer->phone }}</span></td>
        <td>{{ $repair->device_category }}<br><span class="muted">{{ collect([$repair->brand,$repair->model])->filter()->join(' · ') ?: 'Brand/model belum diisi' }}</span></td>
        <td><span class="badge">{{ $repair->status->label() }}</span></td><td>{{ $repair->submitted_at->format('d M Y H:i') }}</td>
    </tr>@empty<tr><td colspan="5">Belum ada request untuk filter ini.</td></tr>@endforelse</tbody>
</table></div>
{{ $requests->links() }}
@endsection
