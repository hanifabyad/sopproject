<!DOCTYPE html>
<html>
<head>
    <style>
        .button {
            padding: 10px 20px;
            background-color: #1e293b;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Halo, {{ $user->username }}!</h2>
    <p>Ada dokumen baru yang membutuhkan review dan tanda tangan Anda di sistem <strong>e-QMS</strong>.</p>
    
    <table style="border: 1px solid #ddd; padding: 15px; width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 5px; border-bottom: 1px solid #eee;"><strong>Judul Dokumen</strong></td>
            <td style="padding: 5px; border-bottom: 1px solid #eee;">: {{ $document->title }}</td>
        </tr>
        <tr>
            <td style="padding: 5px;"><strong>Unit Bisnis (BU)</strong></td>
            <td style="padding: 5px;">: {{ $document->department }}</td>
        </tr>
    </table>

    <p>Silakan login ke dashboard Anda untuk memeriksa dokumen tersebut secara detail.</p>
    <p style="margin-top: 25px;">
        <a href="{{ $magicLoginUrl }}" class="button">Buka e-QMS</a>
    </p>
    <p style="font-size: 0.8em; color: #777; margin-top: 30px;">Ini adalah email otomatis, mohon tidak membalas email ini.</p>
</body>
</html>