<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOP Telah Resmi Terbit - e-QMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #F4F5F6; margin: 0; padding: 30px 15px; color: #1A1A1A;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
        
        <!-- LOGO HEADER -->
        <tr>
            <td align="center" style="padding: 24px 20px; background-color: #ffffff; border-bottom: 1px solid #F4F5F6;">
                <img src="{{ $message->embed(public_path('img/logopkm.png')) }}" style="height: 52px; display: block; margin: 0 auto;" alt="PKM Group Logo">
            </td>
        </tr>

        <!-- COLOR ACCENT BAR (EMERALD / SUCCESS) -->
        <tr>
            <td height="4" bgcolor="#10B981"></td>
        </tr>
        
        <!-- BODY CONTENT -->
        <tr>
            <td style="padding: 36px 28px;">
                <!-- STATUS BADGE -->
                <div style="margin-bottom: 16px;">
                    <span style="display: inline-block; padding: 4px 12px; background-color: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 20px; font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">
                        ✓ SOP RESMI TERBIT & SAH
                    </span>
                </div>

                <h2 style="font-size: 18px; font-weight: 700; margin: 0 0 10px 0; color: #1A1A1A; letter-spacing: -0.01em;">
                    Halo, {{ $recipient->full_name ?? ($recipient->username ?? 'Rekan Kerja') }}
                </h2>
                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin: 0 0 20px 0;">
                    Kabar baik! Usulan pembuatan SOP baru yang Anda ajukan telah berhasil melalui seluruh rangkaian proses perancangan naskah, verifikasi kendali dokumen, penandatanganan digital oleh para pejabat berwenang, dan kini telah <strong>resmi diterbitkan di Katalog E-Library e-QMS</strong>.
                </p>

                <!-- METADATA TABLE -->
                <table width="100%" cellpadding="12" cellspacing="0" style="border-collapse: collapse; border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
                    <tr bgcolor="#F9FAFB">
                        <td colspan="2" style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #047857; border-bottom: 1px solid #E5E7EB;">
                            Informasi Dokumen SOP Sah
                        </td>
                    </tr>
                    <tr>
                        <td width="35%" style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Nomor Dokumen</td>
                        <td style="font-size: 12px; color: #1677B8; border-bottom: 1px solid #F3F4F6; font-weight: 700;">{{ $document->doc_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Judul Dokumen</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 700;">{{ $document->title }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Unit / Departemen</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $document->department }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Status Publikasi</td>
                        <td style="font-size: 12px; color: #047857; border-bottom: 1px solid #F3F4F6; font-weight: 700;">Aktif & Tersedia di E-Library</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280;">Tanggal Pengesahan</td>
                        <td style="font-size: 12px; color: #374151; font-weight: 600;">{{ now()->format('d F Y') }}</td>
                    </tr>
                </table>

                <!-- ACTION BUTTON -->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 28px 0;">
                    <tr>
                        <td align="center">
                            <a href="{{ $libraryUrl ?? url('/library') }}" 
                               style="display: inline-block; background-color: #10B981; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; padding: 13px 28px; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);">
                                Buka Dokumen di E-Library &rarr;
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 12px; color: #6B7280; line-height: 1.5; margin: 0 0 16px 0; text-align: center;">
                    Terima kasih atas kontribusi Anda dalam meningkatkan standardisasi proses kerja dan mutu perusahaan di lingkungan PT Putra Kelana Makmur Group.
                </p>

                <p style="font-size: 11px; color: #9CA3AF; margin: 0; text-align: center;">
                    Tautan ini bersifat aman dan dapat langsung diakses untuk membaca dokumen naskah SOP resmi.
                </p>
            </td>
        </tr>

        <!-- FOOTER -->
        <tr>
            <td style="background-color: #F9FAFB; padding: 20px; text-align: center; border-top: 1px solid #E5E7EB;">
                <p style="font-size: 11px; color: #6B7280; margin: 0 0 4px 0; font-weight: 600;">
                    PT Putra Kelana Makmur - Electronic Quality Management System (e-QMS)
                </p>
                <p style="font-size: 10px; color: #9CA3AF; margin: 0;">
                    Email ini dikirim secara otomatis oleh sistem e-QMS. Jangan membalas langsung ke alamat ini.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
