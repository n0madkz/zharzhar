@extends('layouts.zharzhar')

@section('content')
<style>
.restaurant-app{display:grid;grid-template-columns:210px minmax(0,1fr);gap:28px;min-height:88vh}.restaurant-nav{border-right:1px solid #ddd8cc;padding:8px 22px 20px 0}.restaurant-nav a{display:block;padding:12px 14px;margin:4px 0;color:#17332a;text-decoration:none}.restaurant-nav a.active,.restaurant-nav a:hover{background:#17332a;color:#fff}.restaurant-main{min-width:0}.restaurant-top{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd8cc;padding-bottom:20px}.restaurant-top form{margin:0}.calendar-layout{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(310px,1fr);gap:20px;align-items:start}.panel{background:#fffdf8;border:1px solid #ddd8cc;padding:22px}.calendar-heading{display:flex;justify-content:space-between;align-items:center}.calendar-heading h2{margin:0}.month-actions{display:flex;gap:8px}.month-actions a{border:1px solid #cfc9bc;padding:8px 12px;color:#17332a;text-decoration:none}.weekdays,.calendar-grid{display:grid;grid-template-columns:repeat(7,1fr)}.weekdays{margin-top:22px;color:#798078;font-size:12px;text-align:center}.calendar-grid{border-top:1px solid #ddd8cc;border-left:1px solid #ddd8cc;margin-top:8px}.day{min-height:78px;padding:9px;border-right:1px solid #ddd8cc;border-bottom:1px solid #ddd8cc;color:#17332a;text-decoration:none}.day.muted-day{color:#a5aaa4;background:#faf8f2}.day.selected{background:#eef3ec;outline:2px solid #17332a;outline-offset:-2px}.day-number{display:block;font-weight:600}.slot-bars{display:grid;gap:5px;margin-top:13px}.slot-bar{height:7px;border:1px solid var(--slot-color);background:transparent}.slot-bar.busy{background:var(--slot-color)}.legend{display:flex;gap:15px;flex-wrap:wrap;margin-top:16px;color:#69736b;font-size:12px}.legend-item{display:flex;gap:6px;align-items:center}.legend-mark{width:18px;height:7px;border:1px solid var(--slot-color);display:inline-block}.legend-mark.busy{background:var(--slot-color)}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.field{display:block;margin-bottom:14px}.field.full{grid-column:1/-1}.field input,.field select,.field textarea{width:100%;padding:11px;margin-top:6px;box-sizing:border-box;border:1px solid #cfc9bc;background:#faf9f5}.field textarea{resize:vertical}.submit{width:100%;margin-top:4px}.notice{padding:10px 12px;background:#eef3ec;color:#26724d;margin-bottom:15px}.booking-list{margin-top:24px}.booking-row{display:grid;grid-template-columns:5px 1fr auto;gap:14px;align-items:center;padding:14px 0;border-top:1px solid #ddd8cc}.booking-color{height:100%;background:var(--booking-color)}.booking-row p{margin:4px 0;color:#69736b;font-size:14px}.status{font-size:12px;color:#69736b}
@media(max-width:850px){.restaurant-app{grid-template-columns:1fr}.restaurant-nav{border-right:0;border-bottom:1px solid #ddd8cc;padding:0 0 10px;display:flex;gap:4px;overflow:auto}.restaurant-nav a{white-space:nowrap}.calendar-layout{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.day{min-height:64px;padding:6px}.slot-bars{gap:3px;margin-top:9px}.restaurant-top{gap:15px}}
</style>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');
    :root { --ui-ink:#142b24; --ui-muted:#718078; --ui-line:#e7ece9; --ui-soft:#f7faf8; --ui-accent:#c96952; }
    html { scroll-behavior:smooth; }
    body { background:#fff !important; }
    body, input, select, textarea, button { font-family:'Manrope', Arial, sans-serif; }
    h1, h2, h3, strong { letter-spacing:-.035em; }
    .restaurant-app { max-width:1320px; margin:0 auto; gap:34px; }
    .restaurant-nav { background:#f8faf9; border:1px solid #e6ebe7; border-radius:18px; padding:18px 12px; }
    .restaurant-nav a { border-radius:10px; transition:background .18s ease, color .18s ease; }
    .restaurant-nav a .nav-icon { display:inline-grid; width:20px; height:20px; place-items:center; margin-right:8px; vertical-align:middle; }
    .restaurant-nav a .nav-icon svg { width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:1.8; }
    .month-actions { display:grid; grid-template-columns:44px minmax(92px,auto) 44px; align-items:center; gap:8px; }
    .month-actions a { height:44px; display:inline-flex; align-items:center; justify-content:center; box-sizing:border-box; color:var(--ui-ink); text-decoration:none; border:1px solid #dce5df; background:#fff; }
    .month-arrow { width:44px; border-radius:50%; }
    .month-arrow svg { display:block; width:22px; height:22px; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
    .calendar-today { min-width:92px; padding:0 14px; border-radius:999px; font-size:13px; font-weight:700; }
    .restaurant-nav a.active { background:#17332a; }
    .restaurant-nav a.active { background:transparent; color:var(--ui-ink); }
    .restaurant-nav a.current { background:#17332a; color:#fff; }
    .restaurant-nav a.active { background:transparent !important; color:var(--ui-ink) !important; }
    .restaurant-nav a.current { background:#17332a !important; color:#fff !important; }
    .panel { background:#fff; border:1px solid #e7ebe9; border-radius:16px; box-shadow:0 8px 26px rgba(23,51,42,.06); }
    .restaurant-main h1 { font-size:clamp(30px,4vw,46px); font-weight:800; }
    .restaurant-main h2 { font-size:22px; font-weight:800; }
    .button { border-radius:10px; font-weight:700; transition:transform .18s ease, box-shadow .18s ease, background .18s ease; }
    .button:hover { background:#b85741; box-shadow:0 8px 16px rgba(201,105,82,.22); transform:translateY(-1px); }
    .calendar-panel { position:relative; overflow:hidden; background:linear-gradient(145deg,#fff 0%,#fbfdfb 100%); }
    .calendar-heading { gap:18px; }
    .calendar-title span { display:block; margin-bottom:5px; color:var(--ui-muted); font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .calendar-heading h2 { margin:0; font-size:26px; letter-spacing:-.03em; text-transform:capitalize; }
    .month-actions a { transition:background .18s ease, border-color .18s ease, transform .18s ease; }
    .month-actions a:hover { border-color:#a9bdb1; background:#eef5f0; transform:translateY(-1px); }
    .month-actions a:focus-visible { outline:3px solid rgba(23,51,42,.18); outline-offset:2px; }
    .calendar-live { min-height:18px; margin:8px 0 -8px; color:var(--ui-muted); font-size:12px; }
    .calendar-panel.is-loading .calendar-grid { opacity:.55; pointer-events:none; }
    .calendar-panel.is-loading .calendar-heading h2:after { content:' '; display:inline-block; width:12px; height:12px; margin-left:9px; border:2px solid #cbd8d0; border-top-color:var(--ui-ink); border-radius:50%; animation:calendar-spin .7s linear infinite; }
    @keyframes calendar-spin { to { transform:rotate(360deg); } }
    .weekdays { margin-top:18px; padding:0 4px; font-weight:700; }
    .weekdays span:nth-child(6), .weekdays span:nth-child(7) { color:#bd604b; }
    .calendar-grid { gap:7px; border:0; transition:opacity .16s ease; }
    .day { min-height:88px; padding:11px; border:1px solid #e7ebe8; border-radius:12px; background:#fff; transition:border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
    .day:hover { border-color:#9bb3a4; box-shadow:0 5px 12px rgba(23,51,42,.08); transform:translateY(-1px); }
    .day.muted-day { background:#fafbfa; border-color:#eef1ef; }
    .day.selected { background:#eff6f1; outline:2px solid #17332a; outline-offset:-2px; }
    .day:nth-child(7n + 6):not(.muted-day) .day-number, .day:nth-child(7n + 7):not(.muted-day) .day-number { color:#bd604b; }
    .day-number { font-size:14px; }
    .field input,.field select,.field textarea { border-radius:10px; border-color:#dfe6e1; background:#fbfcfb; transition:border-color .18s ease, box-shadow .18s ease; }
    .field input:focus,.field select:focus,.field textarea:focus { outline:none; border-color:#789c87; box-shadow:0 0 0 4px rgba(120,156,135,.14); }
    .slot-bars { display:flex; gap:5px; min-height:9px; margin-top:16px; }
    .slot-bar { display:none; width:9px; height:9px; border:0; border-radius:50%; background:transparent; }
    .slot-bar.busy { display:block; background:var(--slot-color); box-shadow:0 0 0 3px color-mix(in srgb, var(--slot-color) 16%, transparent); }
    .legend { display:none; }
    .legend-mark { height:6px; border-radius:4px; }
    .booking-list { scroll-margin-top:20px; scroll-margin-bottom:96px; }
    .reports-panel { min-width:0; max-width:100%; overflow:hidden; }
    .report-filter { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; align-items:end; }
    .report-filter .button { min-height:44px; }
    .report-table-wrap { width:100%; max-width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .report-table-wrap table { width:100%; min-width:1120px; table-layout:fixed; border-collapse:collapse; }
    .report-table-wrap th, .report-table-wrap td { padding:12px 10px; text-align:left; vertical-align:middle; overflow-wrap:anywhere; border-bottom:1px solid #e7ebe8; }
    .report-table-wrap th:nth-child(1) { width:90px; }
    .report-table-wrap th:nth-child(6) { width:70px; }
    .report-table-wrap th:nth-child(7), .report-table-wrap th:nth-child(8), .report-table-wrap th:nth-child(9) { width:110px; }
    .pagination { margin-top:18px; overflow-x:auto; }
    .pagination nav { display:flex; justify-content:center; }
    .pagination nav > div { display:flex; gap:6px; align-items:center; flex-wrap:wrap; justify-content:center; }
    .pagination a, .pagination span { display:inline-flex; min-width:40px; min-height:40px; padding:0 10px; align-items:center; justify-content:center; box-sizing:border-box; border:1px solid #dfe6e1; border-radius:9px; color:var(--ui-ink); text-decoration:none; }
    .pagination span[aria-current="page"] { background:#17332a; color:#fff; border-color:#17332a; }
    .info-panel { margin-top:22px; scroll-margin-top:20px; scroll-margin-bottom:96px; }
    .settings-row { display:flex; justify-content:space-between; gap:20px; padding:13px 0; border-top:1px solid #e7ebe8; }
    .booking-card { border-top:1px solid #e7ebe8; }
    .booking-card summary { display:grid; grid-template-columns:5px 1fr auto; gap:14px; align-items:center; padding:15px 0; cursor:pointer; list-style:none; }
    .booking-card summary::-webkit-details-marker { display:none; }
    .booking-card summary small { display:block; margin-top:4px; color:var(--ui-muted); font-size:13px; }
    .booking-card summary:after { content:'+'; font-size:22px; color:var(--ui-muted); }
    .booking-card[open] summary:after { content:'−'; }
    .booking-edit-form { padding:4px 0 16px; }
    .booking-preview { padding:4px 0 16px 19px; color:var(--ui-muted); font-size:14px; }
    .booking-preview p { margin:7px 0; }
    .booking-preview strong { color:var(--ui-ink); }
    .booking-actions { display:flex; gap:10px; align-items:center; }
    .booking-support { display:flex; flex-wrap:wrap; gap:10px; align-items:center; padding:0 0 18px; }
    .booking-support a { text-decoration:none; }
    .button-secondary { background:#fff; color:var(--ui-ink); border:1px solid #d8e1dc; }
    .button-whatsapp { background:#e8f5ed; color:#16653d; border:1px solid #b9ddc6; }
    .mobile-logout { display:none; }
    .mobile-logout button { width:100%; height:100%; border:0; background:transparent; color:var(--ui-ink); cursor:pointer; font:inherit; }
    .settings-logout { display:block; margin-top:20px; }
    .restaurant-top form { display:none !important; }
    .calendar-add-booking { display:block; margin-top:18px; text-align:center; text-decoration:none; }
    .day-modal-backdrop { position:fixed; inset:0; z-index:50; display:none; align-items:center; justify-content:center; padding:20px; background:rgba(15,35,28,.42); }
    .day-modal-backdrop.is-open { display:flex; }
    .day-modal { position:relative; width:min(100%,520px); max-height:min(80vh,680px); overflow:visible; padding:24px; background:#fff; border-radius:18px; box-shadow:0 24px 70px rgba(15,35,28,.24); }
    .day-modal-events, .day-modal-form { max-height:calc(80vh - 110px); overflow:auto; }
    .pricing-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; margin-top:12px; }
    .booking-total { grid-column:1 / -1; margin:0; padding:12px 14px; border-radius:10px; background:#f1f6f2; color:var(--ui-ink); }
    .booking-modal-details p { margin:0; padding:10px 0; border-bottom:1px solid #edf1ee; }
    .booking-modal-actions { display:flex; gap:10px; margin-top:16px; align-items:center; }
    .booking-modal-actions form { margin:0; }
    .settings-price { display:block; margin-top:18px; max-width:360px; }
    .settings-price input { display:block; width:100%; margin-top:7px; }
    .settings-price small { display:block; margin-top:5px; color:var(--ui-muted); }
    @media (max-width:850px) { .report-filter { grid-template-columns:1fr; } .report-filter .button, .report-filter a { width:100%; text-align:center; } }
    @media (max-width:560px) { .pricing-fields { grid-template-columns:1fr; } }
    .day-modal-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; border-bottom:1px solid #e7ebe8; padding-bottom:16px; }
    .day-modal-head h2 { margin:0; font-size:26px; }
    .day-modal-close { position:absolute; top:-16px; right:-16px; z-index:2; width:36px; height:36px; border:1px solid #dfe6e1; border-radius:50%; background:#fff; font-size:24px; line-height:30px; cursor:pointer; color:var(--ui-ink); box-shadow:0 8px 20px rgba(15,35,28,.18); }
    .day-event { display:flex; gap:12px; align-items:flex-start; padding:14px 0; border-bottom:1px solid #edf0ee; }
    .day-event-mark { width:8px; min-width:8px; height:8px; margin-top:6px; border-radius:50%; background:#c96952; }
    .day-event strong { display:block; }
    .day-event small { display:block; margin-top:4px; color:var(--ui-muted); }
    .day-empty { color:var(--ui-muted); padding:20px 0; }
    .button-danger { background:#fff; color:#b24e43; border:1px solid #e5b6ae; }
    .button-danger:hover { background:#fff0ed; color:#9d4035; }
        @media(max-width:850px){
        body{padding-bottom:82px}
        .restaurant-app{display:block;padding-bottom:70px}
        .restaurant-nav{position:fixed;z-index:20;left:12px;right:12px;bottom:12px;height:68px;border:1px solid #e1e8e3;border-radius:18px;background:rgba(255,255,255,.97);box-shadow:0 10px 30px rgba(23,51,42,.16);padding:5px;display:grid;grid-template-columns:repeat(4,1fr);gap:3px;backdrop-filter:blur(14px)}
        .restaurant-nav .brand{display:none}
        .restaurant-nav a{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;text-align:center;font-size:10px;line-height:1.1;margin:0;padding:6px 2px;min-height:44px;font-weight:600}
        .restaurant-nav a .nav-icon{width:20px;height:20px;display:grid;place-items:center;font-size:0;margin-right:0}
        .restaurant-nav a .nav-icon svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.8}
        .mobile-logout{display:block;margin:0;padding:0;min-height:44px}
        .mobile-logout .nav-icon{display:grid;place-items:center;width:20px;height:20px;margin:0 auto}
        .mobile-logout svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.8}
        .settings-logout{display:block;margin:14px 0 0}.settings-logout .button{width:100%}
        .calendar-panel{padding:15px 12px 18px}
        .calendar-heading{display:grid;grid-template-columns:1fr;align-items:stretch;gap:14px}
        .calendar-title{text-align:center}
        .calendar-title span{margin-bottom:3px;font-size:10px}
        .month-actions{grid-template-columns:46px minmax(0,1fr) 46px;width:100%;gap:9px}
        .month-actions a{height:46px}
        .month-arrow{width:46px}
        .month-arrow svg{width:23px;height:23px}
        .calendar-today{width:100%;font-size:13px}
        .calendar-live{text-align:center;margin:5px 0 -5px}
        .weekdays{margin-top:15px;padding:0;font-size:10px}
        .calendar-grid{gap:4px}
        .day{aspect-ratio:1;min-height:0;padding:7px 5px;border-radius:9px}
        .day:hover{transform:none;box-shadow:none}
        .day-number{font-size:12px}
        .slot-bars{gap:3px;margin-top:7px}
        .slot-bar{width:7px;height:7px}
        .settings-row{align-items:flex-start;flex-direction:column;gap:10px}.settings-row>span:last-child{width:100%;justify-content:space-between}.settings-row input[type=time]{flex:1;min-width:0}
        .restaurant-top{padding:14px 0}.restaurant-main h1{font-size:30px;margin:24px 0 10px}.panel{padding:16px;border-radius:14px}.calendar-heading h2{font-size:23px}.booking-row{gap:10px}.booking-support a,.booking-support button{width:100%;text-align:center}
    }
    @media(max-width:380px){
        .calendar-panel{padding-left:9px;padding-right:9px}
        .calendar-grid{gap:3px}
        .day{padding:6px 4px;border-radius:8px}
        .slot-bars{gap:2px}
    }
    /* Современная палитра кабинета партнёра */
    :root{--ui-ink:#172033;--ui-muted:#667085;--ui-line:#e4e7ec;--ui-soft:#f8fafc;--ui-accent:#2563eb}
    body{background:radial-gradient(circle at 90% 0,#e7efff 0,transparent 25%),#f4f7fb !important;color:var(--ui-ink)}
    .restaurant-app{max-width:1380px;gap:26px}
    .restaurant-nav{border:0;border-radius:18px;background:#111827;box-shadow:0 14px 36px rgba(15,23,42,.16)}
    .restaurant-nav .brand{color:#fff;letter-spacing:-.04em}
    .restaurant-nav a{color:#cbd5e1;font-weight:650}
    .restaurant-nav a:hover{background:#1f2937;color:#fff}
    .restaurant-nav a.active{background:transparent !important;color:#cbd5e1 !important}
    .restaurant-nav a.current{background:#2563eb !important;color:#fff !important;box-shadow:0 7px 18px rgba(37,99,235,.3)}
    .restaurant-top{padding:17px 20px;border:1px solid #e4e7ec;border-radius:16px;background:rgba(255,255,255,.9);box-shadow:0 10px 30px rgba(15,23,42,.055);backdrop-filter:blur(12px)}
    .restaurant-main h1,.restaurant-main h2{color:#101828}
    .panel{border-color:#e4e7ec;border-radius:17px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.06)}
    .calendar-panel{background:linear-gradient(145deg,#fff 0%,#f7faff 100%)}
    .month-actions a{border-color:#d0d5dd;color:#344054;background:#fff}
    .month-actions a:hover{border-color:#84adff;background:#eff4ff;color:#1d4ed8}
    .calendar-title span{color:#2563eb}
    .weekdays{color:#667085}.weekdays span:nth-child(6),.weekdays span:nth-child(7){color:#e11d48}
    .day{border-color:#e4e7ec;color:#344054;background:#fff;box-shadow:0 2px 4px rgba(15,23,42,.02)}
    .day:hover{border-color:#84adff;box-shadow:0 6px 14px rgba(37,99,235,.09)}
    .day.muted-day{border-color:#eef1f5;background:#f8fafc;color:#98a2b3}
    .day.selected{outline-color:#2563eb;background:#eff4ff;color:#1d4ed8}
    .day:nth-child(7n + 6):not(.muted-day) .day-number,.day:nth-child(7n + 7):not(.muted-day) .day-number{color:#e11d48}
    .field input,.field select,.field textarea{border-color:#d0d5dd;border-radius:10px;background:#fff;color:#172033}
    .field input:focus,.field select:focus,.field textarea:focus{border-color:#84adff;box-shadow:0 0 0 4px rgba(37,99,235,.11)}
    .button{border-radius:10px;background:#2563eb;box-shadow:0 6px 16px rgba(37,99,235,.18)}
    .button:hover{background:#1d4ed8;box-shadow:0 9px 22px rgba(37,99,235,.23)}
    .button-secondary{border-color:#d0d5dd;background:#fff;color:#344054;box-shadow:none}
    .button-secondary:hover{background:#f8fafc;color:#172033;box-shadow:none}
    .button-whatsapp{border-color:#bbf7d0;background:#ecfdf3;color:#067647;box-shadow:none}
    .button-whatsapp:hover{background:#dcfce7;color:#067647;box-shadow:none}
    .button-danger{border-color:#fecaca;background:#fff;color:#b42318;box-shadow:none}
    .button-danger:hover{background:#fef2f2;color:#b42318;box-shadow:none}
    .notice{border:1px solid #bbf7d0;border-radius:10px;background:#f0fdf4;color:#166534}
    .booking-total{background:#eff4ff;color:#1d4ed8}
    .booking-card,.settings-row,.booking-modal-details p,.day-event{border-color:#eef1f5}
    .day-modal-backdrop{background:rgba(15,23,42,.5);backdrop-filter:blur(4px)}
    .day-modal{border:1px solid #e4e7ec;box-shadow:0 28px 80px rgba(15,23,42,.28)}
    .day-modal-close{border-color:#e4e7ec;color:#344054;box-shadow:0 8px 20px rgba(15,23,42,.16)}
    .day-event-mark{background:#2563eb}
    .pagination a,.pagination span{border-color:#d0d5dd;color:#344054;background:#fff}
    .pagination span[aria-current="page"]{border-color:#2563eb;background:#2563eb;color:#fff}
    .report-table-wrap{border:1px solid #e4e7ec;border-radius:12px}.report-table-wrap th{background:#f8fafc;color:#667085}.report-table-wrap td{border-color:#eef1f5}
    @media(max-width:850px){
        body{background:#f4f7fb !important}
        .restaurant-nav{border:1px solid #e4e7ec;background:rgba(255,255,255,.96);box-shadow:0 14px 34px rgba(15,23,42,.15)}
        .restaurant-nav a{color:#667085}
        .restaurant-nav a:hover{background:#eff4ff;color:#1d4ed8}
        .restaurant-nav a.active{color:#667085 !important}
        .restaurant-nav a.current{background:#2563eb !important;color:#fff !important}
        .mobile-logout button{color:#667085}
        .restaurant-top{padding:15px 16px}
    }
</style>

<div class="restaurant-app">
    <aside class="restaurant-nav">
        <div class="brand" style="margin:4px 0 28px">Жар-Жар</div>
        <a class="active" href="{{ route('restaurant.dashboard') }}#new-booking"><span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 9h18"/></svg></span>Брони</a>
        <a href="#schedule"><span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 13h3M8 17h3M14 13h3"/></svg></span>Календарь</a>
        <a href="#bonuses"><span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M17 7H9.5a3 3 0 1 0 0 6H14a3 3 0 1 1 0 6H6"/></svg></span>Бонусы</a>
        <a href="#settings"><span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l1.5 2.4 2.8.5.5 2.8L19 10l-1.2 2 1.2 2-2.2 1.3-.5 2.8-2.8.5L12 21l-1.5-2.4-2.8-.5-.5-2.8L5 14l1.2-2L5 10l2.2-1.3.5-2.8 2.8-.5L12 3Z"/><circle cx="12" cy="12" r="2.5"/></svg></span>Настройки</a>
        <a href="#reports"><span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 20V10M12 20V4M19 20v-7"/></svg></span>Отчёты</a>
    </aside>

    <main class="restaurant-main">
        <section class="panel reports-panel" id="reports" data-server-rendered="1">
            <h2>Отчёты</h2>
            <p class="muted">Экспорт всех бронирований ресторана с фильтрацией по периоду.</p>
            <form method="GET" action="{{ route('restaurant.dashboard') }}" class="report-filter">
                <label class="field">Дата от<input type="date" name="report_from" value="{{ $reportFrom }}"></label>
                <label class="field">Дата до<input type="date" name="report_to" value="{{ $reportTo }}"></label>
                <label class="field">Период<select name="report_period"><option value="all">Все периоды</option>@foreach($reportPeriods as $period)<option value="{{ $period['key'] }}" @selected($reportPeriod === $period['key'])>{{ $period['label'] }}</option>@endforeach</select></label>
                <button class="button" type="submit">Показать</button>
                <a class="button button-secondary" href="{{ route('restaurant.reports.export') }}?report_period={{ $reportPeriod }}">Экспорт CSV</a>
            </form>
            <div class="report-table-wrap"><table><thead><tr><th>Дата</th><th>Мероприятие</th><th>Посетитель</th><th>Телефон</th><th>Период</th><th>Гостей</th><th>Цена/гость</th><th>Предоплата</th><th>Итого</th><th>Статус</th><th>Примечания</th></tr></thead><tbody>@forelse($reportBookings as $booking)<tr><td>{{ $booking->booking_date->format('d.m.Y') }}</td><td>{{ $booking->event_type ?: '—' }}</td><td>{{ $booking->visitor_name }}</td><td>{{ $booking->phone ?: '—' }}</td><td>{{ $booking->slot?->label ?: '—' }}</td><td>{{ $booking->guest_count }}</td><td>{{ number_format($booking->price_per_guest, 2, ',', ' ') }} ₸</td><td>{{ number_format($booking->prepayment, 2, ',', ' ') }} ₸</td><td>{{ number_format($booking->total_amount, 2, ',', ' ') }} ₸</td><td>{{ $booking->statusLabel() }}</td><td>{{ $booking->note ?: '—' }}</td></tr>@empty<tr><td colspan="11" class="muted">Бронирований пока нет.</td></tr>@endforelse</tbody></table></div>
            <div class="pagination">{{ $reportBookings->links() }}</div>
        </section>
        <div class="restaurant-top">
            <div><strong>{{ $restaurant->name }}</strong><div class="muted">Кабинет ресторана</div></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="button" type="submit">Выйти</button></form>
        </div>
        <h1>Бронирования</h1>
        <p class="muted">Выберите дату в календаре — она автоматически подставится в форму справа.</p>
        @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif

        <div class="calendar-layout">
            <section class="panel calendar-panel" id="schedule" data-calendar-month="{{ $month->format('Y-m') }}">
                <div class="calendar-heading">
                    <div class="calendar-title"><span>Календарь бронирований</span><h2 data-calendar-heading aria-live="polite">{{ $month->translatedFormat('F Y') }}</h2></div>
                    <div class="month-actions" aria-label="Переключение месяца">
                        <a class="month-arrow" data-step="-1" aria-label="Предыдущий месяц" href="{{ route('restaurant.dashboard', ['month' => $month->copy()->subMonth()->format('Y-m'), 'date' => $month->copy()->subMonth()->startOfMonth()->format('Y-m-d')]) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></a>
                        <a class="calendar-today" data-calendar-today href="{{ route('restaurant.dashboard', ['month' => today()->format('Y-m'), 'date' => today()->format('Y-m-d')]) }}">Сегодня</a>
                        <a class="month-arrow" data-step="1" aria-label="Следующий месяц" href="{{ route('restaurant.dashboard', ['month' => $month->copy()->addMonth()->format('Y-m'), 'date' => $month->copy()->addMonth()->startOfMonth()->format('Y-m-d')]) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg></a>
                    </div>
                </div>
                <p class="calendar-live" data-calendar-live aria-live="polite"></p>
                <div class="weekdays"><span>Пн</span><span>Вт</span><span>Ср</span><span>Чт</span><span>Пт</span><span>Сб</span><span>Вс</span></div>
                <div class="calendar-grid">
                    @foreach($calendarDays as $day)
                        @php($dayBookings = $allBookings->filter(fn ($booking) => $booking->booking_date->isSameDay($day)))
                        <a class="day {{ $day->month !== $month->month ? 'muted-day' : '' }} {{ $day->isSameDay($selectedDate) ? 'selected' : '' }}" data-date="{{ $day->format('Y-m-d') }}" href="{{ route('restaurant.dashboard', ['month' => $month->format('Y-m'), 'date' => $day->format('Y-m-d')]) }}"><span class="day-number">{{ $day->day }}</span><div class="slot-bars">@foreach($restaurant->slots as $slot)<span class="slot-bar {{ $dayBookings->where('restaurant_slot_id', $slot->id)->where('status', '!=', 'cancelled')->isNotEmpty() ? 'busy' : '' }}" style="--slot-color:{{ $slot->color }}" title="{{ $slot->label }}"></span>@endforeach</div></a>
                    @endforeach
                </div>
                <div class="legend">@foreach($restaurant->slots as $slot)<span class="legend-item"><i class="legend-mark" style="--slot-color:{{ $slot->color }}"></i>{{ $slot->label }}</span><span class="legend-item"><i class="legend-mark busy" style="--slot-color:{{ $slot->color }}"></i>занято</span>@endforeach</div>
            </section>

            <section class="panel" id="new-booking">
                <h2>Новое бронирование</h2><p class="muted">Дата: <strong>{{ $selectedDate->format('d.m.Y') }}</strong></p>
                <form method="POST" action="{{ route('restaurant.bookings.store') }}"><input type="hidden" name="booking_date" value="{{ $selectedDate->format('Y-m-d') }}">@csrf
                    <label class="field">Тип мероприятия<select name="event_type" required><option value="">Выберите тип</option><option value="Свадьба">Свадьба</option><option value="День рождения">День рождения</option><option value="Юбилей">Юбилей</option><option value="Корпоратив">Корпоратив</option><option value="Другое">Другое</option></select></label>
                    <label class="field">Имя посетителя<input name="visitor_name" value="{{ old('visitor_name') }}" required></label>
                    <div class="form-grid"><label class="field">Телефон<input name="phone" value="{{ old('phone') }}" placeholder="+7 700 000 00 00"></label><label class="field">Гостей<input name="guest_count" type="number" min="1" value="{{ old('guest_count', 2) }}" required></label></div>
                    <label class="field">Период<select name="restaurant_slot_id" required><option value="">Выберите период</option>@foreach($restaurant->slots as $slot)<option value="{{ $slot->id }}" @selected(old('restaurant_slot_id') == $slot->id)>{{ $slot->label }} · {{ $slot->start_time }}–{{ $slot->end_time }}</option>@endforeach</select></label>
                    <label class="field">Примечания<textarea name="note" rows="4" placeholder="Особые пожелания гостя">{{ old('note') }}</textarea></label>
                    <button class="button submit" type="submit">Сохранить бронирование</button>
                </form>
            </section>
        </div>

        <section class="panel booking-list"><h2>Ближайшие записи</h2>@forelse($bookings->sortBy('booking_date')->take(12) as $booking)<details class="booking-card" id="booking-{{ $booking->id }}"><summary><i class="booking-color" style="--booking-color:{{ $booking->color }}"></i><span><strong>{{ $booking->visitor_name }}</strong><small>{{ $booking->booking_date->format('d.m.Y') }} · {{ $booking->slot?->label ?? 'Период не выбран' }} · {{ $booking->guest_count }} гостей</small></span><span class="status">{{ $booking->statusLabel() }}</span></summary><form method="POST" action="{{ route('restaurant.bookings.update', $booking) }}" class="booking-edit-form">@csrf @method('PUT')<div class="form-grid"><label class="field">Имя посетителя<input name="visitor_name" value="{{ $booking->visitor_name }}" required></label><label class="field">Телефон<input name="phone" value="{{ $booking->phone }}"></label><label class="field">Дата<input type="date" name="booking_date" value="{{ $booking->booking_date->format('Y-m-d') }}" required></label><label class="field">Гостей<input type="number" name="guest_count" min="1" value="{{ $booking->guest_count }}" required></label></div><label class="field">Период<select name="restaurant_slot_id" required>@foreach($restaurant->slots as $slot)<option value="{{ $slot->id }}" @selected($booking->restaurant_slot_id === $slot->id)>{{ $slot->label }} · {{ $slot->start_time }}–{{ $slot->end_time }}</option>@endforeach</select></label><label class="field">Статус<select name="status"><option value="pending" @selected($booking->status === 'pending')>Ожидает</option><option value="confirmed" @selected($booking->status === 'confirmed')>Подтверждено</option><option value="cancelled" @selected($booking->status === 'cancelled')>Отменено</option></select></label><label class="field">Примечания<textarea name="note" rows="3">{{ $booking->note }}</textarea></label><div class="booking-actions"><button class="button" type="submit">Сохранить изменения</button></div></form><div class="booking-support"><a class="button button-whatsapp" target="_blank" rel="noopener" href="https://wa.me/77067160199?text={{ rawurlencode($supportMessages[$booking->id]) }}">Написать службе поддержки в WhatsApp</a></div><form method="POST" action="{{ route('restaurant.bookings.destroy', $booking) }}" onsubmit="return confirm('Удалить это бронирование?')">@csrf @method('DELETE')<button class="button button-danger" type="submit">Удалить</button></form></details>@empty<p class="muted">На выбранный месяц записей пока нет.</p>@endforelse</section>

        <section class="panel info-panel" id="bonuses"><h2>Бонусы</h2><p class="muted">Здесь будет отображаться история начислений и доступный баланс ресторана.</p><div class="settings-row"><strong>Доступный баланс</strong><span>0 ₸</span></div></section>
        <section class="panel info-panel" id="settings"><h2>Настройки ресторана</h2><p class="muted">Настройте время доступных периодов ресторана.</p><form method="POST" action="{{ route('restaurant.settings.slots') }}">@csrf @method('PUT')@foreach($restaurant->slots as $slot)<div class="settings-row"><span><strong>{{ $slot->label }}</strong><small style="display:block;color:var(--ui-muted)">Время периода</small></span><span style="display:flex;gap:7px;align-items:center"><input type="time" name="slots[{{ $slot->slot_key }}][start_time]" value="{{ $slot->start_time }}" required style="padding:9px;border:1px solid #dfe6e1;border-radius:8px"><b>—</b><input type="time" name="slots[{{ $slot->slot_key }}][end_time]" value="{{ $slot->end_time }}" required style="padding:9px;border:1px solid #dfe6e1;border-radius:8px"></span></div>@endforeach<button class="button" type="submit" style="margin-top:16px">Сохранить время</button></form></section>
        <form class="settings-logout" method="POST" action="{{ route('logout') }}">@csrf<button class="button button-danger" type="submit">Выйти из кабинета</button></form>
    </main>
</div>
<script>
(() => {
    const tabs = ['schedule', 'bonuses', 'reports', 'settings'];
    const applyTab = () => {
        const active = tabs.includes(window.location.hash.slice(1)) ? window.location.hash.slice(1) : 'schedule';
        const calendar = document.querySelector('.calendar-layout');
        const bookingList = document.querySelector('.booking-list');
        const newBooking = document.getElementById('new-booking');
        const sections = {
            bonuses: document.getElementById('bonuses'),
            reports: document.getElementById('reports'),
            settings: document.getElementById('settings')
        };
        if (calendar) calendar.style.display = active === 'schedule' ? 'grid' : 'none';
        if (bookingList) bookingList.style.display = active === 'schedule' ? 'block' : 'none';
        if (newBooking && !newBooking.closest('.day-modal-form')) newBooking.style.display = active === 'schedule' ? 'block' : 'none';
        Object.entries(sections).forEach(([name, section]) => { if (section) section.style.display = active === name ? 'block' : 'none'; });
        document.querySelectorAll('.restaurant-nav a[href*="#new-booking"]').forEach((link) => link.remove());
    };
    document.querySelectorAll('.restaurant-nav a').forEach((link) => link.addEventListener('click', () => setTimeout(applyTab, 0)));
    window.addEventListener('hashchange', applyTab);
    applyTab();
})();
</script>
<script>
    (() => {
        const sections = ['schedule', 'bonuses', 'reports', 'settings'];
        document.querySelectorAll('.restaurant-nav a[href*="#new-booking"]').forEach((link) => link.remove());
        const reportLinks = [...document.querySelectorAll('.restaurant-nav a[href="#reports"]')];
        reportLinks.slice(1).forEach((link) => link.remove());
        const calendarLink = document.querySelector('.restaurant-nav a[href="#schedule"]');
        if (calendarLink) calendarLink.lastChild.textContent = 'Брони';
        const settingsFormEarly = document.querySelector('#settings form');
        if (settingsFormEarly && !settingsFormEarly.querySelector('[name="default_price_per_guest"]')) {
            const priceField = document.createElement('label');
            priceField.className = 'field settings-price';
            priceField.innerHTML = 'Цена за одного гостя по умолчанию<input name="default_price_per_guest" type="number" min="0" step="0.01" value="{{ (float) $restaurant->default_price_per_guest }}" required><small>Эту цену можно изменить в каждом бронировании.</small>';
            settingsFormEarly.insertBefore(priceField, settingsFormEarly.querySelector('button'));
        }
        const showSection = (requested) => {
            const current = sections.includes(requested) ? requested : 'schedule';
            const calendarLayout = document.querySelector('.calendar-layout');
            const schedule = document.getElementById('schedule');
            const bookingList = document.querySelector('.booking-list');
            const bonuses = document.getElementById('bonuses');
            const reports = document.getElementById('reports');
            const settings = document.getElementById('settings');
            if (calendarLayout) calendarLayout.style.display = current === 'schedule' ? 'grid' : 'none';
            if (schedule) schedule.style.display = current === 'schedule' ? 'block' : 'none';
            if (bookingList) bookingList.style.display = current === 'schedule' ? 'block' : 'none';
            if (bonuses) bonuses.style.display = current === 'bonuses' ? 'block' : 'none';
            if (reports) reports.style.display = current === 'reports' ? 'block' : 'none';
            if (settings) settings.style.display = current === 'settings' ? 'block' : 'none';
            document.querySelectorAll('.restaurant-nav a').forEach((link) => link.classList.toggle('current', link.getAttribute('href') === '#' + current));
        };
        document.querySelectorAll('.restaurant-nav a').forEach((link) => link.addEventListener('click', (event) => {
            const target = link.getAttribute('href')?.split('#')[1];
            if (!sections.includes(target)) return;
            event.preventDefault();
            const sectionUrl = new URL(window.location.href);
            sectionUrl.hash = '#' + target;
            history.replaceState(null, '', sectionUrl);
            showSection(target);
        }));
        showSection(window.location.hash.slice(1));
    })();
    let calendarBookingData = @json($calendarBookingData);
    const dayModalBackdrop = document.createElement('div');
    dayModalBackdrop.className = 'day-modal-backdrop';
    dayModalBackdrop.innerHTML = '<div class="day-modal" role="dialog" aria-modal="true" aria-labelledby="day-modal-title"><div class="day-modal-head"><h2 id="day-modal-title"></h2><button type="button" class="day-modal-close" aria-label="Закрыть">×</button></div><div class="day-modal-events"></div><div class="day-modal-form"></div><button type="button" class="button calendar-add-booking">Добавить мероприятие</button></div>';
    document.body.appendChild(dayModalBackdrop);
    const dayModalEvents = dayModalBackdrop.querySelector('.day-modal-events');
    const dayModalForm = dayModalBackdrop.querySelector('.day-modal-form');
    const newBookingPanel = document.getElementById('new-booking');
    if (newBookingPanel) dayModalForm.appendChild(newBookingPanel);
    let activeModalDate = newBookingPanel?.querySelector('input[name="booking_date"]')?.value || null;
    const dateForForm = (date) => date ? date.split('-').reverse().join('.') : '';
    const closeDayModal = () => dayModalBackdrop.classList.remove('is-open');
    const openBookingModal = (date = activeModalDate) => {
        activeModalDate = date || activeModalDate;
        const formDate = newBookingPanel?.querySelector('input[name="booking_date"]');
        if (formDate && activeModalDate) formDate.value = activeModalDate;
        const formDateText = newBookingPanel?.querySelector('h2 + p strong');
        if (formDateText && activeModalDate) formDateText.textContent = dateForForm(activeModalDate);
        dayModalBackdrop.querySelector('#day-modal-title').textContent = 'Добавить мероприятие';
        dayModalEvents.style.display = 'none';
        dayModalForm.style.display = 'block';
        dayModalBackdrop.querySelector('.calendar-add-booking').style.display = 'none';
        dayModalBackdrop.classList.add('is-open');
        newBookingPanel?.querySelector('input[name="visitor_name"]')?.focus();
    };
    dayModalBackdrop.querySelector('.day-modal-close').addEventListener('click', closeDayModal);
    dayModalBackdrop.addEventListener('click', (event) => { if (event.target === dayModalBackdrop) closeDayModal(); });
    dayModalBackdrop.querySelector('.calendar-add-booking').addEventListener('click', () => openBookingModal(activeModalDate));
    const formatDay = (date) => new Intl.DateTimeFormat('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(date + 'T12:00:00'));
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[char]));
    const whatsappHref = (phone) => { const digits = String(phone || '').replace(/\D/g, ''); return digits ? 'https://wa.me/' + (digits.startsWith('8') ? '7' + digits.slice(1) : digits) : ''; };
    const money = (value) => Number(value || 0).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const renderSelectedDayEvents = (date) => {
        const selectedDayList = document.querySelector('.booking-list');
        if (!selectedDayList || !date) return;
        const normalizedDate = String(date).slice(0, 10);
        const events = calendarBookingData.filter((booking) => String(booking.date).slice(0, 10) === normalizedDate && booking.status !== 'cancelled');
        const listTitle = selectedDayList.querySelector('h2');
        if (listTitle) listTitle.textContent = 'События выбранного дня · ' + dateForForm(normalizedDate);
        selectedDayList.querySelectorAll('.booking-card, .muted').forEach((item) => item.remove());
        selectedDayList.insertAdjacentHTML('beforeend', events.length ? events.map((booking) => '<details class="booking-card" id="booking-' + booking.id + '"><summary><i class="booking-color" style="--booking-color:#c46b58"></i><span><strong>' + escapeHtml(booking.name) + '</strong><small>' + escapeHtml(booking.eventType || 'Мероприятие') + ' · ' + escapeHtml(booking.slot || 'Период не выбран') + ' · ' + booking.guests + ' гостей</small></span><span class="status">' + escapeHtml(booking.statusLabel) + '</span></summary><div class="booking-preview"><p><strong>Тип мероприятия:</strong> ' + escapeHtml(booking.eventType || 'Не указан') + '</p><p><strong>Телефон:</strong> ' + (booking.phone ? '<a class="booking-phone" href="' + whatsappHref(booking.phone) + '" target="_blank" rel="noopener">' + escapeHtml(booking.phone) + '</a>' : 'Не указан') + '</p><p><strong>Количество гостей:</strong> ' + escapeHtml(booking.guests || 0) + '</p><p><strong>Цена за 1 гостя:</strong> ' + money(booking.pricePerGuest || 0) + ' ₸</p><p><strong>Предоплата:</strong> ' + money(booking.prepayment || 0) + ' ₸</p><p><strong>Итого:</strong> ' + money(Number(booking.pricePerGuest || 0) * Number(booking.guests || 0)) + ' ₸</p><p><strong>Примечания:</strong> ' + escapeHtml(booking.note || 'Нет') + '</p><div class="booking-actions"><button type="button" class="button selected-booking-edit" data-booking-id="' + booking.id + '">Изменить</button><form method="POST" action="/restaurant/bookings/' + booking.id + '" onsubmit="return confirm(\'Удалить это бронирование?\')"><input type="hidden" name="_token" value="' + (document.querySelector('input[name=_token]')?.value || '') + '"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="button button-danger">Удалить</button></form></div><div class="booking-support"><a class="button button-whatsapp" href="' + booking.whatsapp + '" target="_blank" rel="noopener">Написать в WhatsApp</a></div></div></details>').join('') : '<p class="muted">На этот день мероприятий нет.</p>');
        selectedDayList.querySelectorAll('.selected-booking-edit').forEach((button) => button.addEventListener('click', () => { const booking = calendarBookingData.find((item) => item.id === Number(button.dataset.bookingId)); const day = [...document.querySelectorAll('.day')].find((item) => new URL(item.href).searchParams.get('date') === booking?.date); day?.click(); const eventElement = dayModalEvents.querySelector('[data-booking-id="' + booking?.id + '"]'); eventElement?.click(); }));
    };
    const bindCalendarDays = () => document.querySelectorAll('.day').forEach((day) => day.addEventListener('click', (event) => {
        event.preventDefault();
        const date = new URL(day.href).searchParams.get('date');
        activeModalDate = date;
        document.querySelectorAll('.day.selected').forEach((selectedDay) => selectedDay.classList.remove('selected'));
        day.classList.add('selected');
        const events = calendarBookingData.filter((booking) => String(booking.date).slice(0, 10) === String(date).slice(0, 10) && booking.status !== 'cancelled');
        renderSelectedDayEvents(date);
        const currentUrl = new URL(window.location.href);
        const renderedMonth = document.getElementById('schedule')?.dataset.calendarMonth || '{{ $month->format('Y-m') }}';
        currentUrl.searchParams.set('month', renderedMonth);
        currentUrl.searchParams.set('date', date);
        currentUrl.hash = '#schedule';
        history.replaceState(null, '', currentUrl);
        dayModalBackdrop.querySelector('#day-modal-title').textContent = formatDay(date);
        dayModalEvents.style.display = 'block';
        dayModalForm.style.display = 'none';
        dayModalBackdrop.querySelector('.calendar-add-booking').style.display = 'block';
        dayModalEvents.innerHTML = events.length
            ? events.map((booking) => '<div class="day-event" data-booking-id="' + booking.id + '"><i class="day-event-mark"></i><div><strong>' + (booking.eventType || 'Мероприятие') + ' · ' + booking.name + '</strong><small>' + (booking.slot || 'Период не выбран') + ' · ' + booking.guests + ' гостей</small></div></div>').join('')
            : '<p class="day-empty">На этот день мероприятий нет.</p>';
        dayModalEvents.querySelectorAll('.day-event').forEach((eventElement, index) => {
            eventElement.style.cursor = 'pointer';
            eventElement.title = 'Открыть мероприятие';
            eventElement.addEventListener('click', () => {
                const booking = events[index];
                const bookingCard = document.getElementById('booking-' + booking.id);
                const details = document.createElement('div');
                details.className = 'booking-modal-details';
                [['Посетитель', booking.name], ['Дата', formatDay(booking.date)], ['Тип мероприятия', booking.eventType || 'Не указан'], ['Период', booking.slot || 'Не выбран'], ['Количество гостей', booking.guests], ['Цена за 1 гостя', money(booking.pricePerGuest) + ' ₸'], ['Предоплата', money(booking.prepayment) + ' ₸'], ['Итого', money(Number(booking.pricePerGuest || 0) * Number(booking.guests || 0)) + ' ₸'], ['Телефон', booking.phone || 'Не указан'], ['Примечания', booking.note || 'Нет']].forEach(([label, value]) => {
                    const row = document.createElement('p');
                    row.innerHTML = '<strong>' + label + ':</strong> ' + (label === 'Телефон' && booking.phone ? '<a class="booking-phone" href="' + whatsappHref(booking.phone) + '" target="_blank" rel="noopener">' + escapeHtml(booking.phone) + '</a>' : escapeHtml(value));
                    details.appendChild(row);
                });
                const actions = document.createElement('div');
                actions.className = 'booking-modal-actions';
                const editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.className = 'button';
                editButton.textContent = 'Редактировать';
                const deleteForm = bookingCard?.querySelector('form[method="POST"]:not(.booking-edit-form)')?.cloneNode(true) || (() => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/restaurant/bookings/' + booking.id;
                    const token = document.querySelector('input[name="_token"]')?.value || '';
                    form.innerHTML = '<input type="hidden" name="_token" value="' + token + '"><input type="hidden" name="_method" value="DELETE"><button class="button button-danger" type="submit">Удалить</button>';
                    form.onsubmit = () => confirm('Удалить это бронирование?');
                    return form;
                })();
                if (deleteForm) {
                    deleteForm.querySelector('button')?.classList.add('button-danger');
                    actions.append(editButton, deleteForm);
                } else actions.append(editButton);
                dayModalEvents.innerHTML = '';
                dayModalEvents.append(details, actions);
                editButton.addEventListener('click', () => {
                    let editForm = bookingCard?.querySelector('.booking-edit-form')?.cloneNode(true);
                    if (!editForm) {
                        editForm = newBookingPanel?.querySelector('form')?.cloneNode(true);
                        if (editForm) {
                            editForm.action = '/restaurant/bookings/' + booking.id;
                            editForm.querySelector('input[name="_method"]')?.remove();
                            editForm.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT"><input type="hidden" name="status" value="' + (booking.status || 'pending') + '">');
                            const values = { visitor_name: booking.name, phone: booking.phone || '', booking_date: booking.date, guest_count: booking.guests, restaurant_slot_id: booking.slotId, event_type: booking.eventType || '', note: booking.note || '' };
                            Object.entries(values).forEach(([name, value]) => { const field = editForm.querySelector('[name="' + name + '"]'); if (field) field.value = value; });
                            const price = editForm.querySelector('[name="price_per_guest"]');
                            const prepayment = editForm.querySelector('[name="prepayment"]');
                            if (price) price.value = booking.pricePerGuest || 0;
                            if (prepayment) prepayment.value = booking.prepayment || 0;
                        }
                    }
                    if (!editForm) return;
                    if (!editForm.querySelector('[name="event_type"]')) {
                        const eventType = document.createElement('input');
                        eventType.type = 'hidden';
                        eventType.name = 'event_type';
                        eventType.value = booking.eventType || '';
                        editForm.prepend(eventType);
                    }
                    dayModalEvents.innerHTML = '';
                    dayModalEvents.append(editForm);
                    addPricingFields(editForm, booking.pricePerGuest, booking.prepayment);
                    const editTotal = editForm.querySelector('.booking-total strong');
                    if (editTotal) editTotal.textContent = money(Number(booking.pricePerGuest || 0) * Number(booking.guests || 0)) + ' ₸';
                });
                dayModalBackdrop.querySelector('#day-modal-title').textContent = booking.eventType || 'Мероприятие';
                return;
                dayModalBackdrop.querySelector('#day-modal-title').textContent = booking.eventType || 'Мероприятие';
                dayModalEvents.innerHTML = '<div class="day-event"><i class="day-event-mark"></i><div><strong>' + booking.name + '</strong><small>Дата: ' + formatDay(booking.date) + '</small><small>Период: ' + (booking.slot || 'не выбран') + '</small><small>Количество гостей: ' + booking.guests + '</small><small>Телефон: ' + (booking.phone || 'не указан') + '</small><small>Примечания: ' + (booking.note || 'нет') + '</small></div></div>';
            });
        });
        dayModalBackdrop.classList.add('is-open');
    }));
    bindCalendarDays();
    const initialDateUrl = new URL(window.location.href);
    const initialCalendarDate = document.querySelector('.day.selected') ? new URL(document.querySelector('.day.selected').href).searchParams.get('date') : null;
    renderSelectedDayEvents(initialDateUrl.searchParams.get('date') || initialCalendarDate || newBookingPanel?.querySelector('input[name="booking_date"]')?.value);
    const calendarPanel = document.getElementById('schedule');
    const calendarGrid = calendarPanel?.querySelector('.calendar-grid');
    const calendarHeading = calendarPanel?.querySelector('[data-calendar-heading]');
    const calendarLive = calendarPanel?.querySelector('[data-calendar-live]');
    const monthLinks = [...document.querySelectorAll('.month-actions a[data-step]')];
    const todayLink = document.querySelector('[data-calendar-today]');
    const todayDate = '{{ today()->format('Y-m-d') }}';
    const todayMonth = todayDate.slice(0, 7);
    const calendarCache = new Map();
    let displayedMonth = calendarPanel?.dataset.calendarMonth || '{{ $month->format('Y-m') }}';
    let renderedMonth = displayedMonth;
    let calendarRequest = 0;
    let calendarAbortController = null;
    const monthValue = (date) => date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
    const shiftMonth = (value, step) => {
        const [year, month] = value.split('-').map(Number);
        return monthValue(new Date(year, month - 1 + step, 1));
    };
    const monthLabel = (value) => new Intl.DateTimeFormat('ru-RU', { month:'long', year:'numeric' }).format(new Date(value + '-01T12:00:00'));
    const updateMonthLinks = (value, selectedDate) => {
        monthLinks.forEach((link) => {
            const target = shiftMonth(value, Number(link.dataset.step));
            link.href = '?month=' + target + '&date=' + target + '-01#schedule';
        });
        if (todayLink) todayLink.href = '?month=' + todayMonth + '&date=' + todayDate + '#schedule';
    };
    const renderCalendarMonth = (data, requestedDate, historyMode = 'push') => {
        const value = data.value || displayedMonth;
        const occupiedSlots = new Set(data.bookings
            .filter((booking) => booking.status !== 'cancelled')
            .map((booking) => booking.date + ':' + booking.slotId));
        const fragment = document.createDocumentFragment();
        data.days.forEach((day) => {
            const link = document.createElement('a');
            link.className = 'day' + (day.inMonth ? '' : ' muted-day');
            link.dataset.date = day.date;
            link.href = '?month=' + value + '&date=' + day.date + '#schedule';
            const number = document.createElement('span');
            number.className = 'day-number';
            number.textContent = day.day;
            const bars = document.createElement('div');
            bars.className = 'slot-bars';
            data.slots.forEach((slot) => {
                const bar = document.createElement('span');
                bar.className = 'slot-bar' + (occupiedSlots.has(day.date + ':' + slot.id) ? ' busy' : '');
                bar.style.setProperty('--slot-color', slot.color);
                bar.title = slot.label;
                bars.appendChild(bar);
            });
            link.append(number, bars);
            fragment.appendChild(link);
        });
        calendarGrid.replaceChildren(fragment);
        calendarBookingData = data.bookings;
        renderedMonth = value;
        displayedMonth = value;
        calendarPanel.dataset.calendarMonth = value;
        calendarHeading.textContent = data.month;
        const selectedDate = requestedDate?.startsWith(value + '-')
            ? requestedDate
            : (value === todayMonth ? todayDate : value + '-01');
        const selectedDay = calendarGrid.querySelector('[data-date="' + selectedDate + '"]');
        selectedDay?.classList.add('selected');
        activeModalDate = selectedDate;
        const formDate = newBookingPanel?.querySelector('input[name="booking_date"]');
        if (formDate) formDate.value = selectedDate;
        renderSelectedDayEvents(selectedDate);
        bindCalendarDays();
        updateMonthLinks(value, selectedDate);
        const targetUrl = new URL(window.location.href);
        targetUrl.searchParams.set('month', value);
        targetUrl.searchParams.set('date', selectedDate);
        targetUrl.hash = '#schedule';
        if (historyMode === 'push') history.pushState({ calendarMonth:value }, '', targetUrl);
        if (historyMode === 'replace') history.replaceState({ calendarMonth:value }, '', targetUrl);
    };
    const loadCalendarMonth = async (targetMonth, requestedDate = targetMonth + '-01', historyMode = 'push') => {
        if (!/^\d{4}-\d{2}$/.test(targetMonth)) return;
        displayedMonth = targetMonth;
        calendarHeading.textContent = monthLabel(targetMonth);
        calendarPanel.classList.add('is-loading');
        calendarLive.textContent = 'Загружаем календарь…';
        calendarAbortController?.abort();
        const requestId = ++calendarRequest;
        if (calendarCache.has(targetMonth)) {
            renderCalendarMonth(calendarCache.get(targetMonth), requestedDate, historyMode);
            calendarPanel.classList.remove('is-loading');
            calendarLive.textContent = '';
            return;
        }
        calendarAbortController = new AbortController();
        try {
            const response = await fetch('{{ route('restaurant.calendar.data') }}?month=' + encodeURIComponent(targetMonth), {
                headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
                signal: calendarAbortController.signal
            });
            if (!response.ok) throw new Error('Calendar request failed');
            const data = await response.json();
            if (requestId !== calendarRequest) return;
            calendarCache.set(targetMonth, data);
            renderCalendarMonth(data, requestedDate, historyMode);
            calendarLive.textContent = '';
        } catch (error) {
            if (error.name === 'AbortError' || requestId !== calendarRequest) return;
            displayedMonth = renderedMonth;
            calendarHeading.textContent = monthLabel(renderedMonth);
            calendarLive.textContent = 'Не удалось загрузить месяц. Попробуйте ещё раз.';
        } finally {
            if (requestId === calendarRequest) calendarPanel.classList.remove('is-loading');
        }
    };
    monthLinks.forEach((link) => link.addEventListener('click', (event) => {
        event.preventDefault();
        const targetMonth = shiftMonth(displayedMonth, Number(link.dataset.step));
        loadCalendarMonth(targetMonth, targetMonth + '-01');
    }));
    todayLink?.addEventListener('click', (event) => {
        event.preventDefault();
        if (renderedMonth === todayMonth) {
            document.querySelector('.day[data-date="' + todayDate + '"]')?.click();
            return;
        }
        loadCalendarMonth(todayMonth, todayDate);
    });
    window.addEventListener('popstate', () => {
        const url = new URL(window.location.href);
        const month = url.searchParams.get('month');
        const date = url.searchParams.get('date');
        if (/^\d{4}-\d{2}$/.test(month || '') && month !== renderedMonth) loadCalendarMonth(month, date, 'none');
    });
    updateMonthLinks(renderedMonth, activeModalDate || renderedMonth + '-01');
    document.querySelectorAll('input[name="guest_count"]').forEach((input) => {
        const label = input.closest('label');
        if (label?.firstChild) label.firstChild.textContent = 'Количество гостей';
    });
    const defaultPricePerGuest = {{ (float) $restaurant->default_price_per_guest }};
    const newBookingPrice = {{ (float) old('price_per_guest', $restaurant->default_price_per_guest) }};
    const newBookingPrepayment = {{ (float) old('prepayment', 0) }};
    const addPricingFields = (form, price, prepayment) => {
        if (!form || form.querySelector('input[name="price_per_guest"]')) return;
        const guestInput = form.querySelector('input[name="guest_count"]');
        if (!guestInput) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'pricing-fields';
        wrapper.innerHTML = '<label class="field">Цена за 1 гостя<input name="price_per_guest" type="number" min="0" step="0.01" value="' + (price ?? defaultPricePerGuest) + '" required></label><label class="field">Предоплата<input name="prepayment" type="number" min="0" step="0.01" value="' + (prepayment ?? 0) + '"></label><p class="booking-total">Итого: <strong>0,00 ₸</strong></p>';
        guestInput.closest('.form-grid')?.insertAdjacentElement('afterend', wrapper);
        const updateTotal = () => {
            const total = Number(guestInput.value || 0) * Number(wrapper.querySelector('[name="price_per_guest"]').value || 0);
            wrapper.querySelector('.booking-total strong').textContent = money(total) + ' ₸';
        };
        guestInput.addEventListener('input', updateTotal);
        wrapper.querySelector('[name="price_per_guest"]').addEventListener('input', updateTotal);
        updateTotal();
    };
    addPricingFields(newBookingPanel?.querySelector('form'), newBookingPrice, newBookingPrepayment);
    document.querySelectorAll('.booking-edit-form').forEach((form) => {
        const id = Number(form.closest('.booking-card')?.id?.replace('booking-', ''));
        const booking = calendarBookingData.find((item) => item.id === id);
        addPricingFields(form, booking?.pricePerGuest ?? defaultPricePerGuest, booking?.prepayment ?? 0);
    });
    const settingsForm = document.querySelector('#settings form');
    if (settingsForm && !settingsForm.querySelector('[name="default_price_per_guest"]')) {
        const priceLabel = document.createElement('label');
        priceLabel.className = 'field settings-price';
        priceLabel.innerHTML = 'Цена за одного гостя по умолчанию<input name="default_price_per_guest" type="number" min="0" step="0.01" value="' + defaultPricePerGuest + '" required><small>Эту цену можно изменить в каждом бронировании.</small>';
        settingsForm.insertBefore(priceLabel, settingsForm.querySelector('button'));
    }
    const settingsSaveButton = document.querySelector('#settings button[type="submit"]');
    if (settingsSaveButton) settingsSaveButton.textContent = 'Сохранить настройки';
    const schedulePanel = document.getElementById('schedule');
    const bookingListTitle = document.querySelector('.booking-list h2');
    if (bookingListTitle) bookingListTitle.textContent = 'События выбранного дня';
    if (schedulePanel && newBookingPanel && !document.getElementById('add-booking-from-calendar')) {
        const addBooking = document.createElement('a');
        addBooking.id = 'add-booking-from-calendar';
        addBooking.className = 'button calendar-add-booking';
        addBooking.href = '#new-booking';
        addBooking.textContent = 'Добавить мероприятие';
        addBooking.addEventListener('click', (event) => {
            event.preventDefault();
            openBookingModal();
        });
        schedulePanel.appendChild(addBooking);
    }
    const guestCountField = document.querySelector('#new-booking input[name="guest_count"]');
    if (guestCountField && guestCountField.value === '2') {
        guestCountField.value = '';
        guestCountField.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (false) {
    const reportsLink = document.createElement('a');
    reportsLink.href = '#reports';
    reportsLink.innerHTML = '<span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 20V10M12 20V4M19 20v-7"/></svg></span>Отчёты';
    const existingReportsLink = document.querySelector('.restaurant-nav a[href="#reports"]');
    if (existingReportsLink) reportsLink.remove(); else document.querySelector('.restaurant-nav a[href="#settings"]')?.before(reportsLink);
    const reportSection = document.getElementById('reports') || document.createElement('section');
    reportSection.id = 'reports';
    reportSection.className = 'panel reports-panel';
    /*
    @verbatim
    reportSection.innerHTML = '<h2>Отчёты</h2><p class="muted">Экспорт всех бронирований ресторана с фильтрацией по периоду.</p><form method="GET" action="" class="report-filter"><label class="field">Период<select name="report_period"><option value="all">Все периоды</option>{{ $restaurant->slots->map(fn ($slot) => '<option value="'.$slot->slot_key.'" '.($reportPeriod === $slot->slot_key ? 'selected' : '').'>'.$slot->label.'</option>')->implode('') }}</select></label><button class="button" type="submit">Показать</button><a class="button button-secondary" href="{{ route('restaurant.reports.export') }}?report_period={{ $reportPeriod }}">Экспорт CSV</a></form><div class="report-table-wrap"><table><thead><tr><th>Дата</th><th>Мероприятие</th><th>Посетитель</th><th>Период</th><th>Гостей</th><th>Итого</th><th>Статус</th></tr></thead><tbody>' + @json($reportBookings->map(fn ($booking) => '<tr><td>'.$booking->booking_date->format('d.m.Y').'</td><td>'.e($booking->event_type).'</td><td>'.e($booking->visitor_name).'</td><td>'.e($booking->slot?->label ?? '—').'</td><td>'.$booking->guest_count.'</td><td>'.number_format($booking->total_amount, 2, ',', ' ').' ₸</td><td>'.e($booking->statusLabel()).'</td></tr>')->implode('')) + '</tbody></table></div>';
    @endverbatim
    */
    if (!reportSection.dataset.serverRendered) reportSection.innerHTML = '<h2>Отчёты</h2><p class="muted">Экспорт бронирований ресторана с фильтрацией по периоду.</p>';
    const reportSelect = reportSection.querySelector('select[name="report_period"]');
    if (reportSelect) @json($reportPeriods).forEach((period) => { const option = document.createElement('option'); option.value = period.key; option.textContent = period.label; option.selected = period.key === @json($reportPeriod); reportSelect.append(option); });
    reportSelect?.closest('.field')?.remove();
    const reportForm = reportSection.querySelector('.report-filter');
    const exportLink = reportSection.querySelector('a[href*="/restaurant/reports/export"]');
    if (reportForm && exportLink) {
        const syncReportExport = () => {
            const params = new URLSearchParams();
            ['report_from', 'report_to'].forEach((name) => { const value = reportForm.querySelector('[name="' + name + '"]')?.value; if (value) params.set(name, value); });
            exportLink.href = '{{ route('restaurant.reports.export') }}' + (params.toString() ? '?' + params.toString() : '');
        };
        reportForm.querySelectorAll('input[type="date"]').forEach((input) => input.addEventListener('change', syncReportExport));
        syncReportExport();
    }
    }
    (() => {
        document.querySelector('.restaurant-nav a[href*="#new-booking"]')?.remove();
        const calendarLink = document.querySelector('.restaurant-nav a[href="#schedule"]');
        if (calendarLink) calendarLink.lastChild.textContent = 'Брони';
        const sections = ['new-booking', 'schedule', 'bonuses', 'reports', 'settings'];
        const navLinks = [...document.querySelectorAll('.restaurant-nav a')];
        const figmaIcons = {
            schedule: '<svg class="figma-icon" viewBox="0 0 17 20" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M13.342 2.426h1.819a1.82 1.82 0 0 1 1.819 1.819v1.819H0V4.245a1.82 1.82 0 0 1 1.819-1.819h1.82V.606A.606.606 0 0 1 4.245 0h1.213a.606.606 0 0 1 .606.606v1.82h4.852V.606A.606.606 0 0 1 11.522 0h1.213a.606.606 0 0 1 .607.606v1.82ZM1.819 19.406A1.82 1.82 0 0 1 0 17.587V7.277h16.98v10.31a1.82 1.82 0 0 1-1.819 1.819H1.819Z"/></svg>',
            settings: '<svg class="figma-icon" viewBox="0 0 19 19" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M16.014 9.075c.01.303-.006.607-.046.908l2.043 1.498a.61.61 0 0 1 .093.59l-1.857 3.131a.61.61 0 0 1-.557.092l-2.321-.908a6.9 6.9 0 0 1-1.578.908l-.372 2.4a.61.61 0 0 1-.464.363H7.238a.61.61 0 0 1-.464-.363l-.371-2.4a6.9 6.9 0 0 1-1.578-.908l-2.312.914a.61.61 0 0 1-.557-.182L.099 12.077a.61.61 0 0 1 .093-.59l1.949-1.5a6.9 6.9 0 0 1 0-1.816L.141 6.671a.61.61 0 0 1-.093-.59L1.905 2.95a.61.61 0 0 1 .557-.092l2.321.908a6.9 6.9 0 0 1 1.578-.908L6.732.368A.61.61 0 0 1 7.196.005h3.713a.61.61 0 0 1 .464.363l.325 2.4a6.9 6.9 0 0 1 1.578.908l2.321-.908a.61.61 0 0 1 .557.182l1.856 3.127a.61.61 0 0 1-.093.59l-1.949 1.5c.04.301.056.605.046.908Zm-10.067-.047a3.05 3.05 0 1 0 6.1.097 3.05 3.05 0 0 0-6.1-.097Z"/></svg>'
        };
        const updateActiveNav = () => {
            const current = sections.includes(window.location.hash.slice(1)) ? window.location.hash.slice(1) : 'schedule';
            navLinks.forEach((link) => {
                link.classList.remove('active');
                link.classList.remove('current');
            });
            navLinks.forEach((link) => {
                const target = link.getAttribute('href').split('#')[1] || 'new-booking';
                if (target === current) link.classList.add('current');
            });
        };
        const calendarLayout = document.querySelector('.calendar-layout');
        const schedule = document.getElementById('schedule');
        const newBooking = document.getElementById('new-booking');
        const bookingList = document.querySelector('.booking-list');
        const bonuses = document.getElementById('bonuses');
        const settings = document.getElementById('settings');
        const reports = document.getElementById('reports');
        const settingsLogout = document.querySelector('.settings-logout');
        if (settingsLogout) settings.appendChild(settingsLogout);
        const setSection = (section) => {
            const current = sections.includes(section) ? section : 'schedule';
            const calendarTab = current === 'schedule' || current === 'new-booking';
            calendarLayout.style.display = calendarTab ? 'grid' : 'none';
            schedule.style.display = current === 'schedule' ? 'block' : 'none';
            newBooking.style.display = calendarTab ? 'block' : 'none';
            bookingList.style.display = calendarTab ? 'block' : 'none';
            bonuses.style.display = current === 'bonuses' ? 'block' : 'none';
            reports.style.display = current === 'reports' ? 'block' : 'none';
            settings.style.display = current === 'settings' ? 'block' : 'none';
            updateActiveNav();
        };
        navLinks.forEach((link) => link.addEventListener('click', (event) => {
            event.preventDefault();
            const target = link.getAttribute('href').split('#')[1] || 'new-booking';
            const oldSectionUrl = new URL(window.location.href);
            oldSectionUrl.hash = `#${target}`;
            history.replaceState(null, '', oldSectionUrl);
            setSection(target);
        }));
        setSection(window.location.hash.slice(1));
        window.addEventListener('hashchange', () => setSection(window.location.hash.slice(1)));
        let startX = 0;
        let startY = 0;
        document.addEventListener('touchstart', (event) => {
            if (event.touches.length !== 1) return;
            startX = event.touches[0].clientX;
            startY = event.touches[0].clientY;
        }, { passive: true });
        document.addEventListener('touchend', (event) => {
            return;
            if (!startX || event.changedTouches.length !== 1) return;
            if (event.target.closest('input, textarea, select, button')) { startX = 0; return; }
            const deltaX = event.changedTouches[0].clientX - startX;
            const deltaY = event.changedTouches[0].clientY - startY;
            startX = 0;
            if (Math.abs(deltaX) < 55 || Math.abs(deltaX) < Math.abs(deltaY)) return;
            const current = sections.indexOf(window.location.hash.replace('#', '')) >= 0 ? sections.indexOf(window.location.hash.replace('#', '')) : 0;
            const next = Math.max(0, Math.min(sections.length - 1, current + (deltaX < 0 ? 1 : -1)));
            if (next !== current) {
                window.location.hash = sections[next];
                updateActiveNav();
            }
        }, { passive: true });
    })();
    document.addEventListener('click', (event) => {
        const editButton = event.target.closest('.selected-booking-edit');
        if (!editButton) return;
        setTimeout(() => dayModalEvents.querySelector('.booking-modal-actions button:not(.button-danger)')?.click(), 0);
    }, true);
    const finalSettingsSaveButton = document.querySelector('#settings button[type="submit"]');
    if (finalSettingsSaveButton) finalSettingsSaveButton.textContent = 'Сохранить';
</script>
@endsection
