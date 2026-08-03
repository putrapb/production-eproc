@extends('layouts.app')

@section('title', 'Pengaturan')

@section('breadcrumb')
  <span class="breadcrumb-active">Pengaturan</span>
@endsection

@section('content')
<style>
  /* ── Responsive Settings Page UI Styling ── */
  .settings-grid {
    display: grid;
    grid-template-columns: 1fr; /* Default Mobile/Tablet: 1 Column vertikal seperti HP */
    gap: 28px;
    align-items: start;
  }
  @media (min-width: 992px) {
    .settings-grid {
      grid-template-columns: 1fr 1.2fr; /* Desktop: 2 Kolom bersisian yang proporsional & mewah */
      gap: 32px;
    }
  }

  /* Cards */
  .settings-card {
    background: var(--color-surface, #ffffff);
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    width: 100%;
  }
  .settings-card-header {
    background: #FFF8F3;
    padding: 18px 24px;
    border-bottom: 1px solid #FFECE0;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  [data-theme="dark"] .settings-card-header {
    background: rgba(249, 115, 22, 0.08);
    border-bottom: 1px solid rgba(249, 115, 22, 0.15);
  }
  .settings-card-icon {
    color: #F97316; /* Vibrant orange */
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .settings-card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--color-ink, #1e293b);
  }

  .settings-card-body {
    padding: 8px 24px 16px;
  }

  /* Row Item */
  .settings-item-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 0;
    border-bottom: 1px solid var(--color-hairline, #f1f5f9);
    gap: 24px;
  }
  .settings-item-row:last-child {
    border-bottom: none;
  }
  .settings-item-label {
    font-size: 15px;
    font-weight: 600;
    color: var(--color-ink, #1e293b);
    margin-bottom: 4px;
  }
  .settings-item-desc {
    font-size: 13px;
    color: var(--color-muted, #64748b);
    line-height: 1.4;
  }

  /* Custom Toggle Switch (Orange Theme) */
  .custom-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
    cursor: pointer;
  }
  .custom-switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }
  .custom-slider {
    position: absolute;
    inset: 0;
    background: #E2E8F0;
    border-radius: 999px;
    transition: background-color 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  [data-theme="dark"] .custom-slider {
    background: #475569;
  }
  .custom-slider:before {
    content: '';
    position: absolute;
    height: 20px;
    width: 20px;
    left: 3px;
    top: 3px;
    background: #ffffff;
    border-radius: 50%;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
  }
  .custom-switch input:checked + .custom-slider {
    background: #FF5A1F; /* Vibrant Orange */
  }
  .custom-switch input:checked + .custom-slider:before {
    transform: translateX(22px);
  }

  /* Specific styling for Dark Mode switch (Dark Slate when checked) */
  .switch-dark input:checked + .custom-slider {
    background: #334155;
  }
  [data-theme="dark"] .switch-dark input:checked + .custom-slider {
    background: #475569;
  }

  /* Select Dropdown */
  .settings-select {
    height: 38px;
    padding: 0 32px 0 16px;
    font-size: 14px;
    font-weight: 500;
    color: var(--color-ink, #334155);
    background-color: var(--color-surface, #ffffff);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2364748b' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    border: 1px solid var(--color-border, #cbd5e1);
    border-radius: 8px;
    appearance: none;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
    min-width: 120px;
  }
  .settings-select:focus {
    outline: none;
    border-color: #FF5A1F;
    box-shadow: 0 0 0 3px rgba(255, 90, 31, 0.1);
  }

  /* Reset Button */
  .btn-reset-settings {
    background: var(--color-surface, #ffffff);
    color: #0E7490; /* Teal tone matching screenshot */
    border: 1px solid #0E7490;
    border-radius: 8px;
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  }
  .btn-reset-settings:hover {
    background: #F0F9FF;
  }
  [data-theme="dark"] .btn-reset-settings {
    background: transparent;
    color: #38BDF8;
    border-color: #38BDF8;
  }
  [data-theme="dark"] .btn-reset-settings:hover {
    background: rgba(56, 189, 248, 0.1);
  }
</style>

{{-- Standard Left-Aligned Page Header --}}
<div class="page-header">
  <div class="page-header-left">
    <h1>Pengaturan</h1>
    <p>Sesuaikan tampilan dan preferensi notifikasi</p>
  </div>
</div>

{{-- Main Responsive Content Area --}}
<div class="page-content" style="padding-bottom: 60px;">

  <div class="settings-grid">
    
    {{-- ── COLUMN 1: TAMPILAN (APPEARANCE) ── --}}
    <div class="settings-card">
      <div class="settings-card-header">
        <span class="settings-card-icon">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
          </svg>
        </span>
        <span class="settings-card-title">Tampilan (Appearance)</span>
      </div>

      <div class="settings-card-body">

        {{-- Dark Mode Toggle --}}
        <div class="settings-item-row">
          <div>
            <div class="settings-item-label">Dark Mode</div>
            <div class="settings-item-desc">Aktifkan tema gelap untuk kenyamanan mata</div>
          </div>
          <label class="custom-switch switch-dark" title="Toggle dark mode">
            <input type="checkbox" id="toggle-dark-mode" onchange="applyTheme(this.checked)">
            <span class="custom-slider"></span>
          </label>
        </div>

        {{-- Compact Mode Toggle --}}
        <div class="settings-item-row">
          <div>
            <div class="settings-item-label">Compact Mode</div>
            <div class="settings-item-desc">Tampilkan lebih banyak data dalam satu layar</div>
          </div>
          <label class="custom-switch" title="Toggle compact mode">
            <input type="checkbox" id="toggle-compact" onchange="applyCompact(this.checked)">
            <span class="custom-slider"></span>
          </label>
        </div>

        {{-- Ukuran Teks --}}
        <div class="settings-item-row">
          <div>
            <div class="settings-item-label">Ukuran Teks</div>
            <div class="settings-item-desc">Pilih ukuran teks default aplikasi</div>
          </div>
          <select id="select-fontsize" class="settings-select" onchange="applyFontSize(this.value)">
            <option value="13">Kecil</option>
            <option value="14">Sedang</option>
            <option value="16">Besar</option>
          </select>
        </div>

      </div>
    </div>

    {{-- ── COLUMN 2: PREFERENSI NOTIFIKASI ── --}}
    <div class="settings-card">
      <div class="settings-card-header">
        <span class="settings-card-icon">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
          </svg>
        </span>
        <span class="settings-card-title">Preferensi Notifikasi</span>
      </div>

      <div class="settings-card-body">

        {{-- Ticket Approved --}}
        <div class="settings-item-row">
          <div>
            <div class="settings-item-label">Ticket Approved</div>
            <div class="settings-item-desc">Notifikasi saat tiket disetujui</div>
          </div>
          <label class="custom-switch">
            <input type="checkbox" id="notif-approved" onchange="saveNotifPref('approved', this.checked)">
            <span class="custom-slider"></span>
          </label>
        </div>

        {{-- Declined / Revision Request --}}
        <div class="settings-item-row">
          <div>
            <div class="settings-item-label">Declined / Revision Request</div>
            <div class="settings-item-desc">Pemberitahuan ketika tiket ditolak atau butuh revisi</div>
          </div>
          <label class="custom-switch">
            <input type="checkbox" id="notif-rejected" onchange="saveNotifPref('rejected', this.checked)">
            <span class="custom-slider"></span>
          </label>
        </div>

        {{-- Document Review Complete --}}
        <div class="settings-item-row">
          <div>
            <div class="settings-item-label">Document Review Complete</div>
            <div class="settings-item-desc">Saat dokumen pengadaan selesai direview</div>
          </div>
          <label class="custom-switch">
            <input type="checkbox" id="notif-document" onchange="saveNotifPref('document', this.checked)">
            <span class="custom-slider"></span>
          </label>
        </div>

        {{-- New Ticket (Team Leader Only) --}}
        <div class="settings-item-row">
          <div>
            <div class="settings-item-label">New Ticket (Team Leader Only)</div>
            <div class="settings-item-desc">Notifikasi pengajuan tiket baru dari tim</div>
          </div>
          <label class="custom-switch">
            <input type="checkbox" id="notif-incoming" onchange="saveNotifPref('incoming', this.checked)">
            <span class="custom-slider"></span>
          </label>
        </div>

        {{-- Form Generated --}}
        <div class="settings-item-row">
          <div>
            <div class="settings-item-label">Form Generated</div>
            <div class="settings-item-desc">Saat dokumen final (PDF) berhasil dibuat sistem</div>
          </div>
          <label class="custom-switch">
            <input type="checkbox" id="notif-po" onchange="saveNotifPref('po', this.checked)">
            <span class="custom-slider"></span>
          </label>
        </div>

      </div>
    </div>

  </div>

  {{-- Reset Button --}}
  <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
    <button type="button" onclick="resetAllSettings()" class="btn-reset-settings">
      Reset ke Default
    </button>
  </div>

</div>

@push('scripts')
<script>
// ── Apply theme (dark/light) ──
function applyTheme(isDark) {
  const theme = isDark ? 'dark' : 'light';
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('eprocTheme', theme);
  if (typeof showToast === 'function') {
    showToast('success', 'Tema Diperbarui', isDark ? 'Dark mode aktif.' : 'Light mode aktif.');
  }
}

// ── Apply compact mode ──
function applyCompact(isCompact) {
  if (isCompact) {
    document.documentElement.setAttribute('data-compact', 'true');
  } else {
    document.documentElement.removeAttribute('data-compact');
  }
  localStorage.setItem('eprocCompact', isCompact ? 'true' : 'false');
  
  // Update space properties dynamically
  document.documentElement.style.setProperty('--space-md', isCompact ? '12px' : '16px');
  document.documentElement.style.setProperty('--space-lg', isCompact ? '16px' : '24px');
  
  if (typeof showToast === 'function') {
    showToast('success', 'Tampilan Diperbarui', isCompact ? 'Compact mode diaktifkan.' : 'Compact mode dinonaktifkan.');
  }
}

// ── Apply font size ──
function applyFontSize(size) {
  document.documentElement.style.fontSize = size + 'px';
  localStorage.setItem('eprocFontSize', size);
  if (typeof showToast === 'function') {
    showToast('success', 'Ukuran Teks Diperbarui', 'Ukuran teks dasar diset ke ' + (size === '13' ? 'Kecil' : (size === '16' ? 'Besar' : 'Sedang')) + '.');
  }
}

// ── Save notification preference ──
function saveNotifPref(key, value) {
  const prefs = JSON.parse(localStorage.getItem('eprocNotifPrefs') || '{}');
  prefs[key] = value;
  localStorage.setItem('eprocNotifPrefs', JSON.stringify(prefs));
  if (typeof showToast === 'function') {
    showToast('info', 'Preferensi Notifikasi', 'Pengaturan notifikasi disimpan.');
  }
}

// ── Reset all settings to default ──
function resetAllSettings() {
  if (!confirm('Apakah Anda yakin ingin mereset semua pengaturan ke default?')) return;

  localStorage.removeItem('eprocTheme');
  localStorage.removeItem('eprocCompact');
  localStorage.removeItem('eprocFontSize');
  localStorage.removeItem('eprocNotifPrefs');

  // Reset UI HTML attributes
  document.documentElement.setAttribute('data-theme', 'light');
  document.documentElement.removeAttribute('data-compact');
  document.documentElement.style.removeProperty('--space-md');
  document.documentElement.style.removeProperty('--space-lg');
  document.documentElement.style.removeProperty('font-size');

  // Reload UI control values
  loadSavedSettings();

  if (typeof showToast === 'function') {
    showToast('success', 'Berhasil Direset', 'Semua pengaturan telah dikembalikan ke default.');
  }
}

// ── Load saved settings and set toggle controls ──
function loadSavedSettings() {
  // Theme
  const theme = localStorage.getItem('eprocTheme') || 'light';
  const darkModeToggle = document.getElementById('toggle-dark-mode');
  if (darkModeToggle) darkModeToggle.checked = (theme === 'dark');

  // Compact Mode
  const compact = localStorage.getItem('eprocCompact') === 'true';
  const compactToggle = document.getElementById('toggle-compact');
  if (compactToggle) compactToggle.checked = compact;

  // Font size (Default is '14' = Sedang)
  const fontSize = localStorage.getItem('eprocFontSize') || '14';
  const fontSelect = document.getElementById('select-fontsize');
  if (fontSelect) fontSelect.value = fontSize;

  // Notif preferences (Default matches screenshot: Document Review is OFF, others ON)
  const prefs = JSON.parse(localStorage.getItem('eprocNotifPrefs') || '{"approved": true, "rejected": true, "document": false, "incoming": true, "po": true}');
  ['approved', 'rejected', 'document', 'incoming', 'po'].forEach(key => {
    const el = document.getElementById('notif-' + key);
    if (el) el.checked = prefs[key] !== false;
  });
}

// ── On page load ──
document.addEventListener('DOMContentLoaded', loadSavedSettings);
</script>
@endpush
@endsection
