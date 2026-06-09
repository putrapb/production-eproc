<?php

namespace Database\Seeders;

use App\Models\HrEmployee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class UtDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset Data (Truncate/Delete)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE TABLE approval_logs, tickets, users, hr_employees, budgets RESTART IDENTITY CASCADE;');
        } else {
            Schema::disableForeignKeyConstraints();
            DB::table('approval_logs')->truncate();
            DB::table('tickets')->truncate();
            DB::table('users')->truncate();
            DB::table('hr_employees')->truncate();
            DB::table('budgets')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        // 2. Re-seed Budgets (required for creating tickets in UT)
        $this->call(BudgetSeeder::class);

        // 3. Inject Data HR from Google Sheets Live CSV
        $url = 'https://docs.google.com/spreadsheets/d/1FCsmaE59nu6wRulQKJAEAkkaFhlnaCu8np1Aw7PEnQI/export?format=csv';
        
        $response = Http::get($url);

        if (!$response->successful()) {
            throw new \Exception('Gagal mengambil data HRIS dari Google Sheets API.');
        }

        $csvData = $response->body();

        // Parse CSV data safely using php temp stream
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csvData);
        rewind($stream);

        $header = true;
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
            $division = trim($row[3]);

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
        }

        fclose($stream);
    }
}
