<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Revisi SOP Masuk - e-QMS</title>
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

        <!-- COLOR ACCENT BAR (AMBER / WARNING) -->
        <tr>
            <td height="4" bgcolor="#F59E0B"></td>
        </tr>
        
        <!-- BODY CONTENT -->
        <tr>
            <td style="padding: 36px 28px;">
                <!-- STATUS BADGE -->
                <div style="margin-bottom: 16px;">
                    <span style="display: inline-block; padding: 4px 12px; background-color: #FEF3C7; border: 1px solid #FDE68A; border-radius: 20px; font-size: 11px; font-weight: 700; color: #B45309; text-transform: capitalize; letter-spacing: 0.5px;">
                        PERMOHONAN REVISI SOP MASUK
                    </span>
                </div>

                <h2 style="font-size: 18px; font-weight: 700; margin: 0 0 10px 0; color: #1A1A1A; letter-spacing: -0.01em;">
                    Pemberitahuan untuk Admin QMS
                </h2>
                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin: 0 0 20px 0;">
                    Terdapat pengajuan permohonan revisi dokumen SOP aktif yang diajukan oleh user dan menunggu peninjauan serta persetujuan dari Admin QMS:
                </p>

                <!-- METADATA TABLE -->
                <table width="100%" cellpadding="12" cellspacing="0" style="border-collapse: collapse; border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
                    <tr bgcolor="#F9FAFB">
                        <td colspan="2" style="font-size: 11px; font-weight: 700; text-transform: capitalize; letter-spacing: 0.5px; color: #1677B8; border-bottom: 1px solid #E5E7EB;">
                            Informasi Permohonan Revisi
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
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Diajukan Oleh</td>
                        <td style="font-size: 12px; color: #1677B8; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $submitter->full_name ?? ($submitter->username ?? 'User Pemohon') }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Tanggal Pengajuan</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $revisionRequest->created_at ? $revisionRequest->created_at->format('d F Y - H:i') . ' WIB' : now()->format('d F Y - H:i') . ' WIB' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280;">Alasan Revisi</td>
                        <td style="font-size: 12px; color: #374151; font-weight: 500;">{{ $revisionRequest->reason }}</td>
                    </tr>
                </table>

                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin: 0 0 24px 0;">
                    Silakan buka menu <strong>Verifikasi Aktivitas</strong> pada tab <strong>Permohonan Revisi</strong> untuk meninjau dan menentukan apakah permohonan disetujui atau ditolak.
                </p>

                <!-- BUTTON CTA -->
                <table align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" bgcolor="#1677B8" style="border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(22, 119, 184, 0.25);">
                            <a href="{{ $actionUrl ?? route('admin.user_reviews.index', ['tab' => 'revision', 'status' => 'pending']) }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 12px; font-weight: 700; color: #ffffff; text-decoration: none; text-transform: capitalize; letter-spacing: 0.5px;">
                                TINJAU PERMOHONAN REVISI &rarr;
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
