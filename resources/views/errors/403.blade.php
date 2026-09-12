<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>403 - Akses Ditolak</title>
</head>
<body style="margin: 0; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: ui-sans-serif, system-ui, sans-serif; background-color: #fff; color: #1f2937;">
    <div style="display: flex; align-items: center; gap: 16px;">
        <span style="font-size: 24px; font-weight: 700; border-right: 2px solid #e5e7eb; padding-right: 16px; color: #800020;">
            403
        </span>
        <span style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #4b5563;">
            {{ $message ?? 'Akses Ditolak: Role Anda tidak diizinkan.' }}
        </span>
    </div>
</body>
</html>