@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<div style="display: flex; justify-content: center; margin-bottom: var(--space-lg);">
  <img src="{{ asset('images/bni-logo.png') }}" alt="Logo BNI" class="h-10 w-auto" style="height: 40px; width: auto;">
</div>

<div class="auth-form-title">Selamat Datang</div>
<div class="auth-form-subtitle">Masuk dengan akun korporat BNI Anda.</div>

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
      placeholder="nama@bni.co.id"
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
    <div style="position:relative;">
      <input
        type="password"
        id="password"
        name="password"
        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
        placeholder="Masukkan password"
        required
        autocomplete="current-password"
        style="padding-right: 44px;"
      >
      <button type="button" onclick="togglePassword()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--color-muted);" id="pwd-toggle">
        <svg id="eye-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
      </button>
    </div>
    @error('password')
      <div class="form-error">{{ $message }}</div>
    @enderror
  </div>

  <div style="display:flex; align-items:center; gap:8px; margin-bottom: var(--space-lg);">
    <input type="checkbox" id="remember" name="remember" style="width:16px;height:16px;accent-color:var(--color-primary);cursor:pointer;">
    <label for="remember" style="font-size:13px;color:var(--color-muted);cursor:pointer;">Ingat saya</label>
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
