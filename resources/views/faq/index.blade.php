@extends('layouts.app')

@section('title', 'Bantuan & FAQ')

@section('breadcrumb')
  <span class="breadcrumb-item">Bantuan & FAQ</span>
@endsection

@section('content')
<div class="page-content">
  <div style="max-width: 760px; margin: 0 auto;">

    {{-- Page Header --}}
    <div style="margin-bottom: 32px; text-align: center;">
      <div style="width: 56px; height: 56px; background: var(--color-primary-soft); color: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>
        </svg>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: var(--color-ink); margin: 0 0 8px;">Pusat Bantuan & FAQ</h1>
      <p style="font-size: 15px; color: var(--color-muted); margin: 0; line-height: 1.6;">
        Jawaban dari pertanyaan yang sering diajukan mengenai sistem Helpdesk E-Procurement dan aturan pengajuan IT.
      </p>
    </div>

    {{-- Kategori 1: Alur Pengajuan --}}
    <div class="faq-category">
      <h2 class="faq-category-title">Alur Pengajuan & Persetujuan</h2>
      
      <div class="faq-card card">
        <div class="faq-item">
          <button class="faq-question" onclick="toggleFaq(this)">
            <span>Bagaimana alur persetujuan tiket di sistem ini?</span>
            <svg class="faq-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="faq-answer">
            <p>Alur persetujuan terdiri dari beberapa tahap:</p>
            <ol style="margin-top: 8px; padding-left: 20px;">
              <li><strong>Draft/Validasi:</strong> Tiket baru melewati Smart Validation (Gate 1-4).</li>
              <li><strong>Review (Team Leader):</strong> Tiket divalidasi dokumen pendukungnya dan dapat diminta revisi.</li>
              <li><strong>Approval (Dept Head):</strong> Tiket disetujui atau ditolak secara final. Jika disetujui, anggaran resmi dipotong.</li>
              <li><strong>Form Terbit:</strong> Team Leader menerbitkan Purchase Order / Form Pengadaan (PDF).</li>
            </ol>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" onclick="toggleFaq(this)">
            <span>Kapan Form Pengadaan (PO) bisa saya download?</span>
            <svg class="faq-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="faq-answer">
            <p>Form Pengadaan (PO) hanya dapat diunduh setelah tiket mencapai status <strong>Form Diterbitkan</strong>. Sebelumnya, tiket harus mendapatkan persetujuan final (Approved) dari Department Head, barulah Team Leader akan men-generate dokumen tersebut.</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Kategori 2: Smart Validation & Budget --}}
    <div class="faq-category" style="margin-top: 32px;">
      <h2 class="faq-category-title">Smart Validation & Anggaran</h2>
      
      <div class="faq-card card">
        <div class="faq-item">
          <button class="faq-question" onclick="toggleFaq(this)">
            <span>Apa itu Smart Validation?</span>
            <svg class="faq-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="faq-answer">
            <p>Smart Validation adalah sistem cerdas yang berjalan dalam 4 tahap (Gate) otomatis saat Anda mengajukan tiket baru:</p>
            <ul style="margin-top: 8px; padding-left: 20px;">
              <li><strong>Gate 1 (Duplikasi):</strong> Mengecek apakah Anda memiliki pengajuan yang mirip/kembar.</li>
              <li><strong>Gate 2 (Nominal):</strong> Memastikan besaran nominal yang diajukan wajar (contoh: di bawah 99 Miliar).</li>
              <li><strong>Gate 3 (Klasifikasi):</strong> Mengklasifikasikan jenis pengeluaran (CAPEX/OPEX) berdasarkan PSAK 16 & 19.</li>
              <li><strong>Gate 4 (Anggaran):</strong> Memeriksa ketersediaan saldo dan mengunci budget secara sementara.</li>
            </ul>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" onclick="toggleFaq(this)">
            <span>Mengapa tiket saya otomatis diubah tipenya oleh sistem (CAPEX ke OPEX)?</span>
            <svg class="faq-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="faq-answer">
            <p>Sistem ini mengacu pada standar akuntansi keuangan (PSAK 16 & 19). Jika Anda mengajukan Lisensi Sistem (Software) namun dalam item barang mengandung kata kunci seperti <em>"Subscription", "Langganan", "Sewa", atau "Cloud"</em>, maka sistem akan mengubahnya dari CAPEX (Aset Tidak Berwujud) menjadi <strong>OPEX (Biaya Operasional)</strong>, karena aset tersebut tidak menjadi milik penuh perusahaan.</p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" onclick="toggleFaq(this)">
            <span>Mengapa tiket saya berstatus "Over Budget" padahal masih ada saldo?</span>
            <svg class="faq-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="faq-answer">
            <p>Sistem E-Procurement menerapkan aturan pembatasan serapan bulanan. Rata-rata budget bulanan dihitung dari <em>(Total Anggaran / 12)</em>. Jika tiket pengajuan Anda melebihi <strong>130% dari limit bulanan rata-rata</strong>, maka sistem akan mencegah transaksi tersebut (Over Budget) demi menjaga stabilitas arus kas. Anda diwajibkan melakukan prosedur <strong>Silang Dana (Cross-Funding)</strong> dengan persetujuan khusus.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
  .faq-category-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-trout);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    padding-bottom: var(--space-sm);
    margin-bottom: var(--space-sm);
  }
  .faq-card {
    padding: 0;
    overflow: hidden;
  }
  .faq-item {
    border-bottom: 1px solid var(--color-hairline);
  }
  .faq-item:last-child {
    border-bottom: none;
  }
  .faq-question {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: var(--space-lg);
    font-size: 15px;
    font-weight: 500;
    color: var(--color-ink);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.2s ease;
  }
  .faq-question:hover {
    background: var(--color-surface-soft);
  }
  .faq-icon {
    color: var(--color-muted);
    transition: transform 0.3s ease;
  }
  .faq-question.active .faq-icon {
    transform: rotate(180deg);
  }
  .faq-answer {
    padding: 0 var(--space-lg);
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out, padding 0.3s ease-out;
    background: var(--color-canvas);
  }
  .faq-answer p {
    font-size: 14px;
    color: var(--color-body);
    line-height: 1.6;
    margin: 0;
  }
</style>

<script>
  function toggleFaq(btn) {
    const isActive = btn.classList.contains('active');
    
    // Optional: Close all other accordions
    document.querySelectorAll('.faq-question').forEach(el => {
      el.classList.remove('active');
      el.nextElementSibling.style.maxHeight = null;
      el.nextElementSibling.style.paddingBottom = '0';
    });

    if (!isActive) {
      btn.classList.add('active');
      const answer = btn.nextElementSibling;
      // Scroll height + some padding
      answer.style.maxHeight = answer.scrollHeight + 32 + 'px';
      answer.style.paddingBottom = '24px';
    }
  }
</script>
@endsection
