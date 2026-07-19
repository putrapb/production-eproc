@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
  <span class="breadcrumb-active">Dashboard</span>
@endsection

@section('content')
<div class="page-header">
  <div class="page-header-left">
    <h1>Dashboard</h1>
    <p>Selamat datang, {{ auth()->user()->name }} — {{ auth()->user()->role_label }}</p>
  </div>
  @if(auth()->user()->isRequester())
    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      Pengajuan Baru
    </a>
  @endif
</div>

<div class="page-content">

  {{-- ─── STAT CARDS ─────────────────────────────── --}}
  <div class="stat-grid">
    @if(auth()->user()->isRequester())
      @php $s = $ticketSummary; @endphp

      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-secondary-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-secondary)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="stat-card-label">Total Tiket</div>
        <div class="stat-card-value">{{ $s['total'] ?? 0 }}</div>
        <div class="stat-card-sub">Semua pengajuan saya</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-info-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-info)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M12 11v5"/></svg>
        </div>
        <div class="stat-card-label">Pending Review</div>
        <div class="stat-card-value">{{ ($s['pending_review'] ?? 0) + ($s['need_to_validate'] ?? 0) + ($s['in_approval'] ?? 0) }}</div>
        <div class="stat-card-sub">Sedang diproses</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-success-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-success)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
        </div>
        <div class="stat-card-label">Disetujui</div>
        <div class="stat-card-value">{{ ($s['approved'] ?? 0) + ($s['form_generated'] ?? 0) }}</div>
        <div class="stat-card-sub">Termasuk form terbit</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-error-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-error)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
        </div>
        <div class="stat-card-label">Ditolak</div>
        <div class="stat-card-value">{{ $s['declined'] ?? 0 }}</div>
        <div class="stat-card-sub">Butuh tindak lanjut</div>
      </div>

    @elseif(auth()->user()->isTeamLeader())
      @php $s = $ticketSummary; @endphp
      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-info-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-info)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M12 11v5"/></svg>
        </div>
        <div class="stat-card-label">Cek Dokumen</div>
        <div class="stat-card-value">{{ $s['pending_review'] ?? 0 }}</div>
        <div class="stat-card-sub">Perlu pemeriksaan dokumen</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-warning-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-warning-text)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
        </div>
        <div class="stat-card-label">Siap Terbit Form</div>
        <div class="stat-card-value">{{ $s['approved'] ?? 0 }}</div>
        <div class="stat-card-sub">Disetujui Dept Head</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-success-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-success)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="stat-card-label">Form Diterbitkan</div>
        <div class="stat-card-value">{{ $s['form_generated'] ?? 0 }}</div>
        <div class="stat-card-sub">Total selesai</div>
      </div>

    @elseif(auth()->user()->isDepartmentHead())
      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-info-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-info)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M12 11v5"/></svg>
        </div>
        <div class="stat-card-label">Menunggu Keputusan Saya</div>
        <div class="stat-card-value">{{ $ticketSummary['pending_dept_head'] ?? 0 }}</div>
        <div class="stat-card-sub">Perlu keputusan segera</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-success-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-success)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
        </div>
        <div class="stat-card-label">Disetujui</div>
        <div class="stat-card-value">{{ $ticketSummary['approved'] ?? 0 }}</div>
        <div class="stat-card-sub">Total persetujuan saya</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon" style="background:var(--color-error-soft);">
          <svg width="22" height="22" fill="none" stroke="var(--color-error)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
        </div>
        <div class="stat-card-label">Ditolak</div>
        <div class="stat-card-value">{{ $ticketSummary['declined'] ?? 0 }}</div>
        <div class="stat-card-sub">Total penolakan saya</div>
      </div>
    @endif
  </div>

  {{-- ─── CHARTS ROW ─────────────────────────────── --}}
  <div class="dashboard-grid dashboard-grid-2-1">
    {{-- Trend Chart --}}
    <div class="card">
      <div class="card-header">
        <div>
          <div class="heading-sm">Tren Pengajuan Pengadaan</div>
          <div class="caption text-muted">Nominal pengajuan tiket per bulan pada tahun {{ date('Y') }}</div>
        </div>
      </div>
      <div class="card-body" style="position: relative; height: 320px;">
        <canvas id="trendChart"></canvas>
      </div>
    </div>

    {{-- Composition Chart --}}
    <div class="card">
      <div class="card-header">
        <div>
          <div class="heading-sm">Komposisi Penggunaan Anggaran</div>
          <div class="caption text-muted">Berdasarkan Asset Class (CAPEX & OPEX Terpakai/Terkunci)</div>
        </div>
      </div>
      <div class="card-body" style="position: relative; height: 320px; display: flex; align-items: center; justify-content: center; padding-bottom: 20px;">
        <canvas id="compositionChart"></canvas>
      </div>
    </div>
  </div>

  {{-- ─── BOTTOM ROW: Budget + Recent Activity ────── --}}
  <div class="dashboard-grid dashboard-grid-1-1">

    {{-- Budget Utilization Card --}}
    <div class="card">
      <div class="card-header">
        <div>
          <div class="heading-sm">Utilisasi Anggaran</div>
          <div class="caption text-muted">Tahun {{ date('Y') }}</div>
        </div>
        <span class="badge badge-category">Fiskal {{ date('Y') }}</span>
      </div>
      <div class="card-body">
        <div class="tab-group">
          <button class="tab-btn active" onclick="switchTab('capex', this)">CAPEX</button>
          <button class="tab-btn" onclick="switchTab('opex', this)">OPEX</button>
        </div>

        <div class="tab-pane active" id="tab-capex">
          @foreach($budgetData as $category => $data)
            @if(in_array($category, ['infrastruktur_utama', 'lisensi_sistem']))
              @php $b = $data['capex']; @endphp
              @if($b)
                @php
                  $pct  = min($b['utilization_percentage'], 100);
                  $fill = $pct >= 90 ? 'critical' : ($pct >= 75 ? 'warn' : '');
                @endphp
                <div class="budget-row">
                  <div class="budget-row-header">
                    <span class="budget-category-name">{{ config('eprocurement.categories.'.$category, $category) }}</span>
                    <span class="budget-pct {{ $pct >= 90 ? 'text-primary' : '' }}">{{ number_format($pct, 1) }}%</span>
                  </div>
                  <div class="progress-bar-track">
                    <div class="progress-bar-fill {{ $fill }}" style="width:{{ $pct }}%"></div>
                  </div>
                  <div class="budget-amounts">
                    <span>Terpakai: Rp {{ number_format($b['used_amount'] + $b['locked_amount'], 0, ',', '.') }}</span>
                    <span>Limit: Rp {{ number_format($b['total_limit'], 0, ',', '.') }}</span>
                  </div>
                </div>
              @endif
            @endif
          @endforeach
        </div>

        <div class="tab-pane" id="tab-opex">
          @foreach($budgetData as $category => $data)
            @if(in_array($category, ['layanan_pemeliharaan', 'perlengkapan_operasional']))
              @php $b = $data['opex']; @endphp
              @if($b)
                @php
                  $pct  = min($b['utilization_percentage'], 100);
                  $fill = $pct >= 90 ? 'critical' : ($pct >= 75 ? 'warn' : '');
                @endphp
                <div class="budget-row">
                  <div class="budget-row-header">
                    <span class="budget-category-name">{{ config('eprocurement.categories.'.$category, $category) }}</span>
                    <span class="budget-pct">{{ number_format($pct, 1) }}%</span>
                  </div>
                  <div class="progress-bar-track">
                    <div class="progress-bar-fill {{ $fill }}" style="width:{{ $pct }}%"></div>
                  </div>
                  <div class="budget-amounts">
                    <span>Terpakai: Rp {{ number_format($b['used_amount'] + $b['locked_amount'], 0, ',', '.') }}</span>
                    <span>Limit: Rp {{ number_format($b['total_limit'], 0, ',', '.') }}</span>
                  </div>
                </div>
              @endif
            @endif
          @endforeach
        </div>
      </div>
    </div>

    {{-- Recent Activity Card --}}
    <div class="card">
      <div class="card-header">
        <div>
          <div class="heading-sm">Aktivitas Terkini</div>
          <div class="caption text-muted">10 tiket terakhir diperbarui</div>
        </div>
        <a href="{{ route('tickets.index') }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
      </div>
      <div style="overflow:hidden; border-radius:0 0 var(--radius-lg) var(--radius-lg);">
        @forelse($recentTickets as $ticket)
          <a href="{{ route('tickets.show', $ticket) }}" class="topbar-menu-item" style="padding:var(--space-md) var(--space-lg); text-decoration:none; display:flex; align-items:center; gap:var(--space-sm); border-bottom:1px solid var(--color-hairline-soft);">
            <div style="flex-shrink:0; width:150px; display:flex;">
              <span class="badge badge-{{ str_replace('_','-',$ticket->status) }}" style="font-size:11px; padding:3px 8px; width:100%; justify-content:center; text-align:center;">
                {{ $ticket->status_label }}
              </span>
            </div>
            <div style="flex:1; min-width:0;">
              <div class="label-md text-ink truncate">{{ $ticket->title }}</div>
              <div class="caption text-muted">{{ $ticket->formatted_total_amount }} · {{ $ticket->updated_at->diffForHumans() }}</div>
            </div>
            <svg width="14" height="14" fill="none" stroke="var(--color-muted-soft)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
          </a>
        @empty
          <div class="empty-state" style="padding:var(--space-xxl) var(--space-xl);">
            <div class="empty-state-icon"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--color-muted);"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg></div>
            <h3>Belum ada tiket</h3>
            <p>Aktivitas pengadaan akan muncul di sini.</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function switchTab(name, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('tab-' + name).classList.add('active');
}

