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
      'pending_review'      => 'Pending Review',
      'revision'            => 'Revisi',
      'need_to_validate'    => 'Validasi',
      'pending_team_leader' => 'Team Leader',
      'pending_dept_head'   => 'Dept Head',
      'approved'            => 'Disetujui',
      'declined'            => 'Ditolak',
      'po_generated'        => 'PO Terbit',
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
  $bulkStatus = auth()->user()->isTeamLeader() ? 'pending_team_leader' : 'pending_dept_head';
  $hasBulkTickets = $tickets->where('status', $bulkStatus)->count() > 0;
@endphp

@if($isBulkRole && $hasBulkTickets)
<div id="bulk-action-bar" style="display:none; position:sticky; top:0; z-index:100; background:var(--color-primary); color:#fff; padding:12px var(--space-lg); border-radius:var(--radius-md); margin-bottom:var(--space-md); display:none; align-items:center; gap:var(--space-md); justify-content:space-between; box-shadow:var(--shadow-md);">
  <span id="bulk-count-label" style="font-weight:600; font-size:14px;">0 tiket dipilih</span>
  <div style="display:flex; gap:var(--space-sm); align-items:center;">
    @if(auth()->user()->isTeamLeader())
      <form id="bulk-forward-form" method="POST" action="{{ route('tickets.bulk-forward') }}" style="margin:0;">
        @csrf
        <div id="bulk-forward-inputs"></div>
        <button type="button" onclick="confirmBulkForward()" class="btn btn-orient" style="font-size:13px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
          Teruskan ke Dept Head
        </button>
      </form>
    @endif

    @if(auth()->user()->isDepartmentHead())
      <form id="bulk-decide-form" method="POST" action="{{ route('tickets.bulk-decide') }}" style="margin:0;">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input" value="">
        <div id="bulk-decide-inputs"></div>
        <button type="button" onclick="confirmBulkAction('decline')" class="btn btn-danger" style="font-size:13px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
          Tolak
        </button>
        <button type="button" onclick="confirmBulkAction('approve')" class="btn" style="background:#fff; color:var(--color-primary); font-size:13px; font-weight:600;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
          Setujui
        </button>
      </form>
    @endif

    <button onclick="clearSelection()" style="background:rgba(255,255,255,0.15); border:none; color:#fff; border-radius:var(--radius-sm); padding:6px 12px; cursor:pointer; font-size:12px;">
      Batal
    </button>
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
const bulkBar      = document.getElementById('bulk-action-bar');
const selectAllCb  = document.getElementById('select-all-checkbox');

function getCheckedBoxes() {
  return [...document.querySelectorAll('.bulk-checkbox:checked')];
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
  const all = document.querySelectorAll('.bulk-checkbox');
  if (selectAllCb) {
    selectAllCb.checked = all.length > 0 && checked.length === all.length;
    selectAllCb.indeterminate = checked.length > 0 && checked.length < all.length;
  }
}

function toggleSelectAll(cb) {
  document.querySelectorAll('.bulk-checkbox').forEach(box => {
    box.checked = cb.checked;
  });
  updateBulkBar();
}

function clearSelection() {
  document.querySelectorAll('.bulk-checkbox').forEach(box => box.checked = false);
  if (selectAllCb) selectAllCb.checked = false;
  updateBulkBar();
}

// Row click — navigate to detail unless clicking on a checkbox
function handleRowClick(e, row) {
  if (e.target.type === 'checkbox') return;
  window.location.href = row.dataset.url;
}

// ── Bulk Forward (Team Leader) ─────────────────────────────────
function confirmBulkForward() {
  const checked = getCheckedBoxes();
  if (checked.length === 0) return;
  if (!confirm(`Teruskan ${checked.length} tiket ke Department Head?`)) return;
  const form = document.getElementById('bulk-forward-form');
  const container = document.getElementById('bulk-forward-inputs');
  container.innerHTML = '';
  checked.forEach(cb => {
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = 'ticket_ids[]';
    inp.value = cb.value;
    container.appendChild(inp);
  });
  form.submit();
}

// ── Bulk Decide (Dept Head) ────────────────────────────────────
function confirmBulkAction(action) {
  const checked = getCheckedBoxes();
  if (checked.length === 0) return;
  const label = action === 'approve' ? 'menyetujui' : 'menolak';
  if (!confirm(`Anda yakin ingin ${label} ${checked.length} tiket?`)) return;
  const form = document.getElementById('bulk-decide-form');
  const container = document.getElementById('bulk-decide-inputs');
  document.getElementById('bulk-action-input').value = action;
  container.innerHTML = '';
  checked.forEach(cb => {
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = 'ticket_ids[]';
    inp.value = cb.value;
    container.appendChild(inp);
  });
  form.submit();
}
</script>
@endpush
@endsection
