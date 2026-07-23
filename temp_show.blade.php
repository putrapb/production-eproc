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
        <span class="badge badge-cross-fund" style="display:inline-flex; align-items:center; gap:4px;">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
          Silang Dana
        </span>
      @endif
      @if($ticket->expenditure_type)
        <span class="badge badge-{{ strtolower($ticket->expenditure_type) }}">{{ $ticket->expenditure_type }}</span>
      @endif
    </div>
  </div>
  <div style="display:flex; gap:var(--space-sm);">
    @if(auth()->user()->isRequester() && in_array($ticket->status, [\App\Models\Ticket::STATUS_REVISION, \App\Models\Ticket::STATUS_PENDING_REVIEW]))
      <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
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

    {{-- ─── LEFT: TICKET DETAIL ─── --}}
    <div>

      {{-- Informasi Pengadaan --}}
      <div class="card mb-lg">
        <div class="card-header">
          <div class="heading-sm">Informasi Pengadaan</div>
        </div>
        <div class="card-body">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md);">
            <div class="detail-field" style="grid-column:1/-1;">
              <div class="detail-field-label">Judul Pengajuan</div>
              <div class="detail-field-value">{{ $ticket->title }}</div>
            </div>
            <div class="detail-field" style="grid-column:1/-1;">
              <div class="detail-field-label">Deskripsi</div>
              <div class="detail-field-value">{{ $ticket->description ?? '-' }}</div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">PIC (Person In Charge)</div>
              <div class="detail-field-value">
                @if(is_array($ticket->pic_name) && count($ticket->pic_name) > 0)
                  <ul style="margin:0; padding-left:16px;">
                    @foreach($ticket->pic_name as $pic)
                      <li>{{ is_array($pic) ? json_encode($pic) : $pic }}</li>
                    @endforeach
                  </ul>
                @elseif(is_string($ticket->pic_name) && !empty($ticket->pic_name))
                  {{ $ticket->pic_name }}
                @else
                  -
                @endif
              </div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Kategori</div>
              <div class="detail-field-value">
                <span class="badge badge-category">{{ config('eprocurement.categories.'.$ticket->category, $ticket->category) }}</span>
              </div>
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
      @if($ticket->expenditure_type)
      @php
        $capexTotal = $ticket->items->filter(fn($i) => $i->effective_expenditure_type === 'CAPEX')->sum('subtotal');
        $opexTotal = $ticket->items->filter(fn($i) => $i->effective_expenditure_type === 'OPEX')->sum('subtotal');
      @endphp
      <div class="card mb-lg">
        <div class="card-header">
          <div class="heading-sm">Klasifikasi Anggaran</div>
        </div>
        <div class="card-body">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md); margin-bottom:var(--space-md);">
            <div class="detail-field">
              <div class="detail-field-label">Kategori Induk (Tiket)</div>
              <div class="detail-field-value">
                <span class="badge badge-category" style="background:var(--color-surface-soft); color:var(--color-text); border:1px solid var(--color-border);">{{ strtoupper(str_replace('_', ' ', $ticket->category)) }}</span>
              </div>
            </div>
            <div class="detail-field">
              <div class="detail-field-label">Silang Dana</div>
              <div class="detail-field-value">
                @if($ticket->is_cross_fund)
                  <span class="badge badge-cross-fund" style="display:inline-flex; align-items:center; gap:4px;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                    Aktif
                  </span>
                @else
                  <span class="text-muted">Tidak</span>
                @endif
              </div>
            </div>
          </div>

          <div style="padding:16px; background:var(--color-surface-soft); border-radius:var(--radius-md); border:1px solid var(--color-hairline);">
            <div style="font-size:12px; font-weight:600; color:var(--color-muted); margin-bottom:12px; text-transform:uppercase; letter-spacing:0.5px;">Estimasi Pemotongan Anggaran</div>
            
            @if($capexTotal > 0)
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:{{ $opexTotal > 0 ? '8px' : '0' }};">
              <div style="display:flex; align-items:center; gap:8px;">
                <span class="badge badge-capex">CAPEX</span>
                <span style="font-size:13px; color:var(--color-text);">Pos Anggaran: {{ strtoupper(str_replace('_', ' ', $ticket->category)) }}</span>
              </div>
              <div style="font-size:14px; font-weight:600; color:var(--color-text);">Rp {{ number_format($capexTotal, 0, ',', '.') }}</div>
            </div>
            @endif

            @if($opexTotal > 0)
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <div style="display:flex; align-items:center; gap:8px;">
                <span class="badge badge-opex">OPEX</span>
                <span style="font-size:13px; color:var(--color-text);">Pos Anggaran: {{ strtoupper(str_replace('_', ' ', $ticket->category)) }}</span>
              </div>
              <div style="font-size:14px; font-weight:600; color:var(--color-text);">Rp {{ number_format($opexTotal, 0, ',', '.') }}</div>
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
          <div style="display:flex; flex-direction:column; gap:var(--space-md);">
            @forelse($ticket->documents as $doc)
              <div style="display:flex; align-items:center; justify-content:space-between; padding:var(--space-sm) var(--space-md); border:1px solid var(--color-hairline); border-radius:var(--radius-md); background:var(--color-surface-card);">
                <div style="display:flex; align-items:center; gap:var(--space-sm);">
                  <div style="width:36px; height:36px; background:var(--color-error-soft); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; color:var(--color-error); flex-shrink:0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  </div>
                  <div>
                    <div class="label-md text-ink">{{ $doc->description }}</div>
                    <div class="caption text-muted" style="margin-top:2px;">
                      Status:
                      @if($doc->isAccepted())
                        <span style="color:var(--color-success); font-weight:600;">{{ $doc->status_label }}</span>
                      @elseif($doc->isRejected())
                        <span style="color:var(--color-error); font-weight:600;">{{ $doc->status_label }}</span>
                        @if($doc->feedback)
                          <br><span style="font-style:italic;">Catatan revisi: "{{ $doc->feedback }}"</span>
                        @endif
                      @else
                        <span style="color:var(--color-info); font-weight:600;">{{ $doc->status_label }}</span>
                      @endif
                    </div>
                  </div>
                </div>
                <div style="display:flex; gap:var(--space-xs);">
                  <a href="{{ route('tickets.document', ['ticketDocument' => $doc->id]) }}" target="_blank" class="btn btn-ghost btn-sm">Lihat PDF</a>
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
      <div class="card mb-lg">
        <div class="card-body">
          <div class="detail-field-label" style="margin-bottom:var(--space-sm);">Form Pengadaan</div>
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <a href="{{ route('tickets.download-po', ['ticket' => $ticket->id]) }}" target="_blank" style="text-decoration:none; display:flex; align-items:center; gap:var(--space-sm); padding:var(--space-sm) var(--space-md); border:1px solid var(--color-hairline); border-radius:var(--radius-md); transition:background 0.2s;">
              <div style="width:36px; height:36px; background:var(--color-success-soft); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; color:var(--color-success-text);">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 12l2 2 4-4"/></svg>
              </div>
              <div>
                <div class="label-md text-ink">Form Pengadaan — FORM-{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="caption text-muted">Klik untuk melihat · Diterbitkan {{ $ticket->po_generated_at?->format('d M Y') }}</div>
              </div>
            </a>
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
              <div class="detail-field-value">{{ $ticket->user?->name ?? 'Karyawan Terhapus' }}</div>
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

    {{-- ─── RIGHT: APPROVAL LOG ─── --}}
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
              <div style="margin-bottom:var(--space-sm); display:flex; justify-content:center;">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--color-muted);"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
              </div>
              <p class="text-muted">Belum ada riwayat aksi.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

