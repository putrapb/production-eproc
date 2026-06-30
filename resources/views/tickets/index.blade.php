@extends('layouts.app')

@section('title', 'Tiket Pengadaan')

@section('breadcrumb')
  <span class="breadcrumb-active">Tiket Pengadaan</span>
@endsection

@section('content')
<div class="page-header">
  <div class="page-header-left">
    <h1>Tiket Pengadaan</h1>
    <p>Kelola semua pengajuan pengadaan</p>
  </div>
  @if(auth()->user()->isRequester())
    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      Pengajuan Baru
    </a>
  @endif
</div>

{{-- Filter + Search + Per-page --}}
<div class="filter-row">
  <div class="filter-tabs">
    <a href="{{ route('tickets.index') }}"
       class="filter-tab {{ !request('status') ? 'active' : '' }}">
      Semua
    </a>
    @foreach([
      'pending_review'    => 'Cek Dokumen',
      'revision'          => 'Revisi',
      'need_to_validate'  => 'Validasi',
      'pending_dept_head' => 'Dept Head',
      'approved'          => 'Disetujui',
      'declined'          => 'Ditolak',
      'form_generated'    => 'Form Terbit',
    ] as $val => $label)
      <a href="{{ route('tickets.index', ['status' => $val, 'per_page' => $perPage]) }}"
         class="filter-tab {{ request('status') === $val ? 'active' : '' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>

  <div style="display:flex; align-items:center; gap:var(--space-sm);">
    {{-- Per-page selector --}}
    <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--color-muted);">
      <span>Tampilkan:</span>
      @foreach([10, 25, 50, 100] as $size)
        <a href="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
           style="padding:3px 10px; border-radius:var(--radius-sm); font-size:12px; font-weight:600; text-decoration:none;
                  background:{{ $perPage == $size ? 'var(--color-primary)' : 'var(--color-surface-card)' }};
                  color:{{ $perPage == $size ? '#fff' : 'var(--color-text)' }};
                  border:1px solid {{ $perPage == $size ? 'var(--color-primary)' : 'var(--color-border)' }};">
          {{ $size }}
        </a>
      @endforeach
    </div>

    {{-- Search --}}
    <div class="search-input-wrap">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input
        type="search"
        class="search-input"
        placeholder="Cari tiket..."
        value="{{ request('search') }}"
        id="search-input"
        onkeydown="if(event.key==='Enter') applySearch()"
      >
    </div>
  </div>
</div>

{{-- Bulk Action Bar (shown when checkboxes are selected) --}}
@php
  $isBulkRole = auth()->user()->isTeamLeader() || auth()->user()->isDepartmentHead();
  // TL: bulk review documents from pending_review queue
  // DH: bulk decide from pending_dept_head queue
  $bulkStatus = auth()->user()->isTeamLeader() ? 'pending_review' : 'pending_dept_head';
  $hasBulkTickets = $tickets->where('status', $bulkStatus)->count() > 0;
  $lsKey = 'bulk_selected_' . auth()->user()->role;
@endphp

@if($isBulkRole && $hasBulkTickets)
<div id="bulk-action-bar" class="action-panel" style="display:none;">
  <span id="bulk-count-label" style="font-weight:600; font-size:14px; color:var(--color-trout);">0 tiket dipilih</span>
  <div class="action-panel-buttons">

    @if(auth()->user()->isTeamLeader())
      {{-- Bulk Review Form: Accept or Reject --}}
      <form id="bulk-review-form" method="POST" action="{{ route('tickets.bulk-review') }}" style="margin:0; display:inline-flex; gap:var(--space-sm);">
        @csrf
        <input type="hidden" name="action" id="bulk-review-action" value="">
        <input type="hidden" name="notes" id="bulk-review-notes" value="">
        <div id="bulk-review-inputs"></div>
        <button type="button" onclick="openBulkRejectModal('review')" class="btn btn-danger" style="font-size:13px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
          Tolak Dokumen
        </button>
        <button type="button" onclick="confirmBulkReview('accept')" class="btn btn-success" style="font-size:13px; font-weight:600;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
          Terima Dokumen
        </button>
      </form>
    @endif

    @if(auth()->user()->isDepartmentHead())
      <form id="bulk-decide-form" method="POST" action="{{ route('tickets.bulk-decide') }}" style="margin:0; display:inline-flex; gap:var(--space-sm);">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input" value="">
        <input type="hidden" name="notes" id="bulk-decide-notes" value="">
        <div id="bulk-decide-inputs"></div>
        <button type="button" onclick="openBulkRejectModal('decide')" class="btn btn-danger" style="font-size:13px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
          Tolak
        </button>
        <button type="button" onclick="confirmBulkAction('approve')" class="btn btn-success" style="font-size:13px; font-weight:600;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
          Setujui
        </button>
      </form>
    @endif

    <button type="button" onclick="clearSelection()" class="btn btn-secondary" style="font-size:13px;">
      Batal
    </button>
  </div>
