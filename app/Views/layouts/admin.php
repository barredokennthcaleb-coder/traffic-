<?php $layoutRole = (string) session()->get('role'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Traffic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --transition-speed: 0.3s;
            --brand-primary: #5865f2;
            --brand-secondary: #7f56d9;
            --brand-gradient: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            --surface-bg: #f3f6ff;
            --surface-muted: #eef2ff;
            --surface-card: #ffffff;
            --text-strong: #1f2440;
            --text-body: #2f3555;
            --text-muted: #67708f;
            --border-soft: #dbe3ff;
            --ease-premium: cubic-bezier(0.22, 1, 0.36, 1);
        }

        :root[data-theme="dark"] {
            --surface-bg: #0f172a;
            --surface-muted: #1e293b;
            --surface-card: #111b2f;
            --text-strong: #e5edff;
            --text-body: #c2d0f3;
            --text-muted: #9fb0d9;
            --border-soft: #2e3a59;
        }

        :root[data-theme="light"] {
            --surface-bg: #f3f6ff;
            --surface-muted: #eef2ff;
            --surface-card: #ffffff;
            --text-strong: #1f2440;
            --text-body: #2f3555;
            --text-muted: #67708f;
            --border-soft: #dbe3ff;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --surface-bg: #0f172a;
                --surface-muted: #1e293b;
                --surface-card: #111b2f;
                --text-strong: #e5edff;
                --text-body: #c2d0f3;
                --text-muted: #9fb0d9;
                --border-soft: #2e3a59;
            }
        }

        body {
            background-color: var(--surface-bg);
            overflow-x: hidden;
            color: var(--text-body);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            position: relative;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            filter: blur(90px);
            z-index: -1;
            opacity: 0.42;
            pointer-events: none;
        }

        body::before {
            top: -110px;
            right: -80px;
            background: #6e7dff;
            animation: cinematicDriftA 18s ease-in-out infinite alternate;
        }

        body::after {
            bottom: -140px;
            left: -120px;
            background: #58d5ff;
            animation: cinematicDriftB 22s ease-in-out infinite alternate;
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 2.2rem;
            color: #98a7cf;
            display: block;
            margin-bottom: 0.5rem;
        }

        .empty-state-title {
            font-weight: 700;
            color: var(--text-strong);
            margin-bottom: 0.2rem;
        }

        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #1e2237 0%, #262c49 100%);
            color: white;
            transition: width var(--transition-speed) ease;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 22px 0 40px rgba(16, 22, 45, 0.32);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 30%);
            pointer-events: none;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            white-space: nowrap;
            overflow: hidden;
            transition: padding var(--transition-speed) ease;
        }

        .sidebar.collapsed a {
            padding: 12px 0;
            justify-content: center;
        }

        .sidebar a i {
            font-size: 1.25rem;
            min-width: 40px;
            text-align: center;
            transition: transform 0.25s var(--ease-premium), filter 0.25s var(--ease-premium);
        }

        .sidebar .nav-text {
            transition: opacity var(--transition-speed) ease, visibility var(--transition-speed) ease;
            opacity: 1;
            visibility: visible;
        }

        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .sidebar-header-text {
            opacity: 0;
            visibility: hidden;
            width: 0;
            display: none;
        }

        .sidebar a:hover, .sidebar a.active {
            color: white;
            background: rgba(255, 255, 255, 0.12);
        }
        
        .sidebar .nav-link {
            border-radius: 12px;
            margin: 3px 12px;
            position: relative;
            backdrop-filter: blur(2px);
            transition: transform 0.25s var(--ease-premium), background-color 0.25s var(--ease-premium);
        }

        .sidebar .nav-link:hover {
            transform: translateX(3px);
        }

        .sidebar .nav-link:hover i {
            transform: translateY(-2px) scale(1.14);
            filter: drop-shadow(0 8px 12px rgba(96, 129, 255, 0.42));
        }

        .sidebar .nav-link.active i {
            transform: scale(1.1);
            filter: drop-shadow(0 8px 14px rgba(118, 142, 255, 0.5));
        }

        .sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 10px;
            width: 4px;
            height: calc(100% - 20px);
            border-radius: 999px;
            background: #fff;
            opacity: 0.95;
        }

        .sidebar-toggle {
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: var(--brand-gradient);
            border: 3px solid white;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            transition: all var(--transition-speed) ease;
            box-shadow: 0 12px 24px rgba(69, 81, 184, 0.45);
            font-size: 1.2rem;
        }

        .sidebar-toggle:hover {
            filter: brightness(0.95);
            transform: translateY(-50%) scale(1.15);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }

        .sidebar.collapsed .sidebar-toggle {
            transform: translateY(-50%) rotate(180deg);
        }

        .sidebar.collapsed .sidebar-toggle:hover {
            transform: translateY(-50%) rotate(180deg) scale(1.15);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed) ease;
            width: calc(100% - var(--sidebar-width));
            padding: 20px;
            min-height: 100vh;
        }

        .sidebar.collapsed + .main-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 16px 42px rgba(38, 54, 116, 0.15);
            background: linear-gradient(160deg, rgba(255,255,255,0.92) 0%, rgba(248,251,255,0.84) 100%);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.65);
            transition: transform 0.28s var(--ease-premium), box-shadow 0.28s var(--ease-premium), border-color 0.28s var(--ease-premium);
        }

        .analytics-tilt {
            transform-style: preserve-3d;
            will-change: transform;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 46px rgba(42, 56, 119, 0.2);
        }

        .card-header {
            border-bottom: 1px solid #edf1ff;
            background-color: transparent;
        }

        .btn-primary {
            border: none;
            background: var(--brand-gradient);
        }

        .btn-primary:hover {
            filter: brightness(0.96);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.2);
        }

        .main-content .h2 {
            color: var(--text-strong);
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .premium-kpi {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            border: 0;
            color: #fff !important;
            box-shadow: 0 18px 38px rgba(46, 62, 135, 0.34);
            animation: kpiFloat 6.6s ease-in-out infinite;
            animation-delay: var(--float-delay, 0s);
        }

        .premium-kpi::after {
            content: '';
            position: absolute;
            inset: auto -35% -40% auto;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            pointer-events: none;
        }

        .premium-kpi::before {
            content: '';
            position: absolute;
            top: -120%;
            left: -25%;
            width: 35%;
            height: 300%;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.32) 50%, rgba(255,255,255,0) 100%);
            transform: rotate(20deg);
            animation: premiumShine 5.1s var(--ease-premium) infinite;
            animation-delay: var(--shine-delay, 0s);
        }

        .premium-kpi.bg-primary {
            background: linear-gradient(135deg, #2e6fff 0%, #4e9bff 48%, #6f8cff 100%) !important;
        }

        .premium-kpi.bg-success {
            background: linear-gradient(135deg, #0fa36f 0%, #16b57d 45%, #4fd1a0 100%) !important;
        }

        .premium-kpi.bg-info {
            background: linear-gradient(135deg, #00a7cc 0%, #23b4db 50%, #4fd2f5 100%) !important;
        }

        .premium-kpi.bg-warning {
            background: linear-gradient(135deg, #e7ae00 0%, #f2bf27 48%, #ffd65f 100%) !important;
            color: #1d2238 !important;
        }

        .table thead th {
            border-bottom: 1px solid #e8ecff;
            color: #4b5474;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .table tbody tr {
            border-color: #eff3ff;
            transition: background-color 0.15s ease;
        }

        .table tbody tr:hover {
            background-color: #f8faff;
        }

        .premium-reveal {
            opacity: 0;
            transform: translateY(14px) scale(0.99);
            animation: premiumReveal 0.7s var(--ease-premium) forwards;
            animation-delay: var(--reveal-delay, 0s);
        }

        .premium-pulse {
            animation: premiumPulse 3s ease-in-out infinite;
        }

        @keyframes premiumReveal {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes premiumShine {
            0%, 68% {
                transform: translateX(0) rotate(20deg);
                opacity: 0;
            }
            72% {
                opacity: 1;
            }
            82%, 100% {
                transform: translateX(430%) rotate(20deg);
                opacity: 0;
            }
        }

        @keyframes premiumPulse {
            0%, 100% { box-shadow: 0 18px 38px rgba(46, 62, 135, 0.3); }
            50% { box-shadow: 0 24px 54px rgba(46, 62, 135, 0.48); }
        }

        @keyframes cinematicDriftA {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(-26px, 18px, 0) scale(1.08); }
            100% { transform: translate3d(16px, -22px, 0) scale(1.04); }
        }

        @keyframes cinematicDriftB {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(22px, -18px, 0) scale(1.07); }
            100% { transform: translate3d(-18px, 22px, 0) scale(1.03); }
        }

        @keyframes kpiFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        .table td, .table th {
            vertical-align: middle;
            background-color: transparent;
        }

        .table-responsive {
            border-radius: 12px;
        }

        .badge {
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .desktop-header {
            background: linear-gradient(120deg, rgba(255,255,255,0.82) 0%, rgba(244,248,255,0.72) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.72);
            border-radius: 16px;
            padding: 0.9rem 1rem !important;
            box-shadow: 0 14px 30px rgba(83, 99, 169, 0.14);
        }

        .desktop-header .text-muted {
            color: var(--text-muted) !important;
        }

        .theme-select {
            min-width: 124px;
            border-radius: 10px;
            border: 1px solid #d6def8;
            background-color: rgba(255, 255, 255, 0.82);
            color: #344066;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .modal-content {
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            box-shadow: 0 26px 72px rgba(25, 39, 86, 0.26);
            overflow: hidden;
        }

        .modal-header {
            border-bottom: 1px solid #e8ecff;
        }

        .modal-footer {
            border-top: 1px solid #edf1ff;
            background: #f8faff;
        }

        .table-light,
        .table > :not(caption) > * > * {
            --bs-table-bg: transparent;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: radial-gradient(circle at 10% 0%, #1a2847 0%, #0f172a 35%, #0b1224 100%);
            }

            .sidebar {
                background: linear-gradient(180deg, #0b1326 0%, #111c35 100%);
                border-right: 1px solid #1f2c49;
            }

            .sidebar a {
                color: #a9b8e5;
            }

            .sidebar a:hover,
            .sidebar a.active {
                color: #eff4ff;
                background: rgba(120, 142, 210, 0.2);
            }

            .mobile-topbar,
            .desktop-header {
                background: rgba(17, 27, 47, 0.88);
                border-color: #2a3758 !important;
            }

            .theme-select {
                border-color: #30436d;
                background-color: rgba(18, 30, 54, 0.88);
                color: #dce6ff;
            }

            .card {
                border: 1px solid #243453;
                box-shadow: 0 20px 52px rgba(2, 8, 24, 0.55);
                background: linear-gradient(160deg, rgba(17,27,47,0.9) 0%, rgba(15,24,43,0.82) 100%);
            }

            .card-header {
                border-bottom-color: #283759;
            }

            .btn-outline-primary {
                color: #b3c4ff;
                border-color: #5872c6;
            }

            .btn-outline-primary:hover {
                background: #2e3e75;
                border-color: #6f86db;
                color: #f4f7ff;
            }

            .form-control,
            .form-select,
            .input-group-text {
                background-color: #121d35;
                border-color: #2a3b60;
                color: #d8e3ff;
            }

            .form-control::placeholder {
                color: #8ea0cf;
            }

            .table thead th {
                color: #aebce0;
                border-bottom-color: #2a3b60;
            }

            .table tbody tr {
                border-color: #223354;
            }

            .table tbody tr:hover {
                background-color: #16233f;
            }

            .badge.bg-dark-subtle {
                background-color: #1f2c4a !important;
                color: #d4e0ff !important;
                border-color: #2e4268 !important;
            }

            .modal-content {
                background: #0f1b34;
                border-color: #2a3b60;
                box-shadow: 0 26px 70px rgba(0, 0, 0, 0.55);
            }

            .modal-header,
            .modal-footer {
                border-color: #2a3b60;
            }

            .modal-footer {
                background: #0d162b;
            }

            .alert {
                color: #dce7ff;
            }

            .alert-success {
                background-color: #153b2f;
            }

            .alert-danger {
                background-color: #4a212a;
            }

            .alert-warning {
                background-color: #4a3b1c;
                color: #ffe8ad;
            }

            .table-premium-mobile tr {
                background: #121d35;
                border-color: #2a3b60;
                box-shadow: 0 10px 22px rgba(2, 8, 24, 0.35);
            }

            .table-premium-mobile td::before {
                color: #96aad8;
            }

            body::before {
                background: #4d68c9;
                opacity: 0.3;
            }

            body::after {
                background: #2c8ca9;
                opacity: 0.24;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .premium-reveal,
            .premium-kpi::before,
            .premium-pulse,
            .premium-kpi,
            body::before,
            body::after {
                animation: none !important;
            }

            .card,
            .sidebar .nav-link {
                transition: none !important;
            }
        }

        .alert {
            border: none;
            border-radius: 12px;
        }

        body.role-enforcer .card-header,
        body.role-enforcer .table-light {
            background: var(--surface-muted) !important;
        }

        body.role-enforcer .border-bottom {
            border-color: #dbe2ff !important;
        }

        .mobile-topbar {
            display: none;
            position: sticky;
            top: 0;
            z-index: 990;
            background: rgba(245, 247, 255, 0.95);
            backdrop-filter: blur(6px);
            border-bottom: 1px solid #dbe2ff;
            padding: 10px 12px;
        }

        .mobile-sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(21, 27, 52, 0.45);
            z-index: 998;
        }

        body.sidebar-open .mobile-sidebar-backdrop {
            display: block;
        }

        .page-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-strong);
            margin: 0;
        }

        @media (max-width: 768px) {
            body::before,
            body::after {
                display: none;
            }

            .sidebar {
                left: calc(-1 * var(--sidebar-width));
                width: min(var(--sidebar-width), 86vw);
                box-shadow: 0 10px 35px rgba(0,0,0,0.28);
                transition: left var(--transition-speed) ease;
            }
            .sidebar.active {
                left: 0;
            }
            .sidebar-toggle {
                display: none;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 12px;
            }

            .container-fluid {
                padding-left: 0.35rem;
                padding-right: 0.35rem;
            }

            .card {
                border-radius: 14px;
            }

            .card-header,
            .card-body,
            .card-footer {
                padding-left: 0.9rem !important;
                padding-right: 0.9rem !important;
            }

            .btn-group {
                flex-wrap: wrap;
                gap: 0.3rem;
            }

            .modal-dialog {
                margin: 0.75rem;
            }

            .table {
                min-width: 640px;
                font-size: 0.9rem;
            }

            .table-premium-mobile {
                min-width: 0;
            }

            .form-control,
            .form-select,
            .input-group-text {
                font-size: 0.95rem;
            }

            .mobile-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin: -12px -12px 10px -12px;
            }
            .desktop-header {
                display: none !important;
            }
            .main-content .h2 {
                font-size: 1.15rem;
            }
            .btn-toolbar {
                margin-left: auto;
            }

            .table-premium-mobile {
                border: 0;
            }

            .table-premium-mobile thead {
                display: none;
            }

            .table-premium-mobile tbody,
            .table-premium-mobile tr,
            .table-premium-mobile td {
                display: block;
                width: 100%;
            }

            .table-premium-mobile tr {
                background: #fff;
                border: 1px solid #e5ebff;
                border-radius: 12px;
                margin-bottom: 10px;
                padding: 6px 0;
                box-shadow: 0 6px 18px rgba(42, 56, 119, 0.06);
            }

            .table-premium-mobile td {
                border: 0 !important;
                padding: 8px 12px !important;
                text-align: left !important;
            }

            .table-premium-mobile td::before {
                content: attr(data-label);
                display: block;
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #6a7396;
                margin-bottom: 2px;
            }

            .table-premium-mobile td[data-label="Actions"]::before {
                margin-bottom: 8px;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .main-content {
                padding: 16px;
            }
            .main-content .h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body class="role-<?= esc($layoutRole) ?>">

<div class="d-flex">
    <?= $this->include('theme/sidebar') ?>
    <div class="mobile-sidebar-backdrop"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="mobile-topbar">
            <button type="button" class="btn btn-outline-primary btn-sm" id="mobileMenuBtn">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h2 class="page-title"><?= $this->renderSection('title') ?></h2>
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm theme-select" id="themeSelectMobile" aria-label="Theme mode">
                    <option value="auto">Auto</option>
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                </select>
                <span class="text-muted small text-truncate d-none d-sm-inline" style="max-width: 30vw;">Hi, <?= esc((string) session()->get('username')) ?></span>
            </div>
        </div>

        <div class="desktop-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?= $this->renderSection('title') ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0 d-flex align-items-center gap-2">
                <select class="form-select form-select-sm theme-select" id="themeSelectDesktop" aria-label="Theme mode">
                    <option value="auto">Auto</option>
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                </select>
                <span class="text-muted">Welcome, <?= session()->get('username') ?></span>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const THEME_KEY = 'trafficThemePreference';
        const root = document.documentElement;
        const themeSelectDesktop = document.getElementById('themeSelectDesktop');
        const themeSelectMobile = document.getElementById('themeSelectMobile');
        const darkPreference = window.matchMedia('(prefers-color-scheme: dark)');

        const getSavedTheme = () => localStorage.getItem(THEME_KEY) || 'auto';

        const applyTheme = (theme, shouldPersist = false) => {
            const mode = (theme === 'light' || theme === 'dark') ? theme : 'auto';
            if (mode === 'auto') {
                root.removeAttribute('data-theme');
            } else {
                root.setAttribute('data-theme', mode);
            }
            if (themeSelectDesktop) themeSelectDesktop.value = mode;
            if (themeSelectMobile) themeSelectMobile.value = mode;
            if (shouldPersist) {
                localStorage.setItem(THEME_KEY, mode);
            }
            window.dispatchEvent(new CustomEvent('themechange', { detail: { mode } }));
        };

        applyTheme(getSavedTheme());

        themeSelectDesktop?.addEventListener('change', function() {
            applyTheme(this.value, true);
        });

        themeSelectMobile?.addEventListener('change', function() {
            applyTheme(this.value, true);
        });

        darkPreference.addEventListener('change', function() {
            if (getSavedTheme() === 'auto') {
                applyTheme('auto');
            }
        });

        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.sidebar-toggle');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const backdrop = document.querySelector('.mobile-sidebar-backdrop');
        const isMobile = () => window.matchMedia('(max-width: 768px)').matches;
        
        if (toggle) {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                
                // Optional: Save state to localStorage
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
        }

        // Restore state
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true' && !isMobile()) {
            sidebar.classList.add('collapsed');
        }

        const closeMobileSidebar = () => {
            sidebar.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        };

        const openMobileSidebar = () => {
            sidebar.classList.add('active');
            document.body.classList.add('sidebar-open');
        };

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                if (sidebar.classList.contains('active')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            });
        }

        backdrop?.addEventListener('click', closeMobileSidebar);

        window.addEventListener('resize', function() {
            if (!isMobile()) {
                closeMobileSidebar();
            }
        });

        document.querySelectorAll('.main-content table').forEach(function(table) {
            if (!table.closest('.table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });

        document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (isMobile()) {
                    closeMobileSidebar();
                }
            });
        });
    });
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
