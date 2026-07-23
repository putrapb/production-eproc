@extends('layouts.app')

@section('title', 'Detail Tiket #' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT))

@section('breadcrumb')
  <a href="{{ route('tickets.index') }}" class="breadcrumb-item">Tiket Pengadaan</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-active">Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</span>
@endsection

@section('content')
<div class="page-header">
  <div class="page-header-left">
    <h1>Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</h1>
    <div style="display:flex; align-items:center; gap:var(--space-sm); margin-top:var(--space-xs);">
      <span class="badge badge-{{ str_replace('_','-',$ticket->status) }}">{{ $ticket->status_label }}</span>
      @if($ticket->is_cross_fund)
        <span class="badge badge-cross-fund">⇄ Silang Dana</span>
      @endif
      @if($ticket->expenditure_type)
        <span class="badge badge-{{ strtolower($ticket->expenditure_type) }}">{{ $ticket->expenditure_type }}</span>
      @endif
    </div>
  </div>
  <div style="display:flex; gap:var(--space-sm); align-items:center;">
    @if(auth()->user()->isRequester() && in_array($ticket->status, ['revision', 'pending_review']))
      <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Edit Tiket
      </a>
    @endif
    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      Kembali
    </a>
  </div>
</div>

{{-- Smart Validation Result Banner --}}
@if(session('validation_result'))
  @php $vr = session('validation_result'); @endphp
  <div class="alert alert-{{ $vr['status'] === 'halted' ? 'warning' : 'success' }}" style="margin:0 var(--space-xl) var(--space-lg);">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
      @if($vr['status'] === 'halted')
        <path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      @else
        <circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/>
      @endif
    </svg>
    <div>
      <strong>{{ $vr['status'] === 'halted' ? 'Validasi Terhenti' : 'Validasi Berhasil' }}</strong>
      — {{ $vr['message'] }}
      @if(!empty($vr['gate'])) (Gate {{ $vr['gate'] }}) @endif
    </div>
  </div>
@endif

