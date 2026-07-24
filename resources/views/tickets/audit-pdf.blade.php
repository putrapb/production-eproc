<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Arsip Audit — Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }

    .header { border-bottom: 3px solid #1e3a8a; padding-bottom: 16px; margin-bottom: 20px; }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .org-name { font-size: 14px; font-weight: 700; color: #1e3a8a; }
    .org-sub { font-size: 10px; color: #6b7280; margin-top: 2px; }
    .doc-meta { text-align: right; }
    .doc-meta .doc-id { font-size: 18px; font-weight: 700; color: #1e3a8a; }
    .doc-meta .doc-type { font-size: 10px; color: #6b7280; margin-top: 2px; }
    .doc-meta .generated-at { font-size: 9px; color: #9ca3af; margin-top: 4px; }

    .badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 700; }
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-declined { background: #fee2e2; color: #991b1b; }
    .badge-capex    { background: #dbeafe; color: #1e40af; }
    .badge-opex     { background: #fef3c7; color: #92400e; }

    .section { margin-bottom: 20px; }
    .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 12px; }

    .info-grid { display: table; width: 100%; }
    .info-row { display: table-row; }
    .info-label { display: table-cell; width: 35%; padding: 5px 0; color: #6b7280; vertical-align: top; }
    .info-value { display: table-cell; padding: 5px 0; font-weight: 500; color: #111827; }

    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    thead tr { background: #1e3a8a; color: #fff; }
    thead th { padding: 8px 10px; text-align: left; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    tbody td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
    tbody tr:last-child td { border-bottom: none; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }

    .total-row td { font-weight: 700; background: #eff6ff; border-top: 2px solid #1e3a8a; }

    .timeline { margin: 0; padding: 0; list-style: none; }
    .timeline li { display: flex; gap: 14px; margin-bottom: 12px; align-items: flex-start; }
    .tl-dot { width: 10px; height: 10px; border-radius: 50%; background: #1e3a8a; margin-top: 3px; flex-shrink: 0; }
    .tl-dot.success { background: #10b981; }
    .tl-dot.danger  { background: #ef4444; }
    .tl-body { flex: 1; }
    .tl-action { font-weight: 600; font-size: 11px; color: #111827; }
    .tl-user { font-size: 10px; color: #6b7280; margin-top: 1px; }
    .tl-notes { font-size: 10px; color: #374151; margin-top: 3px; font-style: italic; background: #f9fafb; padding: 4px 8px; border-left: 3px solid #e5e7eb; border-radius: 2px; }
    .tl-time { font-size: 9px; color: #9ca3af; margin-top: 3px; }

    .pdi-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; }
    .pdi-title { font-size: 10px; font-weight: 700; color: #0369a1; margin-bottom: 8px; }
    .pdi-row { display: flex; gap: 8px; margin-bottom: 6px; font-size: 10px; }
    .pdi-key { color: #6b7280; width: 130px; flex-shrink: 0; }
    .pdi-val { color: #0369a1; font-family: monospace; word-break: break-all; }

    .footer { border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 24px; display: flex; justify-content: space-between; font-size: 9px; color: #9ca3af; }

    .watermark { position: fixed; bottom: 40px; right: 30px; font-size: 48px; color: rgba(30,58,138,0.06); font-weight: 900; transform: rotate(-25deg); z-index: -1; }
  </style>
</head>
<body>

<div class="watermark">E-PROC AUDIT</div>

{{-- HEADER --}}
<div class="header">
  <div class="header-top">
    <div>
      <div class="org-name">Helpdesk E-Procurement</div>
      <div class="org-sub">Sistem Manajemen Pengadaan IT — IT Infrastructure Project Management</div>
    </div>
    <div class="doc-meta">
      <div class="doc-id">Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</div>
      <div class="doc-type">Dissemination Information Package (DIP)</div>
      <div class="generated-at">Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
    </div>
  </div>
  <div style="margin-top: 10px;">
    <span class="badge badge-{{ $ticket->status === 'approved' ? 'approved' : 'declined' }}">
      {{ strtoupper($ticket->status_label) }}
    </span>
    @if($ticket->expenditure_type)
      &nbsp;<span class="badge badge-{{ strtolower($ticket->expenditure_type) }}">{{ $ticket->expenditure_type }}</span>
    @endif
    @if($ticket->is_cross_fund)
      &nbsp;<span class="badge" style="background:#ede9fe; color:#5b21b6;">⇄ Silang Dana</span>
    @endif
  </div>
</div>

{{-- PRESERVATION DESCRIPTION INFORMATION (PDI) --}}
<div class="pdi-box">
  <div class="pdi-title">🔒 Preservation Description Information (PDI) — OAIS ISO 14721</div>
  <div class="pdi-row">
    <span class="pdi-key">Provenance (Asal Usul)</span>
    <span class="pdi-val">Tiket diajukan oleh {{ $ticket->user->name }} ({{ $ticket->user->role_label }}) pada {{ $ticket->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
  </div>
  <div class="pdi-row">
    <span class="pdi-key">Reference (Referensi)</span>
    <span class="pdi-val">TICKET-{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }} | UUID: {{ $ticket->uuid ?? 'N/A' }}</span>
  </div>
  <div class="pdi-row">
    <span class="pdi-key">Fixity (Integritas)</span>
    <span class="pdi-val">SHA-256: {{ hash('sha256', $ticket->id . $ticket->title . $ticket->amount . $ticket->created_at) }}</span>
  </div>
  <div class="pdi-row">
    <span class="pdi-key">Context (Konteks)</span>
    <span class="pdi-val">Kategori: {{ $ticket->category }} | Jenis: {{ $ticket->expenditure_type ?? '-' }}</span>
  </div>
  <div class="pdi-row">
    <span class="pdi-key">Access Rights</span>
    <span class="pdi-val">Hanya dapat diakses oleh: Requester, Team Leader, Department Head, Auditor</span>
  </div>
</div>

{{-- INFORMASI TIKET --}}
<div class="section">
  <div class="section-title">1. Informasi Pengadaan</div>
  <div class="info-grid">
    <div class="info-row">
      <div class="info-label">Judul Pengadaan</div>
      <div class="info-value">{{ $ticket->title }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Vendor</div>
      <div class="info-value">{{ $ticket->vendor_name ?? '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Kategori</div>
      <div class="info-value">{{ ucwords(str_replace('_', ' ', $ticket->category)) }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Tanggal Pengajuan</div>
      <div class="info-value">{{ $ticket->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
    </div>
    <div class="info-row">
      <div class="info-label">Tanggal Selesai</div>
      <div class="info-value">{{ $ticket->updated_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
    </div>
    <div class="info-row">
      <div class="info-label">Penanggung Jawab (PIC)</div>
      <div class="info-value">{{ is_array($ticket->pic_name) ? implode(', ', $ticket->pic_name) : ($ticket->pic_name ?? '-') }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Deskripsi</div>
      <div class="info-value">{{ $ticket->description ?? '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Total Nilai</div>
      <div class="info-value" style="font-weight: 700; font-size: 13px; color: #1e3a8a;">Rp {{ number_format($ticket->amount, 0, ',', '.') }}</div>
    </div>
  </div>
</div>

{{-- DAFTAR ITEM --}}
@if($ticket->items && $ticket->items->count() > 0)
<div class="section">
  <div class="section-title">2. Daftar Item Pengadaan</div>
  <table>
    <thead>
      <tr>
        <th style="width: 5%;">No.</th>
        <th>Nama Item</th>
        <th style="width: 12%; text-align: center;">Klasifikasi</th>
        <th style="width: 8%; text-align: center;">Qty</th>
        <th style="width: 18%; text-align: right;">Harga Satuan</th>
        <th style="width: 18%; text-align: right;">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @foreach($ticket->items as $i => $item)
      <tr>
        <td class="text-center">{{ $i + 1 }}</td>
        <td>{{ $item->item_name }}</td>
        <td class="text-center">
          @if($item->expenditure_type)
            <span class="badge badge-{{ strtolower($item->expenditure_type) }}">{{ $item->expenditure_type }}</span>
          @else
            -
          @endif
        </td>
        <td class="text-center">{{ $item->quantity }}</td>
        <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
        <td class="text-right">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
      </tr>
      @endforeach
      <tr class="total-row">
        <td colspan="5" class="text-right">Total</td>
        <td class="text-right">Rp {{ number_format($ticket->amount, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>
</div>
@endif

{{-- RIWAYAT PERSETUJUAN (APPROVAL LOGS) --}}
<div class="section">
  <div class="section-title">3. Riwayat Persetujuan (Audit Trail — Chain of Custody)</div>
  @if($ticket->approvalLogs->count() > 0)
  <ul class="timeline">
    @foreach($ticket->approvalLogs as $log)
    @php
      $dotClass = 'tl-dot';
      $lowerAction = strtolower($log->action ?? '');
      if (str_contains($lowerAction, 'validated') || str_contains($lowerAction, 'approved') || str_contains($lowerAction, 'followed')) {
          $dotClass .= ' success';
      } elseif (str_contains($lowerAction, 'rejected') || str_contains($lowerAction, 'declined') || str_contains($lowerAction, 'cancel')) {
          $dotClass .= ' danger';
      }
    @endphp
    <li>
      <div class="{{ $dotClass }}"></div>
      <div class="tl-body">
        <div class="tl-action">{{ $log->action_label ?? ucwords(str_replace('_', ' ', $log->action)) }}</div>
        <div class="tl-user">oleh {{ $log->user->name ?? 'Sistem' }} — {{ $log->user->role_label ?? '' }}</div>
        @if($log->notes)
          <div class="tl-notes">"{{ $log->notes }}"</div>
        @endif
        <div class="tl-time">{{ $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB</div>
      </div>
    </li>
    @endforeach
  </ul>
  @else
    <p style="color: #9ca3af; font-style: italic;">Tidak ada riwayat persetujuan tercatat.</p>
  @endif
</div>

{{-- FOOTER --}}
<div class="footer">
  <span>Dokumen Arsip Resmi — Helpdesk E-Procurement | Sistem IT Infrastructure Project Management</span>
  <span>Dihasilkan otomatis oleh sistem pada {{ now()->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
</div>

</body>
</html>
