<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Masuk') | E-Procurement Pejompongan</title>
  <meta name="description" content="Login Sistem E-Procurement IT Infrastructure Management Pejompongan">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
</head>
<body style="background: var(--color-surface-soft);">

<div class="auth-shell">
  <!-- ─── LEFT BRAND PANEL ─────────────────────── -->
  <div class="auth-brand-panel" style="background: linear-gradient(rgba(0, 104, 133, 0.6), rgba(0, 104, 133, 0.6)), url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat; background-blend-mode: multiply; background-color: #006885;">
    <div style="width:56px; height:56px; background: rgba(255,255,255,0.15); border-radius: var(--radius-md); display:flex; align-items:center; justify-content:center; margin-bottom: var(--space-xl); flex-shrink:0;">
      <svg width="30" height="30" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
    </div>

    <div>
      <div class="auth-brand-title">Helpdesk E-Procurement</div>
      <div style="color:rgba(255,255,255,0.55); font-size:12px; text-align:center; margin-top:4px; letter-spacing:1px; text-transform:uppercase;">IT Infrastructure Project Management</div>
    </div>

    <div class="auth-brand-subtitle">
      Sistem pengadaan digital untuk Tim IT Infrastructure Project Management Pejompongan. Cepat, terstruktur, dan dapat diaudit.
    </div>

    <div style="display:flex; flex-direction:column; gap:12px; width:100%; max-width:280px;">
      <div class="auth-brand-feature">
        <div class="auth-brand-feature-icon">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 12l2 2 4-4"/><path d="M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
          </svg>
        </div>
        Smart Validation 
      </div>
      <div class="auth-brand-feature">
        <div class="auth-brand-feature-icon">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
          </svg>
        </div>
        Persetujuan Berjenjang
      </div>
      <div class="auth-brand-feature">
        <div class="auth-brand-feature-icon">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        Form Tiket Pengadaan
      </div>
      <div class="auth-brand-feature">
        <div class="auth-brand-feature-icon">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        Dashboard Monitoring Real-time
      </div>
    </div>

    <div style="font-size:12px; color:rgba(255,255,255,0.35); margin-top:auto;">
      © {{ date('Y') }} E-Procurement Pejompongan
    </div>
  </div>

  <!-- ─── RIGHT FORM PANEL ────────────────────── -->
  <div class="auth-form-panel flex flex-col justify-center min-h-screen" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <div class="auth-form-card">
      @yield('content')
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script>
function showToast(type, title, message, duration = 4000) {
  const icons = {
    success: `<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>`,
    error:   `<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>`,
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

@if(session('success')) showToast('success', 'Berhasil', @json(session('success'))); @endif
@if(session('error'))   showToast('error', 'Gagal', @json(session('error'))); @endif
@if(session('info'))    showToast('info', 'Informasi', @json(session('info'))); @endif
</script>
@stack('scripts')
</body>
</html>
