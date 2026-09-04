<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usulan SOP Baru Masuk - e-QMS</title>
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

        <!-- COLOR ACCENT BAR (BLUE / PRIMARY) -->
        <tr>
            <td height="4" bgcolor="#1677B8"></td>
        </tr>
        
        <!-- BODY CONTENT -->
        <tr>
            <td style="padding: 36px 28px;">
                <!-- STATUS BADGE -->
                <div style="margin-bottom: 16px;">
                    <span style="display: inline-block; padding: 4px 12px; background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 20px; font-size: 11px; font-weight: 700; color: #1E40AF; text-transform: uppercase; letter-spacing: 0.5px;">
                        USULAN SOP BARU MASUK
                    </span>
                </div>

                <h2 style="font-size: 18px; font-weight: 700; margin: 0 0 10px 0; color: #1A1A1A; letter-spacing: -0.01em;">
                    Pemberitahuan untuk Admin QMS
                </h2>
                <p style="font-size: 13px; line-height: 1.6; color: #4B5563; margin: 0 0 20px 0;">
                    Terdapat pengajuan usulan pembuatan SOP baru yang diajukan oleh pengguna dan menunggu peninjauan dari Tim Admin QMS:
                </p>

                <!-- METADATA TABLE -->
                <table width="100%" cellpadding="12" cellspacing="0" style="border-collapse: collapse; border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
                    <tr bgcolor="#F9FAFB">
                        <td colspan="2" style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #1677B8; border-bottom: 1px solid #E5E7EB;">
                            Rincian Usulan Prosedur
                        </td>
                    </tr>
                    <tr>
                        <td width="35%" style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Judul Usulan SOP</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 700;">{{ $sopRequest->title }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Unit / Departemen</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $sopRequest->department }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Diajukan Oleh</td>
                        <td style="font-size: 12px; color: #1677B8; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $submitter->full_name ?? ($submitter->username ?? 'User Pemohon') }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280; border-bottom: 1px solid #F3F4F6;">Tanggal Pengajuan</td>
                        <td style="font-size: 12px; color: #1A1A1A; border-bottom: 1px solid #F3F4F6; font-weight: 600;">{{ $sopRequest->created_at ? $sopRequest->created_at->format('d F Y - H:i') . ' WIB' : now()->format('d F Y - H:i') . ' WIB' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: 600; color: #6B7280;">Deskripsi & Urgensi</td>
                        <td style="font-size: 12px; color: #374151; font-weight: 500; line-height: 1.5; white-space: pre-line;">{{ $sopRequest->description }}</td>
                    </tr>
                </table>

                <!-- ACTION BUTTON -->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 28px 0;">
                    <tr>
                        <td align="center">
                            <a href="{{ $actionUrl ?? url('/admin/user-reviews?tab=new_sop') }}" 
                               style="display: inline-block; background-color: #1677B8; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; padding: 13px 28px; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(22, 119, 184, 0.2);">
                                Buka & Review Usulan SOP Baru &rarr;
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 11px; color: #9CA3AF; margin: 0; text-align: center;">
                    Tautan ini bersifat rahasia dan aman. Mohon tidak membagikan email ini kepada pihak lain.
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