<div class="page-content">
  <div class="detail-grid">

    {{-- ─── LEFT: TICKET DETAIL CARD ───────────────── --}}
    <div>

      {{-- Informasi Pengadaan --}}
      <div class="card mb-lg">
        <div class="card-header">
          <div class="heading-sm">Informasi Pengadaan</div>
        </div>
        <div class="card-body">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md);">
            <div class="detail-field">
              <div class="detail-field-label">Judul</div>
              <div class="detail-field-value">{{ $ticket->title }}</div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Vendor</div>
              <div class="detail-field-value">{{ $ticket->vendor_name }}</div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Total Harga</div>
              <div class="detail-field-value large">{{ $ticket->formatted_total_amount }}</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Daftar Item --}}
      <div class="card mb-lg">
        <div class="card-header">
          <div class="heading-sm">Daftar Item Pengadaan</div>
          <span class="badge badge-category">{{ $ticket->items->count() }} item</span>
        </div>
        <div class="card-body" style="padding:0;">
          @if($ticket->items->isNotEmpty())
          <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
              <thead>
                <tr style="background:var(--color-surface-soft); font-size:12px; font-weight:600; color:var(--color-muted);">
                  <th style="padding:10px 16px; text-align:left; border-bottom:1px solid var(--color-border); width:40px;">No.</th>
                  <th style="padding:10px 16px; text-align:left; border-bottom:1px solid var(--color-border);">Nama Item</th>
                  <th style="padding:10px 16px; text-align:center; border-bottom:1px solid var(--color-border); width:110px;">Klasifikasi</th>
                  <th style="padding:10px 16px; text-align:center; border-bottom:1px solid var(--color-border); width:80px;">Qty</th>
                  <th style="padding:10px 16px; text-align:right; border-bottom:1px solid var(--color-border); width:180px;">Harga Satuan</th>
                  <th style="padding:10px 16px; text-align:right; border-bottom:1px solid var(--color-border); width:180px;">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                @foreach($ticket->items as $i => $item)
                <tr style="border-bottom:1px solid var(--color-hairline);">
                  <td style="padding:10px 16px; font-size:13px; color:var(--color-muted);">{{ $i + 1 }}</td>
                  <td style="padding:10px 16px; font-size:14px; font-weight:500;">{{ $item->item_name }}</td>
                  <td style="padding:10px 16px; text-align:center; font-size:13px;">
                    @if($item->effective_expenditure_type)
                      <span class="badge badge-{{ strtolower($item->effective_expenditure_type) }}">{{ $item->effective_expenditure_type }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td style="padding:10px 16px; text-align:center; font-size:13px;">{{ number_format($item->quantity) }}</td>
                  <td style="padding:10px 16px; text-align:right; font-size:13px;">{{ $item->formatted_unit_price }}</td>
                  <td style="padding:10px 16px; text-align:right; font-size:13px; font-weight:600; color:var(--color-text);">{{ $item->formatted_subtotal }}</td>
                </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr style="background:var(--color-surface-soft);">
                  <td colspan="5" style="padding:12px 16px; text-align:right; font-size:13px; font-weight:700; color:var(--color-muted);">TOTAL</td>
                  <td style="padding:12px 16px; text-align:right; font-size:15px; font-weight:700; color:var(--color-primary);">{{ $ticket->formatted_total_amount }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
          @else
            <div class="empty-state" style="padding:var(--space-lg);">
              <p class="text-muted">Belum ada item terdaftar.</p>
            </div>
          @endif
        </div>
      </div>

      {{-- Klasifikasi Anggaran --}}
      @if($ticket->expenditure_type || $ticket->is_cross_fund !== null)
      <div class="card mb-lg">
        <div class="card-header">
          <div class="heading-sm">Klasifikasi Anggaran</div>
        </div>
        <div class="card-body">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md);">
            <div class="detail-field">
              <div class="detail-field-label">Jenis Pengeluaran</div>
              <div class="detail-field-value">
                @if($ticket->expenditure_type)
                  <span class="badge badge-{{ strtolower($ticket->expenditure_type) }}">{{ $ticket->expenditure_type }}</span>
                @else
                  <span class="text-muted">Belum diklasifikasi</span>
                @endif
              </div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Silang Dana</div>
              <div class="detail-field-value">
                @if($ticket->is_cross_fund)
                  <span class="badge badge-cross-fund">⇄ Silang Dana Aktif</span>
                @else
                  <span class="text-muted">Tidak</span>
                @endif
              </div>
            </div>
            @if($ticket->validated_at)
            <div class="detail-field">
              <div class="detail-field-label">Tanggal Validasi</div>
              <div class="detail-field-value">{{ $ticket->validated_at->format('d M Y, H:i') }}</div>
            </div>
            @endif
          </div>
        </div>
      </div>
      @endif

      {{-- Dokumen --}}
      <div class="card mb-lg">
        <div class="card-header">
          <div class="heading-sm">Dokumen Pendukung</div>
        </div>
        <div class="card-body">
          <div style="display: flex; flex-direction: column; gap: var(--space-md);">
            @forelse($ticket->documents as $doc)
              <div style="display:flex; align-items:center; justify-content:space-between; padding:var(--space-sm) var(--space-md); border:1px solid var(--color-hairline); border-radius:var(--radius-md); background:var(--color-surface-card);">
                <div style="display:flex; align-items:center; gap:var(--space-sm);">
                  <div style="width:36px; height:36px; background:var(--color-error-soft); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; color:var(--color-error); flex-shrink:0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  </div>
                  <div>
                    <div class="label-md text-ink">{{ $doc->description }}</div>
                    <div class="caption text-muted" style="margin-top: 2px;">
                      Status: 
                      @if($doc->isAccepted())
                        <span style="color:var(--color-success); font-weight:600;">{{ $doc->status_label }}</span>
                      @elseif($doc->isRejected())
                        <span style="color:var(--color-error); font-weight:600;">{{ $doc->status_label }}</span>
                        @if($doc->feedback)
                          <br><span style="font-style: italic;">Catatan revisi: "{{ $doc->feedback }}"</span>
                        @endif
                      @else
                        <span style="color:var(--color-info); font-weight:600;">{{ $doc->status_label }}</span>
                      @endif
                    </div>
                  </div>
                </div>
                <div style="display:flex; gap:var(--space-xs);">
                  <a href="{{ route('tickets.document', ['ticketDocument' => $doc->id]) }}" target="_blank" class="btn btn-ghost btn-sm">
                    Lihat PDF
                  </a>
                  <a href="{{ route('tickets.document', ['ticketDocument' => $doc->id, 'download' => 1]) }}" class="btn btn-ghost btn-sm" title="Download">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                  </a>
                </div>
              </div>
            @empty
              <span class="text-muted">Belum ada dokumen</span>
            @endforelse
          </div>
        </div>
      </div>

          {{-- PO Document --}}
          @if($ticket->po_path)
          <hr class="divider" style="margin:var(--space-md) 0;">
          <div class="detail-field">
            <div class="detail-field-label">Form Pengadaan</div>
            <div class="detail-field-value">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--space-md);">
                  <a href="{{ route('tickets.download-po', ['ticket' => $ticket->id]) }}" target="_blank" style="text-decoration:none; display:flex; align-items:center; gap:var(--space-sm); padding:var(--space-sm) var(--space-md); border:1px solid var(--color-hairline); border-radius:var(--radius-md); transition:background 0.2s;">
                    <div style="width:36px; height:36px; background:var(--color-success-soft); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; color:var(--color-success-text);">
                      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 12l2 2 4-4"/></svg>
                    </div>
                    <div>
                      <div class="label-md text-ink">Form Pengadaan — FORM-{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}</div>
                      <div class="caption text-muted">Klik untuk melihat file (PDF) · Diterbitkan {{ $ticket->po_generated_at?->format('d M Y') }}</div>
                    </div>
                  </a>
                  {{-- Download hanya untuk Requester dan Team Leader --}}
                  @if($user->isRequester() || $user->isTeamLeader())
                  <a href="{{ route('tickets.download-po', ['ticket' => $ticket->id, 'download' => 1]) }}" class="btn btn-ghost btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Unduh PO
                  </a>
                  @endif
                </div>
            </div>
          </div>
          @endif

      {{-- Metadata --}}
      <div class="card">
        <div class="card-body">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-sm);">
            <div class="detail-field">
              <div class="detail-field-label">Dibuat Oleh</div>
              <div class="detail-field-value">{{ $ticket->user?->name ?? 'Karyawan Terhapus/Tidak Ditemukan' }}</div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Tanggal Pengajuan</div>
              <div class="detail-field-value">{{ $ticket->created_at->format('d M Y, H:i') }}</div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Terakhir Diperbarui</div>
              <div class="detail-field-value">{{ $ticket->updated_at->diffForHumans() }}</div>
            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- ─── RIGHT: APPROVAL LOG ────────────────────── --}}
    <div>
      <div class="card" style="position:sticky; top:calc(var(--topbar-height) + var(--space-lg));">
        <div class="card-header">
          <div class="heading-sm">Riwayat Persetujuan</div>
          <span class="badge badge-category">{{ $ticket->approvalLogs->count() }} aksi</span>
        </div>
        <div class="card-body">
          @if($ticket->approvalLogs->isNotEmpty())
            <div class="approval-timeline">
              @foreach($ticket->approvalLogs->sortByDesc('created_at') as $log)
                <div class="approval-item">
                  <div class="approval-line-wrap">
                    <div class="approval-dot {{ in_array($log->action, ['accepted','approved','forwarded','po_generated']) ? 'dot-success' : (in_array($log->action, ['rejected','declined']) ? 'dot-error' : ($log->action === 'revised' ? 'dot-warning' : '')) }}"></div>
                    <div class="approval-connector"></div>
                  </div>
                  <div class="approval-content">
                    <div class="approval-actor">
                      {{ $log->user?->name ?? 'Sistem / Karyawan Terhapus' }}
                      <span class="approval-role-tag">({{ $log->user?->role_label ?? 'N/A' }})</span>
                    </div>
                    <div class="approval-action">{{ $log->action_label }}</div>
                    @if($log->notes)
                      <div class="approval-notes">"{{ $log->notes }}"</div>
                    @endif
                    <div class="approval-time">{{ $log->created_at->format('d M Y · H:i') }} · {{ $log->created_at->diffForHumans() }}</div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="empty-state" style="padding:var(--space-xl) 0;">
              <div style="font-size:32px; margin-bottom:var(--space-sm);">📋</div>
              <p class="text-muted">Belum ada riwayat aksi.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

