<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Pemahaman SOP Berkala (6 Bulan) - e-QMS</title>
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

        <!-- COLOR ACCENT BAR (BLUE / CYAN e-QMS) -->
        <tr>
            <td height="4" bgcolor="#1677B8"></td>
        </tr>
        
        <!-- BODY CONTENT -->
        <tr>
            <td style="padding: 36px 28px;">
                <!-- STATUS BADGE -->
                <div style="margin-bottom: 16px;">
                    <span style="display: inline-block; padding: 4px 12px; background-color: #E0F2FE; border: 1px solid #BAE6FD; border-radius: 20px; font-size: 11px; font-weight: 700; color: #0369A1; text-transform: uppercase; letter-spacing: 0.5px;">
                        UJI PEMAHAMAN SOP BERKALA (6 BULAN)
                    </span>
                </div>

                <h2 style="font-size: 18px; font-weight: 800; margin: 0 0 10px 0; color: #0F172A; letter-spacing: -0.01em;">
                    Halo, {{ $user->full_name ?: $user->username }} ({{ $user->role }})
                </h2>
                <p style="font-size: 13px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
                    Sesuai dengan ketentuan penjaminan mutu operasional di lingkungan <strong>PT PKM Group</strong>, setiap <strong>6 (enam) bulan</strong> seluruh personil (Kepala Departemen maupun seluruh anggota bidang) wajib mengikuti uji pemahaman SOP secara berkala pada sistem <strong>e-QMS</strong>.
                </p>

                <!-- METADATA TABLE -->
                <table width="100%" cellpadding="12" cellspacing="0" style="border-collapse: collapse; border: 1px solid #E2E8F0; border-radius: 8px; margin-bottom: 24px; overflow: hidden; background-color: #F8FAFC;">
                    <tr bgcolor="#F1F5F9">
                        <td colspan="2" style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #1677B8; border-bottom: 1px solid #E2E8F0;">
                            Rincian Dokumen SOP
                        </td>
                    </tr>
                    <tr>
                        <td width="35%" style="font-size: 12px; font-weight: 600; color: #64748B; border-bottom: 1px solid #F1F5F9;">Judul Prosedur</td>
                        <td style="font-size: 12px; color: #0F172A; border-bottom: 1px solid #F1F5F9; font-weight: 700;">{{ $document->title }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #64748B; border-bottom: 1px solid #F1F5F9;">Nomor Dokumen</td>
                        <td style="font-size: 12px; color: #0F172A; border-bottom: 1px solid #F1F5F9; font-weight: 700; font-family: monospace;">{{ $document->doc_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #64748B; border-bottom: 1px solid #F1F5F9;">Bidang / Unit</td>
                        <td style="font-size: 12px; color: #0F172A; border-bottom: 1px solid #F1F5F9; font-weight: 700;">{{ $document->department }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #64748B; border-bottom: 1px solid #F1F5F9;">Format Ujian</td>
                        <td style="font-size: 12px; color: #0F172A; border-bottom: 1px solid #F1F5F9; font-weight: 600;">15 Soal Pilihan Ganda</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #64748B;">Standar KKM</td>
                        <td style="font-size: 12px; color: #1677B8; font-weight: 800;">Minimal 60 Poin (Wajib Lulus)</td>
                    </tr>
                </table>

                <!-- INFO BOX -->
                <div style="padding: 14px 16px; background-color: #EFF6FF; border-left: 4px solid #1677B8; border-radius: 4px; margin-bottom: 24px; font-size: 12px; line-height: 1.5; color: #1E40AF;">
                    <strong>Petunjuk Pelaksanaan:</strong><br>
                    Kuis ini bertujuan untuk merefresh pemahaman alur kerja dan mitigasi risiko operasional. Apabila nilai kuis Anda belum mencapai standar KKM 60, Anda dapat mengulang kuis sampai memenuhi kriteria kelulusan.
                </div>

                <!-- BUTTON CTA -->
                <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                    <tr>
                        <td align="center" bgcolor="#1677B8" style="border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(22, 119, 184, 0.3);">
                            <a href="{{ $quizUrl }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 12.5px; font-weight: 800; color: #ffffff; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;">
                                KERJAKAN KUIS PEMAHAMAN SOP &rarr;
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 11px; color: #64748B; margin: 0; line-height: 1.5; text-align: center;">
                    Tautan di atas aman dan otomatis mengenali akun Anda tanpa perlu memasukkan kata sandi kembali (Magic Link aktif selama 7 hari).
                </p>
            </td>
        </tr>

        <!-- FOOTER -->
        <tr>
            <td bgcolor="#F8FAFC" style="padding: 22px 28px; border-top: 1px solid #E2E8F0; text-align: center;">
                <p style="font-size: 11px; color: #334155; margin: 0 0 4px 0; font-weight: 700;">
                    PT PUTRA KELANA MAKMUR (PKM GROUP)
                </p>
                <p style="font-size: 10px; color: #94A3B8; margin: 0; line-height: 1.4;">
                    Notifikasi resmi sistem penjaminan mutu Electronic Quality Management System (e-QMS).
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