{{-- ─── ACTION PANEL ─── --}}
@php $user = auth()->user(); $status = $ticket->status; @endphp

@if(
  ($user->isRequester()     && in_array($status, ['revision', 'form_generated'])) ||
  ($user->isTeamLeader()    && in_array($status, ['pending_review', 'approved', 'form_generated'])) ||
  ($user->isDepartmentHead() && $status === 'pending_dept_head')
)
<div class="action-panel">
  <div class="action-panel-info">
    @if($user->isRequester() && $status === 'revision')
      Dokumen Anda memerlukan revisi. Unggah ulang dokumen untuk melanjutkan proses.
    @elseif($user->isTeamLeader() && $status === 'pending_review')
      Tinjau Dokumen Pendukung. Terima jika valid, atau minta revisi dokumen.
    @elseif($user->isTeamLeader() && $status === 'approved')
      Tiket disetujui oleh Department Head. Generate Form Pengadaan sekarang.
    @elseif($user->isDepartmentHead() && $status === 'pending_dept_head')
      Berikan keputusan final: setujui atau tolak pengajuan pengadaan ini.
    @elseif($status === 'form_generated' && $user->isRequester())
      Form Pengadaan telah diterbitkan. Anda dapat mengunduh dokumen.
    @elseif($status === 'form_generated' && $user->isTeamLeader())
      Form Pengadaan telah berhasil diterbitkan.
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

    {{-- TEAM LEADER: Document review --}}
    @if($user->isTeamLeader() && $status === 'pending_review')
      <button onclick="openModal('modal-review-documents')" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
        Tinjau Dokumen Pendukung
      </button>
    @endif

    {{-- TEAM LEADER: Generate Form --}}
    @if($user->isTeamLeader() && $status === 'approved')
      <button onclick="openModal('modal-generate-form')" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 12l2 2 4-4"/></svg>
        Generate Form Pengadaan
      </button>
    @endif

    {{-- REQUESTER & TEAM LEADER: Unduh Form --}}
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

