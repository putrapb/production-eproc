# Rencana Persiapan Usability Testing & Kesiapan Sistem E-Procurement BNI

Dokumen ini disusun sebagai panduan persiapan menjelang **Usability Testing (UT)** dan implementasi sistem pada infrastruktur Bank BNI. Dokumen ini juga dilengkapi dengan checklist analisis yang dapat diserahkan kepada external Gemini Agent untuk membantu menyusun skenario pengujian dan dokumentasi sistem.

---

## 📌 1. Langkah Persiapan Usability Testing (UT)

Untuk menguji seberapa kompeten, efisien, dan andal sistem ini dalam menyelesaikan masalah pengadaan secara nyata, langkah-langkah persiapan berikut wajib dilakukan:

### A. Penyusunan Skenario & Tugas Pengujian (*Task Scenarios*)
Buat daftar tugas spesifik yang mencerminkan alur kerja nyata BNI. Skenario harus diuji untuk 4 peran pengguna (*User Persona*):
1. **Requester (IT Project Management Staff)**:
   * *Tugas 1*: Mendaftar akun baru dengan NIP Divisi IT Infrastructure BNI Pejompongan.
   * *Tugas 2*: Melakukan verifikasi OTP yang dikirim ke email.
   * *Tugas 3*: Membuat pengajuan baru dengan melampirkan dokumen Izin Prinsip (PDF).
   * *Tugas 4*: Menjalankan Smart Validation, mendeteksi kondisi over-budget, dan mengajukan silang dana (*cross-fund*).
2. **PFA (Procurement Fixed Assets)**:
   * *Tugas 1*: Meninjau dokumen Izin Prinsip yang dikirim Requester (menyetujui atau meminta revisi).
   * *Tugas 2*: Menerbitkan dokumen PO PDF setelah mendapat persetujuan akhir dari Division Head.
3. **Department Head**:
   * *Tugas 1*: Meninjau pengajuan tiket dan meneruskannya (*forward*) ke Division Head dengan catatan khusus.
4. **Division Head**:
   * *Tugas 1*: Memberikan keputusan akhir persetujuan (*approve*) atau penolakan (*decline*).

### B. Penyiapan Lingkungan Pengujian (*Test Environment*)
* **Uji Coba Berkas**: Siapkan berkas PDF dummy (Izin Prinsip) berukuran di bawah 10 MB di komputer/laptop yang akan digunakan oleh penguji.
* **Database Reset**: Jalankan perintah `php artisan migrate:fresh --seed` sebelum UT dimulai agar data pagu anggaran (budget) dan akun pengaju kembali bersih (default).
* **Mail Server (Mailtrap/Log)**: Pastikan koneksi Mailtrap atau driver email aktif agar penguji dapat memverifikasi OTP saat registrasi akun baru.

### C. Penentuan Metrik Keberhasilan (*Usability Metrics*)
* **Task Completion Rate (TCR)**: Persentase tugas yang berhasil diselesaikan oleh peserta tanpa bantuan instruktur.
* **Time on Task**: Durasi waktu yang dibutuhkan peserta untuk menyelesaikan satu alur kerja (misal: dari membuat pengajuan hingga Smart Validation).
* **Error Rate**: Frekuensi kesalahan pengisian form atau kesalahan alur status yang dilakukan peserta.
* **System Usability Scale (SUS)**: Kuesioner standar post-test (10 pertanyaan skala likert) untuk mengukur tingkat kepuasan dan kemudahan penggunaan aplikasi.

---

## 🟢 2. Ringkasan Kesiapan Sistem (*System Readiness Summary*)

Sistem saat ini sudah **sangat siap** secara fungsionalitas backend dan frontend untuk diujicobakan. Berikut ringkasan modul yang sudah siap:

1. **Modul Autentikasi Pegawai BNI**:
   * Auto-derivasi role berdasarkan jabatan riil di BNI.
   * Validasi NIP IT Infrastructure Pejompongan.
   * Sistem verifikasi OTP email (sudah diproteksi dari brute-force).
