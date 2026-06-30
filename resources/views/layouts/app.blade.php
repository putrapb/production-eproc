<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Dashboard') | E-Procurement Pejompongan</title>
  <meta name="description" content="Sistem E-Procurement IT Infrastructure Management Pejompongan">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Design System -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1.0.5">

  <style>
    /* Bulletproof fix for badge-form-generated styling to bypass any server-side static file cache */
    .badge-form-generated {
      background: #E8F9ED !important;
      color: #1A7A36 !important;
    }
    /* Bulletproof fix for buttons to bypass any CSS caching */
    .btn-success {
      background: #34C759 !important;
      color: #fff !important;
    }
    .btn-success:hover {
      background: #1A7A36 !important;
      color: #fff !important;
    }
    .btn-danger {
      background: #E53E3E !important;
      color: #fff !important;
    }
    .btn-danger:hover {
      background: #C53030 !important;
      color: #fff !important;
    }
    /* Fixed bottom action panel layout to respect sidebar width on desktop */
    .action-panel {
      position: fixed !important;
      bottom: 0 !important;
      left: 240px !important;
      right: 0 !important;
      z-index: 95 !important;
      background: var(--color-canvas) !important;
      border-top: 1px solid var(--color-hairline) !important;
      box-shadow: 0 -4px 16px rgba(0,0,0,0.08) !important;
      padding: var(--space-md) var(--space-xl) !important;
      border-radius: 0 !important;
      max-width: none !important;
      width: auto !important;
      transform: none !important;
    }
    .page-content {
      padding-bottom: 100px !important;
    }
    @media (max-width: 1024px) {
      .action-panel {
        left: 0 !important;
      }
    }
  </style>

  @stack('styles')
</head>
<body>

<div class="app-shell">
  <!-- ─── SIDEBAR ─────────────────────────────────── -->
  <aside class="sidebar" id="sidebar">

    <!-- Logo Area -->
    <div class="sidebar-logo" style="display: flex; align-items: center; gap: var(--space-sm); border-bottom: 1px solid var(--color-secondary-active);">
      <div style="width:32px; height:32px; background: linear-gradient(135deg, var(--color-primary), #6366f1); border-radius: var(--radius-sm); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
      </div>
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
        {{-- Notification Bell --}}
        <div style="position:relative; display:flex; align-items:center;">
          <button id="notif-bell" onclick="toggleNotifDropdown()" title="Notifikasi"
            style="position:relative; background:none; border:none; cursor:pointer; padding:4px; color:#9ca3af; display:flex; align-items:center; justify-content:center; border-radius:6px; transition:color 0.15s;"
            onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 01-3.46 0"/>
            </svg>
            {{-- Bintik merah --}}
            <span id="notif-badge" style="display:none; position:absolute; top:2px; right:2px; width:8px; height:8px; background:#ef4444; border-radius:50%; border:2px solid #fff; z-index:1;"></span>
          </button>

          {{-- Notification Dropdown — hidden by default --}}
          <div id="notif-dropdown" onclick="event.stopPropagation()"
            style="display:none; position:absolute; top:calc(100% + 8px); right:0; width:320px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.12); z-index:9999; flex-direction:column; overflow:hidden;">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 16px 10px; border-bottom:1px solid #f3f4f6;">
              <span style="font-weight:600; font-size:14px; color:#111827;">Notifikasi</span>
              <button onclick="markAllRead()" style="font-size:12px; color:var(--color-primary); background:none; border:none; cursor:pointer; font-weight:500;">Tandai semua dibaca</button>
            </div>
            <div id="notif-list" style="max-height:340px; overflow-y:auto;">
              {{-- Diisi JS saat diklik --}}
            </div>
          </div>
        </div>

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
function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebar-backdrop').classList.add('open');
  document.body.classList.add('sidebar-open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-backdrop').classList.remove('open');
  document.body.classList.remove('sidebar-open');
}

