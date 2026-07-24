@extends('layouts.app')

@section('title', 'Pengaturan')

@section('breadcrumb')
  <span class="breadcrumb-item">Pengaturan</span>
@endsection

@section('content')
<div class="page-content">
  <div style="max-width: 720px; margin: 0 auto;">

    {{-- Page Header --}}
    <div style="margin-bottom: 28px;">
      <h1 style="font-size: 24px; font-weight: 700; color: var(--color-text); margin: 0 0 4px;">Pengaturan</h1>
      <p style="font-size: 14px; color: var(--color-muted); margin: 0;">Kelola preferensi dan keamanan akun Anda.</p>
    </div>

    {{-- Ganti Password --}}
    <div class="card" style="margin-bottom: 20px;">
      <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border);">
        <div style="display: flex; align-items: center; gap: 10px;">
          <div style="width: 36px; height: 36px; background: var(--color-primary-soft); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="16" height="16" fill="none" stroke="var(--color-primary)" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
          </div>
          <div>
            <div style="font-weight: 600; font-size: 15px; color: var(--color-text);">Keamanan Akun</div>
            <div style="font-size: 12px; color: var(--color-muted);">Ubah kata sandi akun Anda</div>
          </div>
        </div>
      </div>
      <div style="padding: 20px 24px;">
        <form action="{{ route('profile.password') }}" method="POST">
          @csrf
          @method('PATCH')
          <div style="display: flex; flex-direction: column; gap: 16px;">
            <div>
              <label class="form-label" for="current_password">Password Saat Ini</label>
              <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Masukkan password saat ini">
            </div>
            <div>
              <label class="form-label" for="password">Password Baru</label>
              <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password baru">
            </div>
            <div>
              <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
              <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
            </div>
            <div style="display: flex; justify-content: flex-end;">
              <button type="submit" class="btn btn-primary">Simpan Password</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- Info Akun --}}
    <div class="card">
      <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border);">
        <div style="display: flex; align-items: center; gap: 10px;">
          <div style="width: 36px; height: 36px; background: var(--color-primary-soft); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="16" height="16" fill="none" stroke="var(--color-primary)" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
            </svg>
          </div>
          <div>
            <div style="font-weight: 600; font-size: 15px; color: var(--color-text);">Informasi Akun</div>
            <div style="font-size: 12px; color: var(--color-muted);">Detail akun yang terdaftar di sistem</div>
          </div>
        </div>
      </div>
      <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--color-border);">
          <span style="font-size: 13px; color: var(--color-muted);">Nama Lengkap</span>
          <span style="font-size: 13px; font-weight: 500; color: var(--color-text);">{{ auth()->user()->name }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--color-border);">
          <span style="font-size: 13px; color: var(--color-muted);">Email</span>
          <span style="font-size: 13px; font-weight: 500; color: var(--color-text);">{{ auth()->user()->email }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0;">
          <span style="font-size: 13px; color: var(--color-muted);">Role</span>
          <span class="badge badge-primary">{{ auth()->user()->role_label }}</span>
        </div>
        <div style="margin-top: 4px;">
          <a href="{{ route('profile.show') }}" class="btn btn-secondary" style="font-size: 13px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Edit Profil Lengkap
          </a>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