{{-- ─── ACTION PANEL ─────────────────────────────── --}}
@php $user = auth()->user(); $status = $ticket->status; @endphp

@if(
  ($user->isRequester() && in_array($status, ['need_to_validate', 'revision', 'pending_review', 'form_generated'])) ||
  ($user->isTeamLeader() && in_array($status, ['pending_review', 'approved', 'form_generated'])) ||
  ($user->isDepartmentHead() && $status === 'pending_dept_head')
)
<div class="action-panel">
  <div class="action-panel-info">
    @if($user->isRequester() && $status === 'revision')
      Dokumen Anda memerlukan revisi. Unggah ulang dokumen untuk melanjutkan proses.
    @elseif($user->isRequester() && $status === 'pending_review')
      Tiket sedang menunggu pengecekan dokumen oleh Team Leader. Anda masih dapat mengedit tiket atau mengunggah ulang dokumen jika diperlukan.
    @elseif($user->isRequester() && $status === 'need_to_validate')
      Dokumen Pendukung Anda telah diterima oleh Team Leader. Jalankan Smart Validation untuk mengklasifikasikan anggaran.
    @elseif($user->isTeamLeader() && $status === 'pending_review')
      Tinjau Dokumen Pendukung. Terima jika valid, atau minta revisi dokumen.
    @elseif($user->isTeamLeader() && $status === 'approved')
      Tiket disetujui oleh Department Head. Generate Form Pengadaan sekarang.
    @elseif($user->isDepartmentHead() && $status === 'pending_dept_head')
      Berikan keputusan final: setujui atau tolak pengajuan pengadaan ini.
    @elseif($status === 'form_generated' && $user->isRequester())
      Form Pengadaan telah diterbitkan oleh Team Leader. Anda dapat mengunduh dokumen.
    @elseif($status === 'form_generated' && $user->isTeamLeader())
      Form Pengadaan telah berhasil diterbitkan. Anda dapat mengunduh dokumen.
    @endif
  </div>

  <div class="action-panel-buttons">
    {{-- TEAM LEADER: Document review --}}
    @if($user->isTeamLeader() && $status === 'pending_review')
      <button onclick="openModal('modal-review-documents')" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
        Tinjau Dokumen Pendukung
      </button>
    @endif

    {{-- REQUESTER: Smart Validation --}}
    @if($user->isRequester() && $status === 'need_to_validate')
      <button onclick="openModal('modal-cancel-ticket')" class="btn btn-danger">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Batalkan Tiket
      </button>
      <button onclick="openModal('modal-validate')" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Jalankan Smart Validation
      </button>
    @endif

    {{-- TEAM LEADER: Generate Form --}}
    @if($user->isTeamLeader() && $status === 'approved')
      <button onclick="openModal('modal-generate-form')" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 12l2 2 4-4"/></svg>
        Generate Form Pengadaan
      </button>
    @endif

    {{-- REQUESTER & TEAM LEADER: Unduh Form (when form_generated) --}}
    @if(($user->isRequester() || $user->isTeamLeader()) && $status === 'form_generated' && $ticket->po_path)
      <a href="{{ route('tickets.download-po', ['ticket' => $ticket->id, 'download' => 1]) }}" class="btn btn-orient">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Unduh Form Pengadaan
      </a>
    @endif

    {{-- DEPT HEAD: Decline / Approve --}}
    @if($user->isDepartmentHead() && $status === 'pending_dept_head')
      <button onclick="openModal('modal-decline')" class="btn btn-danger">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
        Tolak Pengajuan
      </button>
      <button onclick="openModal('modal-approve')" class="btn btn-success">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
        Setujui Pengajuan
      </button>
    @endif
  </div>