{{-- ═══ MODALS ═══ --}}

{{-- Modal: Review Documents (Team Leader) --}}
@if($user->isTeamLeader() && $status === 'pending_review')
<div class="modal-overlay" id="modal-review-documents">
  <div class="modal-card" style="max-width:650px; max-height:95vh; display:flex; flex-direction:column; overflow:hidden; padding:24px;">
    <div class="modal-icon" style="background:var(--color-primary-soft);">
      <svg width="24" height="24" fill="none" stroke="var(--color-primary)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="modal-title">Tinjau Dokumen Pendukung</div>
    <div class="modal-body" style="text-align:left; margin-bottom:var(--space-sm);">
      Evaluasi setiap dokumen. Jika ada yang ditolak, tiket dikembalikan ke Requester untuk revisi.
    </div>
    <form method="POST" action="{{ route('tickets.review', $ticket) }}" style="display:flex; flex-direction:column; flex:1; min-height:0;">
      @csrf
      <div style="display:flex; flex-direction:column; gap:var(--space-md); margin:var(--space-sm) 0; text-align:left; flex:1; overflow-y:auto; min-height:0; padding-right:8px;">
        @foreach($ticket->documents as $doc)
          <div style="background:var(--color-surface-soft); padding:var(--space-md); border-radius:var(--radius-md); border:1px solid var(--color-border);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-sm);">
              <span style="font-weight:600; font-size:14px; color:var(--color-text);">{{ $doc->description }}</span>
              <a href="{{ route('tickets.document', ['ticketDocument' => $doc->id]) }}" target="_blank" class="btn btn-ghost btn-sm" style="padding:4px 8px; font-size:11px;">Lihat PDF</a>
            </div>
            <div style="display:flex; gap:var(--space-md); margin-bottom:var(--space-sm);">
              <label style="display:inline-flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                <input type="radio" name="document_status[{{ $doc->id }}]" value="accepted" checked onchange="toggleDocFeedback({{ $doc->id }}, false)">
                <span style="color:var(--color-success); font-weight:600;">Setuju</span>
              </label>
              <label style="display:inline-flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                <input type="radio" name="document_status[{{ $doc->id }}]" value="rejected" onchange="toggleDocFeedback({{ $doc->id }}, true)">
                <span style="color:var(--color-error); font-weight:600;">Perlu Revisi</span>
              </label>
            </div>
            <div id="feedback-container-{{ $doc->id }}" style="display:none;">
              <input type="text" name="document_feedback[{{ $doc->id }}]" class="form-control" placeholder="Masukkan alasan penolakan/revisi..." style="font-size:12px; padding:6px 12px;">
            </div>
          </div>
        @endforeach
      </div>
      <div class="form-group" style="text-align:left; margin-top:var(--space-sm); margin-bottom:var(--space-md); flex-shrink:0;">
        <label class="form-label">Catatan Tinjauan Global (opsional)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Tambahkan catatan untuk seluruh proses pemeriksaan..."></textarea>
      </div>
      <div class="modal-footer" style="flex-shrink:0; margin-top:16px; padding:16px 24px; border-top:1px solid var(--color-border); flex-direction:column; gap:12px;">
        <div style="text-align:left; width:100%;">
          <label style="display:inline-flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:13px; color:var(--color-text);">
            <input type="checkbox" name="digital_signature_consent" id="ds-consent-review" required style="margin-top:3px;" onchange="document.getElementById('btn-review-submit').disabled = !this.checked">
            <span>Saya menyetujui syarat &amp; ketentuan dan menandatangani dokumen ini secara digital.</span>
          </label>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px; width:100%;">
          <button type="button" onclick="closeModal('modal-review-documents')" class="btn btn-secondary">Batal</button>
          <button type="submit" class="btn btn-primary" id="btn-review-submit" disabled>Simpan Tinjauan</button>
        </div>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function toggleDocFeedback(docId, show) {
  const c = document.getElementById('feedback-container-' + docId);
  if (c) { c.style.display = show ? 'block' : 'none'; const i = c.querySelector('input'); if (i) i.required = show; }
}
</script>
@endpush
@endif

