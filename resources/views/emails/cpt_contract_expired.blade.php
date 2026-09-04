<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Kontrak Expired - e-QMS</title>
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

        <!-- COLOR ACCENT BAR (ROSE / RED representing Expired) -->
        <tr>
            <td height="4" bgcolor="#BE123C"></td>
        </tr>
        
        <!-- BODY CONTENT -->
        <tr>
            <td style="padding: 36px 28px;">
                <!-- STATUS BADGE -->
                <div style="margin-bottom: 16px;">
                    <span style="display: inline-block; padding: 4px 12px; background-color: #FFE4E6; border: 1px solid #FECDD3; border-radius: 20px; font-size: 11px; font-weight: 700; color: #BE123C; text-transform: uppercase; letter-spacing: 0.5px;">
                        KONTRAK EXPIRED / KADALUARSA
                    </span>
                </div>

                <h2 style="font-size: 18px; font-weight: 700; margin: 0 0 10px 0; color: #1A1A1A; letter-spacing: -0.01em;">
                    Halo, {{ $recipient->full_name ?? ($recipient->username ?? 'Staf PIC CPT') }}
                </h2>
                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin: 0 0 24px 0;">
                    Pemberitahuan otomatis dari sistem **e-QMS (PT PKM Group)**: Dokumen Kontrak / SPMP pada unit bisnis **PT Cahaya Perdana Transalam (CPT)** di bawah ini telah melewati masa berlaku (Expired) atau memerlukan tindak lanjut perpanjangan/addendum.
                </p>

                <!-- METADATA TABLE -->
                <table width="100%" cellpadding="12" cellspacing="0" style="border-collapse: collapse; border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 24px; overflow: hidden;">
                    <tr bgcolor="#FFF1F2">
                        <td colspan="2" style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #BE123C; border-bottom: 1px solid #FECDD3;">
                            Detail Kontrak CPT
                        </td>
                    </tr>
                    <tr>
                        <td width="35%" style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Customer</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 700;">{{ $contract->customer }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Tipe Dokumen</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $contract->type ?: $contract->contract_type }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Nama Proyek</td>
                        <td style="font-size: 12px; color: #1677B8; border-bottom: 1px solid #F3F4F6; font-weight: 700;">{{ $contract->project_name }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Judul Pekerjaan</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 500;">{{ $contract->project_title }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Nomor Kontrak/SPMP</td>
                        <td style="font-size: 12px; font-family: monospace; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 700;">{{ $contract->project_number }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Periode Kontrak</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 600;">
                            {{ $contract->start_date ? $contract->start_date->format('d/m/Y') : '-' }} s/d {{ $contract->end_date ? $contract->end_date->format('d/m/Y') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Status Saat Ini</td>
                        <td style="font-size: 12px; color: #BE123C; border-bottom: 1px solid #F3F4F6; font-weight: 800; text-transform: uppercase;">
                            {{ $contract->status_label }}
                        </td>
                    </tr>
                    @if($contract->notes)
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280;">Catatan / Tindak Lanjut</td>
                        <td style="font-size: 12px; color: #4B5563; font-style: italic;">{{ $contract->notes }}</td>
                    </tr>
                    @endif
                </table>

                <!-- ACTION INSTRUCTION & BUTTON -->
                <div style="background-color: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 8px; padding: 18px; margin-bottom: 24px;">
                    <p style="font-size: 12px; color: #334155; margin: 0 0 12px 0; line-height: 1.5;">
                        Silakan klik tombol di bawah untuk langsung membuka sistem dan memperbarui status atau informasi perpanjangan kontrak ini:
                    </p>
                    <div style="text-align: center;">
                        <a href="{{ $editUrl }}" style="display: inline-block; background-color: #1677B8; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; padding: 12px 28px; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(22, 119, 184, 0.2);">
                            ✏️ Edit & Perbarui Data Kontrak
                        </a>
                    </div>
                </div>

                <p style="font-size: 11px; color: #9CA3AF; margin: 0; line-height: 1.4;">
                    *Email ini dikirim secara otomatis oleh sistem Electronic Quality Management System (e-QMS) PT PKM Group.
                </p>
            </td>
        </tr>

        <!-- FOOTER -->
        <tr>
            <td align="center" style="padding: 20px; background-color: #F9FAFB; border-top: 1px solid #E5E7EB; font-size: 11px; color: #6B7280;">
                &copy; {{ date('Y') }} PT Putra Kelana Makmur Group &bull; Electronic Quality Management System (e-QMS)
            </td>
        </tr>
    </table>
</body>
</html>
