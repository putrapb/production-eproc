<?php

use App\Models\HrEmployee;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('hris:sync', function () {
    $this->info('Memulai sinkronisasi data karyawan dari Google Sheets...');

    $url = 'https://docs.google.com/spreadsheets/d/1FCsmaE59nu6wRulQKJAEAkkaFhlnaCu8np1Aw7PEnQI/export?format=csv';
    $response = Http::get($url);

    if (!$response->successful()) {
        $this->error('Gagal mengambil data HRIS dari Google Sheets.');
        return 1;
    }

    $csvData = $response->body();
    
    // Parse CSV data safely using php temp stream
    $stream = fopen('php://temp', 'r+');
    fwrite($stream, $csvData);
    rewind($stream);

    $header = true;
    $count = 0;
    while (($row = fgetcsv($stream)) !== false) {
        if ($header) {
            $header = false; // Skip CSV Header
            continue;
        }

        if (count($row) < 4) {
            continue; // Skip invalid rows
        }

        $nip = trim($row[0]);
        $name = trim($row[1]);
        $position = trim($row[2]);
        $division = trim($row[4]);

        if (empty($nip)) {
            continue;
        }

        HrEmployee::updateOrCreate(
            ['nip' => $nip],
            [
                'name' => $name,
                'position' => $position,
                'division' => $division,
            ]
        );
        $count++;
    }

    fclose($stream);
    $this->info("Sinkronisasi selesai! Berhasil memperbarui/menambah {$count} karyawan.");
})->purpose('Sinkronisasi data karyawan dari Google Sheets secara langsung');
