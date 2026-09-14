<!doctype html>
<html lang="kk">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>A5 QR · {{ $restaurant->name }}</title>
<link rel="stylesheet" href="{{ asset('fonts.css') }}">
<style>
@page{size:A5 portrait;margin:0}
*{box-sizing:border-box}
:root{--navy:#111a3a;--blue:#4f56e8;--coral:#f36e58;--mist:#eef1ff}
html,body{margin:0;min-height:100%;background:#dfe3ed;color:var(--navy);font-family:'Noto Sans',Arial,sans-serif}
.screen-actions{position:fixed;z-index:10;top:18px;left:50%;display:flex;width:min(148mm,calc(100% - 28px));justify-content:space-between;gap:12px;transform:translateX(-50%)}
.action{display:inline-flex;min-height:42px;align-items:center;justify-content:center;padding:10px 16px;border:1px solid rgba(17,26,58,.12);border-radius:12px;background:#fff;color:var(--navy);font:750 13px/1 'Noto Sans',Arial,sans-serif;text-decoration:none;box-shadow:0 8px 24px rgba(17,26,58,.13);cursor:pointer}.action.primary{border-color:var(--blue);background:var(--blue);color:#fff}
.sheet{position:relative;width:148mm;height:210mm;margin:82px auto 34px;overflow:hidden;background:linear-gradient(148deg,#fafbff 0 42%,#eef1ff 100%);box-shadow:0 28px 75px rgba(17,26,58,.2)}
.sheet:before{content:'';position:absolute;inset:-22mm auto auto -20mm;width:88mm;height:88mm;border:18mm solid rgba(79,86,232,.08);border-radius:50%}
.sheet:after{content:'';position:absolute;right:-28mm;bottom:-31mm;width:94mm;height:94mm;border:20mm solid rgba(243,110,88,.1);border-radius:50%}
.frame{position:absolute;z-index:1;inset:8mm;border:1px solid rgba(79,86,232,.2);border-radius:8mm}
.content{position:relative;z-index:2;display:flex;height:100%;padding:16mm 13mm 12mm;align-items:center;flex-direction:column;text-align:center}
.brand{font-size:8mm;font-weight:850;letter-spacing:-.8mm;line-height:1}.brand i{color:var(--coral);font-style:normal}.brand-sub{margin-top:2.5mm;color:#737b98;font-size:2.35mm;font-weight:800;letter-spacing:.75mm;text-transform:uppercase}
.restaurant{max-width:112mm;margin:10mm 0 3mm;color:var(--blue);font-size:3mm;font-weight:850;letter-spacing:.55mm;text-transform:uppercase;overflow-wrap:anywhere}
h1{max-width:115mm;margin:0;font:italic 700 10.5mm/.96 'Noto Serif Display','Times New Roman',serif;letter-spacing:-.35mm}
.lead{max-width:103mm;margin:5mm 0 0;color:#626b88;font-size:3.25mm;line-height:1.45}
.qr-stage{position:relative;display:grid;width:73mm;height:73mm;margin:7mm 0 5mm;place-items:center;border-radius:15mm;background:#fff;box-shadow:0 8mm 18mm rgba(48,55,130,.14)}
.qr-stage:before,.qr-stage:after{content:'';position:absolute;width:14mm;height:14mm;border:1.3mm solid var(--coral)}
.qr-stage:before{top:-2.5mm;left:-2.5mm;border-right:0;border-bottom:0;border-radius:6mm 0 0}.qr-stage:after{right:-2.5mm;bottom:-2.5mm;border-top:0;border-left:0;border-radius:0 0 6mm}
.qr-stage img{display:block;width:62mm;height:62mm}.scan{font-size:3.3mm;font-weight:850}.scan-sub{margin-top:1.5mm;color:#727a96;font-size:2.7mm}
.fine{margin-top:auto;color:#8a91aa;font-size:2.15mm;letter-spacing:.18mm}
@media(max-width:650px){.screen-actions{top:10px}.sheet{width:calc(100vw - 20px);height:auto;min-height:calc((100vw - 20px)*1.4189);margin-top:66px}.content{padding:11vw 8vw 7vw}.brand{font-size:7vw}.brand-sub{font-size:1.8vw}.restaurant{margin-top:7vw;font-size:2.4vw}h1{font-size:7.6vw}.lead{font-size:2.55vw}.qr-stage{width:49vw;height:49vw;margin:5vw 0 4vw}.qr-stage img{width:42vw;height:42vw}.scan{font-size:2.8vw}.scan-sub{font-size:2.25vw}.fine{font-size:1.75vw}}
@media print{html,body{width:148mm;height:210mm;background:#fff}.screen-actions{display:none}.sheet{width:148mm;height:210mm;margin:0;box-shadow:none;print-color-adjust:exact;-webkit-print-color-adjust:exact}}
</style>
</head>
<body>
<nav class="screen-actions" aria-label="Действия">
    <a class="action" href="{{ request()->routeIs('admin.*') ? route('admin.dashboard') : route('restaurant.dashboard').'#settings' }}">Назад</a>
    <button class="action primary" type="button" onclick="window.print()">Распечатать A5</button>
</nav>
<main class="sheet">
    <div class="frame" aria-hidden="true"></div>
    <section class="content">
        <div class="brand">zharzhar<i>.</i></div><div class="brand-sub">ерекше күн осында басталады</div>
        <div class="restaurant">{{ $restaurant->name }}</div>
        <h1>Тойыңызға арналған шақыруды жасаңыз</h1>
        <p class="lead">Қонақтарыңызды әдемі онлайн шақырумен қарсы алыңыз. Дизайн, музыка және жауаптар бір сілтемеде.</p>
        <a class="qr-stage" href="{{ $whatsappUrl }}" aria-label="WhatsApp арқылы шақыруға тапсырыс беру"><img src="{{ $qrDataUrl }}" alt="{{ $restaurant->name }} мейрамханасының QR-коды"></a>
        <div class="scan">QR-кодты сканерлеңіз</div><div class="scan-sub">Отсканируйте QR-код, чтобы заказать приглашение</div>
        <div class="fine">QR-кодта «{{ $restaurant->name }}» мейрамханасының атауы автоматты түрде көрсетіледі</div>
    </section>
</main>
</body>
</html>
