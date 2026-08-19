<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Memerlukan Revisi</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f7f6f2; margin: 0; padding: 20px; color: #1e1c14;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #cfc6ac; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <!-- HEADER -->
        <tr>
            <td bgcolor="#333028" style="padding: 25px 30px; border-left: 4px solid #dc2626;">
                <h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;">
                    e-QMS <span style="color: #dc2626; font-weight: 400;">| REVISI DIPERLUKAN</span>
                </h1>
            </td>
        </tr>
        
        <!-- BODY CONTENT -->
        <tr>
            <td style="padding: 30px;">
                <h2 style="font-size: 16px; font-weight: bold; margin-top: 0; color: #333028;">Halo, {{ $user->full_name ?? $user->username }}</h2>
                <p style="font-size: 13px; line-height: 1.6; color: #4d4633; margin-bottom: 20px;">
                    Dokumen yang Anda ajukan atau kelola di bawah ini telah dikembalikan oleh Peninjau untuk dilakukan revisi atau perbaikan berkas.
                </p>

                <!-- METADATA TABLE -->
                <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cfc6ac; margin-bottom: 25px;">
                    <tr bgcolor="#eee8db">
                        <td colspan="2" style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #333028; border-bottom: 1px solid #cfc6ac;">
                            Detail Permintaan Revisi
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" style="font-size: 12px; font-weight: bold; color: #706b5c; border-bottom: 1px solid #e8e2d6;">Judul Dokumen</td>
                        <td style="font-size: 12px; color: #1e1c14; border-bottom: 1px solid #e8e2d6;">{{ $document->title }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: bold; color: #706b5c; border-bottom: 1px solid #e8e2d6;">Nomor Dokumen</td>
                        <td style="font-size: 12px; color: #1e1c14; border-bottom: 1px solid #e8e2d6;">{{ $document->doc_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: bold; color: #706b5c; border-bottom: 1px solid #e8e2d6;">Diminta Oleh</td>
                        <td style="font-size: 12px; color: #1e1c14; border-bottom: 1px solid #e8e2d6;">{{ $requester->full_name ?? $requester->username }} ({{ $requester->role }})</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: bold; color: #706b5c; border-bottom: 1px solid #e8e2d6;">Catatan Revisi</td>
                        <td style="font-size: 12px; color: #dc2626; font-weight: bold; border-bottom: 1px solid #e8e2d6; line-height: 1.5;">{{ $notes }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: bold; color: #706b5c;">Status Dokumen</td>
                        <td style="font-size: 12px; color: #dc2626; font-weight: bold; text-transform: uppercase;">{{ $document->status }} (Need Revision)</td>
                    </tr>
                </table>

                <p style="font-size: 13px; line-height: 1.6; color: #4d4633; margin-bottom: 30px;">
                    Silakan klik tombol di bawah ini untuk mengakses detail dokumen atau mengunggah berkas revisi yang baru.
                </p>

                <!-- BUTTON CTA -->
                <table align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" bgcolor="#333028" style="border-radius: 6px;">
                            <a href="{{ $magicLoginUrl }}" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 12px; font-weight: bold; color: #ffe16e; text-decoration: none; text-transform: uppercase; letter-spacing: 1px;">
                                Perbaiki Dokumen &rarr;
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- FOOTER -->
        <tr>
            <td bgcolor="#f7f6f2" style="padding: 20px 30px; border-top: 1px solid #cfc6ac; text-align: center;">
                <p style="font-size: 11px; color: #706b5c; margin: 0 0 5px 0; font-weight: bold;">
                    PT PUTRA KELANA MAKMUR (PKM GROUP)
                </p>
                <p style="font-size: 10px; color: #8e8775; margin: 0;">
                    Ini adalah notifikasi otomatis dari sistem e-QMS. Mohon untuk tidak membalas email ini secara langsung.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
