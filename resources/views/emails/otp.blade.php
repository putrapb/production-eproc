<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kode OTP | E-Procurement Pejompongan</title>
</head>
<body style="margin:0;padding:0;background:#f4f7fa;font-family:Arial,Helvetica,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fa;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

          <tr>
            <td style="background:#006885;padding:28px 36px;">
              <div style="color:#ffffff;font-size:20px;font-weight:bold;letter-spacing:0.5px;">
                E-Procurement Pejompongan
              </div>
              <div style="color:rgba(255,255,255,0.75);font-size:12px;margin-top:4px;">
                Departemen IT Infrastructure Management
              </div>
            </td>
          </tr>

          <tr>
            <td style="padding:36px 36px 28px;">
              <p style="margin:0 0 16px;font-size:14px;color:#333;">Halo,</p>
              <p style="margin:0 0 24px;font-size:14px;color:#555;line-height:1.6;">
                Berikut adalah kode OTP untuk verifikasi akun E-Procurement Anda.
                Kode ini hanya berlaku selama <strong>{{ $ttlMinutes }} menit</strong>.
              </p>

              <div style="text-align:center;margin:28px 0;">
                <div style="display:inline-block;background:#f0f7fa;border:2px dashed #006885;border-radius:8px;padding:20px 40px;">
                  <div style="font-size:36px;font-weight:bold;letter-spacing:12px;color:#006885;">
                    {{ $otpCode }}
                  </div>
                </div>
              </div>

              <p style="margin:24px 0 8px;font-size:13px;color:#888;text-align:center;">
                Jangan bagikan kode ini kepada siapapun.
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:0 36px;">
              <hr style="border:none;border-top:1px solid #eee;margin:0;">
            </td>
          </tr>

          <tr>
            <td style="padding:20px 36px;background:#f9fbfc;">
              <p style="margin:0;font-size:11px;color:#aaa;text-align:center;line-height:1.6;">
                Email ini dikirim secara otomatis oleh Sistem E-Procurement.<br>
                Jika Anda tidak merasa melakukan registrasi, abaikan email ini.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>

