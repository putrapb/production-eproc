@extends('layouts.app')

@section('title', 'Pengaturan')

@section('breadcrumb')
  <span class="breadcrumb-item">Pengaturan</span>
@endsection

@section('content')
<div class="page-content">
  <div style="max-width: 640px; margin: 0 auto;">

    {{-- Page Header --}}
    <div style="margin-bottom: 28px;">
      <h1 style="font-size: 24px; font-weight: 700; color: var(--color-ink); margin: 0 0 4px;">Pengaturan</h1>
      <p style="font-size: 14px; color: var(--color-muted); margin: 0;">Sesuaikan tampilan dan preferensi notifikasi sistem.</p>
    </div>

    {{-- ── SECTION 1: APPEARANCE ── --}}
    <div class="card" style="margin-bottom: 20px;">
      <div style="padding: 18px 24px; border-bottom: 1px solid var(--color-hairline); display: flex; align-items: center; gap: 12px;">
        <div style="width: 36px; height: 36px; background: var(--color-primary-soft); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
          <svg width="16" height="16" fill="none" stroke="var(--color-primary)" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
          </svg>
        </div>
        <div>
          <div style="font-weight: 600; font-size: 15px; color: var(--color-ink);">Tampilan (Appearance)</div>
          <div style="font-size: 12px; color: var(--color-muted);">Preferensi tampilan antarmuka sistem</div>
        </div>
      </div>

      <div style="padding: 8px 24px 16px;">

        {{-- Dark Mode Toggle --}}
        <div class="settings-row">
          <div>
            <div class="settings-row-label">Mode Gelap (Dark Mode)</div>
            <div class="settings-row-desc">Mengubah tampilan menjadi tema gelap untuk kenyamanan di ruangan redup</div>
          </div>
          <label class="toggle-switch" title="Toggle dark mode">
            <input type="checkbox" id="toggle-dark-mode" onchange="applyTheme(this.checked)">
            <span class="toggle-slider"></span>
          </label>
        </div>

        {{-- Compact Mode Toggle --}}
        <div class="settings-row">
          <div>
            <div class="settings-row-label">Mode Ringkas (Compact)</div>
            <div class="settings-row-desc">Memperkecil jarak antar elemen untuk menampilkan lebih banyak informasi</div>
          </div>
          <label class="toggle-switch" title="Toggle compact mode">
            <input type="checkbox" id="toggle-compact" onchange="applyCompact(this.checked)">
            <span class="toggle-slider"></span>
          </label>
        </div>

        {{-- Font Size --}}
        <div class="settings-row">
          <div>
            <div class="settings-row-label">Ukuran Teks</div>
            <div class="settings-row-desc">Sesuaikan ukuran teks dasar sistem</div>
          </div>
          <select id="select-fontsize" onchange="applyFontSize(this.value)"
            style="height: 34px; padding: 0 12px; border: 1px solid var(--color-hairline); border-radius: var(--radius-md); font-size: 13px; color: var(--color-ink); background: var(--color-canvas); cursor: pointer;">
            <option value="14">Normal (14px)</option>
            <option value="15">Sedang (15px)</option>
            <option value="16">Besar (16px)</option>
          </select>
        </div>

      </div>
    </div>

    {{-- ── SECTION 2: PREFERENSI NOTIFIKASI ── --}}
    <div class="card" style="margin-bottom: 20px;">
      <div style="padding: 18px 24px; border-bottom: 1px solid var(--color-hairline); display: flex; align-items: center; gap: 12px;">
        <div style="width: 36px; height: 36px; background: var(--color-secondary-soft); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
          <svg width="16" height="16" fill="none" stroke="var(--color-secondary)" stroke-width="2" viewBox="0 0 24 24">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
          </svg>
        </div>
        <div>
          <div style="font-weight: 600; font-size: 15px; color: var(--color-ink);">Preferensi Notifikasi</div>
          <div style="font-size: 12px; color: var(--color-muted);">Pilih jenis notifikasi yang ingin Anda terima</div>
        </div>
      </div>

      <div style="padding: 8px 24px 16px;">

        <div class="settings-row">
          <div>
            <div class="settings-row-label">Tiket Disetujui</div>
            <div class="settings-row-desc">Notifikasi saat tiket pengadaan Anda disetujui</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" id="notif-approved" onchange="saveNotifPref('approved', this.checked)" checked>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="settings-row">
          <div>
            <div class="settings-row-label">Tiket Ditolak / Revisi</div>
            <div class="settings-row-desc">Notifikasi saat tiket perlu direvisi atau ditolak</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" id="notif-rejected" onchange="saveNotifPref('rejected', this.checked)" checked>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="settings-row">
          <div>
            <div class="settings-row-label">Dokumen Diterima / Ditolak</div>
            <div class="settings-row-desc">Notifikasi saat Team Leader mengevaluasi dokumen pendukung</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" id="notif-document" onchange="saveNotifPref('document', this.checked)" checked>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="settings-row">
          <div>
            <div class="settings-row-label">Tiket Masuk (Team Leader / DH)</div>
            <div class="settings-row-desc">Notifikasi saat ada tiket baru yang perlu ditinjau</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" id="notif-incoming" onchange="saveNotifPref('incoming', this.checked)" checked>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="settings-row">
          <div>
            <div class="settings-row-label">Form Pengadaan Terbit</div>
            <div class="settings-row-desc">Notifikasi saat Form Pengadaan (PO) sudah dapat diunduh</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" id="notif-po" onchange="saveNotifPref('po', this.checked)" checked>
            <span class="toggle-slider"></span>
          </label>
        </div>

      </div>
    </div>

    {{-- Reset Button --}}
    <div style="text-align: right; margin-top: 8px;">
      <button onclick="resetAllSettings()" class="btn btn-secondary" style="font-size: 13px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
        </svg>
        Reset ke Default
      </button>
    </div>

  </div>