</div>
@endif

{{-- ═══════════════════ MODALS ═══════════════════ --}}

{{-- Modal: Review Documents (Team Leader) --}}
@if($user->isTeamLeader() && $status === 'pending_review')
<div class="modal-overlay" id="modal-review-documents">
  <div class="modal-card" style="max-width: 650px; max-height: 95vh; display: flex; flex-direction: column; overflow: hidden; padding: 24px;">
    <div class="modal-icon" style="background: var(--color-primary-soft);">
      <svg width="24" height="24" fill="none" stroke="var(--color-primary)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="modal-title">Tinjau Dokumen Pendukung</div>
    <div class="modal-body" style="text-align: left; margin-bottom: var(--space-sm);">
      Evaluasi setiap dokumen pendukung di bawah ini. Jika ada satu atau lebih dokumen yang ditolak, tiket akan dikembalikan ke Requester untuk revisi.
    </div>
    <form method="POST" action="{{ route('tickets.review', $ticket) }}" style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
      @csrf
      <div style="display: flex; flex-direction: column; gap: var(--space-md); margin: var(--space-sm) 0; text-align: left; flex: 1; overflow-y: auto; min-height: 0; padding-right: 8px;">
        @foreach($ticket->documents as $doc)
          <div style="background: var(--color-surface-soft); padding: var(--space-md); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-sm);">
              <span style="font-weight: 600; font-size: 14px; color: var(--color-text);">{{ $doc->description }}</span>
              <a href="{{ route('tickets.document', ['ticketDocument' => $doc->id]) }}" target="_blank" class="btn btn-ghost btn-sm" style="padding: 4px 8px; font-size: 11px;">Lihat PDF</a>
            </div>
            
            <div style="display: flex; gap: var(--space-md); margin-bottom: var(--space-sm);">
              <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                <input type="radio" name="document_status[{{ $doc->id }}]" value="accepted" checked onchange="toggleDocFeedback({{ $doc->id }}, false)">
                <span style="color: var(--color-success); font-weight: 600;">Setuju</span>
              </label>
              <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                <input type="radio" name="document_status[{{ $doc->id }}]" value="rejected" onchange="toggleDocFeedback({{ $doc->id }}, true)">
                <span style="color: var(--color-error); font-weight: 600;">Perlu Revisi</span>
              </label>
            </div>

            <div id="feedback-container-{{ $doc->id }}" style="display: none;">
              <input type="text" name="document_feedback[{{ $doc->id }}]" class="form-control" placeholder="Masukkan alasan penolakan/revisi..." style="font-size: 12px; padding: 6px 12px;">
            </div>
          </div>
        @endforeach
      </div>

      <div class="form-group" style="text-align: left; margin-top: var(--space-sm); margin-bottom: var(--space-md); flex-shrink: 0;">
        <label class="form-label">Catatan Tinjauan Global (opsional)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Tambahkan catatan untuk seluruh proses pemeriksaan ini..."></textarea>
      </div>

      <div class="form-group" style="text-align:left; margin-bottom:var(--space-md); flex-shrink: 0;">
        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:13px;">
          <input type="checkbox" name="digital_signature_consent" required style="margin-top:2px;">
          <span>Saya menyetujui syarat &amp; ketentuan dan menandatangani dokumen ini secara digital.</span>
        </label>
      </div>

      <div class="modal-footer" style="flex-shrink: 0; margin-top: auto;">
        <button type="button" onclick="closeModal('modal-review-documents')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Tinjauan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function toggleDocFeedback(docId, show) {
  const container = document.getElementById('feedback-container-' + docId);
  if (container) {
    container.style.display = show ? 'block' : 'none';
    const input = container.querySelector('input');
    if (input) {
      input.required = show;
    }
  }
}
</script>
@endpush
@endif


