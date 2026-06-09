@extends('layouts.guest')

@section('title', 'Lupa Password')

@section('content')
<div style="text-align:center; margin-bottom:var(--space-lg);">
  <div style="width:64px; height:64px; background:var(--color-info-soft); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto var(--space-md);">
    <svg width="28" height="28" fill="none" stroke="var(--color-info)" stroke-width="1.8" viewBox="0 0 24 24">
      <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
  </div>
  <div class="auth-form-title" style="margin-bottom:var(--space-xs);">Lupa Password</div>
  <div class="auth-form-subtitle">Masukkan email korporat Anda untuk menerima kode OTP verifikasi.</div>
</div>

@if($errors->any())
<div class="alert alert-error mb-md">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
  <div>{{ $errors->first() }}</div>
</div>
@endif

<form method="POST" action="{{ route('password.email') }}">
  @csrf

  <div class="form-group">
    <label class="form-label" for="email">Email Korporat</label>
    <input type="email" id="email" name="email"
      class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
      value="{{ old('email') }}" placeholder="nama@bni.co.id" required autofocus>
    @error('email') <div class="form-error">{{ $message }}</div> @enderror
  </div>

  <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
    Kirim OTP
  </button>
</form>

<div style="text-align:center; margin-top:var(--space-xl); font-size:13px; color:var(--color-muted);">
  Kembali ke <a href="{{ route('login') }}" style="color:var(--color-primary); font-weight:600;">Halaman Masuk</a>
</div>
@endsection
