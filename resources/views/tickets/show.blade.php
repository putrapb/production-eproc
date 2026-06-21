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
  <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali
  </a>
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
              <div class="detail-field-label">Nama Item</div>
              <div class="detail-field-value">{{ $ticket->item_name }}</div>
            </div>
            <div class="detail-field" style="grid-column:1/-1;">
              <div class="detail-field-label">Deskripsi</div>
              <div class="detail-field-value">{{ $ticket->description ?? '-' }}</div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">PIC (Person In Charge)</div>
              <div class="detail-field-value">{{ $ticket->pic_name ?? '-' }}</div>
            </div>

            <div class="detail-field">
              <div class="detail-field-label">Kategori</div>
              <div class="detail-field-value">
                <span class="badge badge-category">{{ config('eprocurement.categories.'.$ticket->category, $ticket->category) }}</span>
              </div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Jumlah Unit</div>
              <div class="detail-field-value">{{ number_format($ticket->quantity) }} unit</div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Vendor</div>
              <div class="detail-field-value">{{ $ticket->vendor_name }}</div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Nominal Harga (Satuan)</div>
              <div class="detail-field-value">{{ $ticket->formatted_amount }}</div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Total Harga</div>
              <div class="detail-field-value large">{{ $ticket->formatted_total_amount }}</div>
            </div>
          </div>
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
          <div class="heading-sm">Dokumen</div>
        </div>
        <div class="card-body">
          <div class="detail-field">
            <div class="detail-field-label">Dokumen Pendukung</div>
            <div class="detail-field-value">
              @if($ticket->document_path)
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--space-md);">
                  <a href="{{ route('tickets.document', ['ticket' => $ticket->id]) }}" target="_blank" style="text-decoration:none; display:flex; align-items:center; gap:var(--space-sm); padding:var(--space-sm) var(--space-md); border:1px solid var(--color-hairline); border-radius:var(--radius-md); transition:background 0.2s;">
                    <div style="width:36px; height:36px; background:var(--color-error-soft); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; color:var(--color-error);">
                      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                      <div class="label-md text-ink">Dokumen Pendukung — Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</div>
                      <div class="caption text-muted">Klik untuk melihat file (PDF) · Diunggah {{ $ticket->created_at->format('d M Y') }}</div>
                    </div>
                  </a>
                  <a href="{{ route('tickets.document', ['ticket' => $ticket->id, 'download' => 1]) }}" class="btn btn-ghost btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    Download
                  </a>
                </div>
              @else
                <span class="text-muted">Belum ada dokumen</span>
              @endif
            </div>
          </div>

          {{-- PO Document --}}
          @if($ticket->po_path)
          <hr class="divider" style="margin:var(--space-md) 0;">
          <div class="detail-field">
            <div class="detail-field-label">Purchase Order</div>
            <div class="detail-field-value">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--space-md);">
                  <a href="{{ route('tickets.download-po', ['ticket' => $ticket->id]) }}" target="_blank" style="text-decoration:none; display:flex; align-items:center; gap:var(--space-sm); padding:var(--space-sm) var(--space-md); border:1px solid var(--color-hairline); border-radius:var(--radius-md); transition:background 0.2s;">
                    <div style="width:36px; height:36px; background:var(--color-success-soft); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; color:var(--color-success-text);">
                      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 12l2 2 4-4"/></svg>
                    </div>
                    <div>
                      <div class="label-md text-ink">Purchase Order — PO-{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}</div>
                      <div class="caption text-muted">Klik untuk melihat file (PDF) · Diterbitkan {{ $ticket->po_generated_at?->format('d M Y') }}</div>
                    </div>
                  </a>
                  {{-- Download hanya untuk Requester dan PFA --}}
                  @if($user->isRequester() || $user->isPfa())
                  <a href="{{ route('tickets.download-po', ['ticket' => $ticket->id, 'download' => 1]) }}" class="btn btn-ghost btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Unduh PO
                  </a>
                  @endif
                </div>
            </div>
          </div>
          @endif
        </div>
      </div>

      {{-- Metadata --}}
      <div class="card">
        <div class="card-body">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-sm);">
            <div class="detail-field">
              <div class="detail-field-label">Dibuat Oleh</div>
              <div class="detail-field-value">{{ $ticket->user->name }}</div>
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
                      {{ $log->user->name }}
                      <span class="approval-role-tag">({{ $log->user->role_label }})</span>
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
  ($user->isRequester() && in_array($status, ['need_to_validate', 'revision', 'po_generated'])) ||
  ($user->isPfa() && in_array($status, ['pending_review', 'approved', 'po_generated'])) ||
  ($user->isDepartmentHead() && $status === 'pending_dept_head') ||
  ($user->isDivisionHead() && $status === 'pending_div_head')
)
<div class="action-panel">
  <div class="action-panel-info">
    @if($user->isRequester() && $status === 'revision')
      Dokumen Anda memerlukan revisi. Unggah ulang dokumen untuk melanjutkan proses.
    @elseif($user->isRequester() && $status === 'need_to_validate')
      Dokumen Pendukung Anda telah diterima oleh PFA. Jalankan Smart Validation untuk mengklasifikasikan anggaran dan mengunci budget.
    @elseif($user->isPfa() && $status === 'pending_review')
      Tinjau Dokumen Pendukung. Terima jika valid, atau minta revisi.
    @elseif($user->isPfa() && $status === 'approved')
      Tiket disetujui oleh Division Head. Generate Purchase Order sekarang.
    @elseif($user->isDepartmentHead() && $status === 'pending_dept_head')
      Tinjau dan teruskan ke Division Head jika pengajuan valid.
    @elseif($user->isDivisionHead() && $status === 'pending_div_head')
      Berikan keputusan final: setujui atau tolak pengajuan pengadaan ini.
    @elseif($status === 'po_generated' && $user->isRequester())
      Purchase Order telah diterbitkan oleh PFA. Anda dapat mengunduh dokumen PO.
    @elseif($status === 'po_generated' && $user->isPfa())
      Purchase Order telah berhasil diterbitkan. Anda dapat mengunduh dokumen PO.
    @endif
  </div>

  <div class="action-panel-buttons">
    {{-- REQUESTER: Revision re-upload --}}
    @if($user->isRequester() && $status === 'revision')
      <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Upload Ulang Dokumen
      </a>
    @endif

    {{-- PFA: Document review --}}
    @if($user->isPfa() && $status === 'pending_review')
      <button onclick="openModal('modal-reject')" class="btn btn-danger">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
        Minta Revisi
      </button>
      <button onclick="openModal('modal-accept')" class="btn btn-orient">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
        Terima Dokumen
      </button>
    @endif

    {{-- REQUESTER: Smart Validation --}}
    @if($user->isRequester() && $status === 'need_to_validate')
      <button onclick="openModal('modal-validate')" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Jalankan Smart Validation
      </button>
      <button onclick="openModal('modal-cancel-ticket')" class="btn btn-danger">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Batalkan Tiket
      </button>
    @endif


    {{-- PFA: Generate PO --}}
    @if($user->isPfa() && $status === 'approved')
      <button onclick="openModal('modal-generate-po')" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 12l2 2 4-4"/></svg>
        Generate Purchase Order
      </button>
    @endif

    {{-- REQUESTER & PFA: Unduh PO (when po_generated) --}}
    @if(($user->isRequester() || $user->isPfa()) && $status === 'po_generated' && $ticket->po_path)
      <a href="{{ route('tickets.download-po', ['ticket' => $ticket->id, 'download' => 1]) }}" class="btn btn-orient">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Unduh Purchase Order
      </a>
    @endif

    {{-- DEPT HEAD: Forward --}}
    @if($user->isDepartmentHead() && $status === 'pending_dept_head')
      <button onclick="openModal('modal-forward')" class="btn btn-orient">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
        Teruskan ke Division Head
      </button>
    @endif

    {{-- DIV HEAD: Decline / Approve --}}
    @if($user->isDivisionHead() && $status === 'pending_div_head')
      <button onclick="openModal('modal-decline')" class="btn btn-danger">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
        Tolak Pengajuan
      </button>
      <button onclick="openModal('modal-approve')" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
        Setujui Pengajuan
      </button>
    @endif
  </div>
