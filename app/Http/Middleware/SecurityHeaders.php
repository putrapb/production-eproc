<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * M-4 Security: Tambahkan HTTP Security Headers pada setiap response.
 *
 * Headers ini mencegah:
 * - X-Content-Type-Options: MIME type sniffing (browser tidak boleh tebak-tebak tipe file)
 * - X-Frame-Options: Clickjacking via iframe embedding
 * - Referrer-Policy: Kebocoran URL ke pihak ketiga saat navigasi
 * - Permissions-Policy: Batasi akses ke API browser sensitif (kamera, mikrofon, GPS)
 * - X-XSS-Protection: Legacy header, tapi masih berguna untuk browser lama
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=()'
        );

        // Content-Security-Policy — hati-hati, jangan terlalu ketat karena bisa break inline scripts
        // Di-comment dulu, aktifkan setelah semua inline scripts dipindahkan ke file .js terpisah
        // $response->headers->set('Content-Security-Policy', "default-src 'self'; ...");

        return $response;
    }
}