{{-- Modal: Smart Validation (Requester) --}}
@if($user->isRequester() && $status === 'need_to_validate')
<div class="modal-overlay" id="modal-validate">
  <div class="modal-card">
    <div class="modal-icon" style="background:var(--color-secondary-soft);">
      <svg width="24" height="24" fill="none" stroke="var(--color-secondary)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <div class="modal-title">Jalankan Smart Validation?</div>
    <div class="modal-body">
      Sistem akan menjalankan 4 Gate Validation:
      <ul style="margin:var(--space-sm) 0; padding-left:var(--space-lg);">
        <li>Gate 1: Deteksi duplikasi tiket aktif</li>
        <li>Gate 2: Validasi nominal harga</li>
        <li>Gate 3: Klasifikasi CAPEX / OPEX</li>
        <li>Gate 4: Penguncian anggaran</li>
      </ul>
      Proses tidak dapat dibatalkan setelah dimulai.
    </div>
    <form method="POST" action="{{ route('tickets.validate', $ticket) }}">
      @csrf
      <div class="form-group" style="text-align:left; margin-bottom:var(--space-md);">
        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:13px;">
          <input type="checkbox" name="digital_signature_consent" required style="margin-top:2px;">
          <span>Saya menyetujui syarat & ketentuan dan menandatangani dokumen ini secara digital.</span>
        </label>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-validate')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Jalankan Validasi</button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- Modal: Cancel Ticket (Requester) --}}
