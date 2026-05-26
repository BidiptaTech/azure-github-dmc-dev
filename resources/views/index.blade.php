@extends('layouts.layout')

@section('content')
<style>
    /* ===== Premium SaaS Dashboard ===== */
    :root {
        --dash-bg: #f0f2f5;
        --card-bg: #ffffff;
        --border: #e2e5ea;
        --border-light: #f0f2f5;
        --text-primary: #1a1d26;
        --text-secondary: #5a6170;
        --text-muted: #8b92a5;
        --accent: #4f46e5;
        --accent-light: #eef2ff;
        --accent-hover: #4338ca;
        --green: #059669;
        --green-light: #ecfdf5;
        --amber: #d97706;
        --amber-light: #fffbeb;
        --red: #dc2626;
        --red-light: #fef2f2;
        --blue: #2563eb;
        --blue-light: #eff6ff;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md: 0 4px 6px rgba(0,0,0,0.06), 0 2px 4px rgba(0,0,0,0.04);
        --shadow-lg: 0 10px 25px rgba(0,0,0,0.08), 0 4px 10px rgba(0,0,0,0.04);
        --shadow-xl: 0 20px 40px rgba(0,0,0,0.10), 0 8px 16px rgba(0,0,0,0.06);
        --radius: 14px;
        --radius-sm: 10px;
    }

    .saas-dashboard {
        background: var(--dash-bg);
        min-height: 100vh;
        padding: 28px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* ---- Header ---- */
    .dash-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .dash-header-left h1 {
        font-size: 1.625rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0 0 4px 0;
        line-height: 1.3;
        letter-spacing: -0.02em;
    }
    .dash-header-left p {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin: 0;
    }
    .dash-header-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .dash-date-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 9px 16px;
        font-size: 0.8125rem;
        color: var(--text-secondary);
        font-weight: 500;
        box-shadow: var(--shadow-sm);
    }
    .dash-date-badge i { font-size: 1rem; color: var(--accent); }

    /* ---- Period Toggle ---- */
    .period-toggle {
        display: inline-flex;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 4px;
        gap: 2px;
        box-shadow: var(--shadow-sm);
    }
    .period-toggle .toggle-btn {
        background: transparent;
        border: none;
        padding: 8px 20px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-secondary);
        border-radius: 7px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .period-toggle .toggle-btn:hover {
        color: var(--text-primary);
        background: var(--border-light);
    }
    .period-toggle .toggle-btn.active {
        background: var(--accent);
        color: #fff;
        box-shadow: 0 2px 8px rgba(79,70,229,0.35);
    }

    /* ---- Flash alerts ---- */
    .dash-alert {
        border-radius: var(--radius-sm);
        padding: 14px 18px;
        font-size: 0.8125rem;
        margin-bottom: 16px;
        border: 1px solid;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }
    .dash-alert.success { background: var(--green-light); border-color: #a7f3d0; color: var(--green); }
    .dash-alert.error   { background: var(--red-light);   border-color: #fecaca; color: var(--red); }

    /* ---- KPI Grid ---- */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    /* ==== HERO KPI CARDS — Bold Colored Backgrounds ==== */
    .hero-card {
        border-radius: var(--radius);
        padding: 18px;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
    }
    .hero-card::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -20%;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
        pointer-events: none;
    }
    .hero-card::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -15%;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
        pointer-events: none;
    }
    .hero-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }
    .hero-card .kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
        color: #fff;
        font-size: 1.15rem;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .hero-card .kpi-value {
        font-size: 1.95rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.02em;
    }
    .hero-card .kpi-label {
        color: rgba(255,255,255,0.8);
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .hero-card .kpi-progress {
        background: rgba(255,255,255,0.15);
        height: 5px;
        border-radius: 3px;
        margin-top: 14px;
    }
    .hero-card .kpi-progress-bar {
        background: rgba(255,255,255,0.75);
        height: 100%;
        border-radius: 3px;
    }

    /* Hero color variants */
    .hero-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
        box-shadow: 0 8px 24px rgba(217,119,6,0.3), 0 2px 8px rgba(217,119,6,0.15);
    }
    .hero-indigo {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #4338ca 100%);
        box-shadow: 0 8px 24px rgba(79,70,229,0.3), 0 2px 8px rgba(79,70,229,0.15);
    }
    .hero-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
        box-shadow: 0 8px 24px rgba(5,150,105,0.3), 0 2px 8px rgba(5,150,105,0.15);
    }

    /* ==== STATUS CARDS — Colored fill with depth ==== */
    .status-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        padding: 16px;
        position: relative;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
    }
    .status-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }
    .status-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }
    .status-card .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        font-size: 0.95rem;
    }
    .status-card .kpi-value {
        font-size: 1.4rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .status-card .kpi-label { font-size: 0.75rem; }
    .status-card .kpi-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    /* Status color variants */
    .status-rose::before   { background: linear-gradient(90deg, #e11d48, #fb7185); }
    .status-rose .kpi-icon { background: linear-gradient(135deg, #e11d48, #f43f5e); color: #fff; box-shadow: 0 4px 12px rgba(225,29,72,0.25); }
    .status-rose .kpi-value { color: #be123c; }
    .status-rose .kpi-link  { color: #e11d48; }

    .status-blue::before   { background: linear-gradient(90deg, #2563eb, #60a5fa); }
    .status-blue .kpi-icon { background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; box-shadow: 0 4px 12px rgba(37,99,235,0.25); }
    .status-blue .kpi-value { color: #1d4ed8; }
    .status-blue .kpi-link  { color: #2563eb; }

    .status-amber::before  { background: linear-gradient(90deg, #d97706, #fbbf24); }
    .status-amber .kpi-icon { background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff; box-shadow: 0 4px 12px rgba(217,119,6,0.25); }
    .status-amber .kpi-value { color: #b45309; }
    .status-amber .kpi-link  { color: #d97706; }

    .status-emerald::before { background: linear-gradient(90deg, #059669, #34d399); }
    .status-emerald .kpi-icon { background: linear-gradient(135deg, #059669, #10b981); color: #fff; box-shadow: 0 4px 12px rgba(5,150,105,0.25); }
    .status-emerald .kpi-value { color: #047857; }
    .status-emerald .kpi-link  { color: #059669; }

    /* ==== PRODUCT STAT CARDS — Colored background fills ==== */
    .kpi-card {
        border-radius: var(--radius);
        padding: 16px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid transparent;
        box-shadow: var(--shadow-sm);
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }
    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        margin-bottom: 10px;
    }
    .kpi-value {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
        margin-bottom: 4px;
        letter-spacing: -0.02em;
    }
    .kpi-label { font-size: 0.75rem; margin-bottom: 8px; }
    .kpi-meta { font-size: 0.6875rem; }
    .kpi-label {
        font-size: 0.8125rem;
        color: var(--text-secondary);
        font-weight: 600;
        margin-bottom: 10px;
    }
    .kpi-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
        background: rgba(255,255,255,0.5);
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-flex;
    }
    .kpi-link {
        font-size: 0.8125rem;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 10px;
        color: var(--accent);
    }
    .kpi-link:hover { text-decoration: underline; }
    .kpi-progress {
        height: 5px;
        border-radius: 3px;
        background: rgba(0,0,0,0.06);
        margin-top: 14px;
        overflow: hidden;
    }
    .kpi-progress-bar {
        height: 100%;
        border-radius: 3px;
        background: var(--accent);
        transition: width 0.6s ease;
    }

    /* Product card color fills */
    .kpi-card.fill-orange {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        border-color: #fed7aa;
    }
    .kpi-card.fill-orange .kpi-icon { background: linear-gradient(135deg, #ea580c, #f97316); color: #fff; box-shadow: 0 4px 12px rgba(234,88,12,0.25); }
    .kpi-card.fill-orange .kpi-value { color: #9a3412; }
    .kpi-card.fill-orange:hover { box-shadow: 0 10px 25px rgba(234,88,12,0.15); }

    .kpi-card.fill-violet {
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        border-color: #ddd6fe;
    }
    .kpi-card.fill-violet .kpi-icon { background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: #fff; box-shadow: 0 4px 12px rgba(124,58,237,0.25); }
    .kpi-card.fill-violet .kpi-value { color: #5b21b6; }
    .kpi-card.fill-violet:hover { box-shadow: 0 10px 25px rgba(124,58,237,0.15); }

    .kpi-card.fill-rose {
        background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
        border-color: #fecdd3;
    }
    .kpi-card.fill-rose .kpi-icon { background: linear-gradient(135deg, #e11d48, #f43f5e); color: #fff; box-shadow: 0 4px 12px rgba(225,29,72,0.25); }
    .kpi-card.fill-rose .kpi-value { color: #9f1239; }
    .kpi-card.fill-rose:hover { box-shadow: 0 10px 25px rgba(225,29,72,0.15); }

    .kpi-card.fill-sky {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-color: #bae6fd;
    }
    .kpi-card.fill-sky .kpi-icon { background: linear-gradient(135deg, #0284c7, #0ea5e9); color: #fff; box-shadow: 0 4px 12px rgba(2,132,199,0.25); }
    .kpi-card.fill-sky .kpi-value { color: #075985; }
    .kpi-card.fill-sky:hover { box-shadow: 0 10px 25px rgba(2,132,199,0.15); }

    .kpi-card.fill-teal {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        border-color: #99f6e4;
    }
    .kpi-card.fill-teal .kpi-icon { background: linear-gradient(135deg, #0d9488, #14b8a6); color: #fff; box-shadow: 0 4px 12px rgba(13,148,136,0.25); }
    .kpi-card.fill-teal .kpi-value { color: #115e59; }
    .kpi-card.fill-teal:hover { box-shadow: 0 10px 25px rgba(13,148,136,0.15); }

    .kpi-card.fill-amber {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-color: #fde68a;
    }
    .kpi-card.fill-amber .kpi-icon { background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff; box-shadow: 0 4px 12px rgba(217,119,6,0.25); }
    .kpi-card.fill-amber .kpi-value { color: #92400e; }
    .kpi-card.fill-amber:hover { box-shadow: 0 10px 25px rgba(217,119,6,0.15); }

    .kpi-card.fill-indigo {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border-color: #c7d2fe;
    }
    .kpi-card.fill-indigo .kpi-icon { background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff; box-shadow: 0 4px 12px rgba(79,70,229,0.25); }
    .kpi-card.fill-indigo .kpi-value { color: #3730a3; }
    .kpi-card.fill-indigo:hover { box-shadow: 0 10px 25px rgba(79,70,229,0.15); }

    /* ---- Section Title ---- */
    .section-heading {
        font-size: 1.0625rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 18px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        letter-spacing: -0.01em;
    }
    .section-heading i { color: var(--accent); font-size: 1.25rem; }

    /* ---- Panel Card ---- */
    .panel {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }
    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .panel-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .panel-title i { color: var(--accent); }
    .panel-subtitle {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 400;
    }
    .panel-actions {
        display: flex;
        gap: 6px;
    }
    .panel-action-btn {
        background: var(--card-bg);
        border: 1px solid var(--border);
        color: var(--text-secondary);
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.15s ease;
    }
    .panel-action-btn:hover {
        background: var(--accent-light);
        color: var(--accent);
        border-color: var(--accent);
    }

    /* ---- Chart Controls ---- */
    .chart-type-pills {
        display: inline-flex;
        background: var(--border-light);
        border-radius: 8px;
        padding: 3px;
        gap: 2px;
    }
    .chart-pill {
        background: transparent;
        border: none;
        padding: 6px 14px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .chart-pill:hover { color: var(--text-primary); }
    .chart-pill.active {
        background: var(--accent);
        color: #fff;
        box-shadow: 0 2px 6px rgba(79,70,229,0.3);
    }

    /* ---- Chart Canvas ---- */
    .chart-wrap {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .chart-loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        z-index: 5;
    }
    .spinner {
        width: 28px;
        height: 28px;
        border: 3px solid var(--border);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ---- Insights List ---- */
    .insight-list { list-style: none; padding: 0; margin: 0; }
    .insight-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid var(--border-light);
    }
    .insight-list li:last-child { border-bottom: none; }
    .insight-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
    }
    .insight-text h6 {
        margin: 0 0 3px 0;
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .insight-text span {
        font-size: 0.75rem;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    /* ---- System Overview ---- */
    .sys-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid var(--border-light);
    }
    .sys-item:last-child { border-bottom: none; }
    .sys-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        flex-shrink: 0;
    }
    .sys-label { font-size: 0.8125rem; font-weight: 600; color: var(--text-primary); margin: 0; }
    .sys-value { font-size: 0.75rem; color: var(--text-secondary); margin: 0; }

    /* ---- Data Table ---- */
    .clean-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.8125rem;
    }
    .clean-table thead th {
        background: var(--border-light);
        color: var(--text-secondary);
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 12px 14px;
        border-bottom: 2px solid var(--border);
        text-align: left;
    }
    .clean-table thead th:first-child { border-radius: var(--radius-sm) 0 0 0; }
    .clean-table thead th:last-child  { border-radius: 0 var(--radius-sm) 0 0; }
    .clean-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
        vertical-align: middle;
    }
    .clean-table tbody tr:hover td { background: #f8f9fb; }
    .clean-table .badge-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.6875rem;
        font-weight: 700;
        line-height: 1.4;
    }
    .badge-success { background: var(--green-light); color: var(--green); }
    .badge-warning { background: var(--amber-light); color: var(--amber); }
    .badge-danger  { background: var(--red-light);   color: var(--red); }

    /* ---- Quick Actions ---- */
    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 14px;
    }
    .action-card {
        border-radius: var(--radius);
        padding: 18px 16px;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
        text-align: center;
        position: relative;
        overflow: hidden;
        border: 1px solid transparent;
        box-shadow: var(--shadow-sm);
    }
    .action-card:hover {
        text-decoration: none;
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }
    .action-card .action-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #fff;
    }
    .action-card span {
        font-size: 0.8125rem;
        font-weight: 700;
    }

    /* Action card color variants */
    .action-card.act-amber {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-color: #fde68a;
    }
    .action-card.act-amber .action-icon { background: linear-gradient(135deg, #d97706, #f59e0b); box-shadow: 0 4px 12px rgba(217,119,6,0.3); }
    .action-card.act-amber span { color: #92400e; }
    .action-card.act-amber:hover { box-shadow: 0 10px 25px rgba(217,119,6,0.15); }

    .action-card.act-blue {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #bfdbfe;
    }
    .action-card.act-blue .action-icon { background: linear-gradient(135deg, #2563eb, #3b82f6); box-shadow: 0 4px 12px rgba(37,99,235,0.3); }
    .action-card.act-blue span { color: #1e40af; }
    .action-card.act-blue:hover { box-shadow: 0 10px 25px rgba(37,99,235,0.15); }

    .action-card.act-green {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-color: #a7f3d0;
    }
    .action-card.act-green .action-icon { background: linear-gradient(135deg, #059669, #10b981); box-shadow: 0 4px 12px rgba(5,150,105,0.3); }
    .action-card.act-green span { color: #065f46; }
    .action-card.act-green:hover { box-shadow: 0 10px 25px rgba(5,150,105,0.15); }

    /* ---- Data Table Toggle Container ---- */
    .data-table-wrap {
        margin-top: 20px;
        display: none;
    }
    .data-table-wrap.show { display: block; }

    /* ---- Custom Legend ---- */
    #customLegend {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
        margin-top: 16px;
    }
    #customLegend .legend-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
        cursor: pointer;
        transition: all 0.15s ease;
    }
    #customLegend .legend-chip:hover {
        border-color: var(--accent);
        background: var(--accent-light);
    }
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    #serviceDetails {
        text-align: center;
        margin-top: 10px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--accent);
        min-height: 20px;
    }

    /* ---- Loading state for KPI values ---- */
    .kpi-value .spinner {
        width: 20px;
        height: 20px;
    }

    /* ---- Responsive ---- */
    @media (max-width: 991px) {
        .saas-dashboard { padding: 18px; }
        .kpi-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; }
        .hero-card .kpi-value { font-size: 1.75rem; }
    }
    @media (max-width: 767px) {
        .saas-dashboard { padding: 14px; }
        .dash-header { flex-direction: column; }
        .dash-header-right { width: 100%; justify-content: space-between; }
        .kpi-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
        .hero-card { padding: 18px; }
        .hero-card .kpi-value { font-size: 1.5rem; }
        .kpi-card { padding: 16px; }
        .kpi-value { font-size: 1.375rem; }
        .panel { padding: 16px; }
        .chart-wrap { height: 240px; }
        .action-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
        .kpi-grid { grid-template-columns: 1fr; }
        .action-grid { grid-template-columns: 1fr; }
    }

    /* ---- Force white icons (override any global icon theme styles) ---- */
    .saas-dashboard .hero-card .kpi-icon,
    .saas-dashboard .status-card .kpi-icon,
    .saas-dashboard .kpi-card[class*="fill-"] .kpi-icon,
    .saas-dashboard .action-card .action-icon,
    .saas-dashboard .sys-icon {
        color: #fff !important;
    }
    .saas-dashboard .hero-card .kpi-icon i,
    .saas-dashboard .status-card .kpi-icon i,
    .saas-dashboard .kpi-card[class*="fill-"] .kpi-icon i,
    .saas-dashboard .action-card .action-icon i,
    .saas-dashboard .sys-icon i {
        color: #fff !important;
    }
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<div class="saas-dashboard">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="dash-alert success">
            <i class="ri-check-circle-line"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="dash-alert error">
            <i class="ri-error-warning-line"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Dashboard Header --}}
    <div class="dash-header">
        <div class="dash-header-left">
            <h1>Welcome back, {{ Auth::user()->name }}</h1>
            <p>Here's what's happening with your travel management system.</p>
        </div>
        <div class="dash-header-right">
            <div class="period-toggle">
                <button class="toggle-btn {{ $period == 'today' ? 'active' : '' }}" onclick="changeTimeFilter('today')">Today</button>
                <button class="toggle-btn {{ $period == 'month' ? 'active' : '' }}" onclick="changeTimeFilter('month')">This Month</button>
            </div>
            <div class="dash-date-badge">
                <i class="ri-calendar-line"></i>
                {{ date('M d, Y') }}
            </div>
        </div>
    </div>

    {{-- ===== PRIMARY KPI CARDS — Bold Hero Cards ===== --}}
    @if($userPermissions['canViewBusinessMetrics'] || $userPermissions['canViewEnquiries'])
    <div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
        @if($userPermissions['canViewEnquiries'])
        <div class="hero-card hero-amber">
            <div class="kpi-icon">
                <i class="ri-questionnaire-line"></i>
            </div>
            <div class="kpi-value" id="enquiry-count">{{ $counts['enquiries']['total'] ?? 0 }}</div>
            <div class="kpi-label">Total Enquiries</div>
            @php $eTotal = $counts['enquiries']['total'] ?? 0; $eProg = min(($eTotal / 500) * 100, 100); @endphp
            <div class="kpi-progress">
                <div class="kpi-progress-bar" id="enquiry-progress" style="width: {{ $eProg }}%;"></div>
            </div>
        </div>
        @endif

        @if($userPermissions['canViewBusinessMetrics'])
        <div class="hero-card hero-indigo">
            <div class="kpi-icon">
                <i class="ri-bookmark-3-line"></i>
            </div>
            <div class="kpi-value" id="booking-count">{{ $counts['bookings']['total'] ?? 0 }}</div>
            <div class="kpi-label">Total Bookings</div>
            @php $bTotal = $counts['bookings']['total'] ?? 0; $bProg = min(($bTotal / 500) * 100, 100); @endphp
            <div class="kpi-progress">
                <div class="kpi-progress-bar" id="booking-progress" style="width: {{ $bProg }}%;"></div>
            </div>
        </div>

        <div class="hero-card hero-emerald">
            <div class="kpi-icon">
                <i class="ri-route-line"></i>
            </div>
            <div class="kpi-value" id="tour-count">{{ $counts['tours']['total'] ?? 0 }}</div>
            <div class="kpi-label">Active Tours</div>
            @php $tTotal = $counts['tours']['total'] ?? 0; $tProg = min(($tTotal / 500) * 100, 100); @endphp
            <div class="kpi-progress">
                <div class="kpi-progress-bar" id="tour-progress" style="width: {{ $tProg }}%;"></div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ===== BOOKING STATUS CARDS ===== --}}
    @if($userPermissions['canViewBusinessMetrics'] && isset($counts['bookingStatus']))
    <h2 class="section-heading"><i class="ri-bookmark-3-line"></i> Booking Status &mdash; {{ ucfirst($period) }}</h2>
    <div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr);">
        @php $roleIds = [34, 124, 125, 36, 126, 127]; @endphp
        @if(!in_array(Auth::user()->role_id, $roleIds))
        <div class="status-card status-rose">
            <div class="kpi-icon"><i class="ri-questionnaire-line"></i></div>
            <div class="kpi-value" id="new-enquiries-count">{{ $counts['bookingStatus']['new_enquiries'] ?? 0 }}</div>
            <div class="kpi-label">New Enquiries</div>
            <a href="{{ route('bookings.new-enquiries') }}" class="kpi-link"><i class="ri-arrow-right-s-line"></i> View</a>
        </div>
        <div class="status-card status-blue">
            <div class="kpi-icon"><i class="ri-user-search-line"></i></div>
            <div class="kpi-value" id="prospect-count">{{ $counts['bookingStatus']['prospect'] ?? 0 }}</div>
            <div class="kpi-label">Prospect</div>
            <a href="{{ route('bookings.follow-ups') }}" class="kpi-link"><i class="ri-arrow-right-s-line"></i> View</a>
        </div>
        <div class="status-card status-amber">
            <div class="kpi-icon"><i class="ri-time-line"></i></div>
            <div class="kpi-value" id="tentative-count">{{ $counts['bookingStatus']['tentative'] ?? 0 }}</div>
            <div class="kpi-label">Tentative</div>
            <a href="{{ route('bookings.follow-ups') }}" class="kpi-link"><i class="ri-arrow-right-s-line"></i> View</a>
        </div>
        @endif
        <div class="status-card status-emerald">
            <div class="kpi-icon"><i class="ri-checkbox-circle-line"></i></div>
            <div class="kpi-value" id="confirmed-count">{{ $counts['bookingStatus']['confirmed'] ?? 0 }}</div>
            <div class="kpi-label">Confirmed Tours</div>
            <a href="{{ route('bookings.confirmed') }}" class="kpi-link"><i class="ri-arrow-right-s-line"></i> View</a>
        </div>
    </div>
    @endif

    {{-- ===== PRODUCT STATS ===== --}}
    @php
        $showServicesOverview =
            ($userPermissions['canViewHotels'] ?? false) ||
            ($userPermissions['canViewAttractions'] ?? false) ||
            ($userPermissions['canViewRestaurants'] ?? false) ||
            ($userPermissions['canViewGuides'] ?? false) ||
            ($userPermissions['canViewDrivers'] ?? false) ||
            ($userPermissions['canViewVehicles'] ?? false);
    @endphp
    @if($showServicesOverview)
    <h2 class="section-heading"><i class="ri-apps-2-line"></i> Services Overview</h2>
    <div class="kpi-grid">
        @if($userPermissions['canViewHotels'])
        <div class="kpi-card fill-orange">
            <div class="kpi-icon"><i class="ri-hotel-line"></i></div>
            <div class="kpi-value" id="hotel-count">{{ $counts['hotels']['total'] ?? 0 }}</div>
            <div class="kpi-label">Hotels</div>
            <div class="kpi-meta">Active: {{ $counts['hotels']['active'] ?? 0 }} &middot; Recent: {{ $counts['hotels']['recent'] ?? 0 }}</div>
        </div>
        @endif

        @if($userPermissions['canViewAttractions'])
        <div class="kpi-card fill-violet">
            <div class="kpi-icon"><i class="ri-landscape-line"></i></div>
            <div class="kpi-value">{{ $counts['attractions']['total'] ?? 0 }}</div>
            <div class="kpi-label">Attractions</div>
            <div class="kpi-meta">Active: {{ $counts['attractions']['active'] ?? 0 }} &middot; Recent: {{ $counts['attractions']['recent'] ?? 0 }}</div>
        </div>
        @endif

        @if($userPermissions['canViewRestaurants'])
        <div class="kpi-card fill-rose">
            <div class="kpi-icon"><i class="ri-restaurant-2-line"></i></div>
            <div class="kpi-value">{{ $counts['restaurants']['total'] ?? 0 }}</div>
            <div class="kpi-label">Restaurants</div>
            <div class="kpi-meta">Active: {{ $counts['restaurants']['active'] ?? 0 }} &middot; Recent: {{ $counts['restaurants']['recent'] ?? 0 }}</div>
        </div>
        @endif

        @if($userPermissions['canViewGuides'])
        <div class="kpi-card fill-sky">
            <div class="kpi-icon"><i class="ri-compass-3-line"></i></div>
            <div class="kpi-value">{{ $counts['guides']['total'] ?? 0 }}</div>
            <div class="kpi-label">Guides</div>
            <div class="kpi-meta">Active: {{ $counts['guides']['available'] ?? 0 }} &middot; Recent: {{ $counts['guides']['recent'] ?? 0 }}</div>
        </div>
        @endif

        @if($userPermissions['canViewDrivers'])
        <div class="kpi-card fill-teal">
            <div class="kpi-icon"><i class="ri-steering-2-line"></i></div>
            <div class="kpi-value">{{ $counts['drivers']['total'] ?? 0 }}</div>
            <div class="kpi-label">Drivers</div>
            <div class="kpi-meta">Active: {{ $counts['drivers']['available'] ?? 0 }} &middot; Recent: {{ $counts['drivers']['recent'] ?? 0 }}</div>
        </div>
        @endif

        @if($userPermissions['canViewVehicles'])
        <div class="kpi-card fill-amber">
            <div class="kpi-icon"><i class="ri-car-line"></i></div>
            <div class="kpi-value">{{ $counts['vehicles']['total'] ?? 0 }}</div>
            <div class="kpi-label">Vehicles</div>
            <div class="kpi-meta">Active: {{ $counts['vehicles']['available'] ?? 0 }} &middot; Recent: {{ $counts['vehicles']['recent'] ?? 0 }}</div>
        </div>
        @endif
    </div>
    @endif

    {{-- ===== AGENTS (separate row) ===== --}}
    @if($userPermissions['canViewAgents'])
    <h2 class="section-heading"><i class="ri-user-3-line"></i> Agents</h2>
    <div class="kpi-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); max-width: 280px;">
        <div class="kpi-card fill-indigo">
            <div class="kpi-icon"><i class="ri-user-line"></i></div>
            <div class="kpi-value">{{ $counts['agents']['total'] ?? 0 }}</div>
            <div class="kpi-label">Agents</div>
            <div class="kpi-meta">Active: {{ $counts['agents']['active'] ?? 0 }} &middot; Recent: {{ $counts['agents']['recent'] ?? 0 }}</div>
        </div>
    </div>
    @endif

    {{-- ===== CHART + INSIGHTS (2 column) ===== --}}
    @if($userPermissions['canViewBusinessMetrics'] || $userPermissions['canViewProductAnalytics'])
    <div class="row" style="margin-bottom: 24px;">

        {{-- LEFT: Chart --}}
        <div class="col-lg-8" style="margin-bottom: 24px;">
            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">
                            <i class="ri-bar-chart-2-line"></i>
                            @if($userPermissions['canViewProductAnalytics'] && !$userPermissions['canViewBusinessMetrics'])
                                Product Analytics
                            @else
                                Business Analytics
                            @endif
                        </h3>
                        <span class="panel-subtitle">{{ ucfirst($period) }} &middot; Interactive Dashboard</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div class="chart-type-pills">
                            <button class="chart-pill active" data-type="bar"><i class="ri-bar-chart-line"></i> Bar</button>
                            <button class="chart-pill" data-type="line"><i class="ri-line-chart-line"></i> Line</button>
                            <button class="chart-pill" data-type="doughnut"><i class="ri-donut-chart-line"></i> Donut</button>
                            <button class="chart-pill" data-type="radar"><i class="ri-radar-line"></i> Radar</button>
                        </div>
                        <div class="panel-actions">
                            <button class="panel-action-btn" id="toggleDataTable" title="Toggle Data Table"><i class="ri-table-line"></i></button>
                            <button class="panel-action-btn" id="exportChart" title="Export Chart"><i class="ri-download-line"></i></button>
                            <button class="panel-action-btn" id="refreshChart" title="Refresh Data"><i class="ri-refresh-line"></i></button>
                        </div>
                    </div>
                </div>

                <div class="chart-wrap">
                    <canvas id="businessAnalyticsChart"></canvas>
                    <div class="chart-loading-overlay" id="chartLoading" style="display:none;">
                        <div class="spinner"></div>
                    </div>
                </div>

                <div id="customLegend"></div>
                <div id="serviceDetails"></div>

                {{-- Data Table (toggleable) --}}
                <div class="data-table-wrap" id="dataTableContainer">
                    <table class="clean-table" id="analyticsDataTable">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Total</th>
                                <th>This Month</th>
                                <th>Ratio</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody"></tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Actions (fills free space under Business Analytics on left) --}}
            @php
                $excludedRoles = [38, 81, 84, 87, 90, 93, 96, 99, 102, 105, 108, 111, 114, 117, 120, 123];
                $showQuickActions =
                    (($userPermissions['canViewEnquiries'] ?? false) === true) ||
                    (!in_array(Auth::user()->role_id, $excludedRoles)) ||
                    (Auth::user()->role_id == 1);
            @endphp
            @if($showQuickActions)
            <div class="panel d-none d-lg-block" style="margin-bottom:0;">
                <div class="panel-header" style="margin-bottom:14px;">
                    <h3 class="panel-title"><i class="ri-flashlight-line"></i> Quick Actions</h3>
                </div>
                <div class="action-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom:0;">
                    @if($userPermissions['canViewEnquiries'])
                    <a href="{{ route('bookings.new-enquiries') }}" class="action-card act-amber">
                        <div class="action-icon"><i class="ri-questionnaire-line"></i></div>
                        <span>Manage Negotiation</span>
                    </a>
                    @endif

                    @if(!in_array(Auth::user()->role_id, $excludedRoles))
                    <a href="{{ route('users.index') }}" class="action-card act-blue">
                        <div class="action-icon"><i class="ri-group-line"></i></div>
                        <span>Manage Users</span>
                    </a>
                    @endif

                    @if(Auth::user()->role_id == 1)
                    <a href="{{ route('master-setting') }}" class="action-card act-green">
                        <div class="action-icon"><i class="ri-settings-3-line"></i></div>
                        <span>Settings</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT: Insights + System Overview --}}
        <div class="col-lg-4" style="margin-bottom: 24px;">

            {{-- Key Insights --}}
            <div class="panel" style="margin-bottom:16px;">
                <h3 class="panel-title" style="margin-bottom:16px;">
                    <i class="ri-lightbulb-line"></i> Key Insights
                </h3>
                <ul class="insight-list" id="chartInsights">
                    {{-- Populated by JS --}}
                </ul>
            </div>

            {{-- System Overview --}}
            <div class="panel" style="margin-bottom:0;">
                <h3 class="panel-title" style="margin-bottom:12px;">
                    <i class="ri-dashboard-3-line"></i> System Overview
                </h3>

                @if(isset($counts['orders']))
                <div class="sys-item">
                    <div class="sys-icon" style="background:linear-gradient(135deg, #4f46e5, #6366f1); color:#fff; box-shadow: 0 3px 8px rgba(79,70,229,0.25);">
                        <i class="ri-database-line"></i>
                    </div>
                    <div>
                        <p class="sys-label">Total Orders</p>
                        <p class="sys-value">{{ $counts['orders']['total'] ?? 0 }} total &middot; {{ $counts['orders']['recent'] ?? 0 }} recent</p>
                    </div>
                </div>
                @endif

                @if(isset($counts['facilities']))
                <div class="sys-item">
                    <div class="sys-icon" style="background:linear-gradient(135deg, #059669, #10b981); color:#fff; box-shadow: 0 3px 8px rgba(5,150,105,0.25);">
                        <i class="ri-function-line"></i>
                    </div>
                    <div>
                        <p class="sys-label">Facilities</p>
                        <p class="sys-value">{{ $counts['facilities']['total'] ?? 0 }} active</p>
                    </div>
                </div>
                @endif

                @if($userPermissions['canViewZones'] && isset($counts['zones']))
                <div class="sys-item">
                    <div class="sys-icon" style="background:linear-gradient(135deg, #d97706, #f59e0b); color:#fff; box-shadow: 0 3px 8px rgba(217,119,6,0.25);">
                        <i class="ri-map-pin-user-line"></i>
                    </div>
                    <div>
                        <p class="sys-label">Zones</p>
                        <p class="sys-value">{{ $counts['zones']['total'] ?? 0 }} operational</p>
                    </div>
                </div>
                @endif

                @if($userPermissions['canViewPorts'] && isset($counts['ports']))
                <div class="sys-item">
                    <div class="sys-icon" style="background:linear-gradient(135deg, #2563eb, #3b82f6); color:#fff; box-shadow: 0 3px 8px rgba(37,99,235,0.25);">
                        <i class="ri-ship-line"></i>
                    </div>
                    <div>
                        <p class="sys-label">Ports</p>
                        <p class="sys-value">{{ $counts['ports']['total'] ?? 0 }} available</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ===== QUICK ACTIONS (mobile only; on desktop it's in the right column) ===== --}}
    @php
        $excludedRoles = [38, 81, 84, 87, 90, 93, 96, 99, 102, 105, 108, 111, 114, 117, 120, 123];
        $showQuickActions =
            (($userPermissions['canViewEnquiries'] ?? false) === true) ||
            (!in_array(Auth::user()->role_id, $excludedRoles)) ||
            (Auth::user()->role_id == 1);
    @endphp
    @if($showQuickActions)
    <div class="d-lg-none">
        <h2 class="section-heading"><i class="ri-flashlight-line"></i> Quick Actions</h2>
        <div class="action-grid" style="margin-bottom:24px;">
        @if($userPermissions['canViewEnquiries'])
        <a href="{{ route('bookings.new-enquiries') }}" class="action-card act-amber">
            <div class="action-icon"><i class="ri-questionnaire-line"></i></div>
            <span>Manage Negotiation</span>
        </a>
        @endif

        @if(!in_array(Auth::user()->role_id, $excludedRoles))
        <a href="{{ route('users.index') }}" class="action-card act-blue">
            <div class="action-icon"><i class="ri-group-line"></i></div>
            <span>Manage Users</span>
        </a>
        @endif

        @if(Auth::user()->role_id == 1)
        <a href="{{ route('master-setting') }}" class="action-card act-green">
            <div class="action-icon"><i class="ri-settings-3-line"></i></div>
            <span>Settings</span>
        </a>
        @endif
        </div>
    </div>
    @endif

</div>{{-- /.saas-dashboard --}}


<script>
// ===== Enhanced Chart.js — Clean SaaS Style =====
let businessChart = null;
let currentChartType = 'bar';
let currentData = null;
let userPermissions = @json($userPermissions);

// Muted, professional color palette
const palette = {
    bg: [
        'rgba(79,70,229,0.65)',   // Indigo
        'rgba(5,150,105,0.65)',   // Green
        'rgba(217,119,6,0.65)',   // Amber
        'rgba(37,99,235,0.65)',   // Blue
        'rgba(220,38,38,0.65)',   // Red
        'rgba(124,58,237,0.65)',  // Violet
        'rgba(14,165,233,0.65)',  // Sky
        'rgba(234,88,12,0.65)',   // Orange
        'rgba(236,72,153,0.65)',  // Pink
        'rgba(20,184,166,0.65)',  // Teal
        'rgba(107,114,128,0.65)', // Gray
        'rgba(79,70,229,0.65)'
    ],
    border: [
        '#4f46e5','#059669','#d97706','#2563eb','#dc2626','#7c3aed',
        '#0ea5e9','#ea580c','#ec4899','#14b8a6','#6b7280','#4f46e5'
    ]
};

document.addEventListener('DOMContentLoaded', function() {
    initializeEnhancedChart();
    setupChartControls();
    updateDataTable();
    generateInsights();
});

function initializeEnhancedChart() {
    const ctx = document.getElementById('businessAnalyticsChart').getContext('2d');

    let chartLabels = [];
    let totalData = [];
    let monthData = [];

    if (userPermissions.canViewEnquiries && {!! json_encode(isset($counts['enquiries'])) !!}) {
        chartLabels.push('Enquiries');
        totalData.push({{ $counts['enquiries']['total'] ?? 0 }});
        monthData.push({{ $counts['enquiries']['new'] ?? 0 }});
    }
    if (userPermissions.canViewBusinessMetrics) {
        if ({!! json_encode(isset($counts['bookings'])) !!}) {
            chartLabels.push('Bookings');
            totalData.push({{ $counts['bookings']['total'] ?? 0 }});
            monthData.push({{ $counts['bookings']['confirmed'] ?? 0 }});
        }
        if ({!! json_encode(isset($counts['tours'])) !!}) {
            chartLabels.push('Tours');
            totalData.push({{ $counts['tours']['total'] ?? 0 }});
            monthData.push({{ $counts['tours']['active'] ?? 0 }});
        }
    }
    if (userPermissions.canViewHotels && {!! json_encode(isset($counts['hotels'])) !!}) {
        chartLabels.push('Hotels');
        totalData.push({{ $counts['hotels']['total'] ?? 0 }});
        monthData.push({{ $counts['hotels']['active'] ?? 0 }});
    }
    if (userPermissions.canViewRestaurants && {!! json_encode(isset($counts['restaurants'])) !!}) {
        chartLabels.push('Restaurants');
        totalData.push({{ $counts['restaurants']['total'] ?? 0 }});
        monthData.push({{ $counts['restaurants']['active'] ?? 0 }});
    }
    if (userPermissions.canViewGuides && {!! json_encode(isset($counts['guides'])) !!}) {
        chartLabels.push('Guides');
        totalData.push({{ $counts['guides']['total'] ?? 0 }});
        monthData.push({{ $counts['guides']['available'] ?? 0 }});
    }
    if (userPermissions.canViewDrivers && {!! json_encode(isset($counts['drivers'])) !!}) {
        chartLabels.push('Drivers');
        totalData.push({{ $counts['drivers']['total'] ?? 0 }});
        monthData.push({{ $counts['drivers']['available'] ?? 0 }});
    }
    if (userPermissions.canViewVehicles && {!! json_encode(isset($counts['vehicles'])) !!}) {
        chartLabels.push('Vehicles');
        totalData.push({{ $counts['vehicles']['total'] ?? 0 }});
        monthData.push({{ $counts['vehicles']['available'] ?? 0 }});
    }
    if (userPermissions.canViewAttractions && {!! json_encode(isset($counts['attractions'])) !!}) {
        chartLabels.push('Attractions');
        totalData.push({{ $counts['attractions']['total'] ?? 0 }});
        monthData.push({{ $counts['attractions']['active'] ?? 0 }});
    }
    if (userPermissions.canViewAgents && {!! json_encode(isset($counts['agents'])) !!}) {
        chartLabels.push('Agents');
        totalData.push({{ $counts['agents']['total'] ?? 0 }});
        monthData.push({{ $counts['agents']['active'] ?? 0 }});
    }
    if (userPermissions.canViewZones && {!! json_encode(isset($counts['zones'])) !!}) {
        chartLabels.push('Zones');
        totalData.push({{ $counts['zones']['total'] ?? 0 }});
        monthData.push({{ $counts['zones']['active'] ?? 0 }});
    }
    if (userPermissions.canViewPorts && {!! json_encode(isset($counts['ports'])) !!}) {
        chartLabels.push('Ports');
        totalData.push({{ $counts['ports']['total'] ?? 0 }});
        monthData.push({{ $counts['ports']['active'] ?? 0 }});
    }

    currentData = {
        labels: chartLabels,
        datasets: [{
            label: 'Total Count',
            data: totalData,
            backgroundColor: palette.bg,
            borderColor: palette.border,
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false,
            hoverBackgroundColor: palette.border,
            tension: 0.4
        }, {
            label: 'This Month',
            data: monthData,
            backgroundColor: palette.bg.map(c => c.replace('0.65', '0.25')),
            borderColor: palette.border.map(c => c + 'cc'),
            borderWidth: 1,
            borderRadius: 4,
            borderSkipped: false,
            tension: 0.4
        }]
    };

    businessChart = new Chart(ctx, {
        type: currentChartType,
        data: currentData,
        options: getChartOptions()
    });

    if (currentChartType === 'doughnut') {
        renderCustomLegend();
        document.getElementById('serviceDetails').innerHTML = '';
    } else {
        document.getElementById('customLegend').innerHTML = '';
        document.getElementById('serviceDetails').innerHTML = '';
    }
}

function getChartOptions() {
    const base = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            title: { display: false },
            legend: {
                display: true,
                position: 'top',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 16,
                    font: { size: 11, family: "'Inter', sans-serif", weight: '500' },
                    color: '#6b7280'
                }
            },
            tooltip: {
                backgroundColor: '#111827',
                titleColor: '#f9fafb',
                bodyColor: '#e5e7eb',
                borderColor: '#374151',
                borderWidth: 1,
                cornerRadius: 8,
                titleFont: { size: 12, weight: '600', family: "'Inter', sans-serif" },
                bodyFont: { size: 11, family: "'Inter', sans-serif" },
                padding: 10,
                displayColors: true,
                boxPadding: 4,
                callbacks: {
                    title: ctx => ctx[0].label,
                    label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString(),
                    afterLabel: ctx => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const pct = ((ctx.parsed.y / total) * 100).toFixed(1);
                        return '  ' + pct + '% of total';
                    }
                }
            }
        }
    };

    if (currentChartType === 'bar' || currentChartType === 'line') {
        base.scales = {
            y: {
                beginAtZero: true,
                min: 0,
                max: 200,
                ticks: {
                    stepSize: 20,
                    color: '#9ca3af',
                    font: { size: 10, family: "'Inter', sans-serif" },
                    callback: v => v.toLocaleString()
                },
                grid: { color: '#f3f4f6', drawBorder: false },
                border: { display: false },
                title: {
                    display: true,
                    text: 'Count',
                    color: '#9ca3af',
                    font: { size: 11, weight: '500' }
                }
            },
            x: {
                grid: { display: false },
                border: { display: false },
                ticks: {
                    color: '#9ca3af',
                    font: { size: 10, family: "'Inter', sans-serif" },
                    maxRotation: 45,
                    minRotation: 0
                }
            }
        };
    } else if (currentChartType === 'doughnut') {
        base.cutout = '65%';
        base.plugins.legend = { display: false };
        base.plugins.tooltip.callbacks = {
            label: ctx => {
                const v = ctx.parsed || 0;
                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                return ' ' + ctx.label + ': ' + v + ' (' + ((v / total) * 100).toFixed(1) + '%)';
            }
        };
    }

    return base;
}

// ---- Chart Controls ----
function setupChartControls() {
    document.querySelectorAll('.chart-pill').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-pill').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentChartType = this.dataset.type;
            updateChartType();
        });
    });
    document.getElementById('toggleDataTable').addEventListener('click', toggleDataTable);
    document.getElementById('exportChart').addEventListener('click', exportChart);
    document.getElementById('refreshChart').addEventListener('click', refreshChartData);
}

function updateChartType() {
    if (businessChart) businessChart.destroy();
    document.getElementById('chartLoading').style.display = 'flex';
    setTimeout(() => {
        const ctx = document.getElementById('businessAnalyticsChart').getContext('2d');
        businessChart = new Chart(ctx, {
            type: currentChartType,
            data: currentData,
            options: getChartOptions()
        });
        document.getElementById('chartLoading').style.display = 'none';
        generateInsights();
        if (currentChartType === 'doughnut') {
            renderCustomLegend();
        } else {
            document.getElementById('customLegend').innerHTML = '';
            document.getElementById('serviceDetails').innerHTML = '';
        }
    }, 400);
}

function updateDataTable() {
    const tbody = document.getElementById('dataTableBody');
    const labels = currentData.labels;
    const totals = currentData.datasets[0].data;
    const months = currentData.datasets[1].data;
    tbody.innerHTML = '';
    labels.forEach((label, i) => {
        const t = totals[i], m = months[i];
        const ratio = t > 0 ? ((m / t) * 100).toFixed(1) : 0;
        let status, cls;
        if (m > t * 0.5)      { status = 'Excellent'; cls = 'badge-success'; }
        else if (m > t * 0.3) { status = 'Good';      cls = 'badge-warning'; }
        else                  { status = 'Low';        cls = 'badge-danger'; }
        tbody.innerHTML += `<tr>
            <td><strong>${label}</strong></td>
            <td>${t.toLocaleString()}</td>
            <td>${m.toLocaleString()}</td>
            <td>${ratio}%</td>
            <td><span class="badge-status ${cls}">${status}</span></td>
        </tr>`;
    });
}

function generateInsights() {
    const container = document.getElementById('chartInsights');
    const totals = currentData.datasets[0].data;
    const months = currentData.datasets[1].data;
    if (!totals.length) { container.innerHTML = '<li style="color:var(--text-muted);font-size:.8125rem;">No data available.</li>'; return; }

    const maxT = Math.max(...totals);
    const maxTIdx = totals.indexOf(maxT);
    const maxM = Math.max(...months);
    const maxMIdx = months.indexOf(maxM);
    const totalSum = totals.reduce((a, b) => a + b, 0);
    const monthSum = months.reduce((a, b) => a + b, 0);
    const eff = totalSum > 0 ? ((monthSum / totalSum) * 100).toFixed(1) : 0;

    container.innerHTML = `
        <li>
            <span class="insight-dot" style="background:var(--accent);"></span>
            <div class="insight-text">
                <h6>Top Service</h6>
                <span>${currentData.labels[maxTIdx]} leads with ${maxT.toLocaleString()} records</span>
            </div>
        </li>
        <li>
            <span class="insight-dot" style="background:var(--green);"></span>
            <div class="insight-text">
                <h6>Most Active This Month</h6>
                <span>${currentData.labels[maxMIdx]} with ${maxM.toLocaleString()} entries</span>
            </div>
        </li>
        <li>
            <span class="insight-dot" style="background:var(--amber);"></span>
            <div class="insight-text">
                <h6>Overall Efficiency</h6>
                <span>${eff}% of total records created this month</span>
            </div>
        </li>`;
}

function toggleDataTable() {
    const wrap = document.getElementById('dataTableContainer');
    const btn = document.getElementById('toggleDataTable');
    wrap.classList.toggle('show');
    btn.innerHTML = wrap.classList.contains('show')
        ? '<i class="ri-eye-off-line"></i>'
        : '<i class="ri-table-line"></i>';
    if (wrap.classList.contains('show')) updateDataTable();
}

function exportChart() {
    const link = document.createElement('a');
    link.download = 'analytics-' + new Date().toISOString().slice(0, 10) + '.png';
    link.href = businessChart.toBase64Image('image/png', 1.0);
    link.click();
}

function refreshChartData() {
    const active = document.querySelector('.period-toggle .toggle-btn.active');
    if (active) {
        const p = active.textContent.toLowerCase().trim() === 'today' ? 'today' : 'month';
        changeTimeFilter(p);
    }
}

// ---- Doughnut Custom Legend ----
function renderCustomLegend() {
    const el = document.getElementById('customLegend');
    el.innerHTML = '';
    if (!businessChart) return;
    const labels = businessChart.data.labels;
    const data = businessChart.data.datasets[0].data;
    const colors = palette.border;
    labels.forEach((label, i) => {
        const chip = document.createElement('div');
        chip.className = 'legend-chip';
        chip.innerHTML = `<span class="legend-dot" style="background:${colors[i]};"></span>${label} <span style="color:var(--text-muted);">(${data[i]})</span>`;
        chip.onclick = () => highlightSegment(i);
        el.appendChild(chip);
    });
}

function highlightSegment(index) {
    if (!businessChart) return;
    businessChart.data.datasets[0].backgroundColor = palette.bg.map((c, i) =>
        i === index ? c : c.replace('0.65', '0.15')
    );
    businessChart.update();
    const label = businessChart.data.labels[index];
    const value = businessChart.data.datasets[0].data[index];
    const total = businessChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
    const pct = total ? ((value / total) * 100).toFixed(1) : 0;
    document.getElementById('serviceDetails').innerHTML =
        `<strong>${label}:</strong> ${value.toLocaleString()} (${pct}%)`;
}

// ===== Time Filter (AJAX) =====
function changeTimeFilter(period) {
    showLoadingState();

    document.querySelectorAll('.period-toggle .toggle-btn').forEach(b => b.classList.remove('active'));
    const target = [...document.querySelectorAll('.period-toggle .toggle-btn')].find(b =>
        (period === 'today' && b.textContent.toLowerCase().includes('today')) ||
        (period === 'month' && b.textContent.toLowerCase().includes('month'))
    );
    if (target) target.classList.add('active');

    fetch(`{{ route('dashboard.counts') }}?period=${period}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCounts(data.counts);
            updateChart(data.counts, data.userPermissions);
            updateChartTitle(data.period);
        }
    })
    .catch(e => console.error('Error:', e))
    .finally(() => hideLoadingState());
}

function showLoadingState() {
    document.querySelectorAll('.kpi-value').forEach(el => {
        if (!el.querySelector('.spinner')) {
            el.dataset.originalValue = el.textContent;
            // Use white spinner on hero cards
            const isHero = el.closest('.hero-card');
            el.innerHTML = '<div class="spinner"' + (isHero ? ' style="border-color:rgba(255,255,255,0.3);border-top-color:#fff;"' : '') + '></div>';
        }
    });
}

function hideLoadingState() {
    document.querySelectorAll('.kpi-value .spinner').forEach(s => s.remove());
}

function updateCounts(counts) {
    if (counts.enquiries)     updateElementCount('enquiry-count', counts.enquiries.total || 0);
    if (counts.bookings)      updateElementCount('booking-count', counts.bookings.total || 0);
    if (counts.tours)         updateElementCount('tour-count', counts.tours.total || 0);
    if (counts.hotels)        updateElementCount('hotel-count', counts.hotels.total || 0);
    if (counts.bookingStatus) {
        updateElementCount('new-enquiries-count', counts.bookingStatus.new_enquiries || 0);
        updateElementCount('prospect-count', counts.bookingStatus.prospect || 0);
        updateElementCount('tentative-count', counts.bookingStatus.tentative || 0);
        updateElementCount('confirmed-count', counts.bookingStatus.confirmed || 0);
    }
    updateAllStatsCards(counts);
}

function updateElementCount(id, count) {
    const el = document.getElementById(id);
    if (el) animateCount(el, parseInt(el.textContent) || 0, count);
}

function updateAllStatsCards(counts) {
    const map = {
        'Attractions': counts.attractions ? counts.attractions.total : 0,
        'Restaurants': counts.restaurants ? counts.restaurants.total : 0,
        'Guides': counts.guides ? counts.guides.total : 0,
        'Drivers': counts.drivers ? counts.drivers.total : 0,
        'Vehicles': counts.vehicles ? counts.vehicles.total : 0,
        'Agents': counts.agents ? counts.agents.total : 0,
    };
    Object.entries(map).forEach(([label, value]) => {
        const labelEl = [...document.querySelectorAll('.kpi-label')].find(e => e.textContent.trim() === label);
        if (labelEl) {
            const card = labelEl.closest('.kpi-card, .hero-card, .status-card');
            if (card) {
                const numEl = card.querySelector('.kpi-value');
                if (numEl) animateCount(numEl, parseInt(numEl.textContent) || 0, value);
            }
        }
    });
    updateProgressBars(counts);
    updateStatsDetails(counts);
}

function updateProgressBars(counts) {
    if (counts.enquiries) {
        const bar = document.getElementById('enquiry-progress');
        if (bar) animateProgressBar(bar, Math.min((counts.enquiries.total || 0) / 500 * 100, 100));
    }
    if (counts.bookings) {
        const bar = document.getElementById('booking-progress');
        if (bar) animateProgressBar(bar, Math.min((counts.bookings.total || 0) / 500 * 100, 100));
    }
    if (counts.tours) {
        const bar = document.getElementById('tour-progress');
        if (bar) animateProgressBar(bar, Math.min((counts.tours.total || 0) / 500 * 100, 100));
    }
}

function animateProgressBar(el, target) {
    const start = parseFloat(el.style.width) || 0;
    const t0 = performance.now();
    (function step(now) {
        const p = Math.min((now - t0) / 800, 1);
        el.style.width = (start + (target - start) * (1 - Math.pow(1 - p, 4))) + '%';
        if (p < 1) requestAnimationFrame(step);
    })(t0);
}

function updateStatsDetails(counts) {
    const details = {
        'Hotels': counts.hotels ? `Active: ${counts.hotels.active||0} \u00b7 Recent: ${counts.hotels.recent||0}` : null,
        'Attractions': counts.attractions ? `Active: ${counts.attractions.active||0} \u00b7 Recent: ${counts.attractions.recent||0}` : null,
        'Restaurants': counts.restaurants ? `Active: ${counts.restaurants.active||0} \u00b7 Recent: ${counts.restaurants.recent||0}` : null,
        'Guides': counts.guides ? `Active: ${counts.guides.available||0} \u00b7 Recent: ${counts.guides.recent||0}` : null,
        'Drivers': counts.drivers ? `Active: ${counts.drivers.available||0} \u00b7 Recent: ${counts.drivers.recent||0}` : null,
        'Vehicles': counts.vehicles ? `Active: ${counts.vehicles.available||0} \u00b7 Recent: ${counts.vehicles.recent||0}` : null,
        'Agents': counts.agents ? `Active: ${counts.agents.active||0} \u00b7 Recent: ${counts.agents.recent||0}` : null,
    };
    Object.entries(details).forEach(([label, text]) => {
        if (!text) return;
        const labelEl = [...document.querySelectorAll('.kpi-label')].find(e => e.textContent.trim() === label);
        if (labelEl) {
            const card = labelEl.closest('.kpi-card, .hero-card, .status-card');
            if (card) {
                const meta = card.querySelector('.kpi-meta');
                if (meta) meta.textContent = text;
            }
        }
    });
}

function animateCount(el, start, end) {
    const t0 = performance.now();
    (function step(now) {
        const p = Math.min((now - t0) / 800, 1);
        el.textContent = Math.round(start + (end - start) * (1 - Math.pow(1 - p, 4))).toLocaleString();
        if (p < 1) requestAnimationFrame(step);
    })(t0);
}

function updateChart(counts, permissions) {
    if (!businessChart) return;
    document.getElementById('chartLoading').style.display = 'flex';
    userPermissions = permissions;

    let totalData = [], monthData = [], chartLabels = [];

    if (userPermissions.canViewEnquiries && counts.enquiries) {
        chartLabels.push('Enquiries'); totalData.push(counts.enquiries.total); monthData.push(counts.enquiries.new || 0);
    }
    if (userPermissions.canViewBusinessMetrics) {
        if (counts.bookings) { chartLabels.push('Bookings'); totalData.push(counts.bookings.total); monthData.push(counts.bookings.confirmed || 0); }
        if (counts.tours)    { chartLabels.push('Tours');    totalData.push(counts.tours.total);    monthData.push(counts.tours.active); }
    }
    if (userPermissions.canViewHotels && counts.hotels)           { chartLabels.push('Hotels');      totalData.push(counts.hotels.total);      monthData.push(counts.hotels.active); }
    if (userPermissions.canViewRestaurants && counts.restaurants)  { chartLabels.push('Restaurants'); totalData.push(counts.restaurants.total); monthData.push(counts.restaurants.active); }
    if (userPermissions.canViewGuides && counts.guides)            { chartLabels.push('Guides');      totalData.push(counts.guides.total);      monthData.push(counts.guides.available); }
    if (userPermissions.canViewDrivers && counts.drivers)          { chartLabels.push('Drivers');     totalData.push(counts.drivers.total);     monthData.push(counts.drivers.available); }
    if (userPermissions.canViewVehicles && counts.vehicles)        { chartLabels.push('Vehicles');    totalData.push(counts.vehicles.total);    monthData.push(counts.vehicles.available); }
    if (userPermissions.canViewAttractions && counts.attractions)  { chartLabels.push('Attractions'); totalData.push(counts.attractions.total); monthData.push(counts.attractions.active); }
    if (userPermissions.canViewAgents && counts.agents)            { chartLabels.push('Agents');      totalData.push(counts.agents.total);      monthData.push(counts.agents.active); }
    if (userPermissions.canViewZones && counts.zones)              { chartLabels.push('Zones');       totalData.push(counts.zones.total);       monthData.push(counts.zones.active); }
    if (userPermissions.canViewPorts && counts.ports)              { chartLabels.push('Ports');       totalData.push(counts.ports.total);       monthData.push(counts.ports.active); }

    currentData.labels = chartLabels;
    currentData.datasets[0].data = totalData;
    currentData.datasets[1].data = monthData;
    businessChart.update('active');

    setTimeout(() => {
        document.getElementById('chartLoading').style.display = 'none';
        updateDataTable();
        generateInsights();
        if (currentChartType === 'doughnut') renderCustomLegend();
    }, 600);
}

function updateChartTitle(period) {
    const titleEl = document.querySelector('.panel-title i.ri-bar-chart-2-line');
    if (titleEl) {
        const parent = titleEl.parentElement;
        const dp = period === 'today' ? 'Today' : 'This Month';
        parent.innerHTML = `<i class="ri-bar-chart-2-line"></i> Business Analytics`;
        const sub = parent.closest('.panel').querySelector('.panel-subtitle');
        if (sub) sub.textContent = dp + ' \u00b7 Interactive Dashboard';
    }
}

// Auto-refresh every 5 minutes
setInterval(() => {
    const active = document.querySelector('.period-toggle .toggle-btn.active');
    if (active) {
        let p = active.textContent.toLowerCase().trim();
        if (p === 'this month') p = 'month';
        changeTimeFilter(p);
    }
}, 300000);
</script>

@endsection
