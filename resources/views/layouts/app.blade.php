<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PELITA') - PELITA</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #242044;
            background: #f7f6fc;
            font-synthesis: none;
            --ink: #242044;
            --muted: #77748f;
            --purple: #5836c9;
            --purple-dark: #21164f;
            --purple-light: #eeeaff;
            --line: #e9e6f4;
            --surface: #fff;
            --shadow: 0 18px 50px rgb(41 29 94 / 8%);
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #f7f6fc; }
        body.navigation-open { overflow: hidden; }
        a { color: var(--purple); }
        button, input, select, textarea { font: inherit; }
        button, a { -webkit-tap-highlight-color: transparent; }
        [hidden] { display: none !important; }
        .app-shell { display: grid; grid-template-columns: 248px minmax(0, 1fr); min-height: 100vh; transition: grid-template-columns .2s ease; }
        .sidebar { position: sticky; top: 0; display: flex; flex-direction: column; height: 100vh; overflow-y: auto; padding: 26px 18px 20px; background: #fff; border-right: 1px solid var(--line); }
        .app-shell.sidebar-collapsed { grid-template-columns: 76px minmax(0, 1fr); }
        .sidebar-collapsed .sidebar { padding-right: 10px; padding-left: 10px; }
        .sidebar-collapsed .brand-lockup { justify-content: center; padding-right: 0; padding-left: 0; }
        .sidebar-collapsed .brand-lockup > span, .sidebar-collapsed .nav-caption, .sidebar-collapsed .sidebar-promo { display: none; }
        .sidebar-collapsed .nav-link, .sidebar-collapsed .nav-action { justify-content: center; padding-right: 8px; padding-left: 8px; }
        .sidebar-collapsed .nav-link > span:not(.nav-icon), .sidebar-collapsed .nav-action > span:not(.nav-icon) { display: none; }
        .sidebar-collapsed .sidebar-bottom { padding-top: 8px; }
        .sidebar-overlay { display: none; }
        .brand-lockup { display: flex; align-items: center; gap: 10px; padding: 0 10px 28px; color: var(--purple-dark); text-decoration: none; }
        .brand-mark { width: 34px; height: 34px; color: var(--purple); flex: 0 0 auto; }
        .brand-name { display: block; font-size: 1.12rem; font-weight: 850; letter-spacing: .045em; line-height: 1.05; }
        .brand-caption { display: block; margin-top: 4px; color: #8d89a6; font-size: .68rem; font-weight: 600; letter-spacing: .03em; }
        .nav-caption { padding: 8px 12px 10px; color: #9a96ad; font-size: .67rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
        .sidebar-nav { display: grid; gap: 6px; }
        .nav-link, .nav-action { display: flex; width: 100%; min-height: 42px; align-items: center; gap: 11px; border: 0; border-radius: 11px; padding: 10px 12px; color: #686580; background: transparent; font-size: .86rem; font-weight: 650; text-align: left; text-decoration: none; transition: background .18s ease, color .18s ease; }
        .nav-link:hover, .nav-action:hover { color: var(--purple); background: #f4f1ff; }
        .nav-link.is-active { color: #fff; background: var(--purple); box-shadow: 0 8px 18px rgb(88 54 201 / 22%); }
        .nav-icon { display: inline-flex; width: 19px; height: 19px; align-items: center; justify-content: center; flex: 0 0 auto; }
        .nav-icon svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8; }
        .sidebar-spacer { flex: 1; }
        .sidebar-promo { position: relative; overflow: hidden; margin: 20px 2px 14px; padding: 17px 15px; border: 1px solid #e8e1ff; border-radius: 16px; background: linear-gradient(145deg, #f8f6ff, #eeebff); }
        .sidebar-promo::after { position: absolute; right: -25px; bottom: -34px; width: 90px; height: 90px; border: 1px solid rgb(88 54 201 / 15%); border-radius: 50%; content: ""; }
        .sidebar-promo strong { display: block; max-width: 145px; color: var(--purple-dark); font-size: .83rem; line-height: 1.45; }
        .sidebar-promo span { display: block; margin-top: 7px; color: var(--muted); font-size: .72rem; line-height: 1.5; }
        .sidebar-bottom { padding-top: 10px; border-top: 1px solid var(--line); }
        .sidebar-bottom form { margin: 0; }
        .main-panel { min-width: 0; }
        .topbar { position: sticky; z-index: 5; top: 0; display: flex; min-height: 76px; align-items: center; justify-content: space-between; gap: 20px; padding: 12px clamp(20px, 4vw, 52px); border-bottom: 1px solid rgb(233 230 244 / 85%); background: rgb(255 255 255 / 90%); backdrop-filter: blur(16px); }
        .topbar-leading { display: flex; min-width: 0; align-items: center; gap: 14px; }
        .sidebar-toggle { display: grid; width: 38px; height: 38px; flex: 0 0 auto; place-items: center; border: 1px solid var(--line); border-radius: 10px; color: #5e5878; background: #fff; cursor: pointer; transition: color .16s ease, background .16s ease; }
        .sidebar-toggle:hover { color: var(--purple); background: #f5f2ff; }
        .sidebar-toggle svg { width: 19px; height: 19px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8; }
        .topbar-kicker { display: block; margin-bottom: 3px; color: #9692aa; font-size: .7rem; font-weight: 750; letter-spacing: .1em; text-transform: uppercase; }
        .topbar-title { color: var(--ink); font-size: .95rem; font-weight: 750; }
        .user-chip { display: flex; align-items: center; gap: 9px; color: var(--ink); font-size: .78rem; font-weight: 700; }
        .user-chip small { display: block; margin-top: 2px; color: var(--muted); font-size: .67rem; font-weight: 500; }
        .user-avatar { display: grid; width: 34px; height: 34px; place-items: center; border: 2px solid #e4ddff; border-radius: 50%; color: var(--purple); background: #f3f0ff; font-size: .78rem; font-weight: 800; }
        .guest-pill { border: 1px solid #e6e1f4; border-radius: 999px; padding: 8px 12px; color: #686580; background: #fff; font-size: .73rem; font-weight: 650; }
        .content-shell { width: min(100% - 48px, 1320px); min-height: calc(100vh - 142px); margin: 0 auto; padding: 34px 0 46px; }
        .footer { border-top: 1px solid var(--line); padding: 20px clamp(20px, 4vw, 52px); color: #8d89a6; background: #fff; font-size: .73rem; }
        .footer-inner { display: flex; width: min(100%, 1320px); align-items: center; justify-content: space-between; gap: 22px; margin: 0 auto; }
        .footer-brand strong { display: block; margin-bottom: 4px; color: var(--purple-dark); font-size: .9rem; letter-spacing: .04em; }
        .footer-brand span, .footer-brand p { display: block; margin: 0; line-height: 1.5; }
        .footer-brand p { margin-top: 5px; }
        .eyebrow { color: var(--purple); font-size: .72rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
        h1, h2, h3, p { margin-top: 0; }
        h1 { margin-bottom: 14px; color: var(--ink); font-size: clamp(1.7rem, 3vw, 2.45rem); line-height: 1.12; letter-spacing: -.04em; }
        h2 { margin-bottom: 14px; color: var(--ink); font-size: 1.12rem; letter-spacing: -.02em; }
        h3 { margin-bottom: 8px; color: var(--ink); font-size: .98rem; }
        p { line-height: 1.65; }
        .muted { color: var(--muted); }
        .card { min-width: 0; border: 1px solid var(--line); border-radius: 20px; padding: 25px; background: var(--surface); box-shadow: var(--shadow); }
        .narrow { max-width: 640px; margin: 0 auto; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .field { min-width: 0; margin-bottom: 17px; }
        label { display: block; margin-bottom: 7px; color: #45415f; font-size: .82rem; font-weight: 700; }
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="date"], select, textarea { width: 100%; min-height: 44px; border: 1px solid #e0dced; border-radius: 10px; padding: 10px 12px; color: var(--ink); background: #fff; outline: none; transition: border-color .16s ease, box-shadow .16s ease; }
        textarea { min-height: 100px; resize: vertical; }
        input:focus, select:focus, textarea:focus { border-color: #9d87ed; box-shadow: 0 0 0 3px rgb(113 82 216 / 13%); }
        input[type="checkbox"], input[type="radio"] { accent-color: var(--purple); }
        input:focus-visible, button:focus-visible, a:focus-visible { outline: 3px solid #bcaeff; outline-offset: 2px; }
        .button { display: inline-flex; min-height: 42px; align-items: center; justify-content: center; gap: 8px; border: 1px solid transparent; border-radius: 10px; padding: 10px 17px; color: #fff; background: var(--purple); box-shadow: 0 6px 14px rgb(88 54 201 / 18%); font-size: .82rem; font-weight: 750; text-decoration: none; cursor: pointer; transition: transform .16s ease, background .16s ease, box-shadow .16s ease; }
        .button:hover { color: #fff; background: #4828b5; box-shadow: 0 9px 18px rgb(88 54 201 / 24%); transform: translateY(-1px); }
        .button-secondary { border-color: #e2ddef; color: #514d69; background: #fff; box-shadow: none; }
        .button-secondary:hover { color: var(--purple); background: #f7f5ff; }
        .actions { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 24px; }
        .notice { margin: 16px 0; padding: 14px 16px; border: 1px solid #e4defa; border-radius: 12px; color: #514778; background: #f8f6ff; font-size: .84rem; }
        .error { margin: 14px 0; padding: 13px 15px; border: 1px solid #f3d0d5; border-radius: 11px; color: #932e46; background: #fff5f6; font-size: .84rem; }
        .progress { height: 9px; overflow: hidden; border-radius: 99px; background: #ece9f4; }
        .progress span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #7652e5, #b27bff); transition: width .25s ease; }
        .option { display: flex; align-items: flex-start; gap: 11px; margin: 9px 0; border: 1px solid #e8e4f0; border-radius: 11px; padding: 13px 14px; color: #48445f; background: #fff; cursor: pointer; transition: border-color .16s ease, background .16s ease; }
        .option:hover, .option:has(input:checked) { border-color: #9c83ec; background: #f7f4ff; }
        .option input { margin-top: 4px; }
        .table-wrap { width: 100%; overflow-x: auto; margin-top: 22px; border: 1px solid var(--line); border-radius: 13px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px 14px; color: #78748e; background: #faf9fd; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; white-space: nowrap; }
        td { padding: 13px 14px; border-top: 1px solid #efedf4; color: #4a4662; font-size: .81rem; }
        tbody tr:hover { background: #fcfbff; }
        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
        .pagination { display: flex; justify-content: center; gap: 6px; margin-top: 18px; }
        .pagination a, .pagination span { display: inline-flex; min-width: 34px; min-height: 34px; align-items: center; justify-content: center; border: 1px solid var(--line); border-radius: 8px; padding: 0 9px; color: var(--purple); background: #fff; font-size: .78rem; text-decoration: none; }
        .pagination [aria-current="page"] span { color: #fff; background: var(--purple); }
        .home-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(350px, .85fr); align-items: stretch; gap: 20px; }
        .home-dashboard { display: grid; gap: 20px; }
        .home-hero { min-height: 425px; }
        .home-hero .hero-copy { width: min(100%, 630px); }
        .home-hero .hero-kicker { max-width: 550px; letter-spacing: .03em; line-height: 1.5; text-transform: none; }
        .home-hero .hero-copy h2 { max-width: 590px; font-size: clamp(1.35rem, 2.5vw, 1.85rem); }
        .home-hero .hero-copy > p { max-width: 560px; }
        .home-hero-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
        .home-hero-actions .home-hero-primary { color: #34216f; background: #fff; box-shadow: none; }
        .home-hero-actions .home-hero-primary:hover { color: #21164f; background: #f0ecff; }
        .home-hero-actions .home-hero-secondary { border-color: rgb(255 255 255 / 48%); color: #fff; background: rgb(255 255 255 / 8%); }
        .home-hero-actions .home-hero-secondary:hover { color: #fff; background: rgb(255 255 255 / 17%); }
        .home-info { padding: clamp(22px, 3vw, 30px); }
        .home-info-heading { max-width: 780px; }
        .home-info-heading .eyebrow { margin-bottom: 7px; }
        .home-info-heading h2 { margin-bottom: 8px; font-size: 1.35rem; }
        .home-info-heading p:last-child { margin-bottom: 20px; font-size: .82rem; }
        .home-info-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .home-info-item { min-width: 0; border: 1px solid #ece8f5; border-radius: 13px; padding: 15px; background: linear-gradient(145deg, #fff, #faf9ff); }
        .home-info-icon { display: grid; width: 31px; height: 31px; place-items: center; margin-bottom: 11px; border-radius: 9px; color: var(--purple); background: #f0ecff; font-size: .67rem; font-weight: 800; }
        .home-info-item h3 { margin-bottom: 6px; font-size: .9rem; }
        .home-info-item p { margin-bottom: 0; color: var(--muted); font-size: .76rem; line-height: 1.6; }
        .home-info-result { display: grid; gap: 4px; margin-top: 14px; border-left: 3px solid #8a6be0; border-radius: 0 9px 9px 0; padding: 10px 13px; color: #514778; background: #f7f5ff; font-size: .76rem; }
        .home-info-result strong { color: #30265e; }
        .home-level-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .home-level-card { border: 1px solid #ece8f5; border-radius: 13px; padding: 18px; background: linear-gradient(145deg, #fff, #faf9ff); }
        .home-level-range { display: inline-flex; margin-bottom: 12px; border-radius: 999px; padding: 5px 9px; color: #5d48a4; background: #f0ecff; font-size: .7rem; font-weight: 800; }
        .home-level-card h3 { margin-bottom: 7px; font-size: 1rem; }
        .home-level-card p { margin-bottom: 0; color: var(--muted); font-size: .78rem; line-height: 1.6; }
        .home-benefit-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 24px; margin: 0; padding: 0; list-style: none; }
        .home-benefit-list li { position: relative; padding-left: 27px; color: #514d69; font-size: .82rem; line-height: 1.55; }
        .home-benefit-list li::before { position: absolute; top: 0; left: 0; display: grid; width: 18px; height: 18px; place-items: center; border-radius: 50%; color: var(--purple); background: #f0ecff; content: "✓"; font-size: .7rem; font-weight: 800; }
        .home-final-cta { display: flex; align-items: center; justify-content: space-between; gap: 24px; border: 1px solid #e6defb; border-radius: 18px; padding: clamp(22px, 3vw, 32px); background: linear-gradient(115deg, #f1edff, #fff 80%); }
        .home-final-cta .eyebrow { margin-bottom: 7px; }
        .home-final-cta h2 { margin-bottom: 8px; font-size: clamp(1.2rem, 2.5vw, 1.65rem); }
        .home-final-cta p:last-child { max-width: 740px; margin-bottom: 0; color: var(--muted); font-size: .83rem; line-height: 1.6; }
        .home-final-cta .button { flex: 0 0 auto; }
        #mengenal-pelita, #mulai-asesmen { scroll-margin-top: 90px; }
        #mulai-asesmen { width: min(100%, 860px); margin: 0 auto; scroll-margin-top: 90px; }
        .hero-panel { position: relative; display: flex; min-height: 540px; align-items: center; overflow: hidden; border-radius: 24px; padding: clamp(30px, 4vw, 55px); color: #fff; background: linear-gradient(130deg, #21164f 0%, #31206f 48%, #5b38c5 100%); box-shadow: 0 22px 56px rgb(46 28 112 / 22%); }
        .hero-panel::before { position: absolute; inset: 0; background: radial-gradient(circle at 75% 45%, rgb(148 114 255 / 27%), transparent 32%), linear-gradient(120deg, transparent 35%, rgb(255 255 255 / 4%)); content: ""; }
        .hero-glow { position: absolute; border: 1px solid rgb(222 212 255 / 18%); border-radius: 50%; pointer-events: none; }
        .hero-glow-one { top: -105px; right: 12%; width: 360px; height: 360px; }
        .hero-glow-two { right: 5%; bottom: -180px; width: 410px; height: 410px; }
        .hero-copy { position: relative; z-index: 2; width: min(100%, 460px); }
        .hero-kicker { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 22px; color: #d8d0ff; font-size: .72rem; font-weight: 750; letter-spacing: .1em; text-transform: uppercase; }
        .hero-kicker-dot, .status-pill > span, .required-pill > span { width: 7px; height: 7px; border-radius: 50%; background: #a893ff; box-shadow: 0 0 0 4px rgb(168 147 255 / 15%); }
        .hero-panel h1 { margin: 0 0 2px; color: #fff; font-size: clamp(3.2rem, 6vw, 5rem); font-weight: 850; letter-spacing: -.075em; line-height: 1; }
        .hero-panel h2 { max-width: 390px; margin: 10px 0 13px; color: #f0edff; font-size: clamp(1.16rem, 2vw, 1.5rem); line-height: 1.25; }
        .hero-panel p { max-width: 390px; margin-bottom: 22px; color: #d1caef; font-size: .85rem; line-height: 1.7; }
        .hero-features { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 25px; }
        .hero-feature { display: flex; min-width: 112px; align-items: center; gap: 9px; border: 1px solid rgb(255 255 255 / 16%); border-radius: 12px; padding: 10px 11px; color: #f5f2ff; background: rgb(255 255 255 / 9%); font-size: .68rem; font-weight: 650; line-height: 1.3; backdrop-filter: blur(7px); }
        .feature-icon { display: grid; width: 28px; height: 28px; place-items: center; border-radius: 8px; color: #e5dcff; background: rgb(255 255 255 / 13%); }
        .feature-icon svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.7; }
        .hero-footnote { margin-top: 25px; color: #d2cafa; font-size: .76rem; font-style: italic; }
        .hero-art { position: absolute; right: 2%; bottom: 7%; display: grid; width: 260px; height: 280px; place-items: center; opacity: .76; pointer-events: none; }
        .art-orbit { position: absolute; width: 210px; height: 210px; border: 1px solid rgb(202 185 255 / 26%); border-radius: 50%; transform: rotateX(66deg) rotateZ(-20deg); }
        .orbit-two { width: 245px; height: 245px; transform: rotateX(70deg) rotateZ(35deg); }
        .art-core { z-index: 1; display: grid; width: 145px; height: 145px; place-items: center; border: 1px solid rgb(208 194 255 / 65%); border-radius: 50%; color: #c8b7ff; background: radial-gradient(circle, rgb(142 111 255 / 40%), rgb(69 45 150 / 42%) 60%, transparent 72%); box-shadow: 0 0 40px rgb(148 111 255 / 35%), inset 0 0 28px rgb(218 207 255 / 20%); }
        .art-core svg { width: 112px; height: 112px; fill: none; stroke: #ded5ff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2.2; filter: drop-shadow(0 0 7px rgb(177 151 255 / 90%)); }
        .art-platform { position: absolute; bottom: 2px; display: flex; width: 150px; height: 57px; flex-direction: column; justify-content: space-evenly; border: 1px solid rgb(195 176 255 / 45%); border-radius: 9px; background: linear-gradient(180deg, rgb(119 92 218 / 60%), rgb(33 22 79 / 87%)); box-shadow: 0 13px 25px rgb(13 7 41 / 26%); transform: perspective(120px) rotateX(15deg); }
        .art-platform span { height: 1px; margin: 0 11px; background: rgb(216 204 255 / 30%); }
        .art-spark { position: absolute; width: 7px; height: 7px; border-radius: 50%; background: #e3d8ff; box-shadow: 0 0 14px 5px rgb(207 187 255 / 55%); }
        .spark-one { top: 34px; right: 40px; }
        .spark-two { bottom: 75px; left: 18px; width: 4px; height: 4px; }
        .welcome-card { display: flex; flex-direction: column; justify-content: center; padding: clamp(25px, 3vw, 38px); }
        .welcome-card-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 27px; }
        .welcome-icon, .section-icon { display: grid; width: 42px; height: 42px; place-items: center; border: 1px solid #e8e1ff; border-radius: 13px; color: var(--purple); background: #f5f2ff; }
        .welcome-icon svg, .section-icon svg { width: 21px; height: 21px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.7; }
        .status-pill, .status-pill-pending { display: inline-flex; align-items: center; gap: 8px; border: 1px solid #e6e1f3; border-radius: 999px; padding: 7px 10px; color: #665f83; background: #fbfaff; font-size: .66rem; font-weight: 700; white-space: nowrap; }
        .status-pill > span { width: 6px; height: 6px; background: #4ab792; box-shadow: 0 0 0 3px rgb(74 183 146 / 12%); }
        .status-pill-pending > span { background: #d3a53b; box-shadow: 0 0 0 3px rgb(211 165 59 / 12%); }
        .welcome-card .eyebrow { margin-bottom: 8px; font-size: .66rem; }
        .welcome-card h2 { margin-bottom: 8px; font-size: 1.45rem; }
        .welcome-card > .muted { margin-bottom: 21px; font-size: .82rem; }
        .welcome-card .field { margin-bottom: 15px; }
        .welcome-card input { background: #fdfcff; }
        .privacy-note { display: flex; align-items: flex-start; gap: 9px; margin: 5px 0 18px; color: #77738b; font-size: .69rem; line-height: 1.5; }
        .privacy-note svg { width: 16px; height: 16px; flex: 0 0 auto; fill: none; stroke: var(--purple); stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.7; }
        .button-wide { width: 100%; }
        .welcome-footnote { margin: 14px 0 0; color: #9a96aa; font-size: .66rem; text-align: center; }
        .empty-state-art { position: relative; display: grid; min-height: 140px; place-items: center; overflow: hidden; margin: 12px 0; border-radius: 14px; background: linear-gradient(145deg, #faf9ff, #f1edff); }
        .empty-state-circle { position: absolute; width: 100px; height: 100px; border: 1px solid #ded5ff; border-radius: 50%; box-shadow: 0 0 0 16px rgb(232 225 255 / 45%), 0 0 0 32px rgb(239 235 255 / 65%); }
        .empty-state-art svg { z-index: 1; width: 54px; height: 54px; fill: none; stroke: var(--purple); stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.5; }
        .assessment-page { display: grid; grid-template-columns: minmax(0, 1fr) 276px; align-items: start; gap: 18px; }
        .assessment-main { padding: clamp(22px, 3vw, 34px); }
        .assessment-title-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; }
        .assessment-title-row h1 { margin-bottom: 7px; font-size: clamp(1.55rem, 2.8vw, 2.1rem); }
        .assessment-greeting { margin-bottom: 0; font-size: .82rem; }
        .required-pill, .optional-pill { display: inline-flex; align-items: center; gap: 8px; border: 1px solid #e5e0f0; border-radius: 999px; padding: 7px 10px; color: #716c86; background: #fff; font-size: .65rem; font-weight: 700; white-space: nowrap; }
        .required-pill > span { width: 6px; height: 6px; background: #8b6ce0; box-shadow: 0 0 0 3px #f0ebff; }
        .question-progress { margin: 26px 0 22px; }
        .question-progress-label { display: flex; justify-content: space-between; margin-bottom: 9px; color: #45415f; font-size: .76rem; font-weight: 700; }
        .question-progress-label strong { color: var(--purple); }
        .answer-fieldset { min-width: 0; margin: 0; border: 0; padding: 0; }
        .question-text { display: block; margin: 0 0 12px; color: var(--ink); font-size: 1.08rem; font-weight: 750; line-height: 1.5; }
        .dimension-tag { display: inline-flex; margin-bottom: 10px; border: 1px solid #e8e1ff; border-radius: 999px; padding: 6px 10px; color: #5d48a4; background: #f5f2ff; font-size: .65rem; font-weight: 700; }
        .answer-options { margin-top: 4px; }
        .answer-option { position: relative; display: flex; min-height: 46px; align-items: center; gap: 11px; margin: 8px 0; border: 1px solid #e9e5f1; border-radius: 10px; padding: 11px 13px; color: #4a4662; background: #fff; font-size: .79rem; cursor: pointer; transition: border-color .16s ease, background .16s ease; }
        .answer-option:hover, .answer-option.is-selected { border-color: #9c84e9; background: #f7f4ff; }
        .answer-option input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .radio-indicator { display: grid; width: 17px; height: 17px; flex: 0 0 auto; place-items: center; border: 1.5px solid #c8c3d7; border-radius: 50%; background: #fff; }
        .answer-option.is-selected .radio-indicator { border-color: var(--purple); }
        .answer-option.is-selected .radio-indicator::after { width: 8px; height: 8px; border-radius: 50%; background: var(--purple); content: ""; }
        .answer-option:focus-within { outline: 3px solid #bcaeff; outline-offset: 2px; }
        .assessment-actions { display: flex; justify-content: space-between; gap: 12px; margin-top: 24px; padding-top: 19px; border-top: 1px solid #f0edf5; }
        .action-spacer { flex: 1; }
        .assessment-side { position: sticky; top: 96px; padding: 22px; text-align: center; }
        .assessment-side .eyebrow { margin-bottom: 17px; text-align: left; }
        .progress-ring { display: grid; width: 138px; height: 138px; place-items: center; margin: 0 auto 13px; border-radius: 50%; background: conic-gradient(#7652e5 var(--progress), #eeeaf6 0); }
        .progress-ring-center { display: flex; width: 111px; height: 111px; flex-direction: column; align-items: center; justify-content: center; border-radius: 50%; background: #fff; }
        .progress-ring-center strong { color: var(--ink); font-size: 1.55rem; letter-spacing: -.04em; }
        .progress-ring-center span { color: var(--muted); font-size: .68rem; }
        .progress-count { margin-bottom: 19px; color: var(--muted); font-size: .73rem; }
        .progress-count strong { color: var(--purple); }
        .side-divider { height: 1px; margin: 19px 0; background: var(--line); }
        .assessment-side h2 { margin-bottom: 10px; text-align: left; font-size: .85rem; }
        .current-dimension { display: flex; align-items: center; gap: 9px; border-radius: 9px; padding: 10px; color: #4b4665; background: #f6f3ff; font-size: .72rem; font-weight: 650; text-align: left; }
        .dimension-dot { width: 8px; height: 8px; flex: 0 0 auto; border-radius: 50%; background: #7956df; box-shadow: 0 0 0 4px #e9e2ff; }
        .side-note { display: flex; align-items: flex-start; gap: 8px; margin-top: 20px; color: #817d96; font-size: .68rem; line-height: 1.55; text-align: left; }
        .side-note-icon { color: #8c6ce5; font-size: 1rem; }
        .results-page { display: grid; gap: 20px; }
        .results-overview { display: flex; align-items: center; justify-content: space-between; gap: 24px; border: 1px solid #e7e1fb; border-radius: 20px; padding: 25px 28px; background: linear-gradient(115deg, #f4f1ff, #fff 68%); }
        .results-download { display: grid; justify-items: start; gap: 7px; }
        .results-download .button svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8; }
        .results-download > span { color: var(--muted); font-size: .68rem; }
        .results-intro { display: flex; align-items: center; gap: 18px; }
        .results-intro h1 { margin-bottom: 7px; font-size: clamp(1.55rem, 3vw, 2.15rem); }
        .results-intro p:last-child { margin-bottom: 0; font-size: .8rem; }
        .results-trophy { display: grid; width: 74px; height: 74px; flex: 0 0 auto; place-items: center; border: 1px solid #e3d9ff; border-radius: 50%; color: var(--purple); background: radial-gradient(circle, #fff 45%, #e8e0ff 46%); box-shadow: 0 8px 24px rgb(88 54 201 / 10%); }
        .results-trophy svg { width: 40px; height: 40px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.6; }
        .score-highlight { width: min(100%, 255px); flex: 0 0 auto; border: 1px solid #e7e1f6; border-radius: 15px; padding: 16px; background: #fff; }
        .score-highlight > span:first-child { display: block; color: var(--muted); font-size: .69rem; font-weight: 700; }
        .score-highlight > strong { display: block; margin: 4px 0 9px; color: var(--ink); font-size: 1.65rem; letter-spacing: -.045em; }
        .score-highlight > strong small { color: #9995a9; font-size: .82rem; font-weight: 600; }
        .score-highlight .progress { height: 7px; }
        .maturity-pill { display: inline-flex; margin-top: 10px; border-radius: 999px; padding: 5px 9px; color: #5334b4; background: #f1edff; font-size: .66rem; font-weight: 750; }
        .results-grid { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr); align-items: stretch; gap: 18px; }
        .dimensions-card, .recommendation-card { padding: 24px; }
        .section-heading { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 20px; }
        .section-heading .eyebrow { margin-bottom: 5px; font-size: .64rem; }
        .section-heading h2 { margin-bottom: 0; }
        .section-icon { width: 38px; height: 38px; border-radius: 11px; font-size: 1.1rem; }
        .dimension-results { display: grid; gap: 19px; }
        .dimension-result-heading { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 8px; color: #514d68; font-size: .79rem; }
        .dimension-result-heading strong { color: var(--ink); }
        .dimension-result .progress { height: 8px; }
        .result-status { display: inline-flex; margin-top: 7px; border-radius: 999px; padding: 4px 8px; color: #38745b; background: #eaf8f1; font-size: .64rem; font-weight: 750; }
        .result-status-needs-work { color: #9a5c1f; background: #fff3e1; }
        .result-caption { margin: 19px 0 0; color: #9390a4; font-size: .7rem; line-height: 1.55; }
        .recommendation-card { position: relative; overflow: hidden; border-color: #e4ddfa; background: linear-gradient(145deg, #fff, #f8f6ff); }
        .recommendation-card .eyebrow { margin-bottom: 15px; }
        .recommendation-icon { display: grid; width: 42px; height: 42px; place-items: center; margin-bottom: 13px; border-radius: 13px; color: #6747cd; background: #eee9ff; }
        .recommendation-icon svg { width: 22px; height: 22px; fill: currentColor; }
        .recommendation-card h2 { margin-bottom: 9px; font-size: 1.1rem; }
        .recommendation-card p { color: #716d83; font-size: .8rem; line-height: 1.65; }
        .recommendation-list { display: grid; gap: 14px; }
        .recommendation-item { padding-top: 13px; border-top: 1px solid #e8e2f5; }
        .recommendation-item h3 { margin-bottom: 5px; font-size: .84rem; }
        .recommendation-item p { margin-bottom: 6px; }
        .recommendation-link { margin-top: 7px; }
        .answer-review { margin-top: 20px; }
        .answer-review-list { display: grid; gap: 13px; }
        .answer-review-item { border: 1px solid #ece8f5; border-radius: 13px; padding: 16px; background: #fff; }
        .answer-review-item h3 { margin-bottom: 8px; line-height: 1.5; }
        .answer-review-item p { margin-bottom: 6px; font-size: .8rem; }
        .page-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; margin-bottom: 24px; }
        .page-heading h1 { margin-bottom: 7px; }
        .page-heading p:last-child { margin-bottom: 0; font-size: .82rem; }
        .page-heading .button svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 15px; margin-bottom: 18px; }
        .stat-card { position: relative; min-height: 150px; overflow: hidden; border: 1px solid #e7e2f2; border-radius: 16px; padding: 18px; background: #fff; box-shadow: 0 10px 30px rgb(41 29 94 / 5%); }
        .stat-card::after { position: absolute; right: -25px; bottom: -52px; width: 120px; height: 120px; border: 1px solid currentColor; border-radius: 50%; opacity: .08; content: ""; }
        .stat-purple { color: #694bd0; background: linear-gradient(135deg, #f7f4ff, #fff); }
        .stat-blue { color: #3e71be; background: linear-gradient(135deg, #f2f7ff, #fff); }
        .stat-gold { color: #a8782b; background: linear-gradient(135deg, #fff9ee, #fff); }
        .stat-icon { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 11px; background: rgb(255 255 255 / 80%); }
        .stat-icon svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.7; }
        .stat-label { position: absolute; top: 23px; left: 64px; color: #77738b; font-size: .7rem; font-weight: 700; }
        .stat-value { display: block; margin-top: 12px; color: var(--ink); font-size: 1.8rem; letter-spacing: -.05em; }
        .stat-value small { color: #8d89a2; font-size: .75rem; font-weight: 600; letter-spacing: 0; }
        .stat-caption { display: block; margin-top: 2px; color: #9490a5; font-size: .68rem; }
        .dashboard-grid { display: grid; grid-template-columns: minmax(270px, .75fr) minmax(0, 1.25fr); align-items: start; gap: 18px; }
        .distribution-card, .latest-card { min-width: 0; padding: 22px; }
        .level-row { margin: 17px 0; }
        .level-row-heading { display: flex; align-items: center; gap: 9px; margin-bottom: 8px; color: #57536d; font-size: .76rem; }
        .level-row-heading strong { margin-left: auto; color: var(--ink); }
        .level-row-heading small { width: 35px; color: #908ca2; font-size: .69rem; text-align: right; }
        .level-dot { width: 8px; height: 8px; flex: 0 0 auto; border-radius: 50%; background: #805be1; }
        .level-dot-adopter { background: #a575e8; }
        .level-dot-transformer { background: #5e47c5; }
        .level-row .progress { height: 7px; }
        .level-row .progress span { background: linear-gradient(90deg, #7955dc, #b592f1); }
        .empty-state { display: flex; min-height: 145px; flex-direction: column; align-items: center; justify-content: center; gap: 5px; border: 1px dashed #e2ddef; border-radius: 12px; color: #8b879c; font-size: .74rem; text-align: center; }
        .empty-state strong { color: #55516c; font-size: .84rem; }
        .empty-state-icon { color: #9477e5; font-size: 1.7rem; }
        .text-link { color: var(--purple); font-size: .72rem; font-weight: 750; text-decoration: none; white-space: nowrap; }
        .text-link:hover { text-decoration: underline; }
        .participant-cell { display: flex; align-items: center; gap: 9px; min-width: 155px; }
        .participant-cell > span:last-child { display: block; color: #413d58; font-weight: 700; }
        .participant-cell small { display: block; margin-top: 3px; color: #9692a6; font-size: .65rem; font-weight: 500; }
        .table-avatar { display: grid; width: 30px; height: 30px; flex: 0 0 auto; place-items: center; border-radius: 50%; color: #5b3cc4; background: #f0ecff; font-size: .68rem; font-weight: 800; }
        .table-level { display: inline-flex; border-radius: 999px; padding: 5px 8px; color: #5842a4; background: #f1edff; font-size: .66rem; font-weight: 700; white-space: nowrap; }
        .management-page { padding: clamp(20px, 3vw, 30px); }
        .management-page .page-heading { align-items: flex-start; margin-bottom: 20px; }
        .management-page .page-heading h1 { font-size: clamp(1.7rem, 3vw, 2.2rem); }
        .management-page .page-heading p:last-child { max-width: 680px; }
        .assessment-selector { max-width: 560px; margin-bottom: 18px; }
        .management-actions { justify-content: flex-start; flex-wrap: wrap; margin: 0 0 18px; }
        .management-actions form, .table-actions form, .move-actions form { margin: 0; }
        .management-table-wrap { margin-top: 0; }
        .table-scroll-hint { display: none; margin: 0 0 8px; color: var(--muted); font-size: .72rem; }
        .management-table { min-width: 700px; }
        .management-table th { padding: 13px 15px; }
        .management-table td { vertical-align: middle; padding: 13px 15px; }
        .question-table { min-width: 880px; }
        .question-position { min-width: 76px; color: #5c5872; font-weight: 750; }
        .question-position strong { display: block; margin-bottom: 7px; color: var(--ink); }
        .move-actions { display: flex; gap: 5px; }
        .icon-button { display: grid; width: 31px; height: 31px; place-items: center; border: 1px solid #e4dff0; border-radius: 8px; color: #6552a7; background: #fff; font-weight: 750; cursor: pointer; transition: border-color .16s ease, color .16s ease, background .16s ease; }
        .icon-button:hover { border-color: #c9baf4; color: var(--purple); background: #f6f3ff; }
        .question-copy { min-width: 260px; max-width: 440px; color: #34304e; font-weight: 600; line-height: 1.55; }
        .table-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 7px; }
        .button-compact { min-height: 32px; border-radius: 8px; padding: 6px 10px; font-size: .72rem; }
        .status-tag { display: inline-flex; align-items: center; border: 1px solid #dfeee8; border-radius: 999px; padding: 5px 9px; color: #34745a; background: #eff9f4; font-size: .68rem; font-weight: 750; white-space: nowrap; }
        .status-tag-inactive { border-color: #e8e5ee; color: #79758a; background: #f7f6f9; }
        .dimension-slug { color: #77738b; font-size: .75rem; overflow-wrap: anywhere; }
        .dimension-weight { color: #563db3; font-weight: 750; white-space: nowrap; }
        .dimension-create { margin-top: 26px; border-top: 1px solid var(--line); padding-top: 24px; }
        .dimension-create h2 { margin-bottom: 7px; }
        .dimension-create > p { margin-bottom: 18px; font-size: .8rem; }
        .participant-detail { display: grid; gap: 18px; }
        .participant-detail > .card { padding: clamp(20px, 3vw, 30px); }
        .participant-results-overview { margin-bottom: 15px; }
        .participant-results-overview .results-intro { flex: 1; }
        .participant-results-overview .results-intro .eyebrow { margin-bottom: 6px; }
        .participant-results-overview .results-intro p:last-child { margin-bottom: 0; }
        .participant-summary-text { border: 1px solid #e8e2f5; border-radius: 12px; padding: 14px 16px; color: #514778; background: #faf9ff; }
        .participant-summary-text strong { color: #30265e; font-size: .82rem; }
        .participant-summary-text p { margin: 5px 0 0; font-size: .8rem; }
        .participant-email-status { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 0; }
        .participant-email-status .status-tag { white-space: normal; }
        .participant-dimension-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .participant-dimension-card { border: 1px solid #ece8f5; border-radius: 12px; padding: 15px; background: #fff; }
        .participant-dimension-meta { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 8px; font-size: .7rem; }
        .participant-dimension-meta .result-status { margin-top: 0; }
        .participant-detail .section-heading { margin-bottom: 16px; }
        .participant-detail .answer-review-list { margin-top: 0; }
        @media (max-width: 720px) {
            .participant-dimension-grid { grid-template-columns: 1fr; }
            .participant-email-status { align-items: flex-start; flex-direction: column; }
        }
        @media (max-width: 920px) {
            .app-shell { grid-template-columns: 214px minmax(0, 1fr); }
            .sidebar { padding-right: 13px; padding-left: 13px; }
            .content-shell { width: min(100% - 36px, 1320px); }
            .hero-art { right: -4%; opacity: .5; }
            .home-grid { grid-template-columns: 1fr; }
            .home-info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .home-level-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .hero-panel { min-height: 430px; }
            .assessment-page { grid-template-columns: 1fr; }
            .assessment-side { position: static; }
            .dashboard-grid { grid-template-columns: 1fr; }
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 720px) {
            .table-scroll-hint { display: block; }
            .app-shell { display: block; }
            .sidebar { position: fixed; z-index: 30; top: 0; left: 0; width: min(286px, 86vw); height: 100vh; height: 100dvh; padding: 24px 18px 18px; border-right: 1px solid var(--line); transform: translateX(-102%); visibility: hidden; transition: transform .22s ease, visibility .22s ease; }
            .app-shell.sidebar-open .sidebar { transform: translateX(0); visibility: visible; }
            .sidebar-overlay { position: fixed; z-index: 25; inset: 0; display: block; width: 100%; border: 0; background: rgb(28 22 58 / 42%); cursor: pointer; }
            .sidebar-overlay[hidden] { display: none; }
            .sidebar .nav-caption { display: block; }
            .sidebar-promo { display: block; }
            .sidebar-spacer { display: block; }
            .sidebar-nav { display: grid; overflow: auto; gap: 6px; padding: 0 0 2px; }
            .nav-link, .nav-action { width: auto; min-height: 38px; flex: 0 0 auto; padding: 8px 11px; font-size: .76rem; }
            .sidebar-bottom { display: block; padding-top: 10px; border-top: 1px solid var(--line); }
            .topbar { min-height: 61px; padding: 10px 18px; }
            .topbar-kicker { font-size: .62rem; }
            .topbar-title { font-size: .84rem; }
            .content-shell { width: min(100% - 28px, 1320px); min-height: calc(100vh - 175px); padding: 24px 0 34px; }
            .footer-inner { align-items: flex-start; flex-direction: column; gap: 12px; }
            .card { padding: 21px; border-radius: 16px; }
            .home-grid { grid-template-columns: 1fr; }
            .home-info-grid { grid-template-columns: 1fr; }
            .home-level-grid { grid-template-columns: 1fr; }
            .home-benefit-list { grid-template-columns: 1fr; }
            .home-final-cta { align-items: stretch; flex-direction: column; }
            .home-final-cta .button { width: 100%; }
            .hero-panel { min-height: 410px; padding: 30px; }
            .hero-art { right: 0; bottom: 1%; transform: scale(.8); transform-origin: bottom right; }
            .welcome-card { padding: 25px; }
            .assessment-page { grid-template-columns: 1fr; }
            .assessment-side { position: static; display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 0 18px; text-align: left; }
            .assessment-side .eyebrow { grid-column: 1 / -1; margin-bottom: 14px; }
            .assessment-side .progress-ring { grid-row: span 2; width: 105px; height: 105px; margin: 0; }
            .assessment-side .progress-ring-center { width: 84px; height: 84px; }
            .assessment-side .progress-ring-center strong { font-size: 1.2rem; }
            .assessment-side .progress-count { align-self: end; margin: 0; }
            .assessment-side .side-divider { grid-column: 1 / -1; width: 100%; }
            .assessment-side h2, .assessment-side .current-dimension, .assessment-side .side-note { grid-column: 1 / -1; }
            .assessment-side .side-note { margin-top: 12px; }
            .results-overview { align-items: flex-start; flex-direction: column; }
            .score-highlight { width: 100%; }
            .results-download { width: 100%; }
            .results-download .button { width: 100%; }
            .results-grid { grid-template-columns: 1fr; }
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 560px) {
            .grid { grid-template-columns: 1fr; gap: 0; }
            .actions { align-items: stretch; }
            .actions .button { flex: 1; }
            .user-chip { gap: 6px; }
            .user-chip > span:last-child { display: none; }
            .hero-panel { min-height: 465px; align-items: flex-start; padding: 28px 22px; }
            .hero-art { right: -32px; bottom: -16px; transform: scale(.65); }
            .hero-copy { width: 100%; }
            .hero-panel p { max-width: 320px; }
            .home-hero .hero-copy { width: 100%; }
            .home-hero .hero-copy > p { max-width: 320px; }
            .home-hero-actions { align-items: stretch; flex-direction: column; }
            .hero-features { position: relative; z-index: 2; gap: 6px; }
            .hero-feature { min-width: 100px; gap: 6px; padding: 8px; font-size: .61rem; }
            .feature-icon { width: 24px; height: 24px; }
            .assessment-title-row { flex-direction: column; gap: 12px; }
            .assessment-actions .button { padding: 9px 11px; font-size: .73rem; }
            .results-intro { align-items: flex-start; gap: 12px; }
            .results-trophy { width: 54px; height: 54px; }
            .results-trophy svg { width: 30px; height: 30px; }
            .stat-grid { grid-template-columns: 1fr; }
            .page-heading { align-items: flex-start; flex-direction: column; }
            .page-heading .button { width: 100%; }
            .section-heading { align-items: flex-start; }
            .management-actions { align-items: stretch; }
            .management-actions > *, .management-actions form { flex: 1 1 100%; }
            .management-actions .button { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="primary-sidebar">
            <a class="brand-lockup" href="{{ route('home') }}" aria-label="PELITA - Beranda" title="PELITA">
                <svg class="brand-mark" viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M24 4 28 15 39 10 34 21 45 25 34 29 39 40 28 35 24 46 20 35 9 40 14 29 3 25 14 21 9 10 20 15Z" fill="currentColor"/>
                    <circle cx="24" cy="25" r="5" fill="#fff"/>
                </svg>
                <span><span class="brand-name">PELITA</span><span class="brand-caption">Literasi & kesiapan AI</span></span>
            </a>

            <div class="nav-caption">{{ auth()->check() && auth()->user()->is_admin ? 'Menu admin' : 'Navigasi' }}</div>
            <nav class="sidebar-nav" aria-label="Navigasi utama">
                @auth
                    @if (auth()->user()->is_admin)
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}" aria-label="Dashboard" title="Dashboard">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg></span>
                            <span>Dashboard</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.participants.*') ? 'is-active' : '' }}" href="{{ route('admin.participants.index') }}" aria-label="Data peserta" title="Data peserta">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a4 4 0 0 1 3 4v2"/></svg></span>
                            <span>Data peserta</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.questions.*', 'admin.assessments.*') ? 'is-active' : '' }}" href="{{ route('admin.questions.index') }}" aria-label="Asesmen dan soal" title="Asesmen dan soal">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h8l4 4v14H7zM15 3v5h5M10 12h6M10 16h6"/></svg></span>
                            <span>Asesmen & soal</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.dimensions.*') ? 'is-active' : '' }}" href="{{ route('admin.dimensions.index') }}" aria-label="Dimensi" title="Dimensi">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18M5.6 5.6l12.8 12.8M18.4 5.6 5.6 18.4"/></svg></span>
                            <span>Dimensi</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.scoring-settings.*') ? 'is-active' : '' }}" href="{{ route('admin.scoring-settings.index') }}" aria-label="Pengaturan nilai" title="Pengaturan nilai">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20V7"/><path d="M2 20h21"/></svg></span>
                            <span>Pengaturan nilai</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.privacy-settings.*') ? 'is-active' : '' }}" href="{{ route('admin.privacy-settings.index') }}" aria-label="Pengaturan privasi" title="Pengaturan privasi">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg></span>
                            <span>Pengaturan privasi</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}" href="{{ route('admin.activity-logs.index') }}" aria-label="Aktivitas admin" title="Aktivitas admin">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/><circle cx="7" cy="5" r="1"/><circle cx="7" cy="12" r="1"/><circle cx="7" cy="19" r="1"/></svg></span>
                            <span>Aktivitas admin</span>
                        </a>
                    @else
                        <a class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}" aria-label="Beranda" title="Beranda">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7h-6v7H4a1 1 0 0 1-1-1z"/></svg></span>
                            <span>Beranda</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('assessment.show', 'assessment.complete') ? 'is-active' : '' }}" href="{{ route('home') }}#mulai-asesmen" aria-label="Mulai asesmen" title="Mulai asesmen">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h8l4 4v14H7zM15 3v5h5M10 12h6M10 16h6"/></svg></span>
                            <span>Asesmen</span>
                        </a>
                    @endif
                @else
                    <a class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}" aria-label="Beranda" title="Beranda">
                        <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7h-6v7H4a1 1 0 0 1-1-1z"/></svg></span>
                        <span>Beranda</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('assessment.show', 'assessment.complete') ? 'is-active' : '' }}" href="{{ route('home') }}#mulai-asesmen" aria-label="Mulai asesmen" title="Mulai asesmen">
                        <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h8l4 4v14H7zM15 3v5h5M10 12h6M10 16h6"/></svg></span>
                        <span>Asesmen</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.login') ? 'is-active' : '' }}" href="{{ route('admin.login') }}" aria-label="Masuk admin" title="Masuk admin">
                        <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg></span>
                        <span>Masuk admin</span>
                    </a>
                @endauth
            </nav>

            <div class="sidebar-spacer"></div>
            <div class="sidebar-promo">
                <strong>Kenali potensi, siapkan masa depan bersama PELITA.</strong>
                <span>Pemetaan literasi dan kesiapan untuk penggunaan AI yang lebih bijak.</span>
            </div>
            @auth
                @if (auth()->user()->is_admin)
                    <div class="sidebar-bottom">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="nav-action" type="submit" aria-label="Keluar" title="Keluar">
                                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M21 4v16"/></svg></span>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                @endif
            @endauth
        </aside>
        <button class="sidebar-overlay" id="sidebar-overlay" type="button" aria-label="Tutup navigasi" hidden></button>

        <div class="main-panel">
            <header class="topbar">
                <div class="topbar-leading">
                    <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-controls="primary-sidebar" aria-expanded="true" aria-label="Ciutkan sidebar" title="Ciutkan sidebar">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/><path d="m9 8-3 4 3 4"/></svg>
                    </button>
                    <div>
                        <span class="topbar-kicker">@yield('topbar_eyebrow', 'Pemetaan Literasi AI')</span>
                        <div class="topbar-title">@yield('topbar_title', 'Kenali kesiapan Anda')</div>
                    </div>
                </div>
                <div class="user-chip">
                    @auth
                        @if (auth()->user()->is_admin)
                            <span class="user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span>{{ auth()->user()->name }}<small>Administrator</small></span>
                        @else
                            <span class="guest-pill">Pengguna</span>
                        @endif
                    @else
                        <span class="guest-pill">Asesmen tanpa akun</span>
                    @endauth
                </div>
            </header>
            <main class="content-shell">
                @yield('content')
            </main>
            <footer class="footer">
                <div class="footer-inner">
                    <div class="footer-brand">
                        <strong>PELITA</strong>
                        <span>Pemetaan Literasi dan Kesiapan Teknologi Artificial Intelligence.</span>
                        <p>Platform asesmen untuk mendukung peningkatan literasi dan kesiapan pemanfaatan teknologi AI.</p>
                    </div>
                    <span>© 2026 PELITA. All Rights Reserved.</span>
                </div>
            </footer>
        </div>
    </div>
    <script>
        const appShell = document.querySelector('.app-shell');
        const sidebar = document.getElementById('primary-sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const mobileNavigation = window.matchMedia('(max-width: 720px)');
        let sidebarCollapsed = false;
        let sidebarOpen = false;

        function updateSidebar() {
            const isMobile = mobileNavigation.matches;
            appShell.classList.toggle('sidebar-collapsed', !isMobile && sidebarCollapsed);
            appShell.classList.toggle('sidebar-open', isMobile && sidebarOpen);
            document.body.classList.toggle('navigation-open', isMobile && sidebarOpen);
            sidebarOverlay.hidden = !isMobile || !sidebarOpen;
            sidebar.inert = isMobile && !sidebarOpen;
            sidebarToggle.setAttribute('aria-expanded', String(isMobile ? sidebarOpen : !sidebarCollapsed));

            const label = isMobile
                ? (sidebarOpen ? 'Tutup navigasi' : 'Buka navigasi')
                : (sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
            sidebarToggle.setAttribute('aria-label', label);
            sidebarToggle.title = label;
        }

        sidebarToggle.addEventListener('click', () => {
            if (mobileNavigation.matches) {
                sidebarOpen = !sidebarOpen;
                updateSidebar();
                if (sidebarOpen) {
                    const firstNavigationLink = sidebar.querySelector('.nav-link');
                    if (firstNavigationLink) {
                        firstNavigationLink.focus();
                    }
                } else {
                    sidebarToggle.focus();
                }
                return;
            }

            sidebarCollapsed = !sidebarCollapsed;
            updateSidebar();
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebarOpen = false;
            updateSidebar();
            sidebarToggle.focus();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileNavigation.matches && sidebarOpen) {
                sidebarOpen = false;
                updateSidebar();
                sidebarToggle.focus();
            }
        });

        window.addEventListener('resize', updateSidebar);
        updateSidebar();
    </script>
</body>
</html>
