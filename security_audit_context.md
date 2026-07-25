# 🛡️ Konteks Audit Keamanan Sistem Helpdesk E-Procurement BNI

Dokumen ini berisi pemetaan fitur-fitur baru dan modifikasi pada sistem E-Procurement BNI (berbasis Laravel 8+) untuk kebutuhan **Security Audit (White-box Review)** dengan fokus pada **Routing**, **Authentication & Authorization Policy**, **Endpoint Security**, serta **Business Logic Vulnerability**.

---

## 1. Fitur Arsip Audit / PDF Download (DIP — Dissemination Information Package)
Fitur ini ditambahkan sebagai implementasi konsep *Sains Informasi* (pengarsipan digital DIP & PDI dengan validasi integritas hash SHA-256) untuk mendownload arsip rekam jejak tiket pengadaan yang sudah final (Approved/Declined).

* **Routing & Middleware (`routes/web.php`):**
  ```php
  Route::middleware(['auth', 'verified'])->group(function () {
      Route::get('/tickets/{ticket}/download-audit', [TicketController::class, 'downloadAudit'])
          ->name('tickets.download-audit');
  });
  ```
* **Controller Function (`App\Http\Controllers\TicketController@downloadAudit`):**
  * **Input/Binding:** Menggunakan Route Model Binding (`Ticket $ticket`).
  * **Authorization check:** Memanggil `$this->authorizeView($ticket, $request->user())`. *(Catatan desain sistem: Semua authenticated user diizinkan melihat semua tiket demi transparansi & tracking, namun batasan aksi dibatasi pada level method dan middleware).*
  * **State/Status Gate (Business Logic check):** Memvalidasi status tiket harus `approved` atau `declined`. Jika tidak, mengembalikan HTTP 403 `abort(403)`.
  * **Library & PDF Generation:** Menggunakan library `barryvdh/laravel-dompdf` dengan setelan `isHtml5ParserEnabled => true` dan `isRemoteEnabled => false` (mencegah Server-Side Request Forgery / SSRF atau eksploitasi Remote Code via external links di HTML/Blade template `tickets.audit-pdf`).
* **Fokus Audit yang Disarankan untuk Review:**
  * Apakah penonaktifan `isRemoteEnabled => false` pada DomPDF sudah cukup memitigasi SSRF dan Local File Inclusion (LFI) pada render HTML?
  * Verifikasi apakah potensi XSS pada Blade template `tickets.audit-pdf` (misalnya pada output judul tiket atau catatan audit) ter-escape dengan aman (`{{ $var }}`).

---

## 2. Refaktor UI & Pengaturan Preferensi User (Settings & FAQ)
Pemindahan logika menu akun/profil ke topbar dropdown, penambahan halaman Settings (Tampilan & Preferensi Notifikasi) dan halaman Pusat Bantuan (FAQ).

* **Routing & Middleware (`routes/web.php`):**
  ```php
  Route::middleware(['auth', 'verified'])->group(function () {
      Route::get('/settings', fn () => view('settings.index'))->name('settings.index');
      Route::get('/faq', fn () => view('faq.index'))->name('faq.index');
  });
  ```
* **Implementasi Fitur (Client-Side & Stateless Views):**
  * Halaman `/settings` dan `/faq` diimplementasikan secara **stateless (frontend-only via `localStorage`)** dan tidak melakukan operasi write/update ke database, sehingga tidak memerlukan POST endpoints atau migration baru.
  * Inline Script pada `layouts.app` (`<head>`) membaca `localStorage.getItem('eprocTheme')` sebelum proses render CSS untuk mitigasi anti-flicker pada Dark Mode.
* **Fokus Audit yang Disarankan untuk Review:**
  * Pastikan penggunaan inline JavaScript di dalam file blade (terkait toggle pengaturan dan Dark Mode) tidak rentan terhadap penginjeksian script berbahaya atau pembatasan Content Security Policy (CSP) jika diterapkan nantinya.

---

## 3. Smart Validation Service (4-Gate Approval & Budget Enforcement Engine)
Penyempurnaan arsitektur validasi 4-Gate (`App\Services\SmartValidationService.php`) yang memproses tiket dari status draft/need validation sampai ke Department Head (DH).