{{-- Modal: Generate Form (Team Leader) --}}
@if($user->isTeamLeader() && $status === 'approved')
<div class="modal-overlay" id="modal-generate-form">
  <div class="modal-card">
    <div class="modal-icon success">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="modal-title">Generate Form Pengadaan?</div>
    <div class="modal-body">Sistem akan membuat dokumen Form Pengadaan resmi dalam format PDF. Aksi ini tidak dapat dibatalkan.</div>
    <form method="POST" action="{{ route('tickets.generate-form', $ticket) }}">
      @csrf
      <div class="form-group" style="text-align:left; margin-bottom:var(--space-md);">
        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:13px;">
          <input type="checkbox" name="digital_signature_consent" required style="margin-top:2px;">
          <span>Saya menyetujui syarat &amp; ketentuan dan menandatangani dokumen ini secara digital.</span>
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
      Anda akan menyetujui pengajuan senilai <strong>{{ $ticket->formatted_total_amount }}</strong>. Team Leader akan diberitahu untuk menerbitkan Form Pengadaan.
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
          <span>Saya menyetujui syarat &amp; ketentuan dan menandatangani dokumen ini secara digital.</span>
        </label>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modal-approve')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-success">Setujui</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal: Decline (Dept Head) --}}
<div class="modal-overlay" id="modal-decline">
  <div class="modal-card">
    <div class="modal-icon danger">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
    </div>
    <div class="modal-title">Tolak Pengajuan?</div>
    <div class="modal-body">Pengajuan akan ditolak dan anggaran yang terkunci dikembalikan. Berikan alasan yang jelas untuk audit trail.</div>
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

{{-- Modal: Gate 1 — Duplicate Warning --}}
@if(session('needs_duplicate_confirmation') && $user->isRequester())
<div class="modal-overlay open" id="modal-duplicate-warning">
  <div class="modal-card">
    <div class="modal-icon warning">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    </div>
    <div class="modal-title">Tiket Serupa Terdeteksi (Gate 1)</div>
    <div class="modal-body">
      {{ session('duplicate_warning') }}
      <p style="margin-top:12px; color:var(--color-muted); font-size:13px;">Anda dapat melanjutkan atau membatalkan validasi.</p>
    </div>
    <div class="modal-footer">
      <button type="button" onclick="closeModal('modal-duplicate-warning')" class="btn btn-secondary">Batalkan</button>
      <form method="POST" action="{{ route('tickets.validate', $ticket) }}" style="display:inline; margin:0;">
        @csrf
        <input type="hidden" name="duplicate_confirmed" value="1">
        <button type="submit" class="btn btn-primary">Tetap Lanjutkan</button>
      </form>
    </div>
  </div>
