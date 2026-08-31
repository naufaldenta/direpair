@extends('layouts.operations')
@section('title', $serviceRequest->public_number)
@section('content')
<a class="back-link" href="{{ route('operations.index') }}">Semua request</a>
<div class="page-head">
    <div><span class="route-code">ACTIVE TICKET / DETAIL</span><h1>{{ $serviceRequest->public_number }}</h1><span class="badge ok">{{ $serviceRequest->status->label() }}</span> @if($serviceRequest->is_demo)<span class="badge demo">data demo</span>@endif</div>
    <span class="muted">Masuk {{ $serviceRequest->submitted_at->format('d M Y H:i') }}</span>
</div>
<div class="grid">
    <section class="panel"><span class="route-code">INTAKE / DEVICE</span><h2>Pelanggan & perangkat</h2>
        <p><strong>{{ $serviceRequest->customer->name }}</strong><br>{{ $serviceRequest->customer->phone }}<br>{{ $serviceRequest->customer->email ?: 'Email tidak diisi' }}</p>
        <p><span class="muted">Perangkat</span><br><strong>{{ $serviceRequest->device_category }}</strong> · {{ collect([$serviceRequest->brand,$serviceRequest->model])->filter()->join(' ') ?: 'brand/model belum diisi' }}</p>
        <p><span class="muted">Keluhan</span><br>{{ $serviceRequest->symptom }}</p><p><span class="muted">Metode</span><br>{{ $serviceRequest->preferred_service_method }}</p>
        @if($serviceRequest->service_address)<p><span class="muted">Alamat layanan</span><br>{{ collect($serviceRequest->service_address)->filter()->join(', ') }}</p>@endif
        <p class="muted">{{ $serviceRequest->media->count() }} lampiran tersimpan privat.</p>
    </section>
    <section class="panel yellow"><span class="route-code">ROUTE CONTROL / NEXT</span><h2>Ubah status</h2>
        @if($allowedStatuses === [])<p class="muted">Tidak ada transisi berikutnya dari status ini.</p>@else
        <form method="post" action="{{ route('operations.requests.status.update', $serviceRequest) }}">@csrf @method('PATCH')
            <div class="field"><label for="status">Status berikutnya</label><select id="status" name="status" required>@foreach($allowedStatuses as $status)<option value="{{ $status->value }}">{{ $status->label() }}</option>@endforeach</select></div>
            <div class="field"><label for="public_message">Pesan ke pelanggan</label><textarea id="public_message" name="public_message">{{ old('public_message') }}</textarea></div>
            <input type="hidden" name="visible_to_customer" value="0"><div class="field"><label><input type="checkbox" name="visible_to_customer" value="1" checked> Tampilkan di timeline pelanggan</label></div>
            <button type="submit">Perbarui status</button>
        </form>@endif
    </section>