</div>
@endif

{{-- ═══════════════════ MODALS ═══════════════════ --}}

{{-- Modal: Accept Document (PFA) --}}
@if($user->isPfa() && $status === 'pending_review')
<div class="modal-overlay" id="modal-accept">
  <div class="modal-card">
    <div class="modal-icon success">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="modal-title">Terima Dokumen Pendukung?</div>
    <div class="modal-body">
      Anda akan menerima Dokumen Pendukung tiket ini dan meneruskan ke tahap Smart Validation.
    </div>
    <form method="POST" action="{{ route('tickets.review', $ticket) }}">
      @csrf
      <input type="hidden" name="action" value="accept">
      <div class="form-group">
        <label class="form-label">Catatan (opsional)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Tambahkan catatan..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-accept')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-orient">Terima Dokumen</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal: Reject Document (PFA) --}}
<div class="modal-overlay" id="modal-reject">
  <div class="modal-card">
    <div class="modal-icon danger">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
    </div>
    <div class="modal-title">Minta Revisi?</div>
    <div class="modal-body">
      Tiket akan dikembalikan ke requester untuk perbaikan dokumen. Berikan alasan yang jelas.
    </div>
    <form method="POST" action="{{ route('tickets.review', $ticket) }}">
      @csrf
      <input type="hidden" name="action" value="reject">
      <div class="form-group">
        <label class="form-label">Alasan Revisi <span class="required">*</span></label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Jelaskan dokumen yang perlu diperbaiki..." required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-reject')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-danger">Minta Revisi</button>
      </div>
    </form>
  </div>