// ── Avatar menu toggle ──────────────────────────
function toggleAvatarMenu() {
  document.getElementById('avatar-menu').classList.toggle('open');
}
document.addEventListener('click', function(e) {
  const btn  = document.getElementById('avatar-btn');
  const menu = document.getElementById('avatar-menu');
  if (btn && menu && !btn.contains(e.target)) menu.classList.remove('open');
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

// ── Notification Bell (Polling every 5s) ─────────
const notifBell      = document.getElementById('notif-bell');
const notifDropdown  = document.getElementById('notif-dropdown');
const notifBadge     = document.getElementById('notif-badge');
const notifList      = document.getElementById('notif-list');
let notifOpen = false;

const typeIcon = {
  ticket_submitted: '📋',
  ticket_reviewed:  '✅',
  ticket_revised:   '📝',
  ticket_validated: '🔍',
  ticket_forwarded: '➡️',
  ticket_approved:  '🎉',
  ticket_declined:  '❌',
  po_generated:     '📄',
};

function toggleNotifDropdown() {
  notifOpen = !notifOpen;
  if (notifDropdown) {
    notifDropdown.style.display = notifOpen ? 'flex' : 'none';
    notifDropdown.style.flexDirection = 'column';
  }
  if (notifOpen) fetchNotifications();
}

function closeNotifDropdown() {
  notifOpen = false;
  if (notifDropdown) notifDropdown.style.display = 'none';
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
  const wrapper = notifBell ? notifBell.closest('div') : null;
  if (wrapper && !wrapper.contains(e.target)) closeNotifDropdown();
});

function renderNotifications(data) {
  // Update badge dot
  if (notifBadge) {
    if (data.unread_count > 0) {
      notifBadge.style.display = 'block';
    } else {
      notifBadge.style.display = 'none';
    }
  }

  if (!notifList) return;

  if (!data.notifications || data.notifications.length === 0) {
    notifList.innerHTML = '<div style="padding:32px 16px; text-align:center; color:var(--color-muted); font-size:13px;"><div style="font-size:28px; margin-bottom:8px;">🔔</div>Belum ada notifikasi di sini.</div>';
    return;
  }

  notifList.innerHTML = data.notifications.map(n => `
    <a href="${n.ticket_url || '#'}" class="notif-item ${n.read ? '' : 'unread'}"
       onclick="markRead(${n.id}, this)" style="text-decoration:none; display:block;">
      <div style="display:flex; gap:10px; align-items:flex-start;">
        <span style="font-size:18px; flex-shrink:0;">${typeIcon[n.type] || '🔔'}</span>
        <div style="flex:1; min-width:0;">
          <div style="font-weight:600; font-size:13px; color:var(--color-text);">${n.title}</div>
          <div style="font-size:12px; color:var(--color-muted); margin-top:2px; line-height:1.4; word-break:break-word;">${n.message}</div>
          <div style="font-size:11px; color:var(--color-muted); margin-top:4px;">${n.time}</div>
        </div>
        ${!n.read ? '<span style="width:8px; height:8px; background:var(--color-primary); border-radius:50%; flex-shrink:0; margin-top:4px;"></span>' : ''}
      </div>
    </a>
  `).join('');
}

// Badge-only fetch (background polling) — hanya update dot, tidak buka dropdown
function fetchBadge() {
  fetch('/notifications', {
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
  })
  .then(r => r.json())
  .then(data => {
    if (notifBadge) {
      notifBadge.style.display = data.unread_count > 0 ? 'block' : 'none';
    }
    if (notifOpen) renderNotifications(data);
  })
  .catch(() => {});
}

function fetchNotifications() {
  fetch('/notifications', {
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
  })
  .then(r => r.json())
  .then(data => renderNotifications(data))
  .catch(() => {});
}

function markRead(id, el) {
  fetch(`/notifications/${id}/read`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
  });
  if (el) el.classList.remove('unread');
  el.querySelector('span[style*="border-radius:50%"]')?.remove();
}

function markAllRead() {
  fetch('/notifications/read-all', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
  })
  .then(() => fetchNotifications());
}

// Polling badge setiap 5 detik (background, tidak ganggu dropdown)
setInterval(fetchBadge, 5000);
</script>

@stack('scripts')
</body>
</html>
