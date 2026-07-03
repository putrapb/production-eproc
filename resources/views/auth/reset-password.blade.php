@extends('layouts.guest')

@section('title', 'Atur Ulang Password')

@section('content')
<div style="text-align:center; margin-bottom:var(--space-lg);">
  <div style="width:64px; height:64px; background:var(--color-info-soft); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto var(--space-md);">
    <svg width="28" height="28" fill="none" stroke="var(--color-info)" stroke-width="1.8" viewBox="0 0 24 24">
      <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
  </div>
  <div class="auth-form-title" style="margin-bottom:var(--space-xs);">Reset Password</div>
  <div class="auth-form-subtitle">
    Masukkan kode OTP yang dikirim ke:<br>
    <strong style="color:var(--color-ink);">{{ $email }}</strong>
  </div>
</div>

@if($errors->any())
<div class="alert alert-error mb-md">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
  <div>{{ $errors->first() }}</div>
</div>
@endif

<form method="POST" action="{{ route('password.store') }}" id="otp-reset-form">
  @csrf

  <div class="form-group">
    <label class="form-label" for="otp_code">Kode OTP</label>
    <input type="text" id="otp_code" name="otp_code"
      class="form-control {{ $errors->has('otp_code') ? 'is-invalid' : '' }}"
      placeholder="Masukkan 6 digit OTP" required maxlength="6" autofocus>
    @error('otp_code') <div class="form-error">{{ $message }}</div> @enderror
  </div>

  <div class="form-group">
    <label class="form-label" for="password">Password Baru</label>
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
    <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
    <div class="password-wrapper">
      <input type="password" id="password_confirmation" name="password_confirmation"
        class="form-control" placeholder="Ulangi password baru" required autocomplete="new-password">
      <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password_confirmation', 'eye-icon-conf')">
        <svg id="eye-icon-conf" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
      </button>
    </div>
  </div>

  <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
    Reset Password
  </button>
</form>

<div style="text-align:center; margin-top:var(--space-xl); font-size:13px; color:var(--color-muted);">
  Kembali ke <a href="{{ route('login') }}" style="color:var(--color-primary); font-weight:600;">Halaman Masuk</a>
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
