<?php

namespace Database\Seeders;

use App\Models\ApprovalLog;
use App\Models\Ticket;
use App\Models\TicketDocument;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * TicketTestSeeder
 *
 * Menyediakan tiket uji coba untuk menguji fitur bulk action:
 *
 * Group A — pending_review (5 tiket) : uji bulk TL: Terima/Tolak Dokumen
 * Group B — need_to_validate (3 tiket): uji Smart Validation oleh Requester
 * Group C — pending_dept_head (5 tiket): uji bulk DH: Setujui/Tolak
 * Group D — approved (2 tiket)         : uji Generate Form oleh TL
 *
 * Cara run:
 *   php artisan db:seed --class=TicketTestSeeder
 *
 * Cara reset (hapus semua tiket uji):
 *   php artisan db:seed --class=TicketTestSeeder --force
 *   (atau gunakan flag --fresh untuk full fresh seed)
 */
class TicketTestSeeder extends Seeder
{
    public function run(): void
    {
        $requester = User::where('role', 'requester')->firstOrFail();
        $tl        = User::where('role', 'team_leader')->firstOrFail();
        $dh        = User::where('role', 'department_head')->firstOrFail();

        // ── Group A: pending_review (antrian cek dokumen TL) ──────────────
        $this->command->info('Seeding Group A: pending_review (5 tiket)...');
        $pendingReviewTickets = [
            ['title' => 'Pengadaan Server Rack 42U', 'item' => 'Server Rack', 'category' => 'infrastruktur_utama', 'vendor' => 'PT. Indosat Mega Media', 'amount' => 85_000_000, 'qty' => 2],
            ['title' => 'Pembelian Switch Network Cisco', 'item' => 'Network Switch', 'category' => 'infrastruktur_utama', 'vendor' => 'PT. Mitra Solusi', 'amount' => 45_000_000, 'qty' => 4],
            ['title' => 'Lisensi Microsoft 365 Business', 'item' => 'MS365 License', 'category' => 'lisensi_sistem', 'vendor' => 'PT. Microsoft Indonesia', 'amount' => 12_000_000, 'qty' => 50],
            ['title' => 'Pemeliharaan AC Server Room', 'item' => 'AC Maintenance', 'category' => 'layanan_pemeliharaan', 'vendor' => 'PT. Daikin Indonesia', 'amount' => 8_500_000, 'qty' => 1],
            ['title' => 'Pengadaan UPS 10KVA', 'item' => 'UPS Unit', 'category' => 'infrastruktur_utama', 'vendor' => 'PT. Eaton Indonesia', 'amount' => 55_000_000, 'qty' => 3],
        ];

        foreach ($pendingReviewTickets as $data) {
            $this->createTicket($data, $requester, Ticket::STATUS_PENDING_REVIEW, [
                ApprovalLog::ACTION_SUBMITTED => $requester,
            ]);
        }

        // ── Group B: need_to_validate (siap Smart Validation) ─────────────
        $this->command->info('Seeding Group B: need_to_validate (3 tiket)...');
        $needValidateTickets = [
            ['title' => 'Upgrade Firewall Fortigate 200F', 'item' => 'Fortigate 200F', 'category' => 'infrastruktur_utama', 'vendor' => 'PT. Fortinet Indonesia', 'amount' => 320_000_000, 'qty' => 1],
            ['title' => 'Langganan SaaS Monitoring Datadog', 'item' => 'Datadog License', 'category' => 'lisensi_sistem', 'vendor' => 'Datadog Inc.', 'amount' => 48_000_000, 'qty' => 1],
            ['title' => 'Pembelian Meja Kantor Standing Desk', 'item' => 'Standing Desk', 'category' => 'perlengkapan_operasional', 'vendor' => 'PT. Ergotron', 'amount' => 7_500_000, 'qty' => 10],
        ];

        foreach ($needValidateTickets as $data) {
            $this->createTicket($data, $requester, Ticket::STATUS_NEED_TO_VALIDATE, [
                ApprovalLog::ACTION_SUBMITTED    => $requester,
                ApprovalLog::ACTION_FOLLOWED_UP  => $tl,
            ], docsAccepted: true);
        }

        // ── Group C: pending_dept_head (antrian keputusan DH) ─────────────
        $this->command->info('Seeding Group C: pending_dept_head (5 tiket)...');
        $pendingDhTickets = [
            ['title' => 'Pengadaan Storage NAS Synology', 'item' => 'NAS Storage', 'category' => 'infrastruktur_utama', 'vendor' => 'PT. Synology Indonesia', 'amount' => 180_000_000, 'qty' => 2, 'type' => 'CAPEX'],
            ['title' => 'Lisensi Oracle Database EE', 'item' => 'Oracle DB License', 'category' => 'lisensi_sistem', 'vendor' => 'PT. Oracle Indonesia', 'amount' => 750_000_000, 'qty' => 1, 'type' => 'CAPEX'],
            ['title' => 'Jasa Konsultasi IT Security Audit', 'item' => 'IT Security Audit', 'category' => 'layanan_pemeliharaan', 'vendor' => 'PT. Deloitte Indonesia', 'amount' => 95_000_000, 'qty' => 1, 'type' => 'OPEX'],
            ['title' => 'Pembelian Laptop Dell XPS 15', 'item' => 'Laptop Dell XPS 15', 'category' => 'infrastruktur_utama', 'vendor' => 'PT. Dell Indonesia', 'amount' => 28_000_000, 'qty' => 5, 'type' => 'CAPEX'],
            ['title' => 'Pengadaan Kursi Ergonomis Herman Miller', 'item' => 'Kursi Ergonomis', 'category' => 'perlengkapan_operasional', 'vendor' => 'PT. Herman Miller', 'amount' => 18_000_000, 'qty' => 8, 'type' => 'OPEX'],
        ];

        foreach ($pendingDhTickets as $data) {
            $this->createTicket($data, $requester, Ticket::STATUS_PENDING_DEPT_HEAD, [
                ApprovalLog::ACTION_SUBMITTED    => $requester,
                ApprovalLog::ACTION_FOLLOWED_UP  => $tl,
                ApprovalLog::ACTION_VALIDATED    => $requester,
            ], docsAccepted: true, expenditureType: $data['type'] ?? 'OPEX');
        }

        // ── Group D: approved (siap Generate Form) ─────────────────────────
        $this->command->info('Seeding Group D: approved (2 tiket)...');
        $approvedTickets = [
            ['title' => 'Pengadaan Kabel Fiber Optik 100m', 'item' => 'Kabel Fiber Optik', 'category' => 'infrastruktur_utama', 'vendor' => 'PT. Corning Indonesia', 'amount' => 12_000_000, 'qty' => 5, 'type' => 'OPEX'],
            ['title' => 'Jasa Maintenance Genset Panel Listrik', 'item' => 'Genset Maintenance', 'category' => 'layanan_pemeliharaan', 'vendor' => 'PT. Caterpillar Indonesia', 'amount' => 35_000_000, 'qty' => 1, 'type' => 'OPEX'],
        ];

        foreach ($approvedTickets as $data) {
            $this->createTicket($data, $requester, Ticket::STATUS_APPROVED, [
                ApprovalLog::ACTION_SUBMITTED    => $requester,
                ApprovalLog::ACTION_FOLLOWED_UP  => $tl,
                ApprovalLog::ACTION_VALIDATED    => $requester,
                ApprovalLog::ACTION_APPROVED     => $dh,
            ], docsAccepted: true, expenditureType: $data['type'] ?? 'OPEX');
        }

        $this->command->info('');
        $this->command->info('✅ TicketTestSeeder selesai!');
        $this->command->table(
            ['Grup', 'Status', 'Jumlah', 'Keterangan'],
            [
                ['A', 'pending_review',    5, 'Login TL → cek dokumen (bulk Terima/Tolak)'],
                ['B', 'need_to_validate',  3, 'Login Requester → Jalankan Smart Validation'],
                ['C', 'pending_dept_head', 5, 'Login DH → bulk Setujui/Tolak'],
                ['D', 'approved',          2, 'Login TL → klik Generate Form'],
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────────────

    private function createTicket(
        array $data,
        User $requester,
        string $status,
        array $logs,
        bool $docsAccepted = false,
        string $expenditureType = null,
    ): Ticket {
        $ticket = Ticket::create([
            'user_id'          => $requester->id,
            'title'            => $data['title'],
            'item_name'        => $data['item'],
            'category'         => $data['category'],
            'description'      => 'Tiket uji coba untuk keperluan pengujian sistem e-procurement. ' . $data['title'],
            'pic_name'         => $requester->name,
            'quantity'         => $data['qty'],
            'vendor_name'      => $data['vendor'],
            'amount'           => $data['amount'],
            'status'           => $status,
            'expenditure_type' => $expenditureType,
            'is_cross_fund'    => false,
        ]);

        // Create 2 dummy document entries (no actual file stored — just DB rows)
        $docStatus = $docsAccepted ? 'accepted' : 'pending';
        TicketDocument::create([
            'ticket_id'   => $ticket->id,
            'file_path'   => 'izin_prinsip/TEST_DUMMY_DO_NOT_DOWNLOAD.pdf',
            'description' => 'Surat Permintaan Pengadaan',
            'status'      => $docStatus,
            'feedback'    => null,
        ]);
        TicketDocument::create([
            'ticket_id'   => $ticket->id,
            'file_path'   => 'izin_prinsip/TEST_DUMMY_DO_NOT_DOWNLOAD.pdf',
            'description' => 'Persetujuan Anggaran Departemen',
            'status'      => $docStatus,
            'feedback'    => null,
        ]);

        // Create approval log trail
        $logMessages = [
            ApprovalLog::ACTION_SUBMITTED   => 'Pengajuan tiket dibuat (data uji).',
            ApprovalLog::ACTION_FOLLOWED_UP => 'Dokumen diterima oleh Team Leader (data uji).',
            ApprovalLog::ACTION_VALIDATED   => 'Smart Validation lolos (data uji). Klasifikasi: ' . ($expenditureType ?? 'OPEX'),
            ApprovalLog::ACTION_APPROVED    => 'Disetujui oleh Department Head (data uji).',
        ];

        foreach ($logs as $action => $actor) {
            ApprovalLog::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $actor->id,
                'action'    => $action,
                'notes'     => $logMessages[$action] ?? 'Data uji.',
            ]);
        }

        return $ticket;
    }
}
