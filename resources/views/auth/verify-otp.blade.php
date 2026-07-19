@extends('layouts.guest')

@section('title', 'Verifikasi OTP')

@section('content')
<div style="text-align:center; margin-bottom:var(--space-lg);">
  <div style="width:64px; height:64px; background:var(--color-info-soft); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto var(--space-md);">
    <svg width="28" height="28" fill="none" stroke="var(--color-info)" stroke-width="1.8" viewBox="0 0 24 24">
      <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
    </svg>
  </div>
  <div class="auth-form-title" style="margin-bottom:var(--space-xs);">Verifikasi Email</div>
  <div class="auth-form-subtitle">
    Kode OTP 6 digit telah dikirimkan ke<br>
    <strong style="color:var(--color-ink);">{{ $email }}</strong>
  </div>
</div>

@if($errors->any())
<div class="alert alert-error mb-md">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
  <div>{{ $errors->first() }}</div>
</div>
@endif

<form method="POST" action="{{ route('otp.verify') }}" id="otp-form">
  @csrf

  <input type="hidden" name="otp_code" id="otp-combined">

  <div class="otp-grid" id="otp-grid">
    @for($i = 0; $i < 6; $i++)
      <input
        type="text"
        class="otp-box"
        maxlength="1"
        inputmode="numeric"
        pattern="[0-9]"
        autocomplete="one-time-code"
        id="otp-{{ $i }}"
      >
    @endfor
  </div>

  <div style="text-align:center; font-size:13px; color:var(--color-muted); margin-bottom:var(--space-lg);">
    Kode berlaku selama <strong>10 menit</strong>
  </div>

  <button type="submit" class="btn btn-primary w-full" style="justify-content:center;" id="verify-btn" disabled>
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M9 12l2 2 4-4"/><path d="M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
    </svg>
    Verifikasi OTP
  </button>
</form>

<div style="text-align:center; margin-top:var(--space-xl); font-size:13px; color:var(--color-muted);">
  Tidak menerima kode?
  <form method="POST" action="{{ route('otp.resend') }}" style="display:inline;">
    @csrf
    <button type="submit" style="background:none; border:none; cursor:pointer; color:var(--color-primary); font-weight:600; font-size:13px; padding:0;">
      Kirim Ulang
    </button>
  </form>
</div>

<div style="text-align:center; margin-top:var(--space-sm); font-size:13px;">
  <a href="{{ route('register') }}" style="color:var(--color-muted);">← Kembali ke Pendaftaran</a>
</div>

@push('scripts')
<script>
const boxes = document.querySelectorAll('.otp-box');
const combined = document.getElementById('otp-combined');
const verifyBtn = document.getElementById('verify-btn');
const form = document.getElementById('otp-form');

function syncCombined() {
  const val = Array.from(boxes).map(b => b.value).join('');
  combined.value = val;
  verifyBtn.disabled = val.length < 6;
}

boxes.forEach((box, i) => {
  box.addEventListener('input', function() {
    const val = this.value.replace(/\D/g, '');
    this.value = val.slice(-1);
    if (val && i < boxes.length - 1) boxes[i + 1].focus();
    syncCombined();
  });

  box.addEventListener('keydown', function(e) {
    if (e.key === 'Backspace' && !this.value && i > 0) {
      boxes[i - 1].focus();
      boxes[i - 1].value = '';
      syncCombined();
    }
  });

  box.addEventListener('paste', function(e) {
    e.preventDefault();
    const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    pasted.split('').forEach((char, idx) => {
      if (boxes[idx]) boxes[idx].value = char;
    });
    boxes[Math.min(pasted.length, 5)].focus();
    syncCombined();
  });
});

// Auto-submit when all 6 digits filled
form.addEventListener('submit', function() {
  syncCombined();
});

// Countdown timer display (cosmetic, no enforcement)
let seconds = 600;
const timerEl = document.createElement('div');
timerEl.style.cssText = 'text-align:center; font-size:12px; color:var(--color-muted-soft); margin-top:8px;';
document.getElementById('verify-btn').after(timerEl);

const countdownInterval = setInterval(() => {
  seconds--;
  const m = Math.floor(seconds / 60).toString().padStart(2,'0');
  const s = (seconds % 60).toString().padStart(2,'0');
  timerEl.textContent = `Kode kedaluwarsa dalam ${m}:${s}`;
  if (seconds <= 0) {
    clearInterval(countdownInterval);
    timerEl.textContent = 'Kode telah kedaluwarsa. Kirim ulang OTP.';
    timerEl.style.color = 'var(--color-error-text)';
  }
}, 1000);
</script>
@endpush
@endsection

