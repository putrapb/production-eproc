<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order — E-Procurement BNI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #006885;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #F15A22;
            font-size: 20px;
            margin: 0;
        }
        .header h2 {
            color: #006885;
            font-size: 14px;
            margin: 5px 0 0;
            font-weight: normal;
        }
        .po-number {
            font-size: 16px;
            font-weight: bold;
            color: #006885;
            text-align: right;
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #006885;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            margin: 15px 0 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table td {
            padding: 6px 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        table td:first-child {
            width: 35%;
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-capex { background-color: #0088FF; color: white; }
        .badge-opex  { background-color: #34C759; color: white; }
        .badge-cross-fund { background-color: #FFCC00; color: #333; }
        .approval-log {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .approval-log th {
            background-color: #006885;
            color: white;
            padding: 6px 10px;
            text-align: left;
        }
        .approval-log td {
            padding: 6px 10px;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
        .amount {
            font-size: 16px;
            font-weight: bold;
            color: #F15A22;
        }
    </style>
</head>
<body>

    {{-- ─── Header ─── --}}
    <div class="header">
        <h1>E-Procurement BNI</h1>
        <h2>Divisi IT Infrastructure Management — BNI Pejompongan</h2>
    </div>

    <div class="po-number">
        PURCHASE ORDER — No. PO-{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}
    </div>

    {{-- ─── Procurement Details ─── --}}
    <div class="section-title">DETAIL PENGADAAN</div>
    <table>
        <tr>
            <td>Judul Pengadaan</td>
            <td>{{ $ticket->title }}</td>
        </tr>
        <tr>
            <td>Nama Item</td>
            <td>{{ $ticket->item_name }}</td>
        </tr>
        <tr>
            <td>Kategori</td>
            <td>{{ strtoupper(str_replace('_', ' ', $ticket->category)) }}</td>
        </tr>
        <tr>
            <td>Deskripsi</td>
            <td>{{ $ticket->description ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jumlah Unit</td>
            <td>{{ number_format($ticket->quantity) }} unit</td>
        </tr>
        <tr>
            <td>Vendor</td>
            <td>{{ $ticket->vendor_name }}</td>
        </tr>
        <tr>
            <td>Nominal Harga</td>
            <td><span class="amount">{{ $ticket->formatted_amount }}</span></td>
        </tr>
        <tr>
            <td>Klasifikasi Anggaran</td>
            <td>
                <span class="badge badge-{{ strtolower($ticket->expenditure_type) }}">
                    {{ $ticket->expenditure_type }}
                </span>
                @if($ticket->is_cross_fund)
                    <span class="badge badge-cross-fund">SILANG DANA</span>
                @endif
            </td>
        </tr>
        <tr>
            <td>Requester</td>
            <td>{{ $ticket->user->name }}</td>
        </tr>
        <tr>
            <td>Tanggal Pengajuan</td>
            <td>{{ $ticket->created_at->format('d F Y, H:i') }}</td>
        </tr>
    </table>

    {{-- ─── Approval Chain ─── --}}
    <div class="section-title">RIWAYAT PERSETUJUAN</div>
    <table class="approval-log">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Actor</th>
                <th>Aksi</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ticket->approvalLogs->sortBy('created_at') as $log)
            <tr>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->user->name }}<br><small>{{ $log->user->role_label }}</small></td>
                <td>{{ $log->action_label }}</td>
                <td>{{ $log->notes ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ─── Generation Info ─── --}}
    <div class="section-title">INFORMASI PENERBITAN</div>
    <table>
        <tr>
            <td>Diterbitkan Oleh</td>
            <td>{{ $generated_by->name }} ({{ $generated_by->role_label }})</td>
        </tr>
        <tr>
            <td>Tanggal Terbit</td>
            <td>{{ $generated_at->format('d F Y, H:i') }}</td>
        </tr>
    </table>

    <div class="footer">
        Dokumen ini diterbitkan secara otomatis oleh Sistem E-Procurement BNI.
        Divisi IT Infrastructure Management — BNI Pejompongan.
    </div>

</body>
</html>
