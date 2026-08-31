<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#F8F1E2">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title>@yield('title', 'Operations') · Direpair</title>
    <style>
        @font-face { font-family:Archivo; src:url('/fonts/archivo-variable.woff2') format('woff2'); font-style:normal; font-weight:100 900; font-display:swap; }
        @font-face { font-family:'Azeret Mono'; src:url('/fonts/azeret-mono-variable.woff2') format('woff2'); font-style:normal; font-weight:100 900; font-display:swap; }
        :root { color-scheme:light; --ink:#011d38; --ink-soft:#314b60; --muted:#5c7080; --line:#9cadb6; --paper:#f8f1e2; --paper-deep:#efe4ce; --red:#cc3110; --green:#0b6f5d; --blue:#064395; --yellow:#f4c444; --mono:'Azeret Mono',ui-monospace,monospace; }
        * { box-sizing:border-box; }
        html { background:var(--paper-deep); scrollbar-color:var(--red) var(--paper-deep); }
        body { margin:0; min-width:320px; font:15px/1.55 Archivo,Arial,sans-serif; color:var(--ink); background-color:var(--paper); background-image:url('/media/service-paper-texture.png'); background-size:640px 640px; caret-color:var(--red); }
        ::selection { background:var(--yellow); color:var(--ink); }
        ::-webkit-scrollbar { width:12px; height:12px; }
        ::-webkit-scrollbar-track { background:var(--paper-deep); }
        ::-webkit-scrollbar-thumb { border:3px solid var(--paper-deep); border-radius:8px; background:var(--red); }
        a { color:var(--blue); text-decoration:none; } a:hover { color:var(--red); text-decoration:underline; }
        :focus-visible { outline:3px solid var(--red); outline-offset:3px; }
        .ops-header { position:sticky; top:0; z-index:20; border-bottom:2px solid var(--ink); background:color-mix(in srgb,var(--paper) 96%,transparent); }
        .ops-header .wrap { display:grid; grid-template-columns:auto 1fr auto; align-items:stretch; min-height:70px; }
        .brand { display:flex; align-items:center; padding:0 24px 0 0; color:var(--ink); font-size:1.25rem; font-weight:890; letter-spacing:-.04em; }
        .brand:hover { color:var(--ink); text-decoration:none; }
        .brand small { margin-left:10px; font:700 .6rem/1 var(--mono); letter-spacing:.11em; color:var(--red); }
        .header-route { display:flex; align-items:center; padding:0 22px; border-left:1px solid var(--line); border-right:1px solid var(--line); }
        .header-route::before,.header-route::after { content:''; width:11px; height:11px; border-radius:50%; background:var(--green); box-shadow:0 0 0 3px var(--paper),0 0 0 5px var(--green); }
        .header-route span { height:4px; flex:1; background:var(--green); }
        .session-action { display:flex; align-items:center; padding-left:20px; }
        .wrap { width:min(1240px,calc(100% - 36px)); margin:auto; }
        main { padding:36px 0 64px; }
        h1,h2,h3 { line-height:1.05; margin-top:0; letter-spacing:-.035em; }
        h1 { font-size:clamp(2.25rem,5vw,4rem); margin-bottom:6px; }
        h2 { font-size:1.45rem; margin-bottom:18px; }
        h3 { font-size:1rem; letter-spacing:-.01em; }
        .route-code { display:block; margin-bottom:10px; color:var(--red); font:750 .67rem/1.3 var(--mono); letter-spacing:.13em; text-transform:uppercase; }
        .page-head { display:flex; justify-content:space-between; align-items:end; gap:24px; margin-bottom:24px; padding-bottom:20px; border-bottom:5px solid var(--ink); }
        .panel,.card { margin-bottom:22px; padding:22px; border:1px solid var(--ink); border-radius:0; background:color-mix(in srgb,var(--paper) 92%,white); box-shadow:none; }
        .panel.yellow { background:var(--yellow); }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:22px; }
        .grid.three { grid-template-columns:repeat(3,minmax(0,1fr)); }
        label { display:block; font-weight:730; margin:0 0 6px; }
        input,select,textarea { width:100%; border:1px solid var(--ink); border-radius:0; padding:10px 11px; font:inherit; color:var(--ink); background:#fffaf0; }
        input:focus,select:focus,textarea:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(6,67,149,.16); outline:0; }
        input[type=checkbox] { width:auto; accent-color:var(--green); }
        input:disabled,select:disabled,textarea:disabled,button:disabled { cursor:not-allowed; opacity:.55; }
        textarea { min-height:96px; resize:vertical; }
        button,.button { display:inline-block; min-height:40px; border:2px solid var(--ink); border-radius:0; padding:9px 15px; background:var(--yellow); color:var(--ink); font:800 .78rem/1.2 Archivo,sans-serif; cursor:pointer; box-shadow:none; transition:background-color .18s ease,color .18s ease,transform .18s ease; }
        button:hover,.button:hover { transform:translateY(-1px); background:var(--red); color:var(--paper); text-decoration:none; box-shadow:none; }
        button.secondary,.button.secondary { background:var(--paper); }
        button.link { min-height:0; border:0; padding:0; background:transparent; color:var(--ink); box-shadow:none; }
        button.link:hover { transform:none; color:var(--red); box-shadow:none; }
        table { width:100%; border-collapse:collapse; }
        th,td { padding:12px 10px; border-bottom:1px solid var(--ink); text-align:left; vertical-align:top; }
        th { background:var(--ink); color:var(--paper); font:700 .65rem/1.3 var(--mono); letter-spacing:.08em; text-transform:uppercase; }
        tbody tr:hover td { background:color-mix(in srgb,var(--yellow) 30%,transparent); }
        .muted { color:var(--muted); }
        .money { font-family:var(--mono); font-variant-numeric:tabular-nums; white-space:nowrap; }
        .badge { display:inline-flex; padding:4px 7px; border:1px solid currentColor; border-radius:0; background:transparent; color:var(--blue); font:720 .64rem/1.2 var(--mono); text-transform:uppercase; }
        .badge.ok { color:var(--green); }
        .badge.demo { border-color:var(--yellow); background:var(--yellow); color:var(--ink); }
        .alert { padding:13px 15px; border:1px solid var(--green); border-top-width:4px; margin-bottom:20px; background:#d9ebdf; color:var(--ink); box-shadow:none; }
        .alert.error { border-color:var(--red); background:#ffdfd0; box-shadow:none; }
        .field { margin-bottom:15px; }
        .actions { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .filter-bar { align-items:end; padding:14px; border:1px solid var(--ink); background:var(--paper-deep); }
        .table-panel { padding:0; overflow:hidden; }
        .timeline { position:relative; padding-left:28px; margin-left:5px; }
        .timeline::before { content:''; position:absolute; left:8px; top:7px; bottom:8px; width:4px; background:var(--green); }
        .timeline article { position:relative; margin-bottom:21px; }
        .timeline article::before { content:''; position:absolute; left:-27px; top:4px; width:12px; height:12px; border:3px solid var(--paper); border-radius:50%; background:var(--green); box-shadow:0 0 0 2px var(--green); }
        .item-row { display:grid; grid-template-columns:130px 1fr 100px 150px; gap:10px; margin-bottom:10px; }
        .login-shell { max-width:480px; margin:56px auto; }
        .back-link { display:inline-flex; margin-bottom:18px; font:700 .7rem/1.2 var(--mono); letter-spacing:.08em; text-transform:uppercase; }
        .request-meta { margin-top:18px; }
        .quote-entry { border-bottom:1px solid var(--ink); padding-bottom:16px; margin-bottom:16px; }
        .quote-head { display:flex; justify-content:space-between; align-items:center; gap:14px; margin-bottom:12px; }
        @media (max-width:800px) { .grid,.grid.three,.item-row { grid-template-columns:1fr; } .table-scroll { overflow:auto; } .ops-header .wrap { grid-template-columns:1fr auto; }.header-route{display:none}.page-head{align-items:flex-start;flex-direction:column}.page-head form{width:100%}.filter-bar select{flex:1}.brand{padding-right:12px}.brand small{display:none} }
        @media (max-width:520px) { .wrap{width:min(100% - 22px,1240px)} main{padding-top:24px}.panel,.card{padding:17px;box-shadow:none}.brand{font-size:1.05rem}.session-action{padding-left:10px}h1{font-size:2.3rem} }
    </style>
</head>
<body>
<!--
  DIREPAIR SURFACE CONTRACT — Service Line Atlas / Repair Interchange
  MAJOR STRUCTURE: boxed navigation, route spine, work-ticket panels, sequential maintenance tables.
  COLOR: paper #F8F1E2, navy #011D38, red #CC3110, green #0B6F5D, blue #064395, ticket #F4C444.
  TYPE: Archivo for interface hierarchy; Azeret Mono only for request codes, states, dates, and money.
  PROHIBITED: gradients, glass, pill scaffolds, decorative card grids, invented operational data, emoji icons.
  SEED: 22704d32 · COMPOSITION: Repair Interchange.
-->
<header class="ops-header"><div class="wrap"><a class="brand" href="{{ route('operations.index') }}">DIREPAIR <small>OPS / LIVE</small></a><div class="header-route" aria-hidden="true"><span></span></div><div class="session-action">@auth<form method="post" action="{{ route('operations.logout') }}">@csrf<button class="link" type="submit">Keluar</button></form>@endauth</div></div></header>
<main class="wrap">
    @if (session('success')) <div class="alert">{{ session('success') }}</div> @endif
    @if ($errors->any())<div class="alert error"><strong>Periksa kembali data:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
</body>
</html>
