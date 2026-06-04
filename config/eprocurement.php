<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Capitalization Threshold (Gate 3 — CAPEX/OPEX Classification)
    |--------------------------------------------------------------------------
    |
    | Nilai minimum (dalam Rupiah) untuk item hardware/software agar
    | diklasifikasikan sebagai CAPEX (belanja modal).
    | Item dengan amount >= threshold → CAPEX
    | Item dengan amount < threshold  → OPEX
    |
    */
    'capitalization_threshold' => env('CAPEX_THRESHOLD', 200_000_000),

    /*
    |--------------------------------------------------------------------------
    | OTP Time-to-Live (menit)
    |--------------------------------------------------------------------------
    |
    | Masa berlaku kode OTP yang dikirim ke email korporat saat registrasi.
    | Setelah melewati TTL ini, OTP dianggap kadaluarsa dan tidak dapat digunakan.
    |
    */
    'otp_ttl_minutes' => env('OTP_TTL_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | HR Division Filter
    |--------------------------------------------------------------------------
    |
    | Kata kunci division yang diizinkan mendaftar ke sistem ini.
    | Registrasi dari divisi lain akan ditolak.
    |
    */
    'allowed_division_keyword' => env('ALLOWED_DIVISION', 'IT Infrastructure'),

    /*
    |--------------------------------------------------------------------------
    | Budget Categories
    |--------------------------------------------------------------------------
    */
    'categories' => [
        'hardware'       => 'Hardware',
        'software'       => 'Software',
        'services'       => 'Services',
        'office_supplies' => 'Office Supplies',
        'others'         => 'Others',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supabase Storage Folders
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'izin_prinsip_folder'    => 'izin_prinsip',
        'purchase_orders_folder' => 'purchase_orders',
    ],

];
