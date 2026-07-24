# Implementation Plan: Revisi Sidang Skripsi (Sistem E-Procurement)

Dokumen ini berisi daftar instruksi teknis untuk mengimplementasikan revisi dari dosen penguji (Bpk Gora, Dudu, Mba Tia) terkait sistem E-Procurement. 

Tolong bertindak sebagai AI Software Engineer dan eksekusi instruksi di bawah ini satu per satu.

---

## 🏗️ Konteks Arsitektur Sistem Saat Ini (PENTING DIBACA)
Sebelum melakukan modifikasi, mohon pahami *current state* dari sistem yang baru saja kami refaktor:
1. **Smart Validation di Awal:** Seluruh proses Smart Validation (Pengecekan Duplikat, Nominal, CAPEX/OPEX, dan Budget) sudah dipindah ke **awal**. Requester mengeksekusi ini dan melihat hasilnya di *pop-up modal* pada halaman `create.blade.php` sebelum klik "Ajukan Pengadaan".
2. **Tidak Ada Status `need_to_validate`:** Setelah dokumen disetujui oleh Team Leader (`TicketController@review`), tiket akan **langsung loncat** ke status `pending_dept_head` (Department Head). 
3. **Waktu Budget Lock:** Anggaran tidak dikunci saat Requester membuat tiket, melainkan **dikunci otomatis (Temporary Lock)** saat Team Leader menyetujui dokumen (`$budget->lock()`).
4. **Permanent Lock:** Pengurangan anggaran permanen (`$budget->permanentDeduct()`) terjadi saat Department Head menyetujui pengadaan.

---

## 🛠️ Task 1: Refaktor UI/UX (Layout & Settings)
**Tujuan:** Merapikan tata letak menu agar lebih profesional dan menambahkan menu pengaturan.
- **Instruksi Eksekusi:** 
  1. Pindahkan menu "Profil Saya" dan tombol "Keluar" dari Sidebar (kiri bawah) ke Navbar (atas kanan) dalam bentuk *dropdown* profil.
  2. Tambahkan ikon *Settings* (gerigi) di Navbar (atas kanan) di sebelah profil.
  3. Buatkan *route* baru `/settings` dan view `resources/views/settings/index.blade.php` kosongan dengan desain mengikuti tema saat ini.
- **Context Files:** `resources/views/layouts/app.blade.php`, `routes/web.php`

---

## 🛡️ Task 2: Fitur Arsip & Audit (DIP - Dissemination Information Package)
**Tujuan:** Menyediakan bukti alur audit sesuai teori *Information Package* dari keilmuan Sains Informasi.
- **Instruksi Eksekusi:**
  1. Tambahkan tombol "Download Arsip (PDF)" di file `show.blade.php` yang **HANYA** muncul jika status tiket sudah final (Disetujui/Ditolak).
  2. Buat endpoint baru di `TicketController` untuk men-generate file PDF tersebut.
  3. Desain PDF tersebut harus merangkum seluruh rincian tiket beserta tabel **Approval Logs** (siapa saja yang menyetujui, dan kapan), yang berfungsi sebagai *Preservation Description Information (PDI)*. Gunakan library seperti `barryvdh/laravel-dompdf`.
- **Context Files:** `resources/views/tickets/show.blade.php`, `app/Http/Controllers/TicketController.php`, `app/Models/Ticket.php`

---

## 🤖 Task 3: Automated Testing (Smart Validation)
**Tujuan:** Membuktikan bahwa inovasi *Smart Validation* berjalan dengan benar (Automated Test).
- **Instruksi Eksekusi:**
  1. Buat file pengujian baru `SmartValidationTest.php` menggunakan PHPUnit (Jalankan command `php artisan make:test SmartValidationTest`).
  2. Tulis *test case* untuk memvalidasi keempat fungsi *Gate* pada `SmartValidationService`:
     - **Gate 1:** Pengecekan tiket duplikat.
     - **Gate 2:** Pengecekan batas kewajaran nominal (> 99 Miliar).
     - **Gate 3:** Logika klasifikasi CAPEX/OPEX.
     - **Gate 4:** Pengecekan ketersediaan anggaran (*Budget lock*).
- **Context Files:** `tests/Feature/SmartValidationTest.php`, `app/Services/SmartValidationService.php`

---

## 🕵️ Task 4: Anonimisasi Data (Data Privacy)
**Tujuan:** Menjaga kerahasiaan data instansi dan informan sebelum publikasi laporan.
- **Instruksi Eksekusi:**
  1. Cek semua file Seeder dan ganti referensi nama instansi BNI menjadi nama *dummy* (contoh: "PT Bank ABC" atau "Perusahaan X").
  2. Ganti nama-nama tokoh asli menjadi anonim (misal: "Bapak A - Dept Head", "Budi - Tim Leader") tanpa merusak relasi database dan role-nya.
- **Context Files:** `database/seeders/UserSeeder.php`, `database/seeders/DatabaseSeeder.php` (dan file seeder lainnya yang memuat nama asli).
