<?php
$host = 'aws-1-ap-southeast-2.pooler.supabase.com';
$port = 5432;
$timeout = 10;

echo "Mencoba koneksi ke {$host}:{$port}...\n";
$fp = @fsockopen($host, $port, $errCode, $errStr, $timeout);

if ($fp) {
    echo "BERHASIL! Port {$port} terbuka dan bisa diakses dari laptop lu.\n";
    fclose($fp);
} else {
    echo "GAGAL: {$errStr} ({$errCode})\n";
    echo "Ini berarti jaringan internet/provider lu atau firewall ngeblok port 5432, atau Supabase lagi down.\n";
}
