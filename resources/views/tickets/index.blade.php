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

{{-- Filter + Search --}}
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
      <a href="{{ route('tickets.index', ['status' => $val]) }}"
         class="filter-tab {{ request('status') === $val ? 'active' : '' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>

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

{{-- Table --}}
<div class="page-content">
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
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
          <tr onclick="window.location='{{ route('tickets.show', $ticket) }}'" style="cursor:pointer;">
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
            <td colspan="7">
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

@push('scripts')
<script>
function applySearch() {
  const q = document.getElementById('search-input').value;
  const url = new URL(window.location.href);
  if (q) url.searchParams.set('search', q);
  else url.searchParams.delete('search');
  url.searchParams.delete('page');
  window.location.href = url.toString();
}
</script>
@endpush
@endsection
