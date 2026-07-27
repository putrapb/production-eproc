# Design System — Helpdesk E-Procurement BNI
**File ini adalah sumber tunggal kebenaran (Single Source of Truth) design system aplikasi.**
Gunakan file ini sebagai konteks untuk merekonstruksi prototype di Figma/Stitch dengan presisi.

---

## 1. BRAND & IDENTITAS APLIKASI

| Properti | Nilai |
|---|---|
| **Nama Aplikasi** | Helpdesk E-Procurement |
| **Sub-judul** | E-Procurement |
| **Nama Lengkap (Tab/Title)** | `Dashboard \| Helpdesk E-Procurement Pejompongan` |
| **URL Sistem** | `e-procpejompongan.my.id` |
| **Logo** | Icon dokumen/clipboard (SVG: clipboard dengan garis) di dalam kotak gradient (Primary #F15A22 → #6366f1), border-radius 6px, ukuran 32×32px |
| **Font Utama** | Inter (Google Fonts, weight 400/500/600/700) |
| **Font Button** | Arial, sans-serif |

---

## 2. COLOR PALETTE (DESIGN TOKENS)

### Brand Colors
| Token | Hex | Penggunaan |
|---|---|---|
| `--color-primary` | `#F15A22` | CTA utama, link aktif, border aktif sidebar |
| `--color-primary-active` | `#C94A1A` | Hover state tombol primary |
| `--color-primary-disabled` | `#F9C4AD` | Tombol primary disabled |
| `--color-primary-soft` | `#FDE8DC` | Background badge cross-fund, unread notif |
| `--color-secondary` | `#006885` | Sidebar background, filter tab aktif, link, badge CAPEX |
| `--color-secondary-active` | `#004F65` | Active state sidebar item, border bawah sidebar logo |
| `--color-secondary-soft` | `#E0EFF3` | Badge CAPEX background |

### Neutral / Ink
| Token | Hex | Penggunaan |
|---|---|---|
| `--color-ink` | `#1A1C23` | Heading utama, stat value, table title |
| `--color-body` | `#3D4150` | Body text default |
| `--color-body-strong` | `#252833` | Teks bold/emphasis |
| `--color-muted` | `#6B7080` | Label caption, timestamp, breadcrumb |
| `--color-muted-soft` | `#9396A0` | Placeholder, sub-text minor |
| `--color-trout` | `#494E5C` | Tombol secondary, icon default |

### Canvas / Surface
| Token | Hex | Penggunaan |
|---|---|---|
| `--color-canvas` | `#FFFFFF` | Background halaman card, topbar, modal |
| `--color-surface-soft` | `#F7F8FA` | Background halaman utama (body), hover row tabel |
| `--color-surface-card` | `#F0F2F5` | Background table header, badge OPEX/category |
| `--color-surface-sidebar` | `#006885` | Background sidebar keseluruhan |
| `--color-sidebar-active` | `#004F65` | Background menu aktif di sidebar |
| `--color-sidebar-hover` | `#005A72` | Background hover menu sidebar |

### Semantic Colors
| Token | Hex | Penggunaan |
|---|---|---|
| `--color-success` | `#34C759` | Badge approved, progress bar normal |
| `--color-success-soft` | `#E8F9ED` | Background badge approved |
| `--color-success-text` | `#1A7A36` | Teks badge approved |
| `--color-warning` | `#FFCC00` | Badge revision, dot timeline warning |
| `--color-warning-soft` | `#FFF9D6` | Background badge revision |
| `--color-warning-text` | `#7A6000` | Teks badge revision |
| `--color-error` | `#FF383C` | Badge declined, tombol danger, invalid field |
| `--color-error-soft` | `#FFE8E8` | Background badge declined |
| `--color-error-text` | `#B00004` | Teks badge declined |
| `--color-info` | `#0088FF` | Badge pending, dot timeline default |
| `--color-info-soft` | `#E0F0FF` | Background badge pending |
| `--color-info-text` | `#0055CC` | Teks badge pending |

### Borders
| Token | Hex |
|---|---|
| `--color-hairline` | `#E2E5EA` |
| `--color-hairline-soft` | `#EDF0F3` |

### Notification Badge (Dot merah)
- Warna: `#ef4444`
- Ukuran: 8×8px, `border-radius: 50%`, border 2px solid white

---

## 3. TYPOGRAPHY

### Font Family
- **Body & Heading**: `Inter, sans-serif`
- **Tombol**: `Arial, sans-serif`

### Base
- `html font-size`: 16px
- `body font-size`: 14px
- `body line-height`: 1.55
- `body color`: `#3D4150`

### Type Scale (Utility Classes)
| Class | Size | Weight | Line Height | Letter Spacing | Catatan |
|---|---|---|---|---|---|
| `.heading-xl` | 32px | 700 | 1.2 | -0.5px | |
| `.heading-lg` | 24px | 700 | 1.25 | -0.3px | Judul halaman |
| `.heading-md` | 20px | 600 | 1.3 | -0.2px | |
| `.heading-sm` | 16px | 600 | 1.4 | - | Judul card/section |
| `.body-lg` | 16px | 400 | 1.6 | - | |
| `.body-md` | 14px | 400 | 1.55 | - | Default body |
| `.body-sm` | 13px | 400 | 1.5 | - | |
| `.label-lg` | 14px | 500 | 1.4 | - | |
| `.label-md` | 13px | 500 | 1.4 | - | |
| `.label-sm` | 12px | 500 | 1.4 | 0.2px | |
| `.caption` | 12px | 400 | 1.4 | - | Sub-teks card stat |
| `.caption-upper` | 11px | 600 | - | 1.2px | UPPERCASE — Sidebar section label |
| `.data-number` | 28px | 700 | 1.2 | -0.5px | Stat card value |

---

## 4. SPACING TOKENS

| Token | Nilai |
|---|---|
| `--space-xxs` | 4px |
| `--space-xs` | 8px |
| `--space-sm` | 12px |
| `--space-md` | 16px |
| `--space-lg` | 24px |
| `--space-xl` | 32px |
| `--space-xxl` | 48px |

---

## 5. BORDER RADIUS TOKENS

| Token | Nilai | Penggunaan |
|---|---|---|
| `--radius-xs` | 4px | |
| `--radius-sm` | 6px | Tombol sm, logo sidebar |
| `--radius-md` | 8px | Tombol default, form, badge icon |
| `--radius-lg` | 12px | Card, tabel, dropdown |
| `--radius-xl` | 16px | Modal |
| `--radius-xxl` | 24px | |
| `--radius-pill` | 9999px | Filter tab, badge |

---

## 6. SHADOW TOKENS

| Token | Nilai |
|---|---|
| `--shadow-xs` | `0 1px 2px rgba(0,0,0,0.05)` |
| `--shadow-sm` | `0 2px 8px rgba(0,0,0,0.08)` |
| `--shadow-md` | `0 4px 16px rgba(0,0,0,0.10)` |
| `--shadow-lg` | `0 8px 32px rgba(0,0,0,0.12)` |

---

## 7. LAYOUT UTAMA

| Properti | Nilai |
|---|---|
| Lebar Sidebar | 240px (`--sidebar-width`) |
| Tinggi Topbar | 64px (`--topbar-height`) |
| Layout | Flexbox: Sidebar fixed kiri + Main content kanan |
| Main Content margin-left | 240px |
| Page Content padding | 32px (`--space-xl`) |

---

## 8. KOMPONEN: SIDEBAR

### Struktur
```
SIDEBAR (width: 240px, height: 100vh, bg: #006885)
├── Logo Area (height: 64px, border-bottom: 1px solid #004F65, padding: 0 16px)
│   ├── Icon 32×32px [gradient #F15A22→#6366f1, border-radius 6px]
│   │   └── SVG clipboard icon (stroke: #fff, stroke-width: 2)
│   └── Text Block
│       ├── "Helpdesk" (14px, weight 600, color: #fff)
│       └── "E-Procurement" (11px, weight 400, opacity 0.7, color: #fff)
│
├── Nav (flex: 1, padding: 16px, overflow-y: auto)
│   ├── Section Label: "MENU" (11px, weight 600, ls: 1.2px, UPPERCASE, color: rgba(255,255,255,0.5))
│   ├── Nav Item: Dashboard
│   ├── Section Label: "PENGADAAN"
│   ├── Nav Item: Tiket Pengadaan
│   ├── Nav Item: Pengajuan Baru [Requester only]
│   ├── [Spacer flex: 1]
│   ├── [Divider: 1px solid #E2E5EA, margin: 12px 16px]
│   └── Nav Item: Bantuan & FAQ [di bawah divider]
│
└── User Footer (border-top: 1px solid #004F65, padding: 16px)
    ├── Avatar 36×36px [bg: #fff, color: #F15A22, font: 13px 700] — menampilkan inisial nama
    └── Info
        ├── Nama User (13px, weight 500, color: #fff)
        └── Role Label (11px, color: rgba(255,255,255,0.65))
```

### Nav Item
- Height: 44px
- Padding: 0 16px
- Border-radius: 8px
- Font: 14px, weight 500
- Color: `rgba(255,255,255,0.8)`
- Border-left: 3px solid transparent
- **Hover**: bg `#005A72`, color `#fff`
- **Active**: bg `#004F65`, color `#fff`, border-left-color `#F15A22`

### Nav Item Icons (SVG, 20×20, stroke: currentColor, stroke-width: 1.8)
| Menu | SVG Path |
|---|---|
| Dashboard | 4 kotak 2×2 dengan rx: 1.5 |
| Tiket Pengadaan | Clipboard/dokumen dengan list lines |
| Pengajuan Baru | Circle dengan plus sign |
| Bantuan & FAQ | Circle dengan tanda tanya (M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3, M12 17h.01) |

---

## 9. KOMPONEN: TOPBAR

### Struktur
```
TOPBAR (height: 64px, bg: #fff, border-bottom: 1px solid #E2E5EA, shadow-xs, padding: 0 32px)
├── Kiri: Breadcrumb
│   ├── Link "Helpdesk E-Procurement" (14px, color: #6B7080)
│   ├── Separator "/" (color: #9396A0)
│   └── Active page name (14px, weight 500, color: #1A1C23)
│
└── Kanan: Action Area (gap: 16px, items: center)
    ├── [Settings Icon] — gear SVG 18×18, color: #9ca3af, hover: #374151
    ├── [Bell Icon] — bell SVG 18×18, color: #9ca3af, hover: #374151
    │   └── Red dot (8×8px, bg: #ef4444, border: 2px white) — hanya jika ada notif
    └── [Avatar Button] — circle 36×36px, bg: #FDE8DC, color: #F15A22, font: 13px 700
        └── Dropdown Menu (min-width: 180px, border-radius: 12px, shadow-lg)
            ├── "Profil Saya" (icon: user circle)
            ├── "Pengaturan" (icon: gear)
            ├── [Divider]
            └── "Keluar" (icon: logout arrow, color: #3D4150)
```

### Notification Dropdown
- Width: 320px
- Border-radius: 12px
- Shadow: `0 10px 25px rgba(0,0,0,0.12)`
- **Header**: "Notifikasi" (14px, weight 600, color: #111827)
  - Action: "Tandai dibaca" (12px, color: #F15A22)
  - Separator: "|"
  - Action: "Hapus Semua" (12px, color: #ef4444)
- **List**: max-height: 340px, overflow-y: auto

---

## 10. KOMPONEN: BUTTONS

### Tombol Default (`.btn`)
- Font: Arial, 14px, weight 700
- Height: 40px
- Padding: 0 20px
- Border-radius: 8px
- Display: inline-flex, items: center, gap: 8px
- Transition: `background 0.15s ease, transform 0.1s ease`
- Active: `transform: translateY(1px)`
- Disabled: `opacity: 0.6, cursor: not-allowed`

### Varian Tombol
| Class | Background | Text | Hover BG |
|---|---|---|---|
| `.btn-primary` | `#F15A22` | `#fff` | `#C94A1A` |
| `.btn-secondary` | `#fff` | `#494E5C` | `#F0F2F5` (border: 1px solid #E2E5EA) |
| `.btn-orient` | `#006885` | `#fff` | `#004F65` |
| `.btn-danger` | `#FF383C` | `#fff` | `#CC2428` |
| `.btn-ghost` | `transparent` | `#006885` | `#E0EFF3` |
| `.btn-success` | `#34C759` | `#fff` | `#1A7A36` |

### Ukuran Tombol
| Class | Height | Padding | Font Size | Border Radius |
|---|---|---|---|---|
| Default | 40px | 0 20px | 14px | 8px |
| `.btn-sm` | 32px | 0 14px | 13px | 6px |
| `.btn-icon` | 36px | 0 | - | 8px (border: 1px solid #E2E5EA) |

---

## 11. KOMPONEN: BADGES

### Base Badge (`.badge`)
- Padding: 4px 12px
- Border-radius: 9999px (pill)
- Font: 12px, weight 500
- Display: inline-flex, gap: 4px

### Varian Badge Status
| Class | Background | Text Color | Label UI |
|---|---|---|---|
| `.badge-pending-review` | `#E0F0FF` | `#0055CC` | "Menunggu Cek Dokumen" |
| `.badge-need-to-validate` | `#E0F0FF` | `#0055CC` | "Perlu Validasi" |
| `.badge-pending-dept-head` | `#E0F0FF` | `#0055CC` | "Menunggu Dept Head" |
| `.badge-revision` | `#FFF9D6` | `#7A6000` | "Revisi Dokumen" |
| `.badge-declined` | `#FFE8E8` | `#B00004` | "Ditolak" |
| `.badge-approved` | `#E8F9ED` | `#1A7A36` | "Disetujui" |
| `.badge-form-generated` | `#E8F9ED` | `#1A7A36` | "Form Diterbitkan" |
| `.badge-capex` | `#E0EFF3` | `#004F65` | "CAPEX" |
| `.badge-opex` | `#F0F2F5` | `#494E5C` | "OPEX" |
| `.badge-cross-fund` | `#FDE8DC` | `#C94A1A` | "Silang Dana" (border: 1px solid #F9C4AD) |

---

## 12. KOMPONEN: CARDS

### `.card`
- Background: `#fff`
- Border: `1px solid #E2E5EA`
- Border-radius: 12px
- Shadow: `0 2px 8px rgba(0,0,0,0.08)`

### `.card-header`
- Padding: 24px 32px
- Border-bottom: `1px solid #EDF0F3`
- Display: flex, items: center, justify: space-between

### `.card-body`
- Padding: 32px

---

## 13. KOMPONEN: STAT CARDS (Dashboard)

### `.stat-card`
- Background: `#fff`
- Border: `1px solid #E2E5EA`
- Border-radius: 12px
- Shadow: `0 2px 8px rgba(0,0,0,0.08)`
- Padding: 32px
- Display: flex, flex-direction: column, gap: 12px
- **Hover**: `shadow-md, transform: translateY(-1px)`

### Konten Stat Card
- **Icon Area**: 40×40px, border-radius: 8px, warna background sesuai semantic (lihat contoh di bawah)
- **Label** (`.stat-card-label`): 13px, weight 500, color: `#6B7080`, margin-top: 8px
- **Value** (`.stat-card-value`): 28px, weight 700, color: `#1A1C23`, line-height: 1.1, letter-spacing: -0.5px
- **Sub** (`.stat-card-sub`): 12px, color: `#9396A0`

### Grid Stat Card
- Display: grid, `grid-template-columns: repeat(4, 1fr)`, gap: 24px, margin-bottom: 32px

---

## 14. TEKS UI LENGKAP: SELURUH HALAMAN

### A. Halaman Dashboard (`/dashboard`)

**Page Header:**
- H1: `"Dashboard"`
- Sub: `"Selamat datang, [Nama User] — [Role Label]"`
- Button (Requester only): `"Pengajuan Baru"` (btn-primary, icon: plus circle)

**Stat Cards — Requester:**
| No | Label | Sub-teks |
|---|---|---|
| 1 | Total Tiket | Semua pengajuan saya |
| 2 | Pending Review | Sedang diproses |
| 3 | Disetujui | Termasuk form terbit |
| 4 | Ditolak | Pengadaan yang ditolak/dibatalkan |

**Stat Cards — Team Leader:**
| No | Label | Sub-teks |
|---|---|---|
| 1 | Cek Dokumen | Perlu pemeriksaan dokumen |
| 2 | Siap Terbit Form | Disetujui Dept Head |
| 3 | Form Diterbitkan | Total selesai |

**Stat Cards — Department Head:**
| No | Label | Sub-teks |
|---|---|---|
| 1 | Menunggu Keputusan Saya | Perlu keputusan segera |
| 2 | Disetujui | Total persetujuan saya |
| 3 | Ditolak | Total penolakan saya |

**Card Charts:**
- `"Tren Pengajuan Pengadaan"` — Caption: `"Nominal pengajuan tiket per bulan pada tahun [YYYY]"`
- `"Komposisi Penggunaan Anggaran"` — Caption: `"Berdasarkan Asset Class (CAPEX & OPEX Terpakai/Terkunci)"`

**Card Budget:**
- Judul: `"Utilisasi Anggaran"` — Caption: `"Tahun [YYYY]"` — Badge: `"Fiskal [YYYY]"`
- Tab: `"CAPEX"` | `"OPEX"` (style: pill tab, active: bg #006885, white text)
- Row budget: `"Terpakai: Rp [nominal]"` | `"Limit: Rp [nominal]"`

---

### B. Halaman Tiket Pengadaan (`/tickets`)

**Page Header:**
- H1: `"Tiket Pengadaan"`
- Sub: `"Semua tiket pengajuan pengadaan IT"`

**Filter Tabs (Semua | Status tertentu):**
- `Semua` | `Pending Review` | `Revisi Dokumen` | `Menunggu Dept Head` | `Disetujui` | `Ditolak` | `Form Diterbitkan`

**Search Input placeholder:** `"Cari tiket..."`

**Kolom Tabel:**
- `ID` | `Judul Pengajuan` | `Jenis` | `Kategori` | `Nominal` | `Status` | `Tanggal` | `Aksi`

**Status Label System (persis dari model):**
| Status Sistem | Label UI |
|---|---|
| `pending_review` | Menunggu Cek Dokumen |
| `revision` | Revisi Dokumen |
| `need_to_validate` | Perlu Validasi |
| `pending_dept_head` | Menunggu Dept Head |
| `declined` | Ditolak |
| `approved` | Disetujui |
| `form_generated` | Form Diterbitkan |

**Kategori:**
| Nilai DB | Label Display |
|---|---|
| `infrastruktur_utama` | Infrastruktur Utama |
| `lisensi_sistem` | Lisensi Sistem |
| `layanan_pemeliharaan` | Layanan Pemeliharaan |
| `perlengkapan_operasional` | Perlengkapan Operasional |

---

### C. Halaman Pengajuan Baru (`/tickets/create`)

**Page Header:**
- H1: `"Pengajuan Baru"`
- Breadcrumb: `"Helpdesk E-Procurement / Tiket Pengadaan / Pengajuan Baru"`

**Sections & Fields:**
1. **INFORMASI PENGAJUAN**
   - `Judul Pengajuan *` — input, placeholder: `"Nama proyek atau deskripsi singkat"`
   - `Kategori Pengadaan *` — select
     - `Infrastruktur Utama` (server, storage, jaringan)
     - `Lisensi Sistem` (software, SaaS, subscription)
     - `Layanan Pemeliharaan` (jasa, maintenance)
     - `Perlengkapan Operasional` (ATK, peralatan kantor)
   - `Jenis Pengeluaran *` — select
     - `CAPEX` (Aset tetap/tidak berwujud yang dimiliki penuh)
     - `OPEX` (Biaya operasional / langganan berkala)
   - `Deskripsi Kebutuhan *` — textarea, min-height: 120px
   - `PIC (Person In Charge)` — input array

2. **DAFTAR ITEM PENGADAAN**
   - Kolom tabel: `#` | `Nama Item` | `Qty` | `Harga Satuan (Rp)` | `Subtotal` | `Aksi`
   - Max 9 item
   - Footer: `"Maksimal 9 item. Total dihitung otomatis dari Qty × Harga Satuan."`
   - **TOTAL KESELURUHAN**: label bold color primary, nominal bold

3. **DOKUMEN PENDUKUNG**
   - Label: `"Unggah Dokumen (Maks. 10 MB per file, Format: PDF) *"`
   - Sub-field: `"Nama / Deskripsi Dokumen *"` + `"File PDF *"`
   - Button: `"+ Tambah Dokumen Lainnya"` (ghost/secondary)

4. **VENDOR & NAMA VENDOR**
   - `Nama Vendor/Pemasok *` — input

**Action Bar (sticky bottom):**
- Kiri: `"Tombol Cek Validasi Dulu"` icon ✓ — btn-secondary
- Kanan: `"Kirim Pengajuan"` — btn-primary (hanya muncul setelah validasi pass)

**Tombol:**
| Label | Varian | Icon |
|---|---|---|
| `Cek Validasi Dulu` | btn-secondary (outline) | Checkmark circle |
| `Kirim Pengajuan` | btn-primary | - |
| `+ Tambah Item` | btn-ghost | Plus |
| `+ Tambah Dokumen Lainnya` | link/ghost | Plus circle |

---

### D. Halaman Detail Tiket (`/tickets/{id}`)

**Breadcrumb:** `"Helpdesk E-Procurement / Tiket Pengadaan / Detail Tiket"`

**Sections:**
- `"Informasi Pengajuan"` — card dengan field detail
- `"Daftar Item Pengadaan"` — tabel item dengan subtotal
- `"Dokumen Pendukung"` — list dokumen dengan status
- `"Rekam Jejak & Riwayat Persetujuan"` — timeline approval log
- `"Arsip Audit"` — section khusus untuk download DIP (hanya status final)

**Tombol Action (per role/status):**
| Label | Varian | Tampil Untuk |
|---|---|---|
| `Download Form Pengadaan` | btn-orient (secondary teal) | Semua, jika form_generated |
| `Unduh Arsip Audit` | btn-secondary | Semua, jika approved/declined |
| `Jalankan Smart Validation` | btn-primary | Requester, status need_to_validate |
| `Revisi Tiket` | btn-secondary | Requester, status revision |
| `Batalkan` | btn-danger | Requester, pending/revision |
| `Setujui Dokumen` | btn-success | Team Leader, pending_review |
| `Minta Revisi` | btn-secondary | Team Leader, pending_review |
| `Terbitkan Form Pengadaan` | btn-primary | Team Leader, approved |
| `Setujui` | btn-success | Dept Head, pending_dept_head |
| `Tolak` | btn-danger | Dept Head, pending_dept_head |

**Rekam Jejak Timeline:**
- Dot colors: success `#34C759` | warning `#FFCC00` | error `#FF383C` | default `#006885`
- Connector: 2px solid `#E2E5EA`
- Font actor: 13px weight 500 `#1A1C23`
- Font role tag: 11px `#6B7080`
- Font action: 14px `#3D4150`
- Font notes (italic): 13px `#6B7080`
- Font time: 12px `#9396A0`

---

### E. Halaman Profil (`/profile`)

**Breadcrumb:** `"Helpdesk E-Procurement / Profil Saya"`

**Sections:**
1. **Informasi Profil**
   - Avatar besar (initials, bg: primary-soft, color: primary)
   - `Nama Lengkap`, `Email`, `Role`, `Divisi`
2. **Ubah Password**
   - `Password Saat Ini *`
   - `Password Baru *`
   - `Konfirmasi Password Baru *`
   - Button: `"Simpan Password"` (btn-primary)

---

### F. Halaman Pengaturan (`/settings`)

**Breadcrumb:** `"Helpdesk E-Procurement / Pengaturan"`
**Page H1:** `"Pengaturan"`
**Sub:** `"Sesuaikan tampilan dan preferensi notifikasi sistem."`

**Card 1 — Tampilan (Appearance):**
- Header icon: sun SVG (stroke: #006885, bg: #E0EFF3)
- Title: `"Tampilan (Appearance)"`
- Sub: `"Preferensi tampilan antarmuka sistem"`

| Setting Row | Deskripsi | Kontrol |
|---|---|---|
| Mode Gelap (Dark Mode) | Mengubah tampilan menjadi tema gelap untuk kenyamanan di ruangan redup | Toggle Switch |
| Mode Ringkas (Compact) | Memperkecil jarak antar elemen untuk menampilkan lebih banyak informasi | Toggle Switch |
| Ukuran Teks | Sesuaikan ukuran teks dasar sistem | Dropdown |

Dropdown Ukuran Teks options: `Normal (14px)` | `Sedang (15px)` | `Besar (16px)`

**Card 2 — Preferensi Notifikasi:**
- Header icon: bell SVG (stroke: #006885, bg: #E0EFF3)
- Title: `"Preferensi Notifikasi"`
- Sub: `"Pilih jenis notifikasi yang ingin Anda terima"`

| Setting Row | Deskripsi |
|---|---|
| Tiket Disetujui | Notifikasi saat tiket pengadaan Anda disetujui |
| Tiket Ditolak / Revisi | Notifikasi saat tiket perlu direvisi atau ditolak |
| Dokumen Diterima / Ditolak | Notifikasi saat Team Leader mengevaluasi dokumen pendukung |
| Tiket Masuk (Team Leader / DH) | Notifikasi saat ada tiket baru yang perlu ditinjau |
| Form Pengadaan Terbit | Notifikasi saat Form Pengadaan (PO) sudah dapat diunduh |

**Button Reset:** `"Reset ke Default"` (btn-secondary, icon: refresh/reset arrow)

### Toggle Switch Spec
- Width: 46px, Height: 26px
- Track (off): `#E2E5EA`
- Track (on): `#F15A22`
- Knob: 20×20px, bg: `#fff`, translate-x: 20px saat ON
- Transition: 0.25s ease

---

### G. Halaman Bantuan & FAQ (`/faq`)

**Breadcrumb:** `"Helpdesk E-Procurement / Bantuan & FAQ"`

**Page Center Header:**
- Icon circle 56×56px: question mark SVG, bg: #E0EFF3, color: #006885
- H1: `"Pusat Bantuan & FAQ"`
- Sub: `"Jawaban dari pertanyaan yang sering diajukan mengenai sistem Helpdesk E-Procurement dan aturan pengajuan IT."`

**Kategori 1: "Alur Pengajuan & Persetujuan"**

Accordion items:
1. **Q:** `"Bagaimana alur persetujuan tiket di sistem ini?"`
   - **A:** Alur terdiri dari: Draft/Validasi → Review (Team Leader) → Approval (Dept Head) → Form Terbit

2. **Q:** `"Kapan Form Pengadaan (PO) bisa saya download?"`
   - **A:** Setelah status "Form Diterbitkan", setelah Dept Head approved dan TL generate

**Kategori 2: "Smart Validation & Anggaran"**

Accordion items:
1. **Q:** `"Apa itu Smart Validation?"`
   - **A:** Sistem 4 Gate: Gate 1 (Duplikasi), Gate 2 (Nominal), Gate 3 (Klasifikasi PSAK), Gate 4 (Anggaran)

2. **Q:** `"Mengapa tiket saya otomatis diubah tipenya oleh sistem (CAPEX ke OPEX)?"`
   - **A:** Keyword detection: Subscription, Langganan, SaaS, Cloud, dll → OPEX

3. **Q:** `"Mengapa tiket saya berstatus 'Over Budget' padahal masih ada saldo?"`
   - **A:** Batas 130% dari limit bulanan rata-rata (Total Anggaran/12)

**Accordion Style:**
- Question: 15px, weight 500, color: `#1A1C23`, padding: 24px
- Answer: 14px, color: `#3D4150`, padding-bottom: 24px, line-height: 1.6
- Hover background: `#F7F8FA`
- Arrow icon 20×20: chevron-down, rotates 180° saat open
- Transition: max-height 0.3s ease-out

---

## 15. KOMPONEN: FORM CONTROLS

### Input (`.form-control`)
- Width: 100%, Height: 40px
- Padding: 0 14px
- Background: `#fff`
- Border: `1px solid #E2E5EA`, Border-radius: 8px
- Font: Inter, 14px, color: `#1A1C23`
- Placeholder color: `#9396A0`
- **Focus**: border-color `#F15A22`, border-width 2px, box-shadow `0 0 0 3px #FDE8DC`
- **Invalid**: border-color `#FF383C`, shadow `0 0 0 3px #FFE8E8`

### Textarea
- Min-height: 120px
- Padding: 10px 14px

### Select
- Custom arrow: small SVG chevron, warna `#F15A22`, posisi right 14px center
- Padding-right: 38px

### Form Label (`.form-label`)
- Font: 13px, weight 500, color: `#1A1C23`
- Margin-bottom: 6px
- Required asterisk `*`: color `#FF383C`

---

## 16. KOMPONEN: TABEL DATA

### `.data-table th`
- Padding: 16px 24px
- Font: 11px, weight 600, UPPERCASE, letter-spacing: 0.8px, color: `#6B7080`
- Background: `#F7F8FA`
- Border-bottom: `1px solid #E2E5EA`

### `.data-table td`
- Padding: 16px 24px
- Font: 14px, color: `#3D4150`
- Border-bottom: `1px solid #EDF0F3`

### Row Hover: background `#F7F8FA`

### Special td Classes
- `.table-ticket-id`: 12px, weight 600, color: `#006885`, font-family: monospace
- `.table-title`: weight 500, color: `#1A1C23`
- `.table-amount`: weight 600, color: `#1A1C23`

---

## 17. KOMPONEN: TOAST NOTIFICATIONS

**Position:** Fixed, bottom-right (bottom: 32px, right: 32px)
**Max-width:** 360px
**Animation:** slide up 12px + fade in, 0.3s ease

### Toast Structure
- Padding: 16px 24px
- Border-radius: 12px
- Border-left: 4px solid [semantic color]
- Display: flex, gap: 12px

### Toast Variants
| Type | BG | Border | Icon Color |
|---|---|---|---|
| success | `#E8F9ED` | `#34C759` | `#34C759` |
| error | `#FFE8E8` | `#FF383C` | `#FF383C` |
| warning | `#FFF9D6` | `#FFCC00` | `#FFCC00` |
| info | `#E0F0FF` | `#0088FF` | `#0088FF` |

- **Title**: 13px, weight 500, color: `#1A1C23`
- **Message**: 13px, color: `#6B7080`, margin-top: 2px

### Flash Toast Messages (Trigger dari Server)
| Session | Toast Type | Title |
|---|---|---|
| `success` | success | Berhasil |
| `error` | error | Gagal |
| `warning` | warning | Peringatan |
| `info` | info | Informasi |

---

## 18. KOMPONEN: MODAL

### Overlay: `rgba(0,0,0,0.45)`, z-index: 1000
### Card
- Background: `#fff`
- Border-radius: 16px
- Shadow: `shadow-lg`
- Max-width: 480px
- Padding: 48px
- Animation: scale 0.95 → 1.0, 0.2s ease

### Modal Icon (48×48px, border-radius: 50%)
- warning: bg `#FFF9D6`, color `#7A6000`
- danger: bg `#FFE8E8`, color `#B00004`
- success: bg `#E8F9ED`, color `#1A7A36`

### Typography
- Title (`.modal-title`): 20px, weight 600, color: `#1A1C23`
- Body (`.modal-body`): 14px, color: `#3D4150`, line-height: 1.6
- Footer: flex, gap: 12px, justify: flex-end

---

## 19. KOMPONEN: PROGRESS BAR

### Track
- Height: 8px
- Background: `#F0F2F5`
- Border-radius: 9999px

### Fill
- `default/normal`: `#34C759`
- `.warn` (≥75%): `#FFCC00`
- `.critical` (≥90%): `#FF383C`
- Transition: width 0.6s ease

---

## 20. DARK MODE TOKENS (jika dark theme diaktifkan via `data-theme="dark"`)

| Token Override | Nilai |
|---|---|
| `--color-canvas` | `#0f1117` |
| `--color-surface-soft` | `#1a1d27` |
| `--color-surface-card` | `#22263a` |
| `--color-ink` | `#f0f2f7` |
| `--color-body` | `#c8cdd8` |
| `--color-muted` | `#8b92a8` |
| `--color-hairline` | `#2a2e42` |

---

## 21. ROLES & LABEL UI

| Role DB | Label Display |
|---|---|
| `requester` | IT Infrastructure Project Management |
| `team_leader` | Team Leader |
| `department_head` | Department Head |

---

## 22. RINGKASAN PROMPT UNTUK STITCH/FIGMA

```
Buat prototype Figma untuk aplikasi web "Helpdesk E-Procurement BNI" menggunakan design 
system berikut:

FONT: Inter (body/heading) + Arial (buttons)
WARNA UTAMA: Primary #F15A22, Secondary/Sidebar #006885
BACKGROUND: #F7F8FA (halaman), #FFFFFF (card)
BORDER: #E2E5EA, border-radius card: 12px, button: 8px
SIDEBAR: 240px lebar, background #006885, item aktif #004F65 dengan border-left #F15A22

HALAMAN YANG PERLU DIBUAT:
1. Dashboard (stat cards 4 kolom + chart area + budget utilization)
2. Tiket Pengadaan (tabel dengan filter tabs + search)
3. Pengajuan Baru (form multi-section + item table + action bar sticky)
4. Detail Tiket (info card + items table + timeline + actions)
5. Pengaturan (toggle switches + notification preferences)
6. Bantuan & FAQ (accordion-style)

KOMPONEN KUNCI:
- Sidebar fixed 240px dengan section labels uppercase
- Topbar 64px dengan breadcrumb + settings/bell/avatar icons kanan
- Stat cards hover lift effect (translateY -1px)
- Badge pills untuk status (warna sesuai status: biru=pending, kuning=revisi, hijau=disetujui, merah=ditolak)
- Timeline approval log dengan colored dots
- Toast notifications bottom-right
```
