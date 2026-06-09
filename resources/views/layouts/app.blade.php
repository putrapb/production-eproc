<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Dashboard') — E-Procurement BNI</title>
  <meta name="description" content="Sistem E-Procurement IT Infrastructure Management BNI">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Design System -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  @stack('styles')
</head>
<body>

<div class="app-shell">
  <!-- ─── SIDEBAR ─────────────────────────────────── -->
  <aside class="sidebar" id="sidebar">

    <!-- Logo Area -->
    <div class="sidebar-logo" style="display: flex; align-items: center; gap: var(--space-sm); border-bottom: 1px solid var(--color-secondary-active);">
      <img src="{{ asset('images/bni-logo.png') }}" alt="Logo BNI" class="h-8 w-auto" style="height: 32px; width: auto; background: #fff; border-radius: var(--radius-sm); padding: 4px; flex-shrink: 0;">
      <div class="sidebar-logo-text">
        E-Procurement
        <span>IT Infrastructure</span>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <div class="caption-upper sidebar-section-label">Menu</div>

      <a href="{{ route('dashboard') }}"
         class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/>
          <rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
        </svg>
        Dashboard
      </a>

      <div class="caption-upper sidebar-section-label">Pengadaan</div>

      <a href="{{ route('tickets.index') }}"
         class="sidebar-nav-item {{ request()->routeIs('tickets.index', 'tickets.show', 'tickets.edit') ? 'active' : '' }}">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
          <rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>
        </svg>
        Tiket Pengadaan
      </a>

      @if(auth()->user()->isRequester())
      <a href="{{ route('tickets.create') }}"
         class="sidebar-nav-item {{ request()->routeIs('tickets.create') ? 'active' : '' }}">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>
        </svg>
        Pengajuan Baru
      </a>
      @endif

      <div class="caption-upper sidebar-section-label">Akun</div>

      <a href="{{ route('profile.show') }}"
         class="sidebar-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
        </svg>
        Profil Saya
      </a>

      <form method="POST" action="{{ route('logout') }}" id="logout-form">
        @csrf
        <button type="submit" class="sidebar-nav-item" style="width:100%; text-align:left; background:none; border:none; cursor:pointer;">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
          </svg>
          Keluar
        </button>
      </form>
    </nav>

    <!-- User Info -->
    <div class="sidebar-user">
      <div class="sidebar-avatar">{{ auth()->user()->initials }}</div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
        <div class="sidebar-user-role">{{ auth()->user()->role_label }}</div>
      </div>
    </div>
  </aside>

  <!-- Sidebar backdrop (mobile) -->
  <div class="sidebar-backdrop" id="sidebar-backdrop" onclick="closeSidebar()"></div>

  <!-- ─── MAIN CONTENT ──────────────────────────── -->
  <div class="main-content">

    <!-- TOPBAR -->
    <header class="topbar">
      <div class="flex items-center gap-md">
        <button class="hamburger-btn" onclick="openSidebar()" aria-label="Buka menu">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
          <a href="{{ route('dashboard') }}" class="breadcrumb-item">E-Procurement</a>
          @hasSection('breadcrumb')
            <span class="breadcrumb-sep">/</span>
            @yield('breadcrumb')
          @endif
        </nav>
      </div>

      <div class="topbar-actions">
        <!-- Avatar + Dropdown -->
        <div class="topbar-avatar" id="avatar-btn" onclick="toggleAvatarMenu()">
          {{ auth()->user()->initials }}
          <div class="topbar-avatar-menu" id="avatar-menu">
            <a href="{{ route('profile.show') }}" class="topbar-menu-item">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
              </svg>
              Profil Saya
            </a>
            <hr class="topbar-menu-divider">
            <button onclick="document.getElementById('logout-form').submit()" class="topbar-menu-item" style="width:100%; border:none; background:none; cursor:pointer; text-align:left;">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 16l4-4m0 0l-4-4m4 4H7"/>
              </svg>
              Keluar
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- FLASH MESSAGES via Toast -->
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div id="flash-toast-area"></div>
    @endif

    <!-- PAGE CONTENT -->
    @yield('content')

  </div>
</div>

<!-- ─── TOAST CONTAINER ───────────────────────── -->
<div class="toast-container" id="toast-container"></div>

<!-- ─── SCRIPTS ──────────────────────────────── -->
<script>
// ── Sidebar toggle ──────────────────────────────
function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebar-backdrop').classList.add('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-backdrop').classList.remove('open');
}

// ── Avatar menu toggle ──────────────────────────
function toggleAvatarMenu() {
  document.getElementById('avatar-menu').classList.toggle('open');
}
document.addEventListener('click', function(e) {
  const btn  = document.getElementById('avatar-btn');
  const menu = document.getElementById('avatar-menu');
  if (!btn.contains(e.target)) menu.classList.remove('open');
});

// ── Toast system ────────────────────────────────
function showToast(type, title, message, duration = 4000) {
  const icons = {
    success: `<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>`,
    error:   `<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>`,
    warning: `<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`,
    info:    `<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M12 11v5"/></svg>`,
  };
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <div class="toast-icon ${type}">${icons[type] || icons.info}</div>
    <div style="flex:1">
      <div class="toast-title">${title}</div>
      ${message ? `<div class="toast-message">${message}</div>` : ''}
    </div>
    <button class="toast-close" onclick="this.parentElement.remove()">×</button>
  `;
  container.appendChild(toast);
  if (duration > 0) setTimeout(() => toast.remove(), duration);
}

// ── Auto-fire flash session toasts ─────────────
@if(session('success'))
  showToast('success', 'Berhasil', @json(session('success')));
@endif
@if(session('error'))
  showToast('error', 'Terjadi Kesalahan', @json(session('error')));
@endif
@if(session('warning'))
  showToast('warning', 'Peringatan', @json(session('warning')));
@endif
@if(session('info'))
  showToast('info', 'Informasi', @json(session('info')));
@endif

// ── Modal helpers ────────────────────────────────
function openModal(id) {
  document.getElementById(id).classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow = '';
}
// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});
</script>

@stack('scripts')
</body>
</html>