</div>

{{-- Modal Custom untuk Bulk Reject Notes --}}
<div class="modal-overlay" id="modal-bulk-reject-notes" style="display:none; z-index:10000;">
  <div class="modal-card">
    <div class="modal-icon danger">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
    </div>
    <div class="modal-title">Masukkan Alasan Penolakan</div>
    <div class="modal-body" style="text-align:left;">
      Catatan penolakan ini akan dikirimkan kepada masing-masing Requester dari tiket yang Anda pilih.
      <div class="form-group" style="margin-top:16px;">
        <label class="form-label" style="font-weight:600;">Catatan Penolakan/Revisi <span class="required">*</span></label>
        <textarea id="bulk-reject-textarea" class="form-control" rows="4" placeholder="Tuliskan detail revisi di sini... (Tekan Enter untuk baris baru)" style="width:100%; border-radius:var(--radius-md); font-family:inherit;"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" onclick="closeBulkRejectModal()" class="btn btn-secondary">Kembali</button>
      <button type="button" id="btn-submit-bulk-reject" class="btn btn-danger">Tolak Tiket</button>
    </div>
  </div>
</div>
@endif

{{-- Table --}}
<div class="page-content">
  <div class="table-wrapper">
    <table class="data-table" id="tickets-table">
      <thead>
        <tr>
          @if($isBulkRole && $hasBulkTickets)
            <th style="width:40px; text-align:center;">
              <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll(this)" title="Pilih semua di halaman ini" style="width:16px; height:16px; cursor:pointer; accent-color:var(--color-primary);">
            </th>
          @endif
          <th>ID Tiket</th>
          <th>Judul Pengadaan</th>
          <th>Kategori</th>
          <th>Nominal</th>
          <th>Status</th>
          <th>Tanggal</th>
          <th style="width:60px;"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($tickets as $ticket)
          @php $isBulkable = $isBulkRole && $ticket->status === $bulkStatus; @endphp
          <tr onclick="{{ $isBulkable ? 'handleRowClick(event, this)' : 'window.location=\''.route('tickets.show', $ticket).'\'' }}" style="cursor:pointer;" data-url="{{ route('tickets.show', $ticket) }}">
            @if($isBulkRole && $hasBulkTickets)
              <td onclick="event.stopPropagation()" style="text-align:center;">
                @if($isBulkable)
                  <input type="checkbox" class="bulk-checkbox"
                    value="{{ $ticket->id }}"
                    onchange="updateBulkBar()"
                    style="width:16px; height:16px; cursor:pointer; accent-color:var(--color-primary);">
                @endif
              </td>
            @endif
            <td>
              <span class="table-ticket-id">#{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</span>
            </td>
            <td>
              <div class="table-title">{{ $ticket->title }}</div>
              <div class="caption text-muted">{{ $ticket->item_name }}</div>
            </td>
            <td>
              <span class="badge badge-category">{{ config('eprocurement.categories.'.$ticket->category, $ticket->category) }}</span>
            </td>
            <td>
              <span class="table-amount">{{ $ticket->formatted_total_amount }}</span>
              @if($ticket->expenditure_type)
                <br><span class="badge badge-{{ strtolower($ticket->expenditure_type) }}" style="margin-top:4px; font-size:10px; padding:2px 8px;">{{ $ticket->expenditure_type }}</span>
              @endif
            </td>
            <td>
              <span class="badge badge-{{ str_replace('_','-',$ticket->status) }}">{{ $ticket->status_label }}</span>
              @if($ticket->is_cross_fund)
                <br><span class="badge badge-cross-fund" style="margin-top:4px; font-size:10px; padding:2px 8px;">⇄ Silang Dana</span>
              @endif
            </td>
            <td class="caption text-muted">{{ $ticket->created_at->format('d M Y') }}</td>
            <td onclick="event.stopPropagation()">
              <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-icon btn-sm" title="Lihat Detail">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ ($isBulkRole && $hasBulkTickets) ? 8 : 7 }}">
              <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h3>Belum ada tiket</h3>
                <p>
                  @if(auth()->user()->isRequester())
                    Buat pengajuan pengadaan pertama Anda.
                  @else
                    Tidak ada tiket yang memerlukan tindakan dari Anda.
                  @endif
                </p>
                @if(auth()->user()->isRequester())
                  <a href="{{ route('tickets.create') }}" class="btn btn-primary" style="margin-top:var(--space-md);">Pengajuan Baru</a>
                @endif
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    {{-- Pagination --}}
    <div style="display:flex; align-items:center; justify-content:space-between; padding:var(--space-md) 0; flex-wrap:wrap; gap:var(--space-sm);">
      <div style="font-size:13px; color:var(--color-muted);">
        Menampilkan {{ $tickets->firstItem() ?? 0 }}–{{ $tickets->lastItem() ?? 0 }} dari {{ $tickets->total() }} tiket
      </div>

      @if($tickets->hasPages())
        <div class="pagination">
          {{-- Previous --}}
          @if($tickets->onFirstPage())
            <span class="page-link disabled">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
            </span>
          @else
            <a href="{{ $tickets->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="page-link">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
          @endif

          @foreach($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
            <a href="{{ $url }}&{{ http_build_query(request()->except('page')) }}"
               class="page-link {{ $page === $tickets->currentPage() ? 'active' : '' }}">
              {{ $page }}
            </a>
          @endforeach

          {{-- Next --}}
          @if($tickets->hasMorePages())
            <a href="{{ $tickets->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="page-link">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            </a>
          @else
            <span class="page-link disabled">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            </span>
          @endif
        </div>
      @endif
    </div>
  </div>
</div>

@push('scripts')
<script>
const LS_KEY = '{{ $lsKey ?? "bulk_selected" }}';

// ── Search ────────────────────────────────────────────────────
function applySearch() {
  const q = document.getElementById('search-input').value;
  const url = new URL(window.location.href);
  if (q) url.searchParams.set('search', q);
  else url.searchParams.delete('search');
  url.searchParams.delete('page');
  window.location.href = url.toString();
}

// ── Bulk Selection ────────────────────────────────────────────
const bulkBar     = document.getElementById('bulk-action-bar');
const selectAllCb = document.getElementById('select-all-checkbox');

function getAllCheckboxes() {
  return [...document.querySelectorAll('.bulk-checkbox')];
}
function getCheckedBoxes() {
  return [...document.querySelectorAll('.bulk-checkbox:checked')];
}

// Save current selection to localStorage
function saveSelection() {
  const selected = getCheckedBoxes().map(cb => cb.value);
  localStorage.setItem(LS_KEY, JSON.stringify(selected));
}

// Restore selection from localStorage on page load
function restoreSelection() {
  try {
    const saved = JSON.parse(localStorage.getItem(LS_KEY) || '[]');
    if (saved.length === 0) return;
    getAllCheckboxes().forEach(cb => {
      if (saved.includes(cb.value)) cb.checked = true;
    });
    updateBulkBar();
  } catch (e) { localStorage.removeItem(LS_KEY); }
}

function updateBulkBar() {
  const checked = getCheckedBoxes();
  if (!bulkBar) return;
  if (checked.length > 0) {
    bulkBar.style.display = 'flex';
    document.getElementById('bulk-count-label').textContent = checked.length + ' tiket dipilih';
  } else {
    bulkBar.style.display = 'none';
  }
  // Update select-all indeterminate state
  const all = getAllCheckboxes();
  if (selectAllCb) {
    selectAllCb.checked = all.length > 0 && checked.length === all.length;
    selectAllCb.indeterminate = checked.length > 0 && checked.length < all.length;
  }
  saveSelection();
}

function toggleSelectAll(cb) {
  getAllCheckboxes().forEach(box => { box.checked = cb.checked; });
  updateBulkBar();
}

function clearSelection() {
  getAllCheckboxes().forEach(box => box.checked = false);
  if (selectAllCb) selectAllCb.checked = false;
  localStorage.removeItem(LS_KEY);
  updateBulkBar();
}

// Row click — navigate to detail unless clicking on a checkbox
function handleRowClick(e, row) {
  if (e.target.type === 'checkbox') return;
  window.location.href = row.dataset.url;
}

// ── Bulk Custom Modal Handlers ─────────────────────────────────
let activeBulkActionType = ''; // 'review' or 'decide'

function openBulkRejectModal(type) {
  const checked = getCheckedBoxes();
  if (checked.length === 0) return;
  
  activeBulkActionType = type;
  
  // Reset textarea
  document.getElementById('bulk-reject-textarea').value = '';
  
  // Show modal
  const modal = document.getElementById('modal-bulk-reject-notes');
  if (modal) modal.style.display = 'flex';
  
  // Set up click handler for the submit button inside modal
  const submitBtn = document.getElementById('btn-submit-bulk-reject');
  submitBtn.onclick = function() {
    const notes = document.getElementById('bulk-reject-textarea').value.trim();
    if (!notes) {
      alert('Catatan penolakan wajib diisi!');
      return;
    }
    
    if (activeBulkActionType === 'review') {
      executeBulkReviewReject(notes);
    } else if (activeBulkActionType === 'decide') {
      executeBulkDecideDecline(notes);
    }
  };
}

function closeBulkRejectModal() {
  const modal = document.getElementById('modal-bulk-reject-notes');
  if (modal) modal.style.display = 'none';
}

function executeBulkReviewReject(notes) {
  const checked = getCheckedBoxes();
  if (!confirm(`Anda yakin ingin menolak dokumen untuk ${checked.length} tiket ini?`)) return;

  const form      = document.getElementById('bulk-review-form');
  const container = document.getElementById('bulk-review-inputs');
  
  document.getElementById('bulk-review-action').value = 'reject';
  document.getElementById('bulk-review-notes').value  = notes;
  
  container.innerHTML = '';
  checked.forEach(cb => {
    const inp = document.createElement('input');
    inp.type  = 'hidden';
    inp.name  = 'ticket_ids[]';
    inp.value = cb.value;
    container.appendChild(inp);
  });
  
  localStorage.removeItem(LS_KEY);
  closeBulkRejectModal();
  form.submit();
}

function executeBulkDecideDecline(notes) {
  const checked = getCheckedBoxes();
  if (!confirm(`Anda yakin ingin menolak ${checked.length} pengadaan ini?`)) return;

  const form      = document.getElementById('bulk-decide-form');
  const container = document.getElementById('bulk-decide-inputs');
  
  document.getElementById('bulk-action-input').value = 'decline';
  document.getElementById('bulk-decide-notes').value  = notes;
  
  container.innerHTML = '';
  checked.forEach(cb => {
    const inp = document.createElement('input');
    inp.type  = 'hidden';
    inp.name  = 'ticket_ids[]';
    inp.value = cb.value;
    container.appendChild(inp);
  });
  
  localStorage.removeItem(LS_KEY);
  closeBulkRejectModal();
  form.submit();
}

// ── Bulk Review - Accept (Team Leader) ─────────────────────────
function confirmBulkReview(action) {
  const checked = getCheckedBoxes();
  if (checked.length === 0) return;
  
  if (action === 'accept') {
    if (!confirm(`Anda yakin ingin menyetujui (terima dokumen) untuk ${checked.length} tiket ini?`)) return;

    const form      = document.getElementById('bulk-review-form');
    const container = document.getElementById('bulk-review-inputs');
    
    document.getElementById('bulk-review-action').value = 'accept';
    document.getElementById('bulk-review-notes').value  = 'Semua dokumen diterima.';
    
    container.innerHTML = '';
    checked.forEach(cb => {
      const inp = document.createElement('input');
      inp.type  = 'hidden';
      inp.name  = 'ticket_ids[]';
      inp.value = cb.value;
      container.appendChild(inp);
    });
    
    localStorage.removeItem(LS_KEY);
    form.submit();
  }
}

// ── Bulk Decide - Approve (Dept Head) ──────────────────────────
function confirmBulkAction(action) {
  const checked = getCheckedBoxes();
  if (checked.length === 0) return;
  
  if (action === 'approve') {
    if (!confirm(`Anda yakin ingin menyetujui ${checked.length} pengadaan ini?`)) return;

    const form      = document.getElementById('bulk-decide-form');
    const container = document.getElementById('bulk-decide-inputs');
    
    document.getElementById('bulk-action-input').value = 'approve';
    
    container.innerHTML = '';
    checked.forEach(cb => {
      const inp = document.createElement('input');
      inp.type  = 'hidden';
      inp.name  = 'ticket_ids[]';
      inp.value = cb.value;
      container.appendChild(inp);
    });
    
    localStorage.removeItem(LS_KEY);
    form.submit();
  }
}

// Restore selection state when page loads
document.addEventListener('DOMContentLoaded', restoreSelection);

// Also re-attach onChange listener to checkboxes
document.querySelectorAll('.bulk-checkbox').forEach(cb => {
  cb.addEventListener('change', updateBulkBar);
});
</script>
@endpush
@endsection
