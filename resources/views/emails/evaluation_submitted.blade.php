<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Evaluasi SOP Masuk - e-QMS</title>
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

        <!-- COLOR ACCENT BAR (PURPLE / INDIGO) -->
        <tr>
            <td height="4" bgcolor="#6366F1"></td>
        </tr>
        
        <!-- BODY CONTENT -->
        <tr>
            <td style="padding: 36px 28px;">
                <!-- STATUS BADGE -->
                <div style="margin-bottom: 16px;">
                    <span style="display: inline-block; padding: 4px 12px; background-color: #EEF2FF; border: 1px solid #C7D2FE; border-radius: 20px; font-size: 11px; font-weight: 700; color: #4F46E5; text-transform: uppercase; letter-spacing: 0.5px;">
                        HASIL EVALUASI SOP PERIODIK
                    </span>
                </div>

                <h2 style="font-size: 18px; font-weight: 700; margin: 0 0 10px 0; color: #1A1A1A; letter-spacing: -0.01em;">
                    Pemberitahuan untuk Admin QMS
                </h2>
                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin: 0 0 20px 0;">
                    Evaluator telah selesai mengisi dan menyerahkan <strong>kuesioner formulir evaluasi periodik</strong> untuk dokumen SOP berikut:
                </p>

                <!-- METADATA TABLE -->
                <table width="100%" cellpadding="12" cellspacing="0" style="border-collapse: collapse; border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
                    <tr bgcolor="#F9FAFB">
                        <td colspan="2" style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #4F46E5; border-bottom: 1px solid #E5E7EB;">
                            Ringkasan Evaluasi Dokumen
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
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Departemen / Unit</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $document->department }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Evaluator</td>
                        <td style="font-size: 12px; color: #4F46E5; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $evaluator->full_name ?? ($evaluator->username ?? 'Evaluator') }} ({{ $evaluator->role ?? '-' }})</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Kesimpulan Hasil</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 700;">
                            @if($evaluation->result === 'CONTINUE')
                                <span style="color: #059669;">TETAP DIGUNAKAN (CONTINUE)</span>
                            @elseif($evaluation->result === 'REVISION REQUIRED')
                                <span style="color: #D97706;">PERLU REVISI (REVISION REQUIRED)</span>
                            @elseif($evaluation->result === 'OBSOLETE')
                                <span style="color: #DC2626;">KADALUWARSA / OBSOLETE</span>
                            @else
                                <span style="color: #4B5563;">{{ $evaluation->result }}</span>
                            @endif
                        </td>
                    </tr>
                    @if(!empty($evaluation->recommendation))
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280;">Rekomendasi Evaluator</td>
                        <td style="font-size: 12px; color: #374151; font-weight: 500;">{{ $evaluation->recommendation }}</td>
                    </tr>
                    @endif
                </table>

                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin: 0 0 24px 0;">
                    Silakan buka menu <strong>Evaluasi SOP</strong> pada dashboard admin e-QMS untuk meninjau detail kuesioner dan melakukan tindak lanjut hasil evaluasi.
                </p>

                <!-- BUTTON CTA -->
                <table align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" bgcolor="#4F46E5" style="border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25);">
                            <a href="{{ $actionUrl ?? route('admin.evaluations.show', $evaluation->id) }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 12px; font-weight: 700; color: #ffffff; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;">
                                TINJAU HASIL EVALUASI &rarr;
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
