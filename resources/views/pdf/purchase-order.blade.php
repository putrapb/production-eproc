<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Pengadaan | FORM-{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* ─── Reset & Base ─────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1a1a2e;
            background: #fff;
            line-height: 1.5;
        }

        /* ─── Document Wrapper ──────────────────────────── */
        .page {
            padding: 0;
            max-width: 800px;
            margin: 0 auto;
        }

        /* ─── Header Band ───────────────────────────────── */
        .header-band {
            background: #006885;
            padding: 0;
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            width: 60%;
            padding: 22px 28px;
            vertical-align: middle;
        }
        .header-right {
            display: table-cell;
            width: 40%;
            background: #F15A22;
            padding: 22px 28px;
            vertical-align: middle;
            text-align: right;
        }
        .org-name {
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .org-sub {
            color: rgba(255,255,255,0.80);
            font-size: 10px;
            margin-top: 4px;
            letter-spacing: 0.3px;
        }
        .doc-type {
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.85;
        }
        .po-number {
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        /* ─── Meta Strip ────────────────────────────────── */
        .meta-strip {
            background: #f0f7fa;
            border-bottom: 2px solid #006885;
            padding: 10px 28px;
            display: table;
            width: 100%;
        }
        .meta-item {
            display: table-cell;
            width: 25%;
            padding-right: 16px;
        }
        .meta-label {
            color: #006885;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .meta-value {
            font-size: 11px;
            font-weight: bold;
            color: #1a1a2e;
            margin-top: 2px;
        }

        /* ─── Body Padding ──────────────────────────────── */
        .body {
            padding: 24px 28px;
        }

        /* ─── Section Header ────────────────────────────── */
        .section-header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            margin-top: 20px;
            border-left: 4px solid #F15A22;
            padding-left: 8px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #006885;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ─── Two-column Info Grid ──────────────────────── */
        .info-grid {
            display: table;
            width: 100%;
            border: 1px solid #dce6eb;
            border-radius: 4px;
            overflow: hidden;
        }
        .info-row {
            display: table-row;
        }
        .info-row:nth-child(even) .info-key,
        .info-row:nth-child(even) .info-val {
            background: #f9fbfc;
        }
        .info-key {
            display: table-cell;
            width: 32%;
            padding: 7px 12px;
            font-weight: bold;
            color: #4a5568;
            background: #f4f8fa;
            border-bottom: 1px solid #dce6eb;
            border-right: 1px solid #dce6eb;
            font-size: 10px;
        }
        .info-val {
            display: table-cell;
            padding: 7px 12px;
            color: #1a1a2e;
            border-bottom: 1px solid #dce6eb;
            font-size: 11px;
        }

        /* ─── Amount Highlight ──────────────────────────── */
        .amount-box {
            background: linear-gradient(135deg, #006885 0%, #004d63 100%);
            border-radius: 6px;
            padding: 14px 20px;
            margin: 16px 0;
            display: table;
            width: 100%;
        }
        .amount-label-cell {
            display: table-cell;
            color: rgba(255,255,255,0.75);
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            vertical-align: middle;
        }
        .amount-value-cell {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }
        .amount-value {
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .amount-type-badge {
            display: inline-block;
            background: #F15A22;
            color: white;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 8px;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }
        .cross-fund-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 4px;
            border: 1px solid rgba(255,255,255,0.4);
        }

        /* ─── Approval Table ────────────────────────────── */
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 10px;
        }
        .approval-table thead tr {
            background: #006885;
        }
        .approval-table thead th {
            color: #ffffff;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .approval-table tbody tr:nth-child(even) {
            background: #f9fbfc;
        }
        .approval-table tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e8eef2;
            color: #1a1a2e;
            vertical-align: top;
        }
        .action-pill {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .pill-green  { background: #e6f9f0; color: #1a7f53; }
        .pill-red    { background: #fdecea; color: #c0392b; }
        .pill-blue   { background: #e8f3fd; color: #0056a3; }
        .pill-gray   { background: #f0f0f0; color: #555; }
        .pill-orange { background: #fff3e6; color: #c0560a; }

        /* ─── Signature Block ───────────────────────────── */
        .signature-row {
            display: table;
            width: 100%;
            margin-top: 28px;
        }
        .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 10px;
        }
        .sig-role {
            font-size: 9px;
            color: #006885;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
        }
        .sig-name-box {
            border: 1px solid #b0c8d4;
            border-radius: 4px;
            padding: 8px 10px;
            min-height: 72px;
            background: #f9fbfc;
            position: relative;
        }
        .sig-name {
            font-size: 10px;
            font-weight: bold;
            color: #1a1a2e;
            margin-top: 65px;
        }
        .sig-title {
            font-size: 9px;
            color: #666;
        }
        .sig-stamp {
            position: absolute;
            top: 6px;
            left: 50%;
            transform: translateX(-50%);
            width: 38px;
            height: 38px;
            border: 2px solid #006885;
            border-radius: 50%;
            opacity: 0.12;
        }
        .sig-approved-text {
            position: absolute;
            top: 8px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #006885;
            font-weight: bold;
            opacity: 0.35;
            letter-spacing: 1px;
        }

        /* ─── Footer ────────────────────────────────────── */
        .footer-band {
            margin-top: 24px;
            background: #f0f7fa;
            border-top: 2px solid #006885;
            padding: 10px 28px;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            color: #006885;
            font-size: 9px;
            font-weight: bold;
            vertical-align: middle;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
            color: #999;
            font-size: 9px;
            vertical-align: middle;
        }

        /* ─── Utility ───────────────────────────────────── */
        .text-muted { color: #888; font-style: italic; }
        .bold { font-weight: bold; }
        .divider {
            border: none;
            border-top: 1px dashed #dce6eb;
            margin: 16px 0;
        }

        /* ─── Items Table ───────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 10px;
            border: 1px solid #dce6eb;
        }
        .items-table th {
            background: #006885;
            color: #ffffff;
            padding: 8px 12px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #dce6eb;
            letter-spacing: 0.5px;
        }
        .items-table td {
            padding: 8px 12px;
            border: 1px solid #dce6eb;
            color: #1a1a2e;
            vertical-align: top;
        }
        .items-table tbody tr:nth-child(even) {
            background: #f9fbfc;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ═══ HEADER BAND ═══ --}}
    <div class="header-band">
        <div class="header-left">
            <div class="org-name">Internal System</div>
            <div class="org-sub">Departemen IHS &middot; Kantor Pejompongan, Jakarta</div>
        </div>
        <div class="header-right">
            <div class="doc-type">Form Pengadaan</div>
            <div class="po-number">FORM-{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    {{-- ═══ META STRIP ═══ --}}
    <div class="meta-strip">
        <div class="meta-item">
            <div class="meta-label">Tanggal Terbit</div>
            <div class="meta-value">{{ $generated_at->format('d/m/Y') }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">No. Tiket</div>
            <div class="meta-value">#{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Klasifikasi</div>
            <div class="meta-value">
                {{ $ticket->expenditure_type ?? 'N/A' }}
                @if($ticket->is_cross_fund) · Silang Dana @endif
            </div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Status</div>
            <div class="meta-value" style="color:#006885;">Disetujui &amp; Diterbitkan</div>
        </div>
    </div>

    {{-- ═══ BODY ═══ --}}
    <div class="body">

        {{-- ─── Nominal ─── --}}
        <div class="amount-box">
            <div class="amount-label-cell">
                Total Nilai Pengadaan<br>
                <span style="font-weight:normal;opacity:0.7;font-size:9px;">Termasuk pajak & biaya administrasi sesuai kontrak</span>
            </div>
            <div class="amount-value-cell">
                <span class="amount-value">{{ $ticket->formatted_total_amount }}</span>
                @if($ticket->expenditure_type)
                    <span class="amount-type-badge">{{ $ticket->expenditure_type }}</span>
                @endif
                @if($ticket->is_cross_fund)
                    <span class="cross-fund-badge">⇄ Silang Dana</span>
                @endif
            </div>
        </div>

        {{-- ─── Detail Pengadaan ─── --}}
        <div class="section-header">
            <div class="section-title">A. Informasi Pengadaan</div>
        </div>
        
        <!-- Metadata Pengadaan -->
        <div class="info-grid" style="margin-bottom: 8px;">
            <div class="info-row">
                <div class="info-key">Judul Pengadaan</div>
                <div class="info-val"><strong>{{ $ticket->title }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-key">Nama Vendor</div>
                <div class="info-val"><strong>{{ $ticket->vendor_name }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-key">Tanggal Pengajuan</div>
                <div class="info-val">{{ $ticket->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB</div>
            </div>
        </div>

        <!-- Tabel Detail Barang -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Deskripsi</th>
                    <th style="width: 15%;">PIC</th>
                    <th style="width: 20%;">Kategori</th>
                    <th style="width: 10%; text-align: center;">Jumlah</th>
                    <th style="width: 10%; text-align: right;">Harga Satuan</th>
                    <th style="width: 10%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $ticket->item_name }}</strong>
                        @if($ticket->description)
                            <br><span style="font-size: 8px; color: #666; font-style: italic; white-space: pre-wrap; word-break: break-word; display: block; margin-top: 2px;">{{ $ticket->description }}</span>
                        @endif
                    </td>
                    <td style="white-space: pre-wrap; word-break: break-word; vertical-align: top;">
                        @if(is_array($ticket->pic_name) && count($ticket->pic_name) > 0)
                            {!! implode('<br>', array_map(fn($p) => htmlspecialchars(is_array($p) ? json_encode($p) : $p), $ticket->pic_name)) !!}
                        @elseif(is_string($ticket->pic_name) && !empty($ticket->pic_name))
                            {{ $ticket->pic_name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ strtoupper(str_replace('_', ' ', $ticket->category)) }}</td>
                    <td style="text-align: center;">{{ number_format($ticket->quantity) }} unit</td>
                    <td style="text-align: right;">{{ $ticket->formatted_amount }}</td>
                    <td style="text-align: right;">{{ $ticket->formatted_total_amount }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ─── Requester ─── --}}
        <div class="section-header" style="margin-top:16px;">
            <div class="section-title">B. Data Pengaju</div>
        </div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-key">Nama Pengaju</div>
                <div class="info-val"><strong>{{ $ticket->user->name }}</strong></div>
            </div>
            @if($ticket->user->hrEmployee)
            <div class="info-row">
                <div class="info-key">NIP</div>
                <div class="info-val">{{ $ticket->user->hrEmployee->nip }}</div>
            </div>
            <div class="info-row">
                <div class="info-key">Jabatan</div>
                <div class="info-val">{{ $ticket->user->hrEmployee->position }}</div>
            </div>
            <div class="info-row">
                <div class="info-key">Departemen</div>
                <div class="info-val">{{ $ticket->user->hrEmployee->division }}</div>
            </div>
            @endif
        </div>

        {{-- ─── Riwayat Persetujuan ─── --}}
        <div class="section-header" style="margin-top:16px;">
            <div class="section-title">C. Rantai Persetujuan</div>
        </div>
        <table class="approval-table">
            <thead>
                <tr>
                    <th style="width:14%">Tanggal</th>
                    <th style="width:22%">Nama</th>
                    <th style="width:16%">Jabatan</th>
                    <th style="width:20%">Aksi</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ticket->approvalLogs->sortBy('created_at') as $log)
                @php
                    $actionClass = match(true) {
                        in_array($log->action, ['followed_up', 'approved', 'validated', 'po_issued']) => 'pill-green',
                        in_array($log->action, ['rejected_document', 'declined'])                    => 'pill-red',
                        in_array($log->action, ['submitted', 'revised'])                             => 'pill-blue',
                        $log->action === 'cross_fund_requested'                                      => 'pill-orange',
                        default                                                                       => 'pill-gray',
                    };
                @endphp
                <tr>
                    <td>{{ $log->created_at->format('d/m/Y') }}<br><span class="text-muted">{{ $log->created_at->format('H:i') }} WIB</span></td>
                    <td><strong>{{ $log->user->name }}</strong></td>
                    <td><span class="text-muted">{{ $log->user->role_label }}</span></td>
                    <td><span class="action-pill {{ $actionClass }}">{{ $log->action_label }}</span></td>
                    <td>{{ $log->notes ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ─── Informasi Penerbitan ─── --}}
        <div style="page-break-before: always;">
        <div class="section-header" style="margin-top:22px;">
            <div class="section-title">D. Informasi Penerbitan Form</div>
        </div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-key">Diterbitkan Oleh</div>
                <div class="info-val"><strong>{{ $generated_by->name }}</strong> <span class="text-muted">({{ $generated_by->role_label }})</span></div>
            </div>
            <div class="info-row">
                <div class="info-key">Tanggal &amp; Waktu Terbit</div>
                <div class="info-val">{{ $generated_at->isoFormat('D MMMM Y, HH:mm') }} WIB</div>
            </div>
            <div class="info-row">
                <div class="info-key">Nomor Referensi Sistem</div>
                <div class="info-val" style="font-family:monospace; color:#006885;">EPR-{{ date('Y') }}-TKT{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}-FORM{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <hr class="divider">

        {{-- ─── Tanda Tangan ─── --}}
        <div class="signature-row">
            {{-- Requester --}}
            <div class="signature-cell">
                <div class="sig-role">Dibuat oleh / Requester</div>
                <div class="sig-name-box">
                    @php $qrUrl = \Illuminate\Support\Facades\URL::signedRoute('tickets.verify', ['ticket' => $ticket->id]); @endphp
                    <div style="position: absolute; top: 2px; left: 50%; transform: translateX(-50%); width: 60px; height: 60px; text-align: center;">
                        <img src="data:image/png;base64,{{ \Milon\Barcode\Facades\DNS2DFacade::getBarcodePNG($qrUrl, 'QRCODE', 3, 3) }}" alt="QR Code" style="width: 55px; height: 55px;" />
                    </div>
                    <div class="sig-name">{{ $ticket->user->name }}</div>
                    <div class="sig-title">
                        @if($ticket->user->hrEmployee)
                            {{ $ticket->user->hrEmployee->position }}
                        @else
                            Staff IT Infrastructure
                        @endif
                    </div>
                </div>
            </div>

            {{-- Team Leader --}}
            <div class="signature-cell">
                <div class="sig-role">Diterbitkan oleh / Team Leader</div>
                <div class="sig-name-box">
                    <div style="position: absolute; top: 2px; left: 50%; transform: translateX(-50%); width: 60px; height: 60px; text-align: center;">
                        <img src="data:image/png;base64,{{ \Milon\Barcode\Facades\DNS2DFacade::getBarcodePNG($qrUrl, 'QRCODE', 3, 3) }}" alt="QR Code" style="width: 55px; height: 55px;" />
                    </div>
                    <div class="sig-name">{{ $generated_by->name }}</div>
                    <div class="sig-title">
                        @if($generated_by->hrEmployee)
                            {{ $generated_by->hrEmployee->position }}
                        @else
                            Staff Procurement
                        @endif
                    </div>
                </div>
            </div>

            {{-- Division Head --}}
            @php
                $divHeadLog = $ticket->approvalLogs->filter(fn($l) => $l->action === 'approved')->sortByDesc('created_at')->first();
            @endphp
            <div class="signature-cell">
                <div class="sig-role">Disetujui oleh / Dept. Head</div>
                <div class="sig-name-box">
                    @if($divHeadLog)
                        <div style="position: absolute; top: 2px; left: 50%; transform: translateX(-50%); width: 60px; height: 60px; text-align: center;">
                            <img src="data:image/png;base64,{{ \Milon\Barcode\Facades\DNS2DFacade::getBarcodePNG($qrUrl, 'QRCODE', 3, 3) }}" alt="QR Code" style="width: 55px; height: 55px;" />
                        </div>
                        <div class="sig-name">{{ $divHeadLog->user->name }}</div>
                        <div class="sig-title">{{ $divHeadLog->user->role_label }}</div>
                    @else
                        <div class="sig-name" style="color:#888; font-weight:normal; font-style:italic;">—</div>
                    @endif
                </div>
            </div>
        </div>

        </div>
    </div>{{-- end .body --}}

    {{-- ═══ FOOTER BAND ═══ --}}
    <div class="footer-band">
        <div class="footer-left">
            Helpdesk E-Procurement Pejompongan &nbsp;&middot;&nbsp; Departemen IT Infrastructure Management
        </div>
        <div class="footer-right">
            Dokumen ini diterbitkan secara elektronik oleh Sistem Helpdesk E-Procurement &nbsp;&middot;&nbsp;
            {{ $generated_at->format('d/m/Y H:i') }} WIB
        </div>
    </div>

</div>
</body>
</html>