</div>

@push('scripts')
<script>
// ── Apply theme (dark/light) ──
function applyTheme(isDark) {
  const theme = isDark ? 'dark' : 'light';
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('eprocTheme', theme);
}

// ── Apply compact mode ──
function applyCompact(isCompact) {
  document.documentElement.setAttribute('data-compact', isCompact ? 'true' : 'false');
  localStorage.setItem('eprocCompact', isCompact ? 'true' : 'false');
  // Apply smaller spacing
  document.documentElement.style.setProperty('--space-md', isCompact ? '12px' : '16px');
  document.documentElement.style.setProperty('--space-lg', isCompact ? '18px' : '24px');
}

// ── Apply font size ──
function applyFontSize(size) {
  document.documentElement.style.fontSize = size + 'px';
  localStorage.setItem('eprocFontSize', size);
}

// ── Save notification preference ──
function saveNotifPref(key, value) {
  const prefs = JSON.parse(localStorage.getItem('eprocNotifPrefs') || '{}');
  prefs[key] = value;
  localStorage.setItem('eprocNotifPrefs', JSON.stringify(prefs));
}

// ── Reset all settings ──
function resetAllSettings() {
  if (!confirm('Reset semua pengaturan ke default?')) return;

  localStorage.removeItem('eprocTheme');
  localStorage.removeItem('eprocCompact');
  localStorage.removeItem('eprocFontSize');
  localStorage.removeItem('eprocNotifPrefs');

  // Reset UI
  document.documentElement.setAttribute('data-theme', 'light');
  document.documentElement.removeAttribute('data-compact');
  document.documentElement.style.removeProperty('--space-md');
  document.documentElement.style.removeProperty('--space-lg');
  document.documentElement.style.removeProperty('font-size');

  loadSavedSettings();
}

// ── Load saved settings and apply to toggles ──
function loadSavedSettings() {
  // Theme
  const theme = localStorage.getItem('eprocTheme') || 'light';
  document.getElementById('toggle-dark-mode').checked = (theme === 'dark');

  // Compact
  const compact = localStorage.getItem('eprocCompact') === 'true';
  document.getElementById('toggle-compact').checked = compact;

  // Font size
  const fontSize = localStorage.getItem('eprocFontSize') || '14';
  document.getElementById('select-fontsize').value = fontSize;

  // Notif preferences
  const prefs = JSON.parse(localStorage.getItem('eprocNotifPrefs') || '{}');
  ['approved', 'rejected', 'document', 'incoming', 'po'].forEach(key => {
    const el = document.getElementById('notif-' + key);
    if (el) el.checked = prefs[key] !== false; // default ON
  });
}

// ── On page load ──
document.addEventListener('DOMContentLoaded', loadSavedSettings);
</script>
@endpush
@endsection
