<?php $layoutRole = (string) session()->get('role'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Traffic System</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/pic-1.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --transition-speed: 0.3s;
            /* Theme matching Original Navy Blue Dashboard */
            --brand-primary: #3b82f6;
            --brand-secondary: #2563eb;
            --brand-accent: #8b5cf6;
            --brand-gold: #f59e0b;
            --brand-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --brand-gradient-gold: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --surface-bg: #f1f5f9;
            --surface-muted: #e2e8f0;
            --surface-card: #ffffff;
            --text-strong: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --border-soft: #e2e8f0;
            --ease-premium: cubic-bezier(0.22, 1, 0.36, 1);
        }

        :root[data-theme="dark"] {
            --surface-bg: #0f172a;
            --surface-muted: #1e293b;
            --surface-card: #1e293b;
            --text-strong: #f8fafc;
            --text-body: #cbd5e1;
            --text-muted: #94a3b8;
            --border-soft: rgba(255, 255, 255, 0.1);
        }

        :root[data-theme="light"] {
            --surface-bg: #f1f5f9;
            --surface-muted: #e2e8f0;
            --surface-card: #ffffff;
            --text-strong: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --border-soft: #e2e8f0;
        }

        body {
            background-color: var(--surface-bg);
            overflow-x: hidden;
            color: var(--text-body);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            position: relative;
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
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: white;
            transition: width var(--transition-speed) ease;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 12px 0 32px rgba(0, 0, 0, 0.15);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0) 30%);
            pointer-events: none;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.7);
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
            background: rgba(255, 255, 255, 0.08);
        }
        
        .sidebar .nav-link {
            border-radius: 12px;
            margin: 3px 12px;
            position: relative;
            transition: transform 0.25s var(--ease-premium), background-color 0.25s var(--ease-premium);
        }

        .sidebar .nav-link:hover {
            transform: translateX(3px);
        }

        .sidebar .nav-link:hover i {
            transform: translateY(-2px) scale(1.14);
            filter: drop-shadow(0 4px 10px rgba(16, 185, 129, 0.4));
        }

        .sidebar .nav-link.active i {
            transform: scale(1.1);
            filter: drop-shadow(0 4px 12px rgba(16, 185, 129, 0.5));
        }

        .sidebar-toggle {
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: 3px solid #ffffff;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            transition: all var(--transition-speed) ease;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
            font-size: 1.1rem;
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

        /* Enhanced Glassmorphic Card Styling & Hover Transitions */
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(31, 41, 55, 0.07);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.75);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .analytics-tilt {
            transform-style: preserve-3d;
            will-change: transform;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .card-header {
            border-bottom: 1px solid #e2e8f0;
            background-color: transparent;
        }

        /* Button Micro-Interactions & Hover Lift */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px -4px rgba(16, 185, 129, 0.35);
        }

        .btn:active {
            transform: translateY(1px) scale(0.98);
        }

        .btn-primary {
            border: none;
            background: var(--brand-gradient);
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            color: #ffffff;
        }

        .btn-primary:hover {
            filter: brightness(1.08);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.48);
            color: #ffffff;
        }

        /* Dropdown Pop-In Animation */
        .dropdown-menu {
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.15);
            animation: dropdownFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            transform-origin: top right;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .dropdown-item {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: 8px;
            margin: 2px 4px;
            width: calc(100% - 8px);
        }

        .dropdown-item:hover {
            background-color: rgba(16, 185, 129, 0.08);
            transform: translateX(4px);
            color: var(--brand-primary);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6ee7b7;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.2);
            transform: translateY(-1px);
        }

        .main-content .h2 {
            color: var(--text-strong);
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        /* Premium KPI Cards with Dynamic Motion & Aura Glow */
        .premium-kpi {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            border: 0;
            color: #fff !important;
            box-shadow: 0 16px 36px rgba(150, 15, 25, 0.28);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            animation: kpiFloat 6.6s ease-in-out infinite;
            animation-delay: var(--float-delay, 0s);
        }

        .premium-kpi:hover {
            transform: translateY(-8px) scale(1.02) !important;
            box-shadow: 0 25px 50px -10px rgba(200, 30, 43, 0.45), 0 0 30px rgba(212, 160, 23, 0.25) !important;
        }

        .premium-kpi i {
            transition: transform 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .premium-kpi:hover i {
            transform: scale(1.25) rotate(8deg);
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
            transition: transform 0.5s ease;
        }

        .premium-kpi:hover::after {
            transform: scale(1.3);
        }

        .premium-kpi::before {
            content: '';
            position: absolute;
            top: -120%;
            left: -25%;
            width: 35%;
            height: 300%;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.35) 50%, rgba(255,255,255,0) 100%);
            transform: rotate(20deg);
            animation: premiumShine 5.1s var(--ease-premium) infinite;
            animation-delay: var(--shine-delay, 0s);
        }

        .premium-kpi.bg-primary {
            background: linear-gradient(135deg, #c81e2b 0%, #e03040 48%, #b01520 100%) !important;
        }

        .premium-kpi.bg-success {
            background: linear-gradient(135deg, #0fa36f 0%, #16b57d 45%, #4fd1a0 100%) !important;
        }

        .premium-kpi.bg-info {
            background: linear-gradient(135deg, #b87d00 0%, #d4a017 50%, #f0c040 100%) !important;
        }

        .premium-kpi.bg-warning {
            background: linear-gradient(135deg, #7d3c00 0%, #a05010 48%, #c06820 100%) !important;
            color: #fff9f0 !important;
        }

        /* Interactive Table Row Hover Effects */
        .table thead th {
            border-bottom: 1.5px solid #e2e8f0;
            color: #4a5568;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .table tbody tr {
            border-color: #f1f5f9;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .table tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.04) !important;
        }

        /* Modern Sleek Scrollbar for Responsive Tables */
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: rgba(100, 116, 139, 0.25) transparent;
        }

        .table-responsive::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: transparent;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.25);
            border-radius: 999px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.45);
        }

        .premium-reveal {
            opacity: 0;
            transform: translateY(24px);
            animation: premiumReveal 0.85s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            animation-delay: var(--reveal-delay, 0s);
            will-change: transform, opacity;
        }

        .premium-pulse {
            animation: premiumPulse 3s ease-in-out infinite;
        }

        @keyframes premiumReveal {
            0% {
                opacity: 0;
                transform: translateY(24px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
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
            0%, 100% { box-shadow: 0 18px 38px rgba(200, 30, 43, 0.28); }
            50% { box-shadow: 0 24px 54px rgba(212, 160, 23, 0.38); }
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

        /* Prevent dropdown menus inside tables from clipping or collapsing container bounds */
        .col-actions .dropdown-menu,
        .dropdown-menu {
            z-index: 1060 !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
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
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 0.75rem 1.5rem !important;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .desktop-header {
            background: rgba(26, 32, 53, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        .header-title {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0;
            color: #1e293b;
            letter-spacing: -0.02em;
        }

        [data-bs-theme="dark"] .header-title {
            color: #f1f5f9;
        }

        .user-profile-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        [data-bs-theme="dark"] .user-profile-badge {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .user-profile-badge:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateY(-1px);
        }

        .avatar-circle {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
            overflow: hidden;
            flex-shrink: 0;
        }

        .topbar-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }

        .topbar-avatar-initials {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #c81e2b 0%, #d4a017 100%);
            border-radius: 50%;
        }

        .theme-select {
            min-width: 110px;
            border-radius: 10px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background-color: rgba(255, 255, 255, 0.5);
            color: #475569;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.4rem 2rem 0.4rem 0.75rem;
            cursor: pointer;
        }

        [data-bs-theme="dark"] .theme-select {
            background-color: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: #94a3b8;
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
                background: radial-gradient(circle at 10% 0%, #2d0505 0%, #180202 35%, #0f0101 100%);
            }

            .sidebar {
                background: linear-gradient(180deg, #0f0203 0%, #1c0406 100%);
                border-right: 1px solid rgba(212,160,23,0.15);
            }

            .sidebar a {
                color: #f0c8c8;
            }

            .sidebar a:hover,
            .sidebar a.active {
                color: #fff5f5;
                background: rgba(200, 30, 43, 0.22);
            }

            .mobile-topbar,
            .desktop-header {
                background: rgba(22, 4, 4, 0.88);
                border-color: #4d1010 !important;
            }

            .theme-select {
                border-color: #6d2020;
                background-color: rgba(30, 5, 5, 0.88);
                color: #f5d0d0;
            }

            .card {
                border: 1px solid #3d1010;
                box-shadow: 0 20px 52px rgba(10, 2, 2, 0.65);
                background: linear-gradient(160deg, rgba(28,8,8,0.92) 0%, rgba(22,4,4,0.85) 100%);
            }

            .card-header {
                border-bottom-color: #3d1515;
            }

            .btn-outline-primary {
                color: #f5a0a8;
                border-color: #c04050;
            }

            .btn-outline-primary:hover {
                background: #6d1520;
                border-color: #e05060;
                color: #fff5f5;
            }

            .form-control,
            .form-select,
            .input-group-text {
                background-color: #1c0505;
                border-color: #4d1515;
                color: #f5d8d8;
            }

            .form-control::placeholder {
                color: #c08080;
            }

            .table thead th {
                color: #e8b0b0;
                border-bottom-color: #4d1515;
            }

            .table tbody tr {
                border-color: #331010;
            }

            .table tbody tr:hover {
                background-color: #2d0a0a;
            }

            .badge.bg-dark-subtle {
                background-color: #3d1010 !important;
                color: #f5d0d0 !important;
                border-color: #5d1c1c !important;
            }

            .modal-content {
                background: #160404;
                border-color: #4d1515;
                box-shadow: 0 26px 70px rgba(0, 0, 0, 0.65);
            }

            .modal-header,
            .modal-footer {
                border-color: #4d1515;
            }

            .modal-footer {
                background: #0f0202;
            }

            .alert {
                color: #f5d8d8;
            }

            .alert-success {
                background-color: #0f3020;
            }

            .alert-danger {
                background-color: #4d1010;
            }

            .alert-warning {
                background-color: #3d2800;
                color: #ffe8a0;
            }

            .table-premium-mobile tr {
                background: #1c0505;
                border-color: #4d1515;
                box-shadow: 0 10px 22px rgba(10, 2, 2, 0.45);
            }

            .table-premium-mobile td::before {
                color: #d09090;
            }

            body::before {
                background: #c81e2b;
                opacity: 0.28;
            }

            body::after {
                background: #d4a017;
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

        <header class="desktop-header">
            <div class="d-flex align-items-center gap-3">
                <h1 class="header-title"><?= $this->renderSection('title') ?></h1>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="btn-toolbar d-flex align-items-center gap-2">
                    <div class="position-relative">
                        <i class="bi bi-palette position-absolute start-0 top-50 translate-middle-y ms-2 text-muted" style="font-size: 0.8rem; pointer-events: none;"></i>
                        <select class="form-select form-select-sm theme-select ps-4" id="themeSelectDesktop" aria-label="Theme mode">
                            <option value="auto">Auto</option>
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                        </select>
                    </div>
                </div>

                <div class="user-profile-badge">
                    <div class="avatar-circle overflow-hidden topbar-user-avatar">
                        <?php 
                            $topProfileImg = session()->get('profile_image');
                            $hasTopPhoto = !empty($topProfileImg) && is_file(FCPATH . 'uploads/profile/' . $topProfileImg);
                        ?>
                        <?php if ($hasTopPhoto): ?>
                            <img src="<?= base_url('uploads/profile/' . esc($topProfileImg)) ?>" alt="" class="topbar-avatar-img" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                            <span class="topbar-avatar-initials" style="display:none;"><?= strtoupper(substr((string) (session()->get('username') ?? 'U'), 0, 1)) ?></span>
                        <?php else: ?>
                            <span class="topbar-avatar-initials"><?= strtoupper(substr((string) (session()->get('username') ?? 'U'), 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-bold small text-dark" style="line-height: 1.2;"><?= esc((string) session()->get('username')) ?></span>
                        <span class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em;"><?= esc($layoutRole) ?></span>
                    </div>
                </div>
            </div>
        </header>

        <?= $this->renderSection('content') ?>
    </div>
</div>

<!-- Core Vendor Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Flash Message Handler
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Skip flash popups when in print mode
        const _isPrintMode = new URLSearchParams(window.location.search).get('print') === '1';

        <?php if (session()->getFlashdata('success')): ?>
            if (!_isPrintMode) {
                Toast.fire({
                    icon: 'success',
                    title: '<?= session()->getFlashdata('success') ?>'
                });
            }
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            if (!_isPrintMode) {
                Toast.fire({
                    icon: 'error',
                    title: '<?= session()->getFlashdata('error') ?>'
                });
            }
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            if (!_isPrintMode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: '<ul class="text-start mb-0 ps-3"><?php foreach (session()->getFlashdata('errors') as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul>',
                    confirmButtonColor: 'var(--brand-primary)'
                });
            }
        <?php endif; ?>
    });
</script>

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