</div>
@endif

{{-- Modal: Gate 2 — Nominal Warning --}}
@if(session('needs_nominal_confirmation') && $user->isRequester())
<div class="modal-overlay open" id="modal-nominal-warning">
  <div class="modal-card">
    <div class="modal-icon warning">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    </div>
    <div class="modal-title">Nominal Tidak Wajar (Gate 2)</div>
    <div class="modal-body">
      {{ session('nominal_warning') }}
      <p style="margin-top:12px; color:var(--color-muted); font-size:13px;">Jika nominal sudah benar, klik "Tetap Lanjutkan".</p>
    </div>
    <div class="modal-footer">
      <button type="button" onclick="closeModal('modal-nominal-warning')" class="btn btn-secondary">Batalkan</button>
      <form method="POST" action="{{ route('tickets.validate', $ticket) }}" style="display:inline; margin:0;">
        @csrf
        <input type="hidden" name="nominal_confirmed" value="1">
        <button type="submit" class="btn btn-primary">Tetap Lanjutkan</button>
      </form>
    </div>
  </div>
</div>
@endif

{{-- Modal: Gate 3 — Classification Mismatch --}}
@if(session('needs_classification_confirmation') && $user->isRequester())
<div class="modal-overlay open" id="modal-classification-warning">
  <div class="modal-card">
    <div class="modal-icon warning">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    </div>
    <div class="modal-title">Perbedaan Klasifikasi CAPEX/OPEX (Gate 3)</div>
    <div class="modal-body">
      {{ session('classification_warning') }}
      <p style="margin-top:12px; color:var(--color-muted); font-size:13px;">Pilih "Gunakan Saran Sistem" untuk mengubah ke saran sistem, atau "Pertahankan Pilihan Saya" untuk melanjutkan dengan pilihan Anda.</p>
    </div>
    <div class="modal-footer">
      <form method="POST" action="{{ route('tickets.validate', $ticket) }}" style="display:inline; margin:0;">
        @csrf
        <input type="hidden" name="classification_confirmed" value="0">
        <button type="submit" class="btn btn-secondary">Gunakan Saran Sistem</button>
      </form>
      <form method="POST" action="{{ route('tickets.validate', $ticket) }}" style="display:inline; margin:0;">
        @csrf
        <input type="hidden" name="classification_confirmed" value="1">
        <button type="submit" class="btn btn-primary">Pertahankan Pilihan Saya</button>
      </form>
    </div>
  </div>
</div>
@endif

{{-- Modal: Gate 4 — Over Budget --}}
@if(session('over_budget'))
<div class="modal-overlay open" id="modal-over-budget">
  <div class="modal-card">
    <div class="modal-icon warning">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    </div>
    <div class="modal-title">Saldo Anggaran Tidak Mencukupi (Gate 4)</div>
    <div class="modal-body">
      Saldo anggaran <strong>{{ $ticket->expenditure_type }}</strong> tidak mencukupi untuk nominal pengajuan ini. Hubungi Team Leader untuk koordinasi lebih lanjut.
    </div>
    <div class="modal-footer">
      <button type="button" onclick="closeModal('modal-over-budget')" class="btn btn-secondary">Tutup</button>
      <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" style="display:inline; margin:0;">
        @csrf
        <input type="hidden" name="notes" value="Dibatalkan oleh Requester — saldo anggaran tidak mencukupi.">
        <button type="submit" class="btn btn-danger">Batalkan Tiket</button>
      </form>
    </div>
  </div>
</div>
@endif

@push('scripts')
<script>
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});
</script>
@endpush
@endsection
