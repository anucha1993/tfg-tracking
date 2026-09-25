@props(['robots' => 'noindex,nofollow', 'description' => null, 'author' => null])
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="{{ $robots }}"><meta name="referrer" content="no-referrer">
@if($description)
<meta name="description" content="{{ $description }}">
@endif
@if($author)
<meta name="author" content="{{ $author }}">
@endif
<title>{{ $title }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#f4f6f9;--card:#fff;--ink:#17202b;--muted:#5f6b7a;--line:#e3e8ef;
--brand:#0f4c81;--done:#14804a;--done-bg:#e6f4ec;--active:#1f63d6;--active-bg:#e8effc;
--delay:#b4540a;--delay-bg:#fdf0e3;--pend:#8a95a3;--pend-bg:#eef1f5}
@media (prefers-color-scheme:dark){:root{--bg:#0f141a;--card:#18202a;--ink:#e8edf3;--muted:#9aa6b4;--line:#2a3441;
--brand:#6fa8dc;--done:#4cc38a;--done-bg:#15301f;--active:#7aa7ff;--active-bg:#172a4a;
--delay:#f0a35e;--delay-bg:#3a2612;--pend:#7d8896;--pend-bg:#232c37}}
*{box-sizing:border-box}html,body{margin:0}
body{background:var(--bg);color:var(--ink);font:15px/1.55 "IBM Plex Sans Thai","Sarabun",system-ui,sans-serif;-webkit-font-smoothing:antialiased}
.wrap{max-width:640px;margin:0 auto;padding:16px 16px 40px}
.brand i{width:10px;height:10px;border-radius:3px;background:var(--brand);display:inline-block}
.card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px}
.card+.card{margin-top:12px}
.card.head{position:relative}
.logo{position:absolute;top:14px;right:16px;height:72px;width:auto}
.label{color:var(--muted);font-size:13px}
h1{font-size:22px;margin:2px 0 4px;letter-spacing:.2px}
.cust{color:var(--muted);margin:0 0 10px}
.chips{display:flex;flex-wrap:wrap;gap:6px}
.chip{font-size:12.5px;padding:3px 10px;border-radius:999px;background:var(--pend-bg);color:var(--ink)}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:14px}
.grid b{display:block;font-size:15px}
.bar{height:8px;border-radius:99px;background:var(--pend-bg);overflow:hidden;margin:10px 0 6px}
.bar span{display:block;height:100%;background:var(--done);border-radius:99px}
.now{display:flex;gap:10px;align-items:flex-start}
.now .dot{flex:none;margin-top:6px}
.now b{font-size:16px}
h2{font-size:15px;margin:0 0 12px}
ol{list-style:none;margin:0;padding:0}
li{position:relative;display:flex;gap:12px;padding:0 0 18px}
li:last-child{padding-bottom:0}
li:not(:last-child)::before{content:"";position:absolute;left:11px;top:26px;bottom:2px;width:2px;background:var(--line)}
li.done:not(:last-child)::before{background:var(--done)}
.dot{width:24px;height:24px;border-radius:50%;flex:none;display:grid;place-items:center;font-size:12px;font-weight:700;
background:var(--pend-bg);color:var(--pend);border:2px solid var(--pend)}
.done .dot{background:var(--done);border-color:var(--done);color:#fff}
.active .dot{background:var(--active-bg);border-color:var(--active);color:var(--active)}
.delayed .dot{background:var(--delay-bg);border-color:var(--delay);color:var(--delay)}
.cur .dot{box-shadow:0 0 0 4px var(--active-bg)}
.body{min-width:0;flex:1}
.t{font-weight:600}
.pending .t{color:var(--muted);font-weight:500}
.meta{font-size:13px;color:var(--muted);margin-top:2px}
.badge{display:inline-block;font-size:12px;font-weight:600;padding:1px 8px;border-radius:6px;margin-left:6px;vertical-align:1px}
.b-done{background:var(--done-bg);color:var(--done)}.b-active{background:var(--active-bg);color:var(--active)}
.b-delayed{background:var(--delay-bg);color:var(--delay)}.b-pending{background:var(--pend-bg);color:var(--pend)}
.reason{margin-top:6px;font-size:13px;background:var(--delay-bg);color:var(--ink);padding:8px 10px;border-radius:8px}
.contact{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.btn{flex:1;min-width:140px;text-align:center;text-decoration:none;padding:11px 12px;border-radius:10px;font-weight:600;
border:1px solid var(--line);color:var(--ink);background:var(--card);white-space:nowrap}
.btn.line{background:#06c755;border-color:#06c755;color:#fff}
.foot{color:var(--muted);font-size:12.5px;text-align:center;margin-top:16px}
.empty{text-align:center;padding:36px 18px}
.topbar{display:flex;align-items:center;justify-content:space-between;gap:8px;margin:4px 2px 14px;flex-wrap:wrap}
.brand{display:flex;align-items:center;gap:8px;color:var(--brand);font-weight:600;font-size:14px;margin:0}
.langsw{display:flex;gap:4px}
.langsw a{font-size:12.5px;font-weight:600;padding:4px 9px;border-radius:999px;text-decoration:none;color:var(--muted);border:1px solid var(--line)}
.langsw a.on{color:#fff;background:var(--brand);border-color:var(--brand)}
@media (max-width:420px){.grid{grid-template-columns:1fr 1fr}.grid div:last-child{grid-column:span 2}}
</style></head><body><main class="wrap">
<div class="topbar">
<div class="brand"><i></i>{{ $company['name'] }} · {{ __('ติดตามสถานะงาน') }}</div>
<div class="langsw">
@foreach(['th' => 'ไทย', 'en' => 'EN', 'zh' => '中文'] as $code => $label)
<a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" class="{{ app()->getLocale() === $code ? 'on' : '' }}">{{ $label }}</a>
@endforeach
</div>
</div>
{{ $slot }}
</main></body></html>
