<!doctype html>
<html lang="ru">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $title ?? 'ZharZhar' }}</title><style>body{margin:0;background:#f7f5ef;color:#17332a;font-family:Arial,sans-serif}.shell{max-width:1100px;margin:0 auto;padding:32px 24px}header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd8cc;padding-bottom:20px}.brand{font-size:24px;font-weight:700}.button{background:#c4634b;color:white;border:0;padding:12px 18px;cursor:pointer;text-decoration:none}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}.card{background:#fffdf8;border:1px solid #ddd8cc;padding:22px}.muted{color:#6e776f}.error{color:#a33;margin:8px 0}table{width:100%;border-collapse:collapse;background:#fffdf8}th,td{text-align:left;padding:12px;border-bottom:1px solid #ddd8cc}</style></head>
<body><div class="shell">@yield('content')</div></body>
</html>
