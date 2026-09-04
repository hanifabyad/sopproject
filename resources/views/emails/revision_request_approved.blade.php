<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Revisi Disetujui - e-QMS</title>
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

        <!-- COLOR ACCENT BAR (BLUE) -->
        <tr>
            <td height="4" bgcolor="#1677B8"></td>
        </tr>
        
        <!-- BODY CONTENT -->
        <tr>
            <td style="padding: 36px 28px;">
                <!-- STATUS BADGE -->
                <div style="margin-bottom: 16px;">
                    <span style="display: inline-block; padding: 4px 12px; background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 20px; font-size: 11px; font-weight: 700; color: #1D4ED8; text-transform: capitalize; letter-spacing: 0.5px;">
                        PERMOHONAN REVISI DISETUJUI
                    </span>
                </div>

                <h2 style="font-size: 18px; font-weight: 700; margin: 0 0 10px 0; color: #1A1A1A; letter-spacing: -0.01em;">
                    Halo, {{ $recipient->full_name ?? $recipient->username }}!
                </h2>
                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin: 0 0 20px 0;">
                    Permohonan pengajuan revisi dokumen SOP Anda telah <strong>disetujui oleh Admin QMS</strong>. Akses unggah draf perbaikan dokumen kini telah dibuka.
                </p>

                <!-- METADATA TABLE -->
                <table width="100%" cellpadding="12" cellspacing="0" style="border-collapse: collapse; border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
                    <tr bgcolor="#F9FAFB">
                        <td colspan="2" style="font-size: 11px; font-weight: 700; text-transform: capitalize; letter-spacing: 0.5px; color: #1677B8; border-bottom: 1px solid #E5E7EB;">
                            Detail Permohonan Revisi
                        </td>
                    </tr>
                    <tr>
                        <td width="35%" style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Judul Dokumen</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $document->title }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Nomor Dokumen</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $document->doc_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Unit / Departemen</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; text-transform: capitalize; font-weight: 600;">{{ $document->department }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Alasan Permohonan</td>
                        <td style="font-size: 12px; color: #374151; border-bottom: 1px solid #F3F4F6; font-weight: 500;">{{ $revisionRequest->reason }}</td>
                    </tr>
                    @if(!empty($revisionRequest->admin_notes))
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280;">Catatan Admin</td>
                        <td style="font-size: 12px; color: #1677B8; font-weight: 600;">{{ $revisionRequest->admin_notes }}</td>
                    </tr>
                    @endif
                </table>

                <!-- PENTING ALERT BOX -->
                <div style="background-color: #EFF6FF; border: 1px solid #BFDBFE; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px;">
                    <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 700; color: #1E40AF;">INFORMASI REVISI:</p>
                    <p style="margin: 0; font-size: 12px; color: #1D4ED8; line-height: 1.5;">
                        Silakan mengunggah naskah perbaikan dokumen SOP (Cover, Lembar Pengesahan, Isi, dan Lampiran) agar alur peninjauan dapat segera diproses.
                    </p>
                </div>

                <!-- BUTTON CTA -->
                <table align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" bgcolor="#1677B8" style="border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(22, 119, 184, 0.25);">
                            <a href="{{ $uploadUrl }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 12px; font-weight: 700; color: #ffffff; text-decoration: none; text-transform: capitalize; letter-spacing: 0.5px;">
                                UNGGAH PERBAIKAN REVISI SEKARANG &rarr;
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- FOOTER -->
        <tr>
            <td bgcolor="#F9FAFB" style="padding: 22px 28px; border-top: 1px solid #E5E7EB; text-align: center;">
                <p style="font-size: 11px; color: #374151; margin: 0 0 4px 0; font-weight: 700;">
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