@if($user->isRequester() && $status === 'need_to_validate')
<div class="modal-overlay" id="modal-cancel-ticket">
  <div class="modal-card">
    <div class="modal-icon danger">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
    </div>
    <div class="modal-title">Batalkan Tiket Pengadaan?</div>
    <div class="modal-body">
      Apakah Anda yakin ingin membatalkan pengajuan ini? Status tiket akan diubah menjadi <strong>Ditolak (Declined)</strong> dan proses pengadaan akan dihentikan.
    </div>
    <form method="POST" action="{{ route('tickets.cancel', $ticket) }}">
      @csrf
      <div class="form-group" style="margin-top: 15px; text-align: left;">
        <label class="form-label">Alasan Pembatalan <span class="required">*</span></label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Wajib diisi — jelaskan alasan pembatalan..." required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-cancel-ticket')" class="btn btn-secondary">Kembali</button>
        <button type="submit" class="btn btn-danger">Ya, Batalkan Tiket</button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- Modal: Generate Form (Team Leader) --}}
@if($user->isTeamLeader() && $status === 'approved')
<div class="modal-overlay" id="modal-generate-form">
  <div class="modal-card">
    <div class="modal-icon success">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="modal-title">Generate Form Pengadaan?</div>
    <div class="modal-body">
      Sistem akan membuat dokumen Form Pengadaan resmi dalam format PDF. Aksi ini akan menyelesaikan proses pengadaan dan tidak dapat dibatalkan.
    </div>
    <form method="POST" action="{{ route('tickets.generate-form', $ticket) }}">
      @csrf
      <div class="form-group" style="text-align:left; margin-bottom:var(--space-md);">
        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:13px;">
          <input type="checkbox" name="digital_signature_consent" required style="margin-top:2px;">
          <span>Saya menyetujui syarat & ketentuan dan menandatangani dokumen ini secara digital.</span>
        </label>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-generate-form')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Generate Form</button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- Modal: Approve (Dept Head) --}}
@if($user->isDepartmentHead() && $status === 'pending_dept_head')
<div class="modal-overlay" id="modal-approve">
  <div class="modal-card">
    <div class="modal-icon success">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="modal-title">Setujui Pengajuan?</div>
    <div class="modal-body">
      Anda akan menyetujui pengajuan pengadaan senilai <strong>{{ $ticket->formatted_total_amount }}</strong>. Team Leader akan diberitahu untuk menerbitkan Form Pengadaan.
    </div>
    <form method="POST" action="{{ route('tickets.decide', $ticket) }}">
      @csrf
      <input type="hidden" name="action" value="approve">
      <div class="form-group">
        <label class="form-label">Catatan (opsional)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Tambahkan catatan persetujuan..."></textarea>
      </div>
      <div class="form-group" style="text-align:left; margin-bottom:var(--space-md); margin-top:var(--space-sm);">
        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:13px;">
          <input type="checkbox" name="digital_signature_consent" required style="margin-top:2px;">
          <span>Saya menyetujui syarat & ketentuan dan menandatangani dokumen ini secara digital.</span>
        </label>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-approve')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-success">Setujui</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal: Decline (Div Head) --}}