document.addEventListener("DOMContentLoaded", function () {
  // Chart.js Default Font Styling
  Chart.defaults.font.family = "'Inter', sans-serif";
  Chart.defaults.color = '#6B7080';

  // 1. Trend Chart (Line / Bar)
  const trendCtx = document.getElementById('trendChart').getContext('2d');
  new Chart(trendCtx, {
    type: 'line',
    data: {
      labels: @json($chartData['trend']['labels']),
      datasets: [{
        label: 'Total Nilai (Rupiah)',
        data: @json($chartData['trend']['data']),
        borderColor: '#006885',
        backgroundColor: 'rgba(0, 104, 133, 0.08)',
        borderWidth: 3,
        fill: true,
        tension: 0.3,
        pointBackgroundColor: '#F15A22',
        pointBorderColor: '#fff',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: '#F15A22',
        pointRadius: 4,
        pointHoverRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#EDF0F3' },
          ticks: {
            callback: function(value) {
              if (value >= 1e9) return 'Rp ' + (value / 1e9).toFixed(1) + ' M';
              if (value >= 1e6) return 'Rp ' + (value / 1e6).toFixed(0) + ' Jt';
              return 'Rp ' + value.toLocaleString('id-ID');
            }
          }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });

  // 2. Composition Chart (Doughnut)
  const compCtx = document.getElementById('compositionChart').getContext('2d');
  new Chart(compCtx, {
    type: 'doughnut',
    data: {
      labels: @json($chartData['composition']['labels']),
      datasets: [{
        data: @json($chartData['composition']['data']),
        backgroundColor: [
          '#006885', // infrastruktur_utama - Orient
          '#F15A22', // lisensi_sistem - Flamingo
          '#494E5C', // layanan_pemeliharaan - Trout
          '#0088FF'  // perlengkapan_operasional - Blue
        ],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            usePointStyle: true,
            padding: 15,
            font: { size: 11 }
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const val = context.raw;
              return context.label + ': Rp ' + val.toLocaleString('id-ID');
            }
          }
        }
      },
      cutout: '60%'
    }
  });
});
</script>
@endpush
@endsection
