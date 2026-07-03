@extends('layouts.app')

@section('title', 'Profil Saya')

@section('breadcrumb')
  <span class="breadcrumb-active">Profil Saya</span>
@endsection

@section('content')
<div class="page-header">
  <div class="page-header-left">
    <h1>Profil Saya</h1>
    <p>Kelola informasi akun dan keamanan Anda</p>
  </div>
</div>

<div class="page-content">
  <div class="profile-grid">

    {{-- Profile Card --}}
    <div class="card">
      <div class="card-header">
        <div class="heading-sm">Informasi Akun</div>
      </div>
      <div class="card-body">
        {{-- Avatar Display --}}
        <div style="display:flex; align-items:center; gap:var(--space-lg); margin-bottom:var(--space-xl); padding-bottom:var(--space-xl); border-bottom:1px solid var(--color-hairline-soft);">
          <div style="width:64px; height:64px; border-radius:50%; background:var(--color-primary-soft); color:var(--color-primary); font-size:22px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            {{ auth()->user()->initials }}
          </div>
          <div>
            <div class="heading-sm">{{ auth()->user()->name }}</div>
            <div class="body-sm text-muted">{{ auth()->user()->email }}</div>
            <span class="badge badge-category" style="margin-top:var(--space-xs);">{{ auth()->user()->role_label }}</span>
          </div>
        </div>

        <div class="form-group">
          <div class="detail-field-label">NIP</div>
          <div class="detail-field-value" style="font-family:monospace; font-size:15px;">{{ auth()->user()->employee->nip ?? '-' }}</div>
        </div>
        <div class="form-group">
          <div class="detail-field-label">Nama Lengkap</div>
          <div class="detail-field-value">{{ auth()->user()->name }}</div>
        </div>
        <div class="form-group">
          <div class="detail-field-label">Jabatan</div>
          <div class="detail-field-value">{{ auth()->user()->employee->position ?? '-' }}</div>
        </div>
        <div class="form-group">
          <div class="detail-field-label">Departemen</div>
          <div class="detail-field-value">{{ auth()->user()->employee->department ?? '-' }}</div>
        </div>
        <div class="form-group">
          <div class="detail-field-label">Divisi</div>
          <div class="detail-field-value">{{ auth()->user()->employee->division ?? '-' }}</div>
        </div>
        <div class="form-group">
          <div class="detail-field-label">Role Sistem</div>
          <div class="detail-field-value">{{ auth()->user()->role_label }}</div>
        </div>
        <div class="form-group">
          <div class="detail-field-label">Bergabung Sejak</div>
          <div class="detail-field-value">{{ auth()->user()->created_at->format('d M Y') }}</div>
        </div>
      </div>
    </div>

    {{-- Change Password Card --}}
    <div class="card">
      <div class="card-header">
        <div class="heading-sm">Ubah Password</div>
      </div>
      <div class="card-body">
        @if($errors->has('current_password') || $errors->has('new_password'))
        <div class="alert alert-error mb-md">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
          <div>{{ $errors->first() }}</div>
        </div>
        @endif

        <form method="POST" action="{{ route('profile.password') }}">
          @csrf

          <div class="form-group">
            <label class="form-label" for="current_password">Password Saat Ini <span class="required">*</span></label>
            <div class="password-wrapper">
              <input type="password" id="current_password" name="current_password"
                class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                placeholder="••••••••" required autocomplete="current-password">
              <button type="button" class="toggle-password" onclick="togglePasswordVisibility('current_password', 'eye-icon-curr')">
                <svg id="eye-icon-curr" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="new_password">Password Baru <span class="required">*</span></label>
            <div class="password-wrapper">
              <input type="password" id="new_password" name="new_password"
                class="form-control {{ $errors->has('new_password') ? 'is-invalid' : '' }}"
                placeholder="Minimal 8 karakter" required autocomplete="new-password">
              <button type="button" class="toggle-password" onclick="togglePasswordVisibility('new_password', 'eye-icon-new')">
                <svg id="eye-icon-new" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="new_password_confirmation">Konfirmasi Password Baru <span class="required">*</span></label>
            <div class="password-wrapper">
              <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                class="form-control"
                placeholder="Ulangi password baru" required autocomplete="new-password">
              <button type="button" class="toggle-password" onclick="togglePasswordVisibility('new_password_confirmation', 'eye-icon-conf')">
                <svg id="eye-icon-conf" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="alert alert-info" style="font-size:13px; padding:var(--space-sm) var(--space-md); margin-bottom:var(--space-lg);">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M12 11v5"/></svg>
            <span>Password harus minimal 8 karakter. Gunakan kombinasi huruf, angka, dan simbol untuk keamanan optimal.</span>
          </div>

          <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Perbarui Password
          </button>
        </form>
      </div>
    </div>

  </div>
</div>

<style>
.profile-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-xl);
  max-width: 900px;
}
@media (max-width: 768px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}
</style>
@push('scripts')
<script>
function togglePasswordVisibility(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  if (input.type === 'password') {
    input.type = 'text';
    icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
  } else {
    input.type = 'password';
    icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
  }
}
</script>
@endpush
@endsection
