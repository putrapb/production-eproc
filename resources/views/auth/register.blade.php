@extends('layouts.guest')

@section('title', 'Daftar Akun')

@section('content')
<div style="display: flex; justify-content: center; margin-bottom: var(--space-lg);">
  <div style="width:44px; height:44px; background: linear-gradient(135deg, var(--color-primary), #6366f1); border-radius: var(--radius-md); display:flex; align-items:center; justify-content:center;">
    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
  </div>
</div>

<div class="auth-form-title">Daftar Akun</div>
<div class="auth-form-subtitle">Gunakan NIP dan email Anda untuk mendaftar.</div>

@if($errors->any())
<div class="alert alert-error mb-md">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
  <div>{{ $errors->first() }}</div>
</div>
@endif

<form method="POST" action="{{ route('register') }}">
  @csrf

  <div class="form-group">
    <label class="form-label" for="nip">NIP Karyawan <span class="required">*</span></label>
    <input type="text" id="nip" name="nip"
      class="form-control {{ $errors->has('nip') ? 'is-invalid' : '' }}"
      value="{{ old('nip') }}" placeholder="Nomor Induk Pegawai"
      required maxlength="20" autofocus>
    @error('nip')
      <div class="form-error">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
        {{ $message }}
      </div>
    @enderror
    <div style="font-size:12px; color:var(--color-muted-soft); margin-top:4px;">
      NIP harus terdaftar dalam database karyawan Departemen IT Infrastructure.
    </div>
  </div>

  <div class="form-group">
    <label class="form-label" for="email">Email Korporat <span class="required">*</span></label>
    <input type="email" id="email" name="email"
      class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
      value="{{ old('email') }}" placeholder="nama@perusahaan.co.id"
      required autocomplete="email">
    @error('email') <div class="form-error">{{ $message }}</div> @enderror
  </div>

  <div class="form-group">
    <label class="form-label" for="password">Password <span class="required">*</span></label>
    <div class="password-wrapper">
      <input type="password" id="password" name="password"
        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
        placeholder="Minimal 8 karakter" required autocomplete="new-password">
      <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', 'eye-icon-pass')">
        <svg id="eye-icon-pass" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
      </button>
    </div>
    @error('password') <div class="form-error">{{ $message }}</div> @enderror
  </div>

  <div class="form-group">
    <label class="form-label" for="password_confirmation">Konfirmasi Password <span class="required">*</span></label>
    <div class="password-wrapper">
      <input type="password" id="password_confirmation" name="password_confirmation"
        class="form-control" placeholder="Ulangi password"
        required autocomplete="new-password">
      <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password_confirmation', 'eye-icon-conf')">
        <svg id="eye-icon-conf" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
      </button>
    </div>
  </div>

  <div style="background:var(--color-info-soft); border-left:3px solid var(--color-info); border-radius:var(--radius-sm); padding:var(--space-sm) var(--space-md); margin-bottom:var(--space-lg); font-size:13px; color:var(--color-info-text);">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;margin-right:4px;vertical-align:middle"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M12 11v5"/></svg>
    Setelah mendaftar, kode OTP dikirimkan ke email korporat Anda untuk verifikasi akun.
  </div>

  <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
    Daftar Akun
  </button>
</form>

<div style="text-align:center; margin-top:var(--space-xl); font-size:13px; color:var(--color-muted);">
  Sudah punya akun? <a href="{{ route('login') }}" style="color:var(--color-primary); font-weight:600;">Masuk di sini</a>
</div>
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
