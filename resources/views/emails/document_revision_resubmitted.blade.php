<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Direvisi (Perlu Review Ulang)</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="font-family: 'Poppins', Arial, sans-serif; background-color: #F4F5F6; margin: 0; padding: 30px 20px; color: #1a1a1a;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
        <!-- LOGO HEADER -->
        <tr>
            <td align="center" style="padding: 25px 20px; background-color: #ffffff; border-bottom: 1px solid #F4F5F6;">
                <img src="{{ $message->embed(public_path('img/logopkm.png')) }}" style="height: 48px; display: block;" alt="PKM Group Logo">
            </td>
        </tr>

        <!-- COLOR ACCENT BAR -->
        <tr>
            <td height="4" bgcolor="#1677B8"></td>
        </tr>
        
        <!-- BODY CONTENT -->
        <tr>
            <td style="padding: 40px 30px;">
                <h2 style="font-size: 18px; font-weight: 700; margin-top: 0; color: #1a1a1a; letter-spacing: -0.02em;">Halo, {{ $user->full_name ?? $user->username }}</h2>
                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin-bottom: 25px;">
                    Dokumen SOP berikut telah diperbaiki/direvisi dan diunggah ulang ke sistem e-QMS. Silakan lakukan peninjauan kembali atas berkas revisi terbaru ini.
                </p>

                <!-- METADATA TABLE -->
                <table width="100%" cellpadding="12" cellspacing="0" style="border-collapse: collapse; border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 30px; overflow: hidden;">
                    <tr bgcolor="#F9FAFB">
                        <td colspan="2" style="font-size: 11px; font-weight: 700; text-transform: uppercase; tracking-wider; color: #1677B8; border-bottom: 1px solid #E5E7EB;">
                            Detail Dokumen Revisi
                        </td>
                    </tr>
                    <tr>
                        <td width="35%" style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Judul Dokumen</td>
                        <td style="font-size: 12px; color: #1a1a1a; border-bottom: 1px solid #F3F4F6; font-weight: 500;">{{ $document->title }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Nomor Dokumen</td>
                        <td style="font-size: 12px; color: #1a1a1a; border-bottom: 1px solid #F3F4F6; font-weight: 500;">{{ $document->doc_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Diunggah Oleh</td>
                        <td style="font-size: 12px; color: #1a1a1a; border-bottom: 1px solid #F3F4F6; font-weight: 500;">{{ $updater->full_name ?? $updater->username }} ({{ $updater->role }})</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Status Dokumen</td>
                        <td style="font-size: 12px; color: #D97706; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #F3F4F6;">{{ $document->status }} (Waiting Review)</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280;">Keterangan</td>
                        <td style="font-size: 12px; color: #1a1a1a; font-style: italic; font-weight: 500;">Dokumen telah diperbaiki sesuai catatan peninjau sebelumnya.</td>
                    </tr>
                </table>

                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin-bottom: 30px;">
                    Silakan gunakan tombol di bawah ini untuk membuka halaman antrean verifikasi dan melakukan review terhadap dokumen revisi ini.
                </p>

                <!-- BUTTON CTA -->
                <table align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" bgcolor="#1677B8" style="border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(22, 119, 184, 0.2);">
                            <a href="{{ $magicLoginUrl }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 12px; font-weight: 600; color: #ffffff; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;">
                                Review Dokumen Revisi &rarr;
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- FOOTER -->
        <tr>
            <td bgcolor="#F9FAFB" style="padding: 25px 30px; border-top: 1px solid #E5E7EB; text-align: center;">
                <p style="font-size: 11px; color: #374151; margin: 0 0 5px 0; font-weight: 700;">
                    PT PUTRA KELANA MAKMUR (PKM GROUP)
                </p>
                <p style="font-size: 10px; color: #9CA3AF; margin: 0; line-height: 1.4;">
                    Ini adalah notifikasi otomatis dari sistem e-QMS. Mohon untuk tidak membalas email ini secara langsung.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
