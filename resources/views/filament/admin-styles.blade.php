<style>
    :root { --cat-gold: #8a6300; --cat-cream: #f9f8f4; --cat-line: #ece4d0; }

    /* Page canvas and typography */
    .fi-body { background: var(--cat-cream); font-family: 'Vazirmatn', system-ui, sans-serif; }
    .fi-body:is(.dark *) { background: #1c1a17; }

    /* Sidebar */
    .fi-sidebar { background: #fff; border-inline-end: 1px solid var(--cat-line); }
    .fi-sidebar-header { background: #fff; border-bottom: 1px solid var(--cat-line); box-shadow: none; }
    .fi-sidebar-group-label { font-size: .72rem; letter-spacing: .02em; color: #a8a096; font-weight: 600; }
    .fi-sidebar-item-button { border-radius: .75rem; transition: background .15s, color .15s; }
    .fi-sidebar-item-active .fi-sidebar-item-button { background: #f0e6c8; }
    .fi-sidebar-item-active .fi-sidebar-item-label,
    .fi-sidebar-item-active .fi-sidebar-item-icon { color: var(--cat-gold); font-weight: 700; }

    /* Topbar */
    .fi-topbar > nav { background: rgba(249, 248, 244, .85); backdrop-filter: blur(10px); box-shadow: none; border-bottom: 1px solid var(--cat-line); }

    /* Page header */
    .fi-header-heading { font-weight: 800; letter-spacing: -.01em; color: #2b2620; }
    .fi-breadcrumbs { font-size: .8rem; }

    /* Cards: sections, tables, widgets, stats */
    .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-wi-chart, .fi-fo-wizard {
        border-radius: 1rem; border: 1px solid var(--cat-line); background: #fff;
        box-shadow: 0 1px 2px rgba(43, 38, 32, .04);
    }
    .fi-section:is(.dark *), .fi-ta-ctn:is(.dark *), .fi-wi-stats-overview-stat:is(.dark *) { background: #26231f; border-color: #3a352e; }
    .fi-section-header-heading { font-weight: 700; }
    .fi-wi-stats-overview-stat { padding: 1.25rem; transition: transform .15s, box-shadow .15s; }
    .fi-wi-stats-overview-stat:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(138, 99, 0, .1); }
    .fi-wi-stats-overview-stat-value { font-weight: 800; }

    /* Tables */
    .fi-ta-header-cell { background: #faf7ef; font-weight: 700; color: #6b6154; }
    .fi-ta-row { transition: background .12s; }
    .fi-ta-row:hover { background: #fdfaf0; }
    .fi-ta-header-ctn, .fi-ta-header-toolbar { border-color: var(--cat-line); }

    /* Buttons and inputs */
    .fi-btn { border-radius: .75rem; font-weight: 600; }
    .fi-input-wrp { border-radius: .75rem; }
    .fi-input-wrp:focus-within { box-shadow: 0 0 0 3px rgba(138, 99, 0, .18); }
    .fi-badge { border-radius: 999px; font-weight: 600; }

    /* Gold price widget */
    .cat-gold { display: flex; flex-direction: column; align-items: flex-start; gap: .35rem; }
    .cat-gold__label { font-size: .85rem; color: #6b6154; }
    .cat-gold__price { font-size: 1.9rem; font-weight: 800; color: var(--cat-gold); line-height: 1.3; }
    .cat-gold__price span { font-size: .9rem; font-weight: 500; color: #a8a096; }
    .cat-gold__meta { font-size: .78rem; color: #a8a096; margin-bottom: .6rem; }

    /* Login page */
    .fi-simple-main { border-radius: 1.25rem; border: 1px solid var(--cat-line); box-shadow: 0 12px 40px rgba(138, 99, 0, .08); }
    .fi-simple-layout { background: linear-gradient(160deg, #fdf6e3 0%, var(--cat-cream) 55%, #f0e6c8 100%); }
</style>