</div>
@if(in_array($serviceRequest->status, [\App\RepairStatus::UnitReceived, \App\RepairStatus::Diagnosing, \App\RepairStatus::AwaitingApproval], true))
<section class="panel"><span class="route-code">DIAGNOSIS / NEW QUOTE</span><h2>Buat diagnosis & quotation baru</h2><p class="muted">Quotation aktif sebelumnya otomatis menjadi superseded. Nominal menggunakan Rupiah tanpa desimal.</p>
    <form method="post" action="{{ route('operations.requests.quotations.store', $serviceRequest) }}">@csrf
        <div class="grid"><div class="field"><label>Kesimpulan diagnosis</label><textarea name="diagnosis_summary" required>{{ old('diagnosis_summary') }}</textarea></div><div>
            <div class="field"><label>Repairability</label><select name="repairability"><option value="repairable">Repairable</option><option value="needs_parts">Menunggu parts</option><option value="not_repairable">Tidak repairable</option></select></div>
            <div class="field"><label>Estimasi hari</label><input type="number" name="estimated_days" min="1" max="180" value="{{ old('estimated_days', 5) }}"></div></div></div>
        <div class="field"><label>Temuan untuk pelanggan</label><textarea name="findings">{{ old('findings') }}</textarea></div><div class="field"><label>Catatan internal</label><textarea name="internal_notes">{{ old('internal_notes') }}</textarea></div>
        <h3>Item biaya</h3><div id="quotation-items"><div class="item-row">
            <select name="items[0][type]"><option value="labor">Jasa</option><option value="diagnosis">Diagnosis</option><option value="part">Sparepart</option><option value="shipping">Pengiriman</option><option value="other">Lainnya</option></select>
            <input name="items[0][label]" placeholder="Contoh: jasa repair" required><input name="items[0][quantity]" type="number" min="0.01" step="0.01" value="1" required><input name="items[0][unit_price]" type="number" min="0" step="1" placeholder="Harga/unit" required>
        </div></div><button class="secondary" type="button" id="add-item">Tambah item</button>
        <div class="grid three request-meta"><div class="field"><label>Discount</label><input type="number" name="discount" min="0" value="0"></div><div class="field"><label>Pajak</label><input type="number" name="tax" min="0" value="0"></div><div class="field"><label>Berlaku (hari)</label><input type="number" name="valid_days" min="1" max="90" value="7"></div></div>
        <input type="hidden" name="deposit_required" value="0"><div class="field"><label><input type="checkbox" name="deposit_required" value="1"> Wajib deposit sebelum repair</label></div><div class="field"><label>Nominal deposit</label><input type="number" name="deposit_amount" min="0" value="0"></div>
        <div class="field"><label>Catatan quotation untuk pelanggan</label><textarea name="customer_notes">Harga final hanya berlaku untuk pekerjaan dan komponen di atas.</textarea></div><button type="submit">Kirim quotation</button>
    </form>
</section>
<script>(()=>{let index=1;document.querySelector('#add-item')?.addEventListener('click',()=>{const row=document.createElement('div');row.className='item-row';row.innerHTML=`<select name="items[${index}][type]"><option value="labor">Jasa</option><option value="diagnosis">Diagnosis</option><option value="part">Sparepart</option><option value="shipping">Pengiriman</option><option value="other">Lainnya</option></select><input name="items[${index}][label]" placeholder="Nama item" required><input name="items[${index}][quantity]" type="number" min="0.01" step="0.01" value="1" required><input name="items[${index}][unit_price]" type="number" min="0" step="1" placeholder="Harga/unit" required>`;document.querySelector('#quotation-items')?.appendChild(row);index++;});})();</script>
@endif
<div class="grid">
    <section class="panel"><span class="route-code">QUOTE / PAYMENT</span><h2>Quotation & payment</h2>@forelse($serviceRequest->quotations as $quote)<article class="quote-entry">
        <div class="quote-head"><strong>Versi {{ $quote->version }}</strong><span class="badge">{{ $quote->status->value }}</span></div><p>{{ $quote->diagnosis?->summary }}</p>
        <table>@foreach($quote->items as $item)<tr><td>{{ $item->label }}</td><td class="money">Rp {{ number_format($item->total,0,',','.') }}</td></tr>@endforeach<tr><th>Total</th><th class="money">Rp {{ number_format($quote->total,0,',','.') }}</th></tr></table>
        @foreach($quote->invoices as $invoice)<p><strong>{{ $invoice->number }}</strong> · {{ $invoice->kind }} · {{ $invoice->status->value }} · <span class="money">Rp {{ number_format($invoice->amount,0,',','.') }}</span></p>@endforeach
    </article>@empty<p class="muted">Belum ada quotation.</p>@endforelse</section>
    <section class="panel"><span class="route-code">EVENT / HISTORY</span><h2>Timeline</h2><div class="timeline">@foreach($serviceRequest->statusEvents->sortByDesc('occurred_at') as $event)<article><strong>{{ $event->public_label }}</strong><br><span class="muted">{{ $event->occurred_at->format('d M Y H:i') }}</span>@if($event->public_message)<p>{{ $event->public_message }}</p>@endif</article>@endforeach</div></section>
</div>
@endsection
