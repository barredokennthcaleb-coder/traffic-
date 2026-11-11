<?php
    $layoutRole = (string) session()->get('role');
    $isAdminDashboard = $layoutRole === 'admin' && uri_string() === 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- saveData: the same title is shown again in the phone bar and the
         desktop header, and the first render used to consume it, leaving both
         of them empty. -->
    <title><?= $this->renderSection('title', true) ?> - Traffic System</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/pic-1.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('vendor/bootstrap-icons/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendor/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendor/inter/inter.css') ?>">
    
    <style>
        /* Mobile: keep form controls at >=16px so iOS Safari does not zoom in
           when a field is focused and the keyboard opens. */
        @media (max-width: 767.98px) {
            input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
            select,
            textarea,
            .form-control,
            .form-select {
                font-size: 16px !important;
            }
        }
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --transition-speed: 0.3s;
            /* Electric Blue & Emerald Green Color System */
            --brand-primary: #0066ff;
            --brand-secondary: #0052cc;
            --brand-accent: #0080ff;
            --brand-highlight: #38bdf8;
            --brand-500: #3b82f6;
            --brand-600: #2563eb;
            --brand-800: #1e40af;
            --brand-900: #1e3a8a;
            --brand-tint-50: #eff6ff;
            --brand-tint-100: #dbeafe;
            --brand-tint-300: #93c5fd;
            --brand-tint-400: #60a5fa;
            --brand-rgb: 0, 102, 255;
            --brand-500-rgb: 59, 130, 246;
            --surface-bg: #f8fafc;
            --surface-muted: #f1f5f9;
            --surface-card: #ffffff;
            --text-strong: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-subtle: #94a3b8;
            --focus-ring: var(--brand-primary);
            --border-soft: #e2e8f0;
            --ease-premium: cubic-bezier(0.22, 1, 0.36, 1);
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 4px 14px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 10px 30px rgba(15, 23, 42, 0.12);
        }

        .btn-back-electric {
            background: linear-gradient(135deg, #0066ff 0%, #0052cc 100%) !important;
            border-color: #0066ff !important;
            color: #ffffff !important;
            transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }
        .btn-back-electric:hover,
        .btn-back-electric:focus-visible {
            background: linear-gradient(135deg, #0052cc 0%, #0047b3 100%) !important;
            border-color: #0047b3 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 82, 204, 0.28);
            transform: translateY(-1px);
        }

        .desktop-header.dashboard-shell-header {
            min-height: 62px;
            gap: 1rem;
        }
        .dashboard-breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .dashboard-breadcrumb a {
            color: inherit;
            text-decoration: none;
        }
        .dashboard-breadcrumb a:hover,
        .dashboard-breadcrumb [aria-current="page"] { color: var(--text-strong); }
        .dashboard-header-actions { min-width: 0; }
        .dashboard-header-search {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            width: min(250px, 24vw);
            height: 40px;
            padding: 0 0.65rem;
            color: var(--text-muted);
            background: var(--surface-muted);
            border: 1px solid var(--border-soft);
            border-radius: 10px;
        }
        .dashboard-header-search:focus-within {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary) 18%, transparent);
        }
        .dashboard-header-search input {
            min-width: 0;
            flex: 1;
            border: 0;
            outline: 0;
            color: var(--text-strong);
            background: transparent;
            font-size: 0.82rem;
        }
        .dashboard-header-search input::placeholder { color: var(--text-muted); }
        .dashboard-header-search button,
        .dashboard-notification,
        .mobile-header-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            background: transparent;
            border: 0;
            text-decoration: none;
        }
        .dashboard-header-search button { padding: 0.2rem; }
        .dashboard-notification,
        .mobile-header-icon {
            position: relative;
            width: 40px;
            height: 40px;
            border: 1px solid var(--border-soft);
            border-radius: 10px;
            font-size: 1.05rem;
        }
        .dashboard-notification:hover,
        .mobile-header-icon:hover,
        .dashboard-header-search button:hover { color: var(--brand-primary); }
        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            border: 2px solid var(--surface-card);
            border-radius: 50%;
            background: #dc2626;
        }
        .mobile-header-icon { display: none; }

        :root[data-theme="dark"] {
            --surface-bg: #0b0f19;
            --surface-muted: #131c2e;
            --surface-card: #0f172a;
            --text-strong: #f8fafc;
            --text-body: #cbd5e1;
            --text-muted: #94a3b8;
            --text-subtle: #64748b;
            --focus-ring: var(--brand-accent);
            --border-soft: #1e293b;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.35);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.45);
            --shadow-lg: 0 12px 36px rgba(0, 0, 0, 0.6);
        }
        @media (max-width: 768px) {
            .mobile-header-icon { display: inline-flex; width: 36px; height: 38px; border: 0; }
            .mobile-header-icon .notification-dot { top: 4px; right: 3px; }
            .dashboard-breadcrumb { display: none; }
            .dashboard-shell-header .user-profile-badge { display: none; }
        }

        :root[data-theme="light"] {
            --surface-bg: #f8fafc;
            --surface-muted: #f1f5f9;
            --surface-card: #ffffff;
            --text-strong: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-subtle: #94a3b8;
            --focus-ring: var(--brand-primary);
            --border-soft: #e2e8f0;
        }

        /* Role identity. The body carries the signed-in role as a class, so each
           role recolors the brand tokens it renders with: admins read electric
           blue (the :root defaults), enforcers read emerald green. The themed
           selectors are repeated per color scheme so they outrank the
           :root[data-theme] focus-ring declarations above. */
        body.role-enforcer,
        :root[data-theme="dark"] body.role-enforcer,
        :root[data-theme="light"] body.role-enforcer {
            --brand-primary: #047857;
            --brand-secondary: #065f46;
            --brand-accent: #10b981;
            --brand-highlight: #34d399;
            --brand-500: #10b981;
            --brand-600: #059669;
            --brand-800: #065f46;
            --brand-900: #064e3b;
            --brand-tint-50: #ecfdf5;
            --brand-tint-100: #d1fae5;
            --brand-tint-300: #6ee7b7;
            --brand-tint-400: #34d399;
            --brand-rgb: 4, 120, 87;
            --brand-500-rgb: 16, 185, 129;
            --focus-ring: #10b981;
        }

        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            background-color: var(--surface-bg);
            overflow-x: hidden;
            color: var(--text-body);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            position: relative;
            transition: background-color 0.3s ease, color 0.3s ease;
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
            background: linear-gradient(180deg, #0b1329 0%, #070d1d 100%);
            color: white;
            transition: width var(--transition-speed) var(--ease-premium), 
                        transform var(--transition-speed) var(--ease-premium);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
            border-right: 1px solid rgba(255, 255, 255, 0.07);
        }

        .sidebar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0) 30%);
            pointer-events: none;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* The nav column scrolls on short laptop screens instead of pushing
           the Logout item below the bottom edge, where a fixed sidebar can
           never be scrolled to. The collapse toggle is a sibling of this
           column, so it keeps sitting on the sidebar border. */
        .sidebar > .position-sticky {
            max-height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.25) transparent;
        }

        .sidebar > .position-sticky::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar > .position-sticky::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 3px;
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            white-space: nowrap;
            overflow: hidden;
            transition: all var(--transition-speed) var(--ease-premium);
            border-radius: 12px;
            margin: 4px 12px;
        }

        .sidebar.collapsed a {
            padding: 14px 0;
            justify-content: center;
            margin: 4px 8px;
        }

        .sidebar a i {
            font-size: 1.25rem;
            min-width: 40px;
            text-align: center;
            transition: transform 0.3s var(--ease-premium);
        }

        .sidebar .nav-text {
            transition: opacity var(--transition-speed) ease, 
                        visibility var(--transition-speed) ease;
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

        .sidebar a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(4px);
        }
        
        .sidebar a.active {
            color: white;
            background: var(--brand-primary);
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(var(--brand-rgb), 0.4);
        }
        
        .sidebar .nav-link {
            position: relative;
        }

        .sidebar .nav-link:hover i {
            transform: scale(1.1);
        }

        .sidebar .nav-link.active i {
            transform: scale(1.08);
        }

        .sidebar-toggle {
            position: absolute;
            right: -30px;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            padding: 0;
            background: #0066ff;
            border: 1px solid #0066ff;
            border-radius: 50%;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            transition: background-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
            opacity: 1;
            box-shadow: 0 2px 8px rgba(0, 102, 255, 0.45);
            font-size: 1.4rem;
        }

        .role-admin .sidebar-toggle {
            right: -29.5px;
            width: 59px;
            height: 59px;
        }

        .sidebar-toggle:hover,
        .sidebar-toggle:focus-visible {
            background: #0052cc;
            border-color: #0052cc;
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(0, 102, 255, 0.55);
        }

        .sidebar.collapsed .sidebar-toggle {
            transform: translateY(-50%) rotate(180deg);
        }

        .sidebar.collapsed .sidebar-toggle:hover {
            transform: translateY(-50%) rotate(180deg) scale(1.05);
        }
        .sidebar-toggle:focus-visible {
            outline: 3px solid #ffffff;
            outline-offset: 2px;
        }

        @keyframes pageFadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed) ease;
            width: calc(100% - var(--sidebar-width));
            padding: 20px;
            min-height: 100vh;
            animation: pageFadeIn 0.22s ease-out;
        }

        .sidebar.collapsed + .main-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        /* Modal & Backdrop Stacking Context Fix */
        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
            background-color: rgba(15, 23, 42, 0.65) !important;
        }
        .modal-backdrop.show {
            opacity: 1 !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
        }
        .modal.fade .modal-dialog {
            transform: scale(0.96) translateY(10px);
            opacity: 0;
            transition: transform 0.24s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.24s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .modal.show .modal-dialog {
            transform: scale(1) translateY(0) !important;
            opacity: 1 !important;
        }

        /* Enhanced Glassmorphic Card Styling & Hover Transitions */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            background: var(--surface-card);
            border: 1px solid var(--border-soft);
            transition: transform 0.35s var(--ease-premium), 
                        box-shadow 0.35s var(--ease-premium), 
                        border-color 0.35s var(--ease-premium);
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--brand-accent);
        }

        .card-header {
            border-bottom: 1px solid var(--border-soft);
            background-color: transparent;
            font-weight: 600;
            color: var(--text-strong);
        }

        /* Button Micro-Interactions & Hover Lift */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.25s var(--ease-premium);
            border: 2px solid transparent;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn:active {
            transform: translateY(0) scale(0.98);
        }

        /* Primary Electric Blue Buttons */
        .btn-primary {
            background: var(--brand-primary);
            border: 2px solid var(--brand-primary);
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(var(--brand-rgb), 0.35);
        }

        .btn-primary:hover {
            background: var(--brand-secondary);
            border-color: var(--brand-secondary);
            color: #ffffff;
            box-shadow: 0 4px 18px rgba(var(--brand-rgb), 0.45);
        }

        .btn-outline-primary {
            border: 2px solid var(--brand-primary);
            color: var(--brand-primary);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--brand-primary);
            border-color: var(--brand-primary);
            color: #ffffff;
        }

        /* Success Emerald Green Buttons */
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: 2px solid #10b981;
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.35);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            box-shadow: 0 4px 18px rgba(16, 185, 129, 0.45);
        }

        /* Warning/Pending Mustard Buttons */
        .btn-warning {
            background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
            border: 2px solid #ca8a04;
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(202, 138, 4, 0.35);
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #ca8a04 0%, #a16207 100%);
            border-color: #a16207;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(202, 138, 4, 0.45);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: 2px solid #ef4444;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            border-color: #dc2626;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.4);
        }

        .btn-secondary {
            background: #808080;
            border: 2px solid #808080;
            color: #ffffff;
        }

        .btn-secondary:hover {
            background: #666666;
            border-color: #666666;
            color: #ffffff;
        }

        .btn-info {
            background: #f0f0f0;
            border: 2px solid #000000;
            color: #000000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-info:hover {
            background: #e0e0e0;
            border-color: #000000;
            color: #000000;
        }

        .btn-light {
            background: var(--surface-muted);
            border: 2px solid var(--border-soft);
            color: var(--text-body);
        }

        .btn-light:hover {
            background: var(--surface-card);
            border-color: #000000;
        }

        .btn-dark {
            background: #000000;
            border: 2px solid #000000;
            color: #ffffff;
        }

        .btn-dark:hover {
            background: #1a1a1a;
            color: #ffffff;
        }

        /* Dropdown Fade Animation */
        .dropdown-menu {
            border-radius: 12px;
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-lg);
            animation: dropdownFadeIn 0.2s var(--ease-premium);
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            transition: all 0.2s var(--ease-premium);
            border-radius: 8px;
            margin: 2px 4px;
            width: calc(100% - 8px);
            color: var(--text-body);
        }

        .dropdown-item:hover {
            background-color: var(--surface-muted);
            transform: translateX(4px);
            color: var(--text-strong);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--brand-500);
            box-shadow: 0 0 0 0.2rem rgba(var(--brand-rgb), 0.25);
            transform: translateY(-1px);
            transition: all 0.2s var(--ease-premium);
        }

        .form-control,
        .form-select {
            border-color: var(--border-soft);
            transition: all 0.2s var(--ease-premium);
        }

        .main-content .h2 {
            color: var(--text-strong);
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        /* Premium KPI Cards - Colorful with Smooth Animations */
        .premium-kpi {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            border: none;
            color: #ffffff !important;
            box-shadow: var(--shadow-md);
            transition: all 0.4s var(--ease-premium);
            animation: kpiFloat 6s ease-in-out infinite;
            animation-delay: var(--float-delay, 0s);
        }

        .premium-kpi:hover {
            transform: translateY(-8px) scale(1.02) !important;
            box-shadow: var(--shadow-lg) !important;
        }

        .premium-kpi i {
            transition: transform 0.4s var(--ease-premium);
            opacity: 0.9;
        }

        .premium-kpi:hover i {
            transform: scale(1.2) rotate(5deg);
            opacity: 1;
        }

        .premium-kpi::after {
            content: '';
            position: absolute;
            bottom: -40%;
            right: -35%;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
            pointer-events: none;
            transition: transform 0.5s ease;
        }

        .premium-kpi:hover::after {
            transform: scale(1.4);
        }

        .premium-kpi::before {
            content: '';
            position: absolute;
            top: -120%;
            left: -25%;
            width: 35%;
            height: 300%;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.25) 50%, rgba(255,255,255,0) 100%);
            transform: rotate(20deg);
            animation: premiumShine 6s var(--ease-premium) infinite;
            animation-delay: var(--shine-delay, 0s);
        }

        /* Colorful KPI Card Backgrounds */
        .premium-kpi.bg-primary {
            background: linear-gradient(135deg, var(--brand-accent) 0%, var(--brand-secondary) 100%) !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(var(--brand-rgb), 0.4);
        }

        .premium-kpi.bg-primary:hover {
            box-shadow: 0 12px 32px rgba(var(--brand-rgb), 0.5) !important;
        }

        .premium-kpi.bg-primary i {
            color: #ffffff !important;
        }

        .premium-kpi.bg-success {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        }

        .premium-kpi.bg-success:hover {
            box-shadow: 0 12px 32px rgba(16, 185, 129, 0.5) !important;
        }

        .premium-kpi.bg-success i {
            color: #ffffff !important;
        }

        .premium-kpi.bg-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(6, 182, 212, 0.4);
        }

        .premium-kpi.bg-info:hover {
            box-shadow: 0 12px 32px rgba(6, 182, 212, 0.5) !important;
        }

        .premium-kpi.bg-info i {
            color: #ffffff !important;
        }

        .premium-kpi.bg-warning {
            background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%) !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(202, 138, 4, 0.4);
        }

        .premium-kpi.bg-warning:hover {
            box-shadow: 0 12px 32px rgba(202, 138, 4, 0.5) !important;
        }

        .premium-kpi.bg-warning i {
            color: #ffffff !important;
        }

        .premium-kpi.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
        }

        .premium-kpi.bg-danger:hover {
            box-shadow: 0 12px 32px rgba(239, 68, 68, 0.5) !important;
        }

        .premium-kpi.bg-danger i {
            color: #ffffff !important;
        }

        /* Interactive Table Row Hover Effects */
        .table thead th {
            border-bottom: 2px solid var(--border-soft);
            color: var(--text-strong);
            font-size: 0.75rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 700;
            background-color: var(--surface-muted);
        }

        .table tbody tr {
            border-color: var(--border-soft);
            transition: all 0.25s var(--ease-premium);
        }

        .table tbody tr:hover {
            background-color: var(--surface-muted) !important;
            transform: scale(1.01);
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
            transform: translateY(12px);
            animation: premiumReveal 0.38s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            animation-delay: var(--reveal-delay, 0s);
            will-change: transform, opacity;
        }

        .premium-pulse {
            animation: premiumPulse 3s ease-in-out infinite;
        }

        @keyframes premiumReveal {
            0% {
                opacity: 0;
                transform: translateY(12px);
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

        /* Colorful Status Badges - Restored */
        .badge {
            font-weight: 700;
            letter-spacing: 0.03em;
            padding: 0.5em 1em;
            border-radius: 8px;
            transition: all 0.2s var(--ease-premium);
            border: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* Success/Paid - Emerald */
        .badge.bg-success {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
        }

        .badge.bg-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.5);
        }

        /* Warning/Pending - Mustard */
        .badge.bg-warning {
            background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(202, 138, 4, 0.4);
        }

        .badge.bg-warning:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(202, 138, 4, 0.5);
        }

        /* Danger/Cancelled - Red */
        .badge.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        }

        .badge.bg-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.5);
        }

        /* Primary - Electric Blue */
        .badge.bg-primary {
            background: linear-gradient(135deg, var(--brand-accent) 0%, var(--brand-secondary) 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(var(--brand-rgb), 0.4);
        }

        .badge.bg-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(var(--brand-rgb), 0.5);
        }

        /* Secondary - Gray (keeping monochrome) */
        .badge.bg-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Info - Cyan */
        .badge.bg-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(6, 182, 212, 0.4);
        }

        /* Light - White with border (monochrome) */
        .badge.bg-light {
            background-color: #fafafa !important;
            color: #000000 !important;
            border: 1px solid #e0e0e0 !important;
        }

        /* Dark - Black (monochrome) */
        .badge.bg-dark {
            background-color: #000000 !important;
            color: #ffffff !important;
        }

        /* Dark subtle - for ticket IDs etc (monochrome) */
        .badge.bg-dark-subtle {
            background-color: #f5f5f5 !important;
            color: #000000 !important;
            border: 1px solid #e0e0e0 !important;
            font-weight: 600;
        }

        .badge.bg-secondary-subtle {
            background-color: #f5f5f5 !important;
            color: #000000 !important;
            border: 1px solid #e0e0e0 !important;
            font-weight: 600;
        }

        /* Rounded pill badges */
        .badge.rounded-pill {
            padding: 0.4em 1.2em;
        }

        /* Warning / Pending Color Palette - Mustard */
        .text-warning {
            color: #ca8a04 !important;
        }
        .bg-warning-subtle {
            background-color: #fef9c3 !important;
            color: #854d0e !important;
        }
        .text-warning-emphasis {
            color: #854d0e !important;
        }
        .border-warning-subtle {
            border-color: #fef08a !important;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .desktop-header {
            background: var(--surface-card);
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            /* The bar carries only the theme switch and the profile badge, so
               it stays a slim control strip instead of a mostly empty band
               at the top of every screen. */
            padding: 0.5rem 1rem !important;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s var(--ease-premium);
        }

        [data-bs-theme="dark"] .desktop-header {
            background: var(--surface-card);
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-md);
        }

        .header-title {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0;
            color: var(--text-strong);
            letter-spacing: -0.02em;
        }

        [data-bs-theme="dark"] .header-title {
            color: var(--text-strong);
        }

        .user-profile-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--surface-muted);
            border: 1px solid var(--border-soft);
            border-radius: 12px;
            transition: all 0.3s var(--ease-premium);
        }

        [data-bs-theme="dark"] .user-profile-badge {
            background: var(--surface-muted);
            border: 1px solid var(--border-soft);
        }

        /* Utility colours are written for light cards, so on the dark palette
           they turn into near-black text on navy. Remap them; badges keep
           their own colour pairing and are left alone. */
        [data-bs-theme="dark"] .text-dark:not(.badge) {
            color: #e2e8f0 !important;
        }

        [data-bs-theme="dark"] .text-primary:not(.badge) {
            color: var(--brand-tint-300) !important;
        }

        [data-bs-theme="dark"] .text-danger:not(.badge) {
            color: #fca5a5 !important;
        }

        [data-bs-theme="dark"] .text-success:not(.badge) {
            color: #6ee7b7 !important;
        }

        [data-bs-theme="dark"] .text-warning:not(.badge) {
            color: #fde047 !important;
        }

        [data-bs-theme="dark"] .bg-warning-subtle {
            background-color: rgba(202, 138, 4, 0.22) !important;
            color: #fef08a !important;
        }

        [data-bs-theme="dark"] .text-warning-emphasis {
            color: #fef08a !important;
        }

        [data-bs-theme="dark"] .border-warning-subtle {
            border-color: rgba(202, 138, 4, 0.4) !important;
        }

        /* Bootstrap ships .bg-white / .bg-light with !important, so a panel
           painted with one of them stayed white while its text took the dark
           palette's near-white -- a 1.05:1 card header. Remap the surfaces
           themselves. Only the background is touched: .card-header already
           sets its own colour and the .text-* remaps above still apply.
           Badges, progress fills and the small white discs (avatar circles,
           KPI medallions) are intentionally light and stay that way. */
        [data-bs-theme="dark"] .bg-white:not(.badge):not(.progress-bar):not(.bg-opacity-25):not(.rounded-circle),
        [data-bs-theme="dark"] .bg-light:not(.badge):not(.progress-bar) {
            background-color: var(--surface-muted) !important;
            border-color: var(--border-soft);
        }

        /* The phone top bar is the one surface the page slides under, so it has
           to read as a layer above the content. In dark mode the bar and the
           panels beneath it are painted from the same slate ramp and the light
           theme's black drop shadow disappears against a near-black page, so a
           half-scrolled KPI row looked like a stray sliver glued to the bar
           rather than content passing beneath it. A lit surface plus a
           visible hairline marks the edge. z-index stays at 990: the drawer
           (1000) still has to cover the menu button. */
        [data-bs-theme="dark"] .mobile-topbar {
            background-color: var(--surface-muted);
            border-bottom-color: #334155;
        }

        [data-bs-theme="dark"] .mobile-topbar.is-scrolled {
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.05),
                        0 10px 26px rgba(0, 0, 0, 0.55);
        }

        [data-bs-theme="dark"] .btn-outline-danger {
            --bs-btn-color: #fca5a5;
            --bs-btn-border-color: #f87171;
            --bs-btn-hover-bg: #7f1d1d;
            --bs-btn-hover-border-color: #f87171;
            --bs-btn-hover-color: #fee2e2;
            --bs-btn-active-bg: #991b1b;
            --bs-btn-active-border-color: #f87171;
            --bs-btn-active-color: #fee2e2;
        }

        [data-bs-theme="dark"] .desktop-header .text-danger,
        [data-bs-theme="dark"] .mobile-topbar .text-danger {
            color: #fca5a5 !important;
        }

        .user-profile-badge:hover {
            background: var(--surface-card);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .avatar-circle {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--brand-600) 0%, var(--brand-800) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 2px 8px rgba(var(--brand-rgb), 0.3);
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid var(--brand-tint-400);
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
            border: 1px solid var(--border-soft);
            background-color: var(--surface-card);
            color: var(--text-body);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.4rem 2rem 0.4rem 0.75rem;
            cursor: pointer;
            transition: all 0.2s var(--ease-premium);
        }

        .theme-select:hover {
            border-color: var(--brand-primary);
        }

        [data-bs-theme="dark"] .theme-select {
            background-color: var(--surface-card);
            border: 1px solid var(--border-soft);
            color: var(--text-body);
        }

        .modal-content {
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            background: var(--surface-card);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-soft);
            background: var(--surface-card);
        }

        .modal-footer {
            border-top: 1px solid var(--border-soft);
            background: var(--surface-muted);
        }

        .table-light,
        .table > :not(caption) > * > * {
            --bs-table-bg: transparent;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        /* ═══════════════════════════════════════════════════════════════
           INTERACTION & ACCESSIBILITY LAYER
           One set of rules for every screen: keyboard focus you can see,
           finger-sized controls, and text colours that stay readable.
           ═══════════════════════════════════════════════════════════════ */

        /* Keyboard focus. Browsers drop the default ring on styled buttons and
           links, which left keyboard users with no idea where they were. */
        a:focus-visible,
        button:focus-visible,
        [role="button"]:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible,
        summary:focus-visible,
        .nav-link:focus-visible,
        .dropdown-item:focus-visible,
        .page-link:focus-visible {
            outline: 3px solid var(--focus-ring) !important;
            outline-offset: 2px !important;
            border-radius: 8px;
        }

        /* Mouse users keep the calm look: the ring is for keyboard only. */
        a:focus:not(:focus-visible),
        button:focus:not(:focus-visible) {
            outline: none;
        }

        /* Anything that behaves like a control should look clickable. */
        [role="button"],
        [data-bs-toggle]:not(button):not(a),
        label[for],
        .cursor-pointer {
            cursor: pointer;
        }

        ::selection {
            background: rgba(var(--brand-rgb), 0.22);
            color: inherit;
        }

        /* Form focus matches the ring above, so every field reacts the same. */
        .form-control:focus,
        .form-select:focus,
        .form-check-input:focus {
            border-color: var(--brand-tint-400);
            box-shadow: 0 0 0 3px rgba(var(--brand-rgb), 0.18);
        }

        /* Disabled controls read as disabled without needing a hover. */
        .btn:disabled,
        .btn.disabled,
        button:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* A submit that is on its way gets a spinner and stops accepting a
           second click (see the busy-state script at the end of the layout). */
        .btn.is-busy {
            pointer-events: none;
            opacity: 0.85;
        }

        .btn.is-busy .btn-busy-spinner {
            display: inline-block;
            width: 0.9em;
            height: 0.9em;
            margin-right: 0.45em;
            vertical-align: -0.1em;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: btnBusySpin 0.6s linear infinite;
        }

        @keyframes btnBusySpin {
            to { transform: rotate(360deg); }
        }

        @media (prefers-color-scheme: dark) {
            /* The OS dark setting is only a fallback: an explicit "Light"
               pick must still render light, so everything below is skipped
               when the user has chosen the light theme.

               This palette is the same navy/blue the Dark option uses. It used
               to be burgundy and gold, so "Auto" on a dark OS rendered a red
               system while "Dark" rendered blue: two different products from
               one setting. The matching tokens are declared here too, so Auto
               on a dark OS also gets readable text on the dark surfaces. */
            :root:not([data-theme="light"]) {
            --surface-bg: #0b0f19;
            --surface-muted: #131c2e;
            --surface-card: #0f172a;
            --text-strong: #f8fafc;
            --text-body: #cbd5e1;
            --text-muted: #94a3b8;
            --text-subtle: #64748b;
            --focus-ring: var(--brand-500);
            --border-soft: #1e293b;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.35);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.45);
            --shadow-lg: 0 12px 36px rgba(0, 0, 0, 0.6);

            body {
                background: #0b0f19;
            }

            .sidebar {
                background: linear-gradient(180deg, #0b1329 0%, #070d1d 100%);
                border-right: 1px solid rgba(255, 255, 255, 0.07);
            }

            .sidebar a {
                color: #94a3b8;
            }

            .sidebar a:hover {
                color: #f8fafc;
                background: rgba(255, 255, 255, 0.08);
            }

            .sidebar a.active {
                color: #ffffff;
                background: var(--brand-primary);
                box-shadow: 0 4px 14px rgba(var(--brand-rgb), 0.45);
            }

            .mobile-topbar,
            .desktop-header {
                background: #0f172a;
                border-color: #1e293b !important;
            }

            .theme-select {
                border-color: #1e293b;
                background-color: #131c2e;
                color: #cbd5e1;
            }

            .card {
                border: 1px solid #1e293b;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
                background: #0f172a;
            }

            .card-header {
                border-bottom-color: #1e293b;
            }

            .btn-outline-primary {
                color: var(--brand-tint-300);
                border-color: var(--brand-500);
            }

            .btn-outline-primary:hover {
                background: var(--brand-900);
                border-color: var(--brand-tint-400);
                color: var(--brand-tint-50);
            }

            .form-control,
            .form-select,
            .input-group-text {
                background-color: #131c2e;
                border-color: #1e293b;
                color: #f8fafc;
            }

            .form-control::placeholder {
                color: #64748b;
            }

            .table thead th {
                color: #cbd5e1;
                border-bottom-color: #1e293b;
                background-color: #131c2e;
            }

            .table tbody tr {
                border-color: #1e293b;
            }

            .table tbody tr:hover {
                background-color: #162238;
            }

            .badge.bg-dark-subtle {
                background-color: #131c2e !important;
                color: #cbd5e1 !important;
                border-color: #1e293b !important;
            }

            .modal-content {
                background: #0f172a;
                border-color: #1e293b;
                box-shadow: 0 26px 70px rgba(0, 0, 0, 0.65);
            }

            .modal-header,
            .modal-footer {
                border-color: #1e293b;
            }

            .modal-footer {
                background: #131c2e;
            }

            .alert {
                color: var(--brand-tint-100);
            }

            .alert-success {
                background-color: #0c3a2a;
            }

            .alert-danger {
                background-color: #3f1420;
            }

            .alert-warning {
                background-color: #3a2c08;
                color: #ffe8a0;
            }

            .table-premium-mobile tr {
                background: #0f2a44;
                border-color: #1e4976;
                box-shadow: 0 10px 22px rgba(2, 8, 20, 0.5);
            }

            .table-premium-mobile td::before {
                color: #9dc2e0;
            }

            body::before {
                background: var(--brand-600);
                opacity: 0.22;
            }

            body::after {
                background: var(--brand-highlight);
                opacity: 0.18;
            }
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

            /* Cancelling the reveal animation must not leave it at opacity 0. */
            .premium-reveal {
                opacity: 1 !important;
                transform: none !important;
            }

            .card,
            .sidebar .nav-link {
                transition: none !important;
            }

            /* Respect the OS "reduce motion" setting everywhere, not just the
               decorative layer: no slides, fades or count-up easing remain. */
            *,
            *::before,
            *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.001ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* Blue Theme Alerts */
        .alert {
            border: 2px solid;
            border-radius: 12px;
            transition: all 0.3s var(--ease-premium);
            font-weight: 600;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #10b981;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border-color: #ef4444;
        }

        .alert-warning {
            background-color: #fef9c3;
            color: #854d0e;
            border-color: #eab308;
        }

        .alert-info {
            background-color: var(--brand-tint-100);
            color: var(--brand-800);
            border-color: var(--brand-500);
        }

        .alert-primary {
            background-color: var(--brand-tint-100);
            color: var(--brand-900);
            border-color: var(--brand-600);
        }

        body.role-enforcer .card-header,
        body.role-enforcer .table-light {
            background: var(--surface-muted) !important;
        }

        body.role-enforcer .border-bottom {
            border-color: var(--brand-tint-100) !important;
        }

        .mobile-topbar {
            display: none;
            position: sticky;
            top: 0;
            z-index: 990;
            background: var(--surface-card);
            border-bottom: 1px solid var(--border-soft);
            padding: 12px;
            box-shadow: var(--shadow-sm);
        }

        .mobile-sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
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

        /* The phone bar stays one line high whatever the page is called: the
           title takes the space that is left and shortens with an ellipsis
           instead of pushing the controls off the edge or wrapping. */
        .mobile-topbar .page-title {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        /* The bar keeps its controls narrow and a touch shorter than the
           form default, so it reads as a header rather than a form row. */
        @media (max-width: 768px) {
            .mobile-topbar .theme-select {
                min-width: 0;
                width: 92px;
                min-height: 38px;
                padding-left: 0.55rem;
                font-size: 0.82rem;
            }
        }

        @media (max-width: 400px) {
            .mobile-topbar .page-title {
                font-size: 0.98rem;
            }

            .mobile-topbar .theme-select {
                width: 82px;
            }
        }

        @media (max-width: 768px) {
            /* On a phone the sidebar is a drawer, not a rail: it is either
               off-screen or open at a readable width. The desktop collapse state
               is ignored here — an 80px rail with its labels clipped is what made
               this menu look cramped and hard to read. */
            .sidebar,
            .sidebar.collapsed,
            .sidebar.active.collapsed {
                /* The drawer is wider than the desktop rail it used to be parked
                   behind: at -var(--sidebar-width) a min(320px, 86vw) drawer kept
                   40px on screen. Because the sidebar outranks the phone topbar
                   (z-index 1000 vs 990) that strip also covered the menu button,
                   so tapping it did nothing and the menu looked broken. Offset by
                   the drawer's own width, and drop the shadow while shut so none
                   of it bleeds back past the edge. */
                --drawer-width: min(320px, 86vw);
                left: calc(-1 * var(--drawer-width));
                width: var(--drawer-width);
                box-shadow: none;
                /* A shut drawer must never swallow a tap meant for the page
                   beneath it, whatever width it ends up with. */
                pointer-events: none;
                transition: left var(--transition-speed) var(--ease-premium), width var(--transition-speed) var(--ease-premium), box-shadow var(--transition-speed) var(--ease-premium);
            }
            .sidebar.active,
            .sidebar.active.collapsed {
                left: 0;
                box-shadow: var(--shadow-lg);
                pointer-events: auto;
            }
            .sidebar.collapsed .nav-text,
            .sidebar.collapsed .sidebar-header-text,
            .sidebar.active.collapsed .nav-text {
                display: inline;
                opacity: 1;
                visibility: visible;
                width: auto;
            }
            .sidebar.collapsed a,
            .sidebar.active.collapsed a {
                justify-content: flex-start;
                margin: 4px 12px;
            }
            .sidebar .brand-title { font-size: 1.05rem; }
            .sidebar .brand-subtitle { font-size: 0.78rem; }
            .sidebar-nav .nav-link { font-size: 0.95rem; }
            .sidebar-toggle {
                display: none;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 12px;
            }

            .container-fluid {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
                padding-bottom: calc(1rem + env(safe-area-inset-bottom));
            }

            /* Thumb-sized controls: 44px rows, and buttons never smaller than
               40px, so nothing has to be aimed at on a phone. */
            .form-control,
            .form-select,
            .input-group-text,
            .dropdown-item,
            .nav-link {
                min-height: 44px;
            }

            .btn:not(.btn-close):not(.btn-link) {
                min-height: 40px;
            }

            .card {
                border-radius: 14px;
            }

            .card-header,
            .card-body,
            .card-footer {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .btn-group {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .modal-dialog {
                margin: 1rem;
            }

            .table {
                min-width: 640px;
                font-size: 0.9rem;
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
                margin: -12px -12px 12px -12px;
            }
            .desktop-header {
                display: none !important;
            }
            .main-content .h2 {
                font-size: 1.25rem;
            }

            .premium-kpi {
                margin-bottom: 1rem;
            }

            /* Optimize for touch */
            .btn {
                min-height: 44px;
            }

            .sidebar a {
                padding: 16px 20px;
            }

            .table tbody tr:hover {
                transform: none;
            }
        }

        /* Tablet Portrait */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            
            .main-content {
                margin-left: 240px;
                width: calc(100% - 240px);
            }

            .premium-kpi {
                font-size: 0.95rem;
            }
        }

        /* Large Desktop */
        @media (min-width: 1920px) {
            .container-fluid {
                max-width: 1800px;
                margin: 0 auto;
            }

            .sidebar {
                width: 300px;
            }

            .main-content {
                margin-left: 300px;
                width: calc(100% - 300px);
            }
        }

        /* Mobile: ledger tables stack into cards. This block lost its opening
           rule in an earlier edit, which left a stray "}-mobile td" selector
           and pushed the card rules to every screen width. */
        @media (max-width: 768px) {
            .table-premium-mobile {
                border: 0;
                min-width: 0;
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
                border: 1px solid var(--brand-tint-100);
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

        /* ═══════════════════════════════════════════════════════════════
           SMOOTH DELIVERY LAYER
           Motion and scrolling only — no layout, wording or behaviour is
           changed here, so every screen keeps its present content.
           ═══════════════════════════════════════════════════════════════ */

        html {
            scroll-behavior: smooth;
        }

        /* Colour changes cross-fade for the length of a theme switch. The class
           is only present while switching, so normal rendering stays cheap. */
        html.theme-switching,
        html.theme-switching body,
        html.theme-switching body * {
            transition: background-color 0.32s var(--ease-premium),
                        color 0.32s var(--ease-premium),
                        border-color 0.32s var(--ease-premium),
                        box-shadow 0.32s var(--ease-premium) !important;
        }

        /* Each page fades up into place instead of appearing in a single frame.
           The animation is opacity-only on purpose: a transform here would turn
           the shell into a containing block and drag fixed buttons (like the
           officer's quick-add FAB) away from the viewport. */
        @keyframes contentDeliver {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .main-content {
            animation: contentDeliver 0.3s var(--ease-premium) both;
            transition: margin-left var(--transition-speed) var(--ease-premium),
                        width var(--transition-speed) var(--ease-premium),
                        opacity 0.18s ease-in;
        }

        /* Leaving a page: the shell stays put and only the content fades out. */
        html.is-leaving .main-content {
            opacity: 0;
        }

        /* Wide tables scroll like a native pane: momentum and a thin bar. */
        .table-responsive,
        .report-table-scroll {
            -webkit-overflow-scrolling: touch;
            /* Scroll chaining stays enabled on purpose — do not put
               "contain" back. Bootstrap's .table-responsive only sets
               overflow-x, which quietly makes the pane a scroll container on
               both axes. A tall ledger fills most of the screen, so the
               wheel lands on that pane instead of the page; with containment
               set on either axis Chrome kept the gesture inside it and the
               page never moved — the list looked frozen under the mouse.
               The page cannot slide sideways anyway (body is overflow-x
               hidden), so nothing is lost by letting the gesture chain. */
            overscroll-behavior: auto;
            scrollbar-width: thin;
            touch-action: pan-x pan-y;
        }


        .table-responsive::-webkit-scrollbar,
        .report-table-scroll::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb,
        .report-table-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.5);
            border-radius: 99px;
        }

        /* State changes ease instead of snapping. */
        .table tbody tr {
            transition: background-color 0.24s var(--ease-premium),
                        box-shadow 0.24s var(--ease-premium),
                        transform 0.24s var(--ease-premium);
        }

        .badge,
        .form-control,
        .form-select,
        .input-group-text,
        .dropdown-item,
        .nav-link {
            transition: background-color 0.24s var(--ease-premium),
                        color 0.24s var(--ease-premium),
                        border-color 0.24s var(--ease-premium),
                        box-shadow 0.24s var(--ease-premium),
                        transform 0.24s var(--ease-premium);
        }

        /* The dialog itself is already animated by the premium modal rules
           above; the backdrop is left alone on purpose — Bootstrap waits for
           the backdrop before it opens the dialog, so a fading backdrop would
           delay every dialog by its own duration. */

        /* Taps register instantly and quietly instead of flashing the
           browser's default highlight over a pressed row or button. */
        .btn,
        .table tbody tr,
        .dropdown-item {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        /* A modal taller than the screen scrolls inside its own box, and that
           scrollbar eats ~15px on the right — which pushed the whole dialog
           off-centre (bigger gap on the right than on the left). Reserving the
           gutter on both edges keeps dialogs optically centred, whether or not
           they need to scroll. */
        .modal {
            scrollbar-gutter: stable both-edges;
        }

        /* Readers who ask for less motion get the same screens, instantly. */
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .main-content { animation: none !important; }
            html.is-leaving .main-content { opacity: 1; }
            html.theme-switching body * { transition: none !important; }
            .modal.fade .modal-dialog { transition: none !important; transform: none; }
        }

        /* ═══════════════════════════════════════════════════════════════
           PREMIUM ULTRA-SMOOTH & RESPONSIVE SWEETALERT2 STYLING
           ═══════════════════════════════════════════════════════════════ */

        /* Smooth Backdrop.
           The blur is painted on a full-screen fixed layer, so only devices
           that can afford it get it — and it is never animated: the tint
           fades, the blur simply applies. Animating a backdrop-filter is what
           made the overlay stutter on phones. */
        .swal2-container {
            z-index: 999999 !important;
            padding: 1rem !important;
            transition: background-color 0.24s var(--ease-premium) !important;
            -webkit-tap-highlight-color: transparent;
        }
        .swal2-container.swal2-backdrop-show {
            background: rgba(15, 23, 42, 0.45) !important;
        }
        @media (min-width: 768px) and (hover: hover) {
            .swal2-container.swal2-backdrop-show {
                backdrop-filter: blur(6px) saturate(120%);
                -webkit-backdrop-filter: blur(6px) saturate(120%);
            }
        }
        :root[data-theme="dark"] .swal2-container.swal2-backdrop-show {
            background: rgba(0, 0, 0, 0.65) !important;
        }
        /* Toasts reuse the backdrop-show class, but they are notifications,
           not modal dialogs: without this the tint and blur were painted as a
           dark strip behind the toast card. */
        body.swal2-toast-shown .swal2-container {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        /* The dialog and toast animations themselves live further down, in
           "DIALOG DELIVERY", so each keyframe is declared exactly once. */
        .swal2-popup:not(.swal2-toast).swal2-show {
            animation: swal2SmoothIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
            will-change: transform, opacity;
        }
        .swal2-popup:not(.swal2-toast).swal2-hide {
            animation: swal2SmoothOut 0.22s cubic-bezier(0.4, 0, 1, 1) forwards !important;
            will-change: transform, opacity;
        }

        /* Modern Glass Card */
        .swal2-popup:not(.swal2-toast) {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            background: #ffffff !important;
            color: #0f172a !important;
            border-radius: 22px !important;
            padding: 2rem 2rem 1.6rem !important;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.22),
                        0 0 0 1px rgba(15, 23, 42, 0.05) !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            max-width: 480px !important;
            width: 100% !important;
        }

        :root[data-theme="dark"] .swal2-popup:not(.swal2-toast) {
            background: #1e293b !important;
            color: #f8fafc !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.65),
                        0 0 0 1px rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        /* Typography */
        .swal2-title {
            font-weight: 700 !important;
            font-size: 1.35rem !important;
            line-height: 1.3 !important;
            letter-spacing: -0.02em !important;
            color: inherit !important;
            padding: 0.6rem 0 0 0 !important;
        }

        .swal2-html-container {
            font-size: 0.95rem !important;
            color: #475569 !important;
            line-height: 1.55 !important;
            margin: 0.85rem 0 1.25rem !important;
        }
        :root[data-theme="dark"] .swal2-html-container {
            color: #5c6b80 !important;
        }

        /* Refined Icons with soft pulsing rings */
        .swal2-icon {
            margin: 0.5rem auto 0.6rem !important;
            border-width: 2.5px !important;
            transform: scale(0.96);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        }
        .swal2-popup:hover .swal2-icon {
            transform: scale(1.04);
        }
        /* A tap leaves the hover state stuck on touch screens. */
        @media (hover: none) {
            .swal2-popup:hover .swal2-icon {
                transform: scale(0.96);
            }
        }

        /* Success Icon */
        .swal2-icon.swal2-success {
            border-color: #10b981 !important;
            color: #10b981 !important;
        }
        .swal2-icon.swal2-success [class^='swal2-success-line'] {
            background-color: #10b981 !important;
        }
        .swal2-icon.swal2-success .swal2-success-ring {
            border-color: rgba(16, 185, 129, 0.25) !important;
        }

        /* Warning Icon */
        .swal2-icon.swal2-warning {
            border-color: #eab308 !important;
            color: #eab308 !important;
        }

        /* Error Icon */
        .swal2-icon.swal2-error {
            border-color: #ef4444 !important;
            color: #ef4444 !important;
        }
        .swal2-icon.swal2-error [class^='swal2-x-mark-line'] {
            background-color: #ef4444 !important;
        }

        /* Info Icon */
        .swal2-icon.swal2-info {
            border-color: var(--brand-500) !important;
            color: var(--brand-500) !important;
        }

        /* Buttons & Actions */
        .swal2-actions {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 12px !important;
            margin: 1.25rem 0 0 0 !important;
            width: 100% !important;
        }

        .swal2-styled {
            border-radius: 12px !important;
            padding: 0.65rem 1.4rem !important;
            font-weight: 600 !important;
            font-size: 0.92rem !important;
            letter-spacing: 0.01em !important;
            transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1),
                        box-shadow 0.18s cubic-bezier(0.16, 1, 0.3, 1),
                        background-color 0.18s ease !important;
            border: none !important;
            box-shadow: none !important;
            min-height: 42px !important;
            /* No !important here: SweetAlert hides the buttons a dialog does not
               use with an inline display:none, and an !important rule would
               force every Confirm/Deny/Cancel button back on screen. */
            display: inline-flex;
            align-items: center !important;
            justify-content: center !important;
            /* Drops the tap delay and the grey flash on touch screens. */
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
        .swal2-styled:hover {
            transform: translateY(-1.5px) !important;
        }
        .swal2-styled:active {
            transform: scale(0.97) !important;
        }
        .swal2-styled:focus {
            box-shadow: 0 0 0 3px rgba(var(--brand-500-rgb), 0.35) !important;
        }
        /* The ring is for keyboards, not for the finger that just tapped. */
        .swal2-styled:focus:not(:focus-visible) {
            box-shadow: none !important;
        }
        @media (hover: none) {
            .swal2-styled:hover {
                transform: none !important;
            }
        }

        /* Confirm button */
        .swal2-styled.swal2-confirm {
            background: linear-gradient(135deg, var(--brand-500) 0%, var(--brand-600) 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(var(--brand-rgb), 0.32) !important;
        }
        .swal2-styled.swal2-confirm:hover {
            box-shadow: 0 6px 20px rgba(var(--brand-rgb), 0.44) !important;
        }

        /* Dangerous confirmations (deleting a ticket, a record) opt in with
           customClass: { popup: 'swal-danger' }. The icon, the title and the
           confirm button turn red so the destructive answer cannot be mistaken
           for a routine one. */
        .swal2-popup.swal-danger .swal2-icon.swal2-warning {
            border-color: #dc2626 !important;
            color: #dc2626 !important;
        }
        .swal2-popup.swal-danger .swal2-title {
            color: #b91c1c !important;
        }
        .swal2-popup.swal-danger .swal2-styled.swal2-confirm {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(190, 18, 60, 0.32) !important;
        }
        .swal2-popup.swal-danger .swal2-styled.swal2-confirm:hover {
            box-shadow: 0 6px 20px rgba(190, 18, 60, 0.44) !important;
        }

        /* Cancel button */
        .swal2-styled.swal2-cancel {
            background: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #cbd5e1 !important;
        }
        .swal2-styled.swal2-cancel:hover {
            background: #e2e8f0 !important;
            color: #1e293b !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06) !important;
        }
        :root[data-theme="dark"] .swal2-styled.swal2-cancel {
            background: #334155 !important;
            color: #e2e8f0 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }
        :root[data-theme="dark"] .swal2-styled.swal2-cancel:hover {
            background: #475569 !important;
            color: #ffffff !important;
        }

        /* ── TOAST NOTIFICATIONS (SMOOTH SLIDE & GLOW) ── */
        @keyframes toastSlideIn {
            0% {
                opacity: 0;
                transform: translateX(45px) scale(0.92);
            }
            100% {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        @keyframes toastSlideOut {
            0% {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
            100% {
                opacity: 0;
                transform: translateX(45px) scale(0.92);
            }
        }

        .swal2-popup.swal2-toast {
            border-radius: 16px !important;
            padding: 0.8rem 1.15rem !important;
            background: rgba(255, 255, 255, 0.95) !important;
            color: #0f172a !important;
            box-shadow: 0 16px 36px -6px rgba(15, 23, 42, 0.16),
                        0 0 0 1px rgba(0, 0, 0, 0.05) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(226, 232, 240, 0.85) !important;
            font-family: 'Inter', sans-serif !important;
            overflow: hidden !important;
        }
        .swal2-popup.swal2-toast.swal2-show {
            animation: toastSlideIn 0.32s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
        }
        .swal2-popup.swal2-toast.swal2-hide {
            animation: toastSlideOut 0.24s cubic-bezier(0.4, 0, 1, 1) forwards !important;
        }

        :root[data-theme="dark"] .swal2-popup.swal2-toast {
            background: rgba(30, 41, 59, 0.94) !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 16px 36px -6px rgba(0, 0, 0, 0.55),
                        0 0 0 1px rgba(255, 255, 255, 0.08) !important;
        }

        .swal2-popup.swal2-toast .swal2-title {
            font-size: 0.92rem !important;
            font-weight: 600 !important;
            padding: 0 !important;
            margin: 0 !important;
            color: inherit !important;
            letter-spacing: -0.01em !important;
        }

        .swal2-popup.swal2-toast .swal2-icon {
            margin: 0 10px 0 0 !important;
            width: 26px !important;
            height: 26px !important;
            border-width: 2px !important;
        }

        .swal2-popup.swal2-toast .swal2-timer-progress-bar {
            background: linear-gradient(90deg, var(--brand-500) 0%, #10b981 100%) !important;
            height: 3px !important;
            border-radius: 0 0 16px 16px !important;
        }

        /* ── RESPONSIVE ADAPTATIONS (MOBILE DEVICES) ── */
        @media (max-width: 576px) {
            .swal2-container {
                padding: 12px !important;
            }
            .swal2-popup:not(.swal2-toast) {
                width: 100% !important;
                max-width: calc(100vw - 24px) !important;
                margin: auto !important;
                padding: 1.4rem 1.15rem 1.25rem !important;
                border-radius: 18px !important;
            }
            .swal2-title {
                font-size: 1.2rem !important;
            }
            .swal2-html-container {
                font-size: 0.88rem !important;
                margin: 0.6rem 0 1rem !important;
            }
            .swal2-icon {
                transform: scale(0.85);
                margin: 0.25rem auto 0.4rem !important;
            }
            .swal2-actions {
                flex-direction: column !important;
                gap: 8px !important;
                margin-top: 1rem !important;
                width: 100% !important;
            }
            .swal2-styled {
                width: 100% !important;
                margin: 0 !important;
                padding: 0.72rem 1rem !important;
                font-size: 0.94rem !important;
                min-height: 44px !important;
            }
            /* Toast positioning on mobile */
            .swal2-container.swal2-top-end,
            .swal2-container.swal2-top {
                padding: 10px !important;
            }
            /* Width follows the toast container instead of the whole
               viewport: the grid column is narrower than 100vw, so a fixed
               width made the card run off the right edge and clip its text. */
            .swal2-popup.swal2-toast {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                margin: 0 !important;
                border-radius: 14px !important;
            }
        }

        /* ── DIALOG DELIVERY: SMALL-SCREEN FIT AND CHEAPER MOTION ─────
           Dialogs keep every word and button; only the way they arrive and
           the way they fit a small screen are tuned here. */

        /* A tall dialog must never run off a short screen: it scrolls inside
           itself instead (landscape phones, error lists, long confirms). */
        .swal2-popup:not(.swal2-toast) {
            max-height: calc(100dvh - 2rem);
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
        .swal2-popup:not(.swal2-toast)::-webkit-scrollbar {
            width: 8px;
        }
        .swal2-popup:not(.swal2-toast)::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.45);
            border-radius: 99px;
        }

        /* Desktop: a short, soft spring. Only opacity and transform are
           animated — a blur layer here is what made the card stutter.
           (These are the only swal2Smooth* keyframes in the file; the same
           declarations used to be repeated, so the later copy silently won.) */
        @keyframes swal2SmoothIn {
            0%   { opacity: 0; transform: scale(0.94) translateY(12px); }
            70%  { opacity: 1; transform: scale(1.012) translateY(-1px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes swal2SmoothOut {
            0%   { opacity: 1; transform: scale(1) translateY(0); }
            100% { opacity: 0; transform: scale(0.96) translateY(6px); }
        }

        /* Phones: the dialog rises from the bottom edge instead of scaling
           from nowhere, which reads as a sheet and matches the swipe-down
           habit of the rest of the mobile screens. */
        @keyframes swal2SheetIn {
            0%   { opacity: 0; transform: translateY(26px) scale(0.985); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes swal2SheetOut {
            0%   { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(18px) scale(0.99); }
        }

        /* Body and actions settle a beat after the card lands — a short beat,
           so the dialog never feels like it is still loading. */
        @keyframes swal2ContentIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .swal2-show .swal2-title,
        .swal2-show .swal2-html-container {
            animation: swal2ContentIn 0.22s 0.02s var(--ease-premium) both;
        }
        .swal2-show .swal2-actions {
            animation: swal2ContentIn 0.24s 0.05s var(--ease-premium) both;
        }
        .swal2-show .swal2-icon {
            animation: swal2ContentIn 0.28s var(--ease-premium) both;
        }

        @media (max-width: 576px) {
            .swal2-popup:not(.swal2-toast).swal2-show {
                animation-name: swal2SheetIn !important;
                animation-duration: 0.26s !important;
            }
            .swal2-popup:not(.swal2-toast).swal2-hide {
                animation-name: swal2SheetOut !important;
                animation-duration: 0.18s !important;
            }
        }

        /* Close button eases instead of popping. */
        .swal2-close {
            transition: transform 0.2s var(--ease-premium), color 0.2s ease, opacity 0.2s ease !important;
        }
        .swal2-close:hover {
            transform: scale(1.12) rotate(90deg);
        }

        /* Toasts keep a comfortable gap and never exceed the screen. */
        .swal2-container.swal2-top-end,
        .swal2-container.swal2-top {
            gap: 0.5rem;
        }
        .swal2-popup.swal2-toast {
            max-width: min(420px, calc(100vw - 1.5rem));
        }
        @media (max-width: 576px) {
            /* SweetAlert caps the toast container at 360px and pins it to the
               right, which left a wide strip on the left and none on the
               right. Full width with equal gutters reads as one band. */
            .swal2-container.swal2-top-end,
            .swal2-container.swal2-top {
                width: 100% !important;
                left: 0 !important;
                right: 0 !important;
                justify-content: center !important;
                /* Clear of the 69px sticky top bar, so a toast never hides
                   the menu button while it is on screen. */
                padding: calc(79px + env(safe-area-inset-top, 0px)) 12px 12px !important;
            }
            .swal2-popup:not(.swal2-toast) {
                max-height: calc(100dvh - 1.5rem);
                padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px)) !important;
            }
        }

        /* Very small phones: keep everything readable without crowding. */
        @media (max-width: 380px) {
            .swal2-popup:not(.swal2-toast) {
                padding: 1.2rem 0.95rem 1.1rem !important;
                border-radius: 18px !important;
            }
            .swal2-title {
                font-size: 1.08rem !important;
            }
            .swal2-html-container {
                font-size: 0.84rem !important;
            }
            .swal2-actions {
                gap: 8px !important;
            }
        }

        /* Landscape phones: the viewport is short, so dialogs hug the top and
           scale their icon down instead of being cut off. */
        @media (max-height: 520px) and (orientation: landscape) {
            .swal2-container {
                padding: 0.5rem !important;
                align-items: flex-start !important;
            }
            .swal2-popup:not(.swal2-toast) {
                max-height: calc(100dvh - 1rem);
                padding: 1rem 1.25rem !important;
                border-radius: 16px !important;
            }
            .swal2-icon {
                transform: scale(0.72);
                margin: 0.1rem auto 0.2rem !important;
            }
            .swal2-actions {
                flex-direction: row !important;
                margin-top: 0.9rem !important;
            }
            .swal2-styled {
                width: auto !important;
                flex: 1 1 0;
            }
        }

        /* No motion at all for readers who ask for it — dialogs still fit. */
        @media (prefers-reduced-motion: reduce) {
            .swal2-popup.swal2-show,
            .swal2-popup.swal2-hide,
            .swal2-popup.swal2-toast.swal2-show,
            .swal2-popup.swal2-toast.swal2-hide,
            .swal2-show .swal2-title,
            .swal2-show .swal2-html-container,
            .swal2-show .swal2-actions,
            .swal2-show .swal2-icon {
                animation: none !important;
            }
            .swal2-close:hover {
                transform: none;
            }
        }

        /* ═══════════════════════════════════════════════════════════════
           DESIGN CRAFT LAYER
           Alignment, scroll affordance and phone density for the screens
           that already exist. No markup, wording, route or figure is
           changed here — only how the same content is presented.
           ═══════════════════════════════════════════════════════════════ */

        /* ── 1. Digits that line up ─────────────────────────────────────
           Fines, counts, percentages and ticket numbers are read as
           columns. Proportional figures make those columns ragged, so a
           list of amounts looks unsettled and two totals are hard to
           compare. Tabular figures give every digit the same advance
           width, which stacks the decimal points and lets the eye scan a
           column of pesos in one pass. */
        .table,
        .table th,
        .table td,
        .report-table,
        .premium-kpi,
        .stat-counter-value,
        .nature-kpi-chip,
        .badge,
        .page-link,
        .btn-group .btn,
        [data-tabular] {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
        }

        /* ── 2. A wide table says that it scrolls ───────────────────────
           A table wider than its card used to just stop at the card edge,
           which reads as missing columns rather than more to the right.
           Two soft shadows sit on the edges, and the local/scroll
           background-attachment pattern fades each one out as soon as
           that edge is reached. Pure CSS: no script, no extra markup. */
        .table-responsive,
        .report-table-scroll {
            background-image:
                linear-gradient(to right, var(--surface-card) 40%, rgba(255, 255, 255, 0)),
                linear-gradient(to left, var(--surface-card) 40%, rgba(255, 255, 255, 0)),
                radial-gradient(farthest-side at 0 50%, rgba(15, 23, 42, 0.18), rgba(15, 23, 42, 0)),
                radial-gradient(farthest-side at 100% 50%, rgba(15, 23, 42, 0.18), rgba(15, 23, 42, 0));
            background-position: 0 0, 100% 0, 0 0, 100% 0;
            background-repeat: no-repeat;
            background-size: 34px 100%, 34px 100%, 14px 100%, 14px 100%;
            background-attachment: local, local, scroll, scroll;
        }

        /* A printed report is white paper in every theme, so its tables fade
           to paper white rather than to the card token — otherwise a dark
           band would sit on the paper edges in dark mode. */
        .report-document-card .table-responsive,
        .report-document-card .report-table-scroll {
            background-image:
                linear-gradient(to right, #ffffff 40%, rgba(255, 255, 255, 0)),
                linear-gradient(to left, #ffffff 40%, rgba(255, 255, 255, 0)),
                radial-gradient(farthest-side at 0 50%, rgba(15, 23, 42, 0.18), rgba(15, 23, 42, 0)),
                radial-gradient(farthest-side at 100% 50%, rgba(15, 23, 42, 0.18), rgba(15, 23, 42, 0));
        }

        /* Headline cards hold the same height however their footer line
           wraps, in the same way the charts already share a row height. */
        .premium-kpi {
            height: 100%;
            /* Lets the figure below size itself against the card it sits in
               rather than against the window. */
            container-type: inline-size;
        }

        /* The peso total is the longest string a headline card holds. At a
           fixed 28px it overran the medallion, which flexbox then squashed
           into an oval instead of letting it shrink. The figure now scales
           with the card width, so it always fits with room to spare. */
        .premium-kpi h3 {
            font-size: clamp(1.05rem, 8.7cqi, 1.75rem);
            white-space: nowrap;
        }

        .premium-kpi .bg-white.bg-opacity-25 {
            flex-shrink: 0;
        }

        .admin-analytics-inner .col-md-3 > a {
            display: block;
            height: 100%;
        }

        /* ── 3. Phones: the four headline numbers share one screen ──────
           The KPI row fell to one full-width card per row, so a phone user
           scrolled past four tall cards before reaching any chart. Two-up
           with a condensed scale keeps every word and figure, and puts the
           whole summary in the first screen. */
        @media (max-width: 575.98px) {
            /* Two columns, tighter gutters. */
            .admin-analytics-inner > .row:first-child {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }

            .admin-analytics-inner > .row:first-child > .col-md-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .premium-kpi .card-body {
                padding: 0.8rem 0.85rem !important;
            }

            /* The figure is what the card is for: it keeps one line, scales
               with the phone, and the medallion shrinks to leave it room. */
            .premium-kpi h3 {
                font-size: clamp(1rem, 4.35vw, 1.42rem);
                line-height: 1.15;
                letter-spacing: -0.01em;
                white-space: nowrap;
            }

            .premium-kpi h6 {
                font-size: 0.64rem !important;
                letter-spacing: 0.03em !important;
                line-height: 1.25;
            }

            .premium-kpi > .card-body > div:first-child {
                margin-bottom: 0.65rem !important;
            }

            .premium-kpi .bg-white.bg-opacity-25 {
                width: 28px !important;
                height: 28px !important;
                padding: 0.24rem !important;
                flex-shrink: 0;
            }

            .premium-kpi .bg-white.bg-opacity-25 i {
                font-size: 0.85rem !important;
            }

            .premium-kpi .mt-4 {
                margin-top: 0.6rem !important;
            }

            .premium-kpi .small,
            .premium-kpi .badge {
                font-size: 0.66rem !important;
                line-height: 1.3;
            }

            .premium-kpi .fw-bold[style*="0.95rem"] {
                font-size: 0.82rem !important;
            }
        }

        /* Tablets sat between the two layouts with four cramped cards: a
           700px screen put four headline figures in the space two need, and
           a 1000px screen still squeezed them to 142px each once the sidebar
           took its share. Two up holds until there is width for four. */
        @media (min-width: 576px) and (max-width: 1199.98px) {
            .admin-analytics-inner > .row:first-child > .col-md-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        /* On the narrowest phones the medallion is the first thing to go: the
           label and the figure both need the width more than the ornament
           does. Nothing is lost from the card's meaning. */
        @media (max-width: 359.98px) {
            .premium-kpi .bg-white.bg-opacity-25 {
                display: none;
            }
        }

        /* ── 4. No ghost hover on a touch screen ────────────────────────
           A finger has no pointer to move away, so the lift stayed applied
           to whatever was tapped last and the card looked stuck in the air.
           Touch screens get the settled state; the press still registers
           through :active. */
        @media (hover: none) {
            .card:hover,
            .premium-kpi:hover,
            .user-profile-badge:hover,
            .btn:hover,
            .sidebar a:hover {
                transform: none !important;
            }

            .premium-kpi:hover i,
            .sidebar .nav-link:hover i {
                transform: none !important;
            }
        }

        /* ── 5. The phone bar steps forward as content passes under it ──
           At rest the top bar is flat; once the page is scrolling it gets
           the same soft elevation the desktop header has, so the heading
           the user is reading never merges into the content behind it. */
        .mobile-topbar {
            transition: box-shadow 0.25s var(--ease-premium),
                        background-color 0.25s var(--ease-premium);
        }

        .mobile-topbar.is-scrolled {
            box-shadow: 0 6px 22px rgba(15, 23, 42, 0.16);
        }

        /* In-page jumps land below the sticky phone bar instead of under
           it. */
        html {
            scroll-padding-top: 72px;
        }

        @media (max-width: 768px) {
            html {
                scroll-padding-top: 64px;
            }
        }

        /* ── PRINT: strip the app chrome ────────────────────────────────
           The print dialog has no preview, and the sidebar, topbar, theme
           picker and toasts used to be printed on top of the document. */
        @media print {
            /* Paper is always fully delivered, never mid-transition. */
            .main-content,
            html.is-leaving .main-content {
                animation: none !important;
                opacity: 1 !important;
            }
            .sidebar,
            .sidebar-toggle,
            .mobile-topbar,
            .desktop-header,
            .user-profile-badge,
            .theme-select,
            .swal2-container,
            .alert-dismissible .btn-close {
                display: none !important;
            }
            /* Reveal animations never finish in a print snapshot, which left
               the hero and cards at opacity 0 — a blank sheet on paper. */
            .premium-reveal,
            .premium-reveal * {
                opacity: 1 !important;
                transform: none !important;
                animation: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }
            body {
                background: #fff !important;
                color: #0f172a !important;
            }
            /* Keep coloured state badges and progress bars on paper. */
            .badge,
            .progress-bar {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .card {
                box-shadow: none !important;
            }
            /* The on-screen edge shadows are a "there is more to the right"
               hint; on paper they would print as a grey smear on the table
               border. */
            .table-responsive,
            .report-table-scroll {
                background-image: none !important;
            }
            .mobile-topbar.is-scrolled {
                box-shadow: none !important;
            }
        }
    </style>
    <style>
        /* ── Cross-document view transitions (temporary demo) ──────────────
           Opts every page into the browser's own cross-fade between
           navigations: no JavaScript, no controller changes. Browsers
           without support simply navigate as before, and the whole block
           can be deleted to go back to instant page loads. */
        @view-transition {
            navigation: auto;
        }

        ::view-transition-old(root),
        ::view-transition-new(root) {
            animation-duration: 220ms;
            animation-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
        }

        /* Motion-sensitive readers keep the instant switch. */
        @media (prefers-reduced-motion: reduce) {
            @view-transition {
                navigation: none;
            }
        }
    </style>
    <?= $this->renderSection('styles') ?>
    <?php
        // SweetAlert takes concrete colors — it cannot read a CSS variable — so
        // the signed-in role's accent is handed to JS here and read back through
        // window.__brand. Keep these in step with the body.role-* block above.
        $brandJs = $layoutRole === 'enforcer'
            ? ['primary' => '#047857', 'accent' => '#10b981']
            : ['primary' => '#0066ff', 'accent' => '#0080ff'];
    ?>
    <script>window.__brand = <?= json_encode($brandJs) ?>;</script>
</head>
<body class="role-<?= esc($layoutRole) ?> <?= $isAdminDashboard ? 'dashboard-layout' : '' ?>">

<div class="d-flex">
    <?= $this->include('theme/sidebar') ?>
    <div class="mobile-sidebar-backdrop"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="mobile-topbar">
            <button type="button" class="btn btn-outline-primary btn-sm" id="mobileMenuBtn" aria-label="Expand sidebar" aria-expanded="false">
                <i class="bi bi-list fs-5" aria-hidden="true"></i>
            </button>
            <h2 class="page-title"><?= $this->renderSection('title', true) ?></h2>
            <div class="d-flex align-items-center gap-2">
                <?php if ($isAdminDashboard): ?>
                    <a class="mobile-header-icon" href="<?= base_url('lookup') ?>" aria-label="Search citations">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </a>
                    <button class="mobile-header-icon" type="button" aria-label="Notifications">
                        <i class="bi bi-bell" aria-hidden="true"></i><span class="notification-dot" aria-hidden="true"></span>
                    </button>
                <?php endif; ?>
                <select class="form-select form-select-sm theme-select" id="themeSelectMobile" aria-label="Theme mode">
                    <option value="auto">Auto</option>
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                </select>
            </div>
        </div>

        <header class="desktop-header <?= $isAdminDashboard ? 'dashboard-shell-header' : '' ?>">
            <?php if ($isAdminDashboard): ?>
                <nav class="dashboard-breadcrumb" aria-label="Breadcrumb">
                    <a href="<?= base_url('dashboard') ?>">Home</a>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    <span aria-current="page">Dashboard</span>
                </nav>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <div class="dashboard-header-actions d-flex align-items-center gap-3">
                <?php if ($isAdminDashboard): ?>
                    <form class="dashboard-header-search" action="<?= base_url('lookup') ?>" method="get" role="search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" name="query" aria-label="Search citations" placeholder="Search citations">
                        <button type="submit" aria-label="Submit citation search"><i class="bi bi-arrow-return-left" aria-hidden="true"></i></button>
                    </form>
                    <button class="dashboard-notification" type="button" aria-label="Notifications">
                        <i class="bi bi-bell" aria-hidden="true"></i><span class="notification-dot" aria-hidden="true"></span>
                    </button>
                <?php endif; ?>
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
<script src="<?= base_url('vendor/sweetalert2/sweetalert2.all.min.js') ?>"></script>
<script src="<?= base_url('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('js/mobile-print.js?v=2') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Every dialog opens without the page jumping underneath it: SweetAlert
        // otherwise pads <body> to hide the scrollbar and the whole screen
        // shifts sideways for a frame. Options passed by a caller still win.
        const swalFire = Swal.fire;
        Swal.fire = function (...args) {
            if (args.length === 1 && args[0] && typeof args[0] === 'object') {
                args[0] = Object.assign({ heightAuto: false }, args[0]);
            }
            // Keep the callee's own `this`: a Swal.mixin() class (the toast
            // helper) carries its defaults there, and binding to Swal would
            // turn every toast into a full dialog.
            return swalFire.apply(this, args);
        };

        // Pages that build their own toast (Swal.mixin) would otherwise miss
        // those defaults and re-introduce the body jump, so mixins get the
        // same treatment. A mixin's own options still win.
        const swalMixin = Swal.mixin;
        Swal.mixin = function (...args) {
            const mixin = swalMixin.apply(this, args);
            const mixinFire = mixin.fire;
            mixin.fire = function (...fireArgs) {
                if (fireArgs.length === 1 && fireArgs[0] && typeof fireArgs[0] === 'object') {
                    fireArgs[0] = Object.assign({ heightAuto: false }, fireArgs[0]);
                }
                return mixinFire.apply(this, fireArgs);
            };
            return mixin;
        };

        // Global Enhanced Toast Mixin
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true,
            heightAuto: false,
            showClass: { popup: 'swal2-show' },
            hideClass: { popup: 'swal2-hide' },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
                // Also support touch devices smoothly
                toast.addEventListener('touchstart', Swal.stopTimer, { passive: true });
                toast.addEventListener('touchend', Swal.resumeTimer, { passive: true });
            }
        });

        // Make Toast globally accessible for any page/AJAX
        window.Toast = Toast;
        window.AppToast = Toast;

        // Unified Smooth Confirm Dialog Helper
        window.AppConfirm = function(options) {
            return Swal.fire({
                title: options.title || 'Are you sure?',
                text: options.text || '',
                html: options.html || undefined,
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonText: options.confirmText || '<i class="bi bi-check2 me-1"></i> Confirm',
                cancelButtonText: options.cancelText || 'Cancel',
                confirmButtonColor: options.confirmColor || (window.__brand && window.__brand.primary) || '#0066ff',
                reverseButtons: true,
                focusCancel: true,
                ...options
            });
        };

        // Skip flash popups when in print mode
        const _isPrintMode = new URLSearchParams(window.location.search).get('print') === '1';

        <?php if (session()->getFlashdata('success')): ?>
            if (!_isPrintMode) {
                Toast.fire({
                    icon: 'success',
                    title: <?= json_encode(session()->getFlashdata('success'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                });
            }
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            if (!_isPrintMode) {
                Toast.fire({
                    icon: 'error',
                    title: <?= json_encode(session()->getFlashdata('error'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                });
            }
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            if (!_isPrintMode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Please Check Your Input',
                    html: `
                        <div class="text-start p-2 rounded-3 bg-light border" style="max-height: 220px; overflow-y: auto;">
                            <ul class="mb-0 ps-3 text-danger small">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li class="py-1"><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    `,
                    confirmButtonText: 'Understood',
                    confirmButtonColor: (window.__brand && window.__brand.primary) || '#0066ff'
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

        // Cross-fade the palette during a switch instead of snapping. The class
        // lives only for the length of the change.
        let themeSwitchTimer = null;
        const flashThemeChange = () => {
            root.classList.add('theme-switching');
            window.clearTimeout(themeSwitchTimer);
            themeSwitchTimer = window.setTimeout(function() {
                root.classList.remove('theme-switching');
            }, 380);
        };

        const applyTheme = (theme, shouldPersist = false) => {
            const mode = (theme === 'light' || theme === 'dark') ? theme : 'auto';
            if (mode === 'auto') {
                root.removeAttribute('data-theme');
            } else {
                root.setAttribute('data-theme', mode);
            }

            // Bootstrap has its own theme hook, and the page styles use it for
            // dark surfaces. Nothing was setting it, so every
            // [data-bs-theme="dark"] rule was dead code and dark mode left
            // light cards and dark text behind.
            root.setAttribute('data-bs-theme', mode === 'auto'
                ? (darkPreference.matches ? 'dark' : 'light')
                : mode);
            if (themeSelectDesktop) themeSelectDesktop.value = mode;
            if (themeSelectMobile) themeSelectMobile.value = mode;
            if (shouldPersist) {
                localStorage.setItem(THEME_KEY, mode);
            }
            window.dispatchEvent(new CustomEvent('themechange', { detail: { mode } }));
        };

        applyTheme(getSavedTheme());

        themeSelectDesktop?.addEventListener('change', function() {
            flashThemeChange();
            applyTheme(this.value, true);
        });

        themeSelectMobile?.addEventListener('change', function() {
            flashThemeChange();
            applyTheme(this.value, true);
        });

        darkPreference.addEventListener('change', function() {
            if (getSavedTheme() === 'auto') {
                flashThemeChange();
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
                toggle.setAttribute('aria-expanded', String(!isCollapsed));
                toggle.setAttribute('aria-label', isCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
            });
        }

        // Restore state
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true' && !isMobile()) {
            sidebar.classList.add('collapsed');
        }
        if (toggle) {
            const isCollapsed = sidebar.classList.contains('collapsed');
            toggle.setAttribute('aria-expanded', String(!isCollapsed));
            toggle.setAttribute('aria-label', isCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
        }

        const closeMobileSidebar = () => {
            sidebar.classList.remove('active');
            sidebar.classList.remove('collapsed');
            document.body.classList.remove('sidebar-open');
            if (mobileMenuBtn) {
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
                mobileMenuBtn.setAttribute('aria-label', 'Expand sidebar');
            }
        };

        const openMobileSidebar = () => {
            // The drawer opens full width. It used to inherit the desktop rail
            // state, which left a phone with an 80px strip of unreadable icons.
            sidebar.classList.add('active');
            sidebar.classList.remove('collapsed');
            document.body.classList.add('sidebar-open');
            if (mobileMenuBtn) {
                mobileMenuBtn.setAttribute('aria-expanded', 'true');
                mobileMenuBtn.setAttribute('aria-label', 'Collapse sidebar');
            }
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
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                sidebar.classList.toggle('collapsed', isCollapsed);
                if (toggle) {
                    toggle.setAttribute('aria-expanded', String(!isCollapsed));
                    toggle.setAttribute('aria-label', isCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
                }
            }
        });

        /* The phone bar lifts off the page as soon as content moves beneath
           it, and settles back at the top. Reads the scroll offset once per
           frame at most, so it stays free on long pages. */
        const mobileBar = document.querySelector('.mobile-topbar');

        if (mobileBar) {
            // One class toggle per scroll event: cheap enough to run inline,
            // and it settles back the moment the page returns to the top.
            const syncMobileBar = function() {
                mobileBar.classList.toggle('is-scrolled', window.scrollY > 4);
            };

            window.addEventListener('scroll', syncMobileBar, { passive: true });
            syncMobileBar();
        }

        document.querySelectorAll('.main-content table').forEach(function(table) {
            if (!table.closest('.table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });

        // Smooth delivery: every in-app navigation steps the content out while
        // the shell stays still. Delegated, so card links and buttons move the
        // same way as the sidebar. New tabs, downloads, external sites, the
        // print view and same-page links are left completely alone.
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            if (link.target === '_blank' || link.hasAttribute('download')) return;
            if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

            const href = link.getAttribute('href');
            if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;

            let target;
            try {
                target = new URL(link.href, window.location.href);
            } catch (err) {
                return;
            }

            if (target.origin !== window.location.origin) return;
            if (target.searchParams.get('print') === '1') return;
            if (target.pathname === window.location.pathname && target.search === window.location.search) return;
            // Downloads keep the same page on screen, so they must not fade it.
            if (/export|download|\.csv$|\.pdf$/i.test(target.pathname + target.search)) return;

            if (isMobile()) {
                closeMobileSidebar();
            }

            document.documentElement.classList.add('is-leaving');

            // If the navigation is cancelled (a blocked popup, a stalled
            // request), bring the page back instead of leaving it faded.
            window.setTimeout(function() {
                document.documentElement.classList.remove('is-leaving');
            }, 1500);
        });

        // Returning through the browser cache must never restore a faded page.
        window.addEventListener('pageshow', function(event) {
            document.documentElement.classList.remove('is-leaving');
            if (event.persisted) {
                document.querySelectorAll('.main-content').forEach(function(mc) {
                    mc.style.opacity = '';
                    mc.style.transform = '';
                    mc.style.transition = '';
                });
            }
        });

        // Ensure all modals are direct children of document.body so they are never trapped
        // by container stacking contexts, keeping the backdrop firmly over all page content.
        document.querySelectorAll('.modal').forEach(function(modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });

        // ── One click, one submission ────────────────────────────────────
        // A slow connection made it possible to click "Record Payment" twice
        // and post the same cash twice. Forms that take over their own submit
        // (SweetAlert confirmations, AJAX checks) call preventDefault(), so the
        // guard below only ever touches a plain, about-to-navigate post.
        // Opt a form out with data-no-busy.
        document.addEventListener('submit', function(event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.hasAttribute('data-no-busy') || form.dataset.submitted === '1') return;

            window.setTimeout(function() {
                if (event.defaultPrevented || form.dataset.submitted === '1') return;
                form.dataset.submitted = '1';

                const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                buttons.forEach(function(button) {
                    button.disabled = true;
                    button.classList.add('is-busy');

                    if (button.tagName === 'INPUT') {
                        button.value = 'Submitting…';
                    } else if (!button.querySelector('.btn-busy-spinner')) {
                        const spinner = document.createElement('span');
                        spinner.className = 'btn-busy-spinner';
                        spinner.setAttribute('aria-hidden', 'true');
                        button.prepend(spinner);
                    }
                });

                // If the navigation never happens (blocked, offline), let the
                // form be usable again instead of leaving it dead.
                window.setTimeout(function() {
                    form.dataset.submitted = '';
                    buttons.forEach(function(button) {
                        button.disabled = false;
                        button.classList.remove('is-busy');
                        button.querySelectorAll('.btn-busy-spinner').forEach(function(spinner) {
                            spinner.remove();
                        });
                    });
                }, 15000);
            }, 0);
        });
    });
</script>
<?= $this->renderSection('scripts') ?>
<?= $this->include('theme/posthog') ?>
</body>
</html>