2. **Modul Pengadaan & Validasi Pintar**:
   * Pengajuan tiket pengadaan barang/jasa dengan lampiran dokumen Izin Prinsip.
   * Smart Validation 4-Gate (Deteksi duplikat, nominal harga wajar, klasifikasi otomatis CAPEX/OPEX, kunci saldo budget, dan pengajuan silang dana).
3. **Rantai Approval Sesuai Jabatan**:
   * Halaman dashboard dinamis berdasar role.
   * Pengoperasian status transisi yang aman (dilindungi transaksi database untuk mencegah double-lock/spending).
   * Hak akses terbatas (IDOR protection aktif).
4. **Output Dokumen PO**:
   * Generator berkas PDF PO berdesain premium BNI dengan struktur tabel item.
   * Native browser PDF preview langsung tanpa auto-download yang mengganggu (ramah IDM).

---

## 📋 3. Checklist Prompt untuk External Gemini Agent

Gunakan prompt di bawah ini untuk diserahkan ke Gemini Agent lain (di luar sistem ini) guna menganalisis kebutuhan dokumentasi teknis dan skenario Usability Testing secara terperinci.

***

### 📋 COPY-PASTE PROMPT & CHECKLIST DI BAWAH INI:

```markdown
Halo Gemini, saya sedang mempersiapkan Usability Testing (UT) dan implementasi aplikasi E-Procurement BNI Divisi IT Infrastructure Management. Aplikasi ini menggunakan Laravel, PostgreSQL (via Supabase), dan Blade templates.

Tolong bantu saya menganalisis kebutuhan sistem dan buatkan dokumen persiapan Usability Testing dengan fokus pada 4 persona (Requester, PFA, Dept Head, Div Head) dan alur Smart Validation (klasifikasi CAPEX/OPEX & lock anggaran).

Gunakan panduan checklist analisis berikut untuk menyusun output Anda:

### 🗳️ Checklist Tugas Analisis Gemini Agent:
- [ ] 1. **Penyusunan Skenario Kasus UT Riil**:
  - Tuliskan 3 skenario uji kasus pengadaan (Kasus Normal, Kasus Revisi Berkas, dan Kasus Pagu Anggaran Kurang / Silang Dana).
  - Tentukan langkah detail step-by-step untuk setiap peran/user.
- [ ] 2. **Kuesioner Evaluasi (SUS & Kepuasan)**:
  - Buat daftar 10 pertanyaan standar System Usability Scale (SUS) yang disesuaikan dengan konteks perbankan BNI.
  - Buat daftar pertanyaan wawancara kualitatif pasca-UT untuk mengukur tingkat efisiensi sistem.
- [ ] 3. **Draft Formulir Penilaian Pengamat (Observer Sheet)**:
  - Buat format tabel untuk diisi oleh pengamat selama UT berjalan (kolom: Kode Tugas, Peran, Status Keberhasilan (Lulus/Gagal/Dengan Bantuan), Catatan Kebingungan UI, Durasi Waktu).
- [ ] 4. **Identifikasi Kebutuhan Infrastruktur Implementasi Bank BNI**:
  - Analisis kebutuhan server minimum (PHP version, DB PostgreSQL, redis/queue server, https ssl, dsb) untuk menjalankan sistem ini pada infrastruktur server lokal/internal Bank BNI.
  - Susun rekomendasi konfigurasi keamanan web server untuk melindungi folder /storage/ agar file script berbahaya (.php/.js) tidak dapat dieksekusi secara remote.
- [ ] 5. **Draft Panduan Pengguna Ringkas (User Manual Outline)**:
  - Buat struktur kerangka buku panduan pengguna (User Manual) untuk masing-masing peran agar mempermudah implementasi ke divisi IT BNI.
```
***
##### Catatan Feedback:
*Setelah external Gemini Agent memberikan responsnya, silakan salin kembali rekomendasinya ke sini untuk kita implementasikan pada file dokumentasi sistem kita!*