<div class="modal-overlay" id="modal-decline">
  <div class="modal-card">
    <div class="modal-icon danger">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
    </div>
    <div class="modal-title">Tolak Pengajuan?</div>
    <div class="modal-body">
      Pengajuan akan ditolak secara permanen dan anggaran yang terkunci akan dikembalikan. Berikan alasan yang jelas untuk audit trail.
    </div>
    <form method="POST" action="{{ route('tickets.decide', $ticket) }}">
      @csrf
      <input type="hidden" name="action" value="decline">
      <div class="form-group">
        <label class="form-label">Alasan Penolakan <span class="required">*</span></label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Wajib diisi — jelaskan alasan penolakan..." required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-decline')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- Modal: Gate 1 Duplicate Warning (Requester) --}}
@if(session('needs_duplicate_confirmation') && $user->isRequester() && $status === 'need_to_validate')
<div class="modal-overlay open" id="modal-duplicate-warning">
  <div class="modal-card">
    <div class="modal-icon warning">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    </div>
    <div class="modal-title">Tiket Serupa Terdeteksi</div>
    <div class="modal-body">
      {{ session('duplicate_warning') }}
      <p style="margin-top:12px;color:var(--color-muted);font-size:13px;">Anda dapat membatalkan atau melanjutkan dengan memberikan justifikasi.</p>
    </div>
    <div class="modal-footer">
      <button type="button" onclick="closeModal('modal-duplicate-warning')" class="btn btn-secondary">Batalkan</button>
      <form method="POST" action="{{ route('tickets.validate', $ticket) }}" style="display:inline;margin:0;">
        @csrf
        <input type="hidden" name="duplicate_confirmed" value="1">
        <button type="submit" class="btn btn-primary">Tetap Lanjutkan</button>
      </form>
    </div>
  </div>
</div>
@endif

{{-- Modal: Gate 2 Nominal Warning (Requester) --}}
@if(session('needs_nominal_confirmation') && $user->isRequester() && $status === 'need_to_validate')
<div class="modal-overlay open" id="modal-nominal-warning">
  <div class="modal-card">
    <div class="modal-icon warning">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    </div>
    <div class="modal-title">Nominal Tidak Wajar</div>
    <div class="modal-body">
      {{ session('nominal_warning') }}
      <p style="margin-top:12px;color:var(--color-muted);font-size:13px;">Jika nominal sudah benar, klik &quot;Tetap Lanjutkan&quot; untuk melanjutkan validasi.</p>
    </div>
    <div class="modal-footer">
      <button type="button" onclick="closeModal('modal-nominal-warning')" class="btn btn-secondary">Batalkan</button>
      <form method="POST" action="{{ route('tickets.validate', $ticket) }}" style="display:inline;margin:0;">
        @csrf
        <input type="hidden" name="nominal_confirmed" value="1">
        <button type="submit" class="btn btn-primary">Tetap Lanjutkan</button>
      </form>
    </div>
  </div>
</div>
@endif

{{-- Modal: Cross-fund (Requester) --}}
@if(session('over_budget'))
<div class="modal-overlay open" id="modal-cross-fund">
  <div class="modal-card">
    <div class="modal-icon warning">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    </div>
    <div class="modal-title">Saldo Anggaran Tidak Mencukupi</div>
    <div class="modal-body">
      Saldo anggaran {{ $ticket->expenditure_type }} untuk kategori ini tidak mencukupi nominal pengajuan. Anda dapat mengajukan <strong>Silang Dana</strong> untuk menggunakan saldo anggaran kategori lain sebagai penopang.
    </div>
    <div class="modal-footer" style="display: flex; gap: var(--space-sm); justify-content: flex-end; margin-top: var(--space-md);">
      <button type="button" onclick="closeModal('modal-cross-fund')" class="btn btn-secondary">Tutup</button>
      
      <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" style="display: inline; margin: 0;">
        @csrf
        <input type="hidden" name="notes" value="Dibatalkan oleh Requester - Tidak mengajukan Silang Dana setelah saldo tidak mencukupi.">
        <button type="submit" class="btn btn-danger">Batalkan Tiket</button>
      </form>

      <form method="POST" action="{{ route('tickets.cross-fund', $ticket) }}" style="display: inline; margin: 0;">
        @csrf
        <button type="submit" class="btn btn-primary">Ajukan Silang Dana</button>
      </form>
    </div>
  </div>
</div>
@endif

@push('scripts')
<script>
// Re-initialize modals after DOM load (for dynamically injected ones)
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});
</script>
@endpush
@endsection