</div>
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

{{-- Modal: Generate PO (PFA) --}}
@if($user->isPfa() && $status === 'approved')
<div class="modal-overlay" id="modal-generate-po">
  <div class="modal-card">
    <div class="modal-icon success">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="modal-title">Generate Purchase Order?</div>
    <div class="modal-body">
      Sistem akan membuat dokumen Purchase Order resmi dalam format PDF. Aksi ini akan menyelesaikan proses pengadaan dan tidak dapat dibatalkan.
    </div>
    <form method="POST" action="{{ route('tickets.generate-po', $ticket) }}">
      @csrf
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-generate-po')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Generate PO</button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- Modal: Forward (Dept Head) --}}
@if($user->isDepartmentHead() && $status === 'pending_dept_head')
<div class="modal-overlay" id="modal-forward">
  <div class="modal-card">
    <div class="modal-icon" style="background:var(--color-secondary-soft);">
      <svg width="24" height="24" fill="none" stroke="var(--color-secondary)" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
    </div>
    <div class="modal-title">Teruskan ke Division Head?</div>
    <div class="modal-body">
      Pengajuan akan dikirimkan ke Division Head untuk keputusan final. Pastikan Anda telah memeriksa semua detail pengadaan.
    </div>
    <form method="POST" action="{{ route('tickets.forward', $ticket) }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Catatan (opsional)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Tambahkan catatan untuk Division Head..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-forward')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-orient">Teruskan</button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- Modal: Approve (Div Head) --}}
@if($user->isDivisionHead() && $status === 'pending_div_head')
<div class="modal-overlay" id="modal-approve">
  <div class="modal-card">
    <div class="modal-icon success">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="modal-title">Setujui Pengajuan?</div>
    <div class="modal-body">
      Anda akan menyetujui pengajuan pengadaan senilai <strong>{{ $ticket->formatted_amount }}</strong>. PFA akan diberitahu untuk menerbitkan Purchase Order.
    </div>
    <form method="POST" action="{{ route('tickets.decide', $ticket) }}">
      @csrf
      <input type="hidden" name="action" value="approve">
      <div class="form-group">
        <label class="form-label">Catatan (opsional)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Tambahkan catatan persetujuan..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-approve')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Setujui</button>
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