* **Alur Kerja & Endpoints Terkait (`routes/web.php`):**
  * Berada di bawah middleware `['auth', 'verified']` dan dilindungi rute khusus ber-middleware `role:requester`:
    ```php
    Route::post('/tickets/{ticket}/validate', [TicketController::class, 'runSmartValidation'])->name('tickets.validate');
    Route::post('/tickets/preview-validation', [TicketController::class, 'previewValidation'])->name('tickets.preview-validation');
    Route::post('/tickets/{ticket}/cross-fund', [TicketController::class, 'applyCrossFund'])->name('tickets.cross-fund');
    ```
* **Logika Kerja 4-Gate (di `SmartValidationService::run`):**
  * **Gate 1 (Duplikasi):** Mengecek kesesuaian judul (`$ticket->title`) dengan pengajuan sebelumnya yang aktif (tidak ditolak/declined) menggunakan query Eloquent.
  * **Gate 2 (Nominal):** Hard fail (`abort`/return false) jika nominal $\le 0$. Soft warning apabila nominal melebihi batas (misal $> 99$ Miliar) yang memerlukan override confirmation (`nominalConfirmed = true`).
  * **Gate 3 (Klasifikasi PSAK 16 & 19 - Bugfix):** 
    * Modifikasi terbaru memperbaiki pencarian kata kunci aset (seperti *"saas", "cloud", "subscription"*, dll.). 
    * Kode yang dimodifikasi memanggil relasi items via Pluck: `$itemNamesRaw = $ticket->items->pluck('item_name')->implode(' ');` dan memvalidasi tipe CAPEX vs OPEX berdasarkan pola kata kunci tersebut.
    * Jika terdapat perubahan klasifikasi dari saran sistem, maka dikunci dalam DB Transaction: `DB::transaction(fn() => $ticket->update(['expenditure_type' => $classifiedType]))`.
  * **Gate 4 (Ketersediaan Anggaran & Financial Locking):**
    * Dijalankan dalam satu blok `DB::transaction()`.
    * Menggunakan aturan batas bulanan: Jika penarikan mekorok/melebihi batas bulanan rata-rata plus toleransi (`$monthlyLimit * 1.30`), sistem memaksa status `over_budget` untuk memicu silang dana.
    * Pemanggilan `Budget::lock($amount)` (temporary reserve) dipicu jika verifikasi gate lulus sebelum tiket dipindahkan ke status `pending_dept_head`.
    * Pemanggilan `Budget::permanentDeduct($amount)` dilakukan setelah persetujuan akhir dari Department Head (`pending_dept_head` $\rightarrow$ `approved`).
* **Fokus Audit yang Disarankan untuk Review:**
  * **Race Condition / Concurrency Audit:** Dalam `Gate 4`, periksa apakah proses pemotongan saldo / penguncian saldo (`Budget::lock()` dan pengecekan `$available`) sudah memitigasi race condition saat banyak request paralel diproses (misal: pentingkah penambahan pessimistic lock seperti `->lockForUpdate()` atau atomic column decrement pada Eloquent?).
  * **Business Logic & Bypassing Analysis:** Apakah parameter konfirmasi dari user seperti `duplicateConfirmed=true`, `nominalConfirmed=true`, dan `classificationConfirmed=true` diproses secara aman dari payload HTTP agar user tidak bisa memanipulasi atau menembus **Gate 4** (yang wajib bersifat hard-lock)?
  * **SQL Injection & Mass Assignment:** Verifikasi apakah `pluck('item_name')` serta method update pada tiket & budget rentan terhadap mass-assignment vulnerability atau improper input sanitization.

---

## 4. Daftar File Utama untuk Diverifikasi (Code Checklist)
1. `app/Http/Controllers/TicketController.php` *(khusus method `downloadAudit`, `runSmartValidation`, `previewValidation`)*
2. `app/Services/SmartValidationService.php` *(seluruh method `run`, `gate3Classification`, dan blok DB transaction pada Gate 4)*
3. `app/Models/Budget.php` & `app/Models/Ticket.php` *(terkait properti `$fillable`, constant status, serta logic method `lock` dan `permanentDeduct`)*
4. `routes/web.php` *(pemeriksaan peletakan middleware `auth`, `verified`, dan `role:*`)*
5. `resources/views/tickets/audit-pdf.blade.php` *(uji keamanan XSS pada blade escaping saat render ke PDF)*
6. `tests/Feature/SmartValidation/SmartValidationTest.php` *(test case Pest untuk skenario security bypass & logic unit penguji)*
