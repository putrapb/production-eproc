@extends('layouts.app')

@section('title', 'Audit Trail Logs')

@section('breadcrumb')
<span class="breadcrumb-active">Audit Logs</span>
@endsection

@section('content')
<!-- Header -->
<div class="page-header">
  <div class="page-header-left">
    <h1>Audit Trail Logs</h1>
    <p>Pelacakan riwayat aktivitas pengadaan IT Infrastructure secara kronologis.</p>
  </div>
</div>

<div class="page-content">
  <!-- Filters -->
  <div class="card" style="margin-bottom: var(--space-lg);">
    <div class="card-body" style="padding: var(--space-md) var(--space-lg);">
      <form method="GET" action="{{ route('audit-logs.index') }}" class="audit-filters-form" style="display: flex; gap: var(--space-md); flex-wrap: wrap; align-items: flex-end; width: 100%;">
        
        <div class="form-group" style="flex: 1; min-width: 240px; margin-bottom: 0;">
          <label class="form-label" for="search" style="font-size: 12px; margin-bottom: 6px;">Pencarian Tiket / Aktor</label>
          <input type="text" id="search" name="search" class="form-control" 
                 placeholder="Cari Judul, Kode Tiket, Aktor, NIP..." value="{{ $search }}">
        </div>

        <div class="form-group" style="min-width: 200px; margin-bottom: 0;">
          <label class="form-label" for="action" style="font-size: 12px; margin-bottom: 6px;">Filter Aktivitas</label>
          <select id="action" name="action" class="form-control" style="padding-right: var(--space-xl);">
            <option value="">Semua Aktivitas</option>
            @foreach($actionOptions as $key => $label)
              <option value="{{ $key }}" {{ $action === $key ? 'selected' : '' }}>
                {{ $label }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-buttons" style="display: flex; gap: 8px; margin-bottom: 0;">
          <button type="submit" class="btn btn-primary" style="height: 38px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 4px;">
              <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Cari
          </button>
          
          @if($search || $action)
            <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
              Reset
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  <!-- Logs Table (Desktop Only) -->
  <div class="table-wrapper desktop-only">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 160px;">Tanggal & Waktu</th>
          <th style="width: 220px;">Kode & Judul Tiket</th>
          <th style="width: 180px;">Aktor (NIP)</th>
          <th style="width: 200px;">Aktivitas</th>
          <th>Catatan / Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr>
            <td style="white-space: nowrap; color: var(--color-muted); font-size: 13px;">
              {{ $log->created_at->format('d M Y, H:i') }} WIB
            </td>
            <td>
              @if($log->ticket)
                <div style="font-weight: 600; color: var(--color-ink); font-size: 13px;">
                  <a href="{{ route('tickets.show', $log->ticket_id) }}" style="color: var(--color-secondary); text-decoration: none; font-weight: 700;">
                    #{{ str_pad($log->ticket->id, 4, '0', STR_PAD_LEFT) }}
                  </a>
                </div>
                <div class="truncate" style="font-size: 12px; color: var(--color-muted); max-width: 200px;" title="{{ $log->ticket->title }}">
                  {{ $log->ticket->title }}
                </div>
              @else
                <span class="badge badge-neutral" style="color: var(--color-muted-soft);">Tiket Dihapus</span>
              @endif
            </td>
            <td>
              <div style="font-weight: 500; color: var(--color-ink); font-size: 13px;">
                {{ $log->user ? $log->user->name : 'System' }}
              </div>
              <div style="font-size: 11px; color: var(--color-muted-soft);">
                @if($log->user && $log->user->hrEmployee)
                  NIP: {{ $log->user->hrEmployee->nip }} · {{ $log->user->role_label }}
                @else
                  System Automated
                @endif
              </div>
            </td>
            <td>
              @php
                $badgeType = match ($log->action) {
                  \App\Models\ApprovalLog::ACTION_SUBMITTED            => 'info',
                  \App\Models\ApprovalLog::ACTION_FOLLOWED_UP          => 'success',
                  \App\Models\ApprovalLog::ACTION_REJECTED_DOCUMENT    => 'error',
                  \App\Models\ApprovalLog::ACTION_REVISED              => 'warning',
                  \App\Models\ApprovalLog::ACTION_VALIDATED            => 'success',
                  \App\Models\ApprovalLog::ACTION_CROSS_FUND_REQUESTED => 'warning',
                  \App\Models\ApprovalLog::ACTION_FORWARDED            => 'info',
                  \App\Models\ApprovalLog::ACTION_APPROVED             => 'success',
                  \App\Models\ApprovalLog::ACTION_DECLINED             => 'error',
                  \App\Models\ApprovalLog::ACTION_PO_ISSUED            => 'success',
                  default                                              => 'neutral',
                };
              @endphp
              <span class="badge badge-{{ $badgeType }}" style="font-size: 11px; font-weight: 600;">
                {{ $log->action_label }}
              </span>
            </td>
            <td style="font-size: 13px; color: var(--color-ink); line-height: 1.5; word-break: break-word;">
              {{ $log->notes ?: '-' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="text-align: center; padding: var(--space-xl); color: var(--color-muted-soft);">
              <div style="margin-bottom: var(--space-sm);">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color: var(--color-muted-soft);">
                  <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              Belum ada data log pelacakan yang cocok dengan kriteria pencarian.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Logs Card List (Mobile Only) -->
  <div class="mobile-only mobile-log-cards">
    @forelse($logs as $log)
      <div class="log-card">
        <div class="log-card-header">
          <span>{{ $log->created_at->format('d M Y, H:i') }} WIB</span>
          <div>
            @if($log->ticket)
              <a href="{{ route('tickets.show', $log->ticket_id) }}" class="log-card-ticket-link">
                #{{ str_pad($log->ticket->id, 4, '0', STR_PAD_LEFT) }}
              </a>
            @else
              <span class="badge-neutral-text">Tiket Dihapus</span>
            @endif
          </div>
        </div>
        
        @if($log->ticket)
          <div class="log-card-title">{{ $log->ticket->title }}</div>
        @endif

        <div class="log-card-row">
          <div class="log-card-actor">
            <div class="actor-name">{{ $log->user ? $log->user->name : 'System' }}</div>
            <div class="actor-nip">
              @if($log->user && $log->user->hrEmployee)
                NIP: {{ $log->user->hrEmployee->nip }} · {{ $log->user->role_label }}
              @else
                System Automated
              @endif
            </div>
          </div>
          
          <div class="log-card-action">
            @php
              $badgeType = match ($log->action) {
                \App\Models\ApprovalLog::ACTION_SUBMITTED            => 'info',
                \App\Models\ApprovalLog::ACTION_FOLLOWED_UP          => 'success',
                \App\Models\ApprovalLog::ACTION_REJECTED_DOCUMENT    => 'error',
                \App\Models\ApprovalLog::ACTION_REVISED              => 'warning',
                \App\Models\ApprovalLog::ACTION_VALIDATED            => 'success',
                \App\Models\ApprovalLog::ACTION_CROSS_FUND_REQUESTED => 'warning',
                \App\Models\ApprovalLog::ACTION_FORWARDED            => 'info',
                \App\Models\ApprovalLog::ACTION_APPROVED             => 'success',
                \App\Models\ApprovalLog::ACTION_DECLINED             => 'error',
                \App\Models\ApprovalLog::ACTION_PO_ISSUED            => 'success',
                default                                              => 'neutral',
              };
            @endphp
            <span class="badge badge-{{ $badgeType }}" style="font-size: 11px; font-weight: 600;">
              {{ $log->action_label }}
            </span>
          </div>
        </div>

        @if($log->notes)
          <div class="log-card-notes">
            <strong>Catatan:</strong> {{ $log->notes }}
          </div>
        @endif
      </div>
    @empty
      <div class="empty-state">
        <div class="empty-state-icon"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--color-muted);"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
        <h3>Belum ada data</h3>
        <p>Belum ada data log pelacakan yang cocok dengan kriteria pencarian.</p>
      </div>
    @endforelse
  </div>

  <!-- Pagination -->
  @if($logs->hasPages())
    <div style="margin-top: var(--space-lg); display: flex; justify-content: center; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: var(--space-xs);">
      {{ $logs->links() }}
    </div>
  @endif
</div>
@endsection

@push('styles')
<style>
  /* Responsive dual-layout classes */
  @media (min-width: 769px) {
    .mobile-only {
      display: none !important;
    }
  }

  @media (max-width: 768px) {
    .desktop-only {
      display: none !important;
    }

    /* Stack filters vertically on mobile */
    .audit-filters-form {
      flex-direction: column !important;
      align-items: stretch !important;
    }

    .audit-filters-form .form-group {
      flex: none !important;
      width: 100% !important;
    }

    .audit-filters-form .form-buttons {
      display: flex;
      gap: var(--space-sm);
      width: 100%;
      margin-top: var(--space-xs);
    }

    .audit-filters-form .form-buttons button,
    .audit-filters-form .form-buttons a {
      flex: 1;
      text-align: center;
      justify-content: center;
    }

    /* Mobile Cards Styling */
    .mobile-log-cards {
      display: flex;
      flex-direction: column;
      gap: var(--space-md);
    }

    .log-card {
      background: var(--color-canvas);
      border: 1px solid var(--color-hairline);
      border-radius: var(--radius-lg);
      padding: var(--space-md);
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      gap: var(--space-sm);
    }

    .log-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 12px;
      color: var(--color-muted);
      border-bottom: 1px solid var(--color-hairline-soft);
      padding-bottom: var(--space-xs);
    }

    .log-card-ticket-link {
      color: var(--color-secondary);
      font-weight: 700;
      text-decoration: none;
    }

    .log-card-ticket-link:hover {
      text-decoration: underline;
    }

    .badge-neutral-text {
      color: var(--color-muted-soft);
      font-size: 11px;
    }

    .log-card-title {
      font-size: 14px;
      font-weight: 600;
      color: var(--color-ink);
      line-height: 1.4;
    }

    .log-card-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: var(--space-md);
    }

    .log-card-actor {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .actor-name {
      font-size: 13px;
      font-weight: 500;
      color: var(--color-ink);
    }

    .actor-nip {
      font-size: 11px;
      color: var(--color-muted-soft);
    }

    .log-card-notes {
      padding: var(--space-xs) var(--space-sm);
      background: var(--color-surface-soft);
      border-radius: var(--radius-sm);
      font-size: 12px;
      color: var(--color-body);
      line-height: 1.4;
      word-break: break-word;
    }
  }
</style>
@endpush

