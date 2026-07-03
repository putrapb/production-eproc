@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<div style="display: flex; justify-content: center; margin-bottom: var(--space-lg);">
  <div style="width:44px; height:44px; background: linear-gradient(135deg, var(--color-primary), #6366f1); border-radius: var(--radius-md); display:flex; align-items:center; justify-content:center; margin-bottom: var(--space-sm);">
    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
  </div>
</div>
<div class="auth-form-title">Masuk ke Sistem</div>
<div class="auth-form-subtitle">Masuk dengan akun Anda untuk melanjutkan.</div>

{{-- Validation Errors --}}
@if($errors->any())
<div class="alert alert-error mb-md">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
  <div>{{ $errors->first() }}</div>
</div>
@endif

<form method="POST" action="{{ route('login') }}" id="login-form">
  @csrf

  <div class="form-group">
    <label class="form-label" for="email">Email Korporat</label>
    <input
      type="email"
      id="email"
      name="email"
      class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
      value="{{ old('email') }}"
      placeholder="nama@perusahaan.co.id"
      required
      autocomplete="email"
      autofocus
    >
    @error('email')
      <div class="form-error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
      <label class="form-label" for="password" style="margin:0">Password</label>
      <a href="{{ route('password.request') }}" style="font-size:12px; color:var(--color-primary); font-weight:500;">Lupa Password?</a>
    </div>
    <div style="position:relative; display:flex; align-items:center;">
      <input
        type="password"
        id="password"
        name="password"
        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
        placeholder="Masukkan password"
        required
        autocomplete="current-password"
        style="padding-right: 40px; width: 100%;"
      >
      <button type="button" onclick="togglePassword()" id="pwd-toggle" style="position:absolute; right:12px; display:flex; align-items:center; justify-content:center; background:none; border:none; padding:0; cursor:pointer; color:var(--color-muted-soft);">
        <svg id="eye-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
      </button>
    </div>
    @error('password')
      <div class="form-error">{{ $message }}</div>
    @enderror
  </div>



  <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
    </svg>
    Masuk
  </button>
</form>

<div style="text-align:center; margin-top:var(--space-xl); font-size:13px; color:var(--color-muted);">
  Belum punya akun?
  <a href="{{ route('register') }}" style="color:var(--color-primary); font-weight:600;">Daftar sekarang</a>
</div>

@push('scripts')
<script>
function togglePassword() {
  const input = document.getElementById('password');
  const icon  = document.getElementById('eye-icon');
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
