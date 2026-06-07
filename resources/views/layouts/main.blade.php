<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'The Dosage Drip System')) - @yield('page-title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #7A4B2B; /* brand brown */
            --primary-dark: #5A331B;
            --muted: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-color: #2b2b2b;
            --bg: #f7f9fb;
            --card-bg: #ffffff;
            --border: #e6eef6;
            --radius: 10px;
            --sidebar-width: 260px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Figtree', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            background: var(--bg);
            color: var(--dark-color);
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }

        a { color: inherit; }

        /* Sidebar */
        .sidebar {
            position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh;
            background: linear-gradient(180deg, var(--primary-dark), var(--primary-color));
            padding: 0; z-index: 1000; transition: all .25s ease; box-shadow: 0 6px 20px rgba(39, 33, 29, 0.12);
        }

        .sidebar-brand { padding: 1.25rem 1rem; display:flex; align-items:center; gap:12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar-brand h4 { color: #fff; font-weight:700; margin:0; font-size:1.05rem; }
        .sidebar-brand .brand-img { width:64px; height:64px; border-radius:10px; object-fit:contain; background: rgba(255,255,255,0.06); padding:8px; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
        .sidebar-brand .brand-img:hover { transform: translateY(-3px) scale(1.05) rotate(3deg); box-shadow: 0 12px 28px rgba(0,0,0,0.2); }

        .sidebar-nav { padding: 0.65rem 0; }
        .sidebar-nav .nav-item { margin: 6px 10px; }
        .sidebar-nav .nav-link { display:flex; align-items:center; gap:12px; padding:10px 14px; color: rgba(255,255,255,0.88); border-radius: 8px; font-weight:600; text-decoration:none; }
        .sidebar-nav .nav-link i { width:22px; text-align:center; font-size:1.05rem; }
        .sidebar-nav .nav-link { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
        .sidebar-nav .nav-link:hover { background: rgba(255,255,255,0.1); transform: translateX(5px); }
        .sidebar-nav .nav-link:hover::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 3px; background: rgba(255,255,255,0.5); border-radius: 0 3px 3px 0; }
        .sidebar-nav .nav-link.active { background: rgba(255,255,255,0.15); color: #fff; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.05); border-left: 3px solid rgba(255,255,255,0.6); }

        .sidebar-heading { color: rgba(255,255,255,0.8); padding: 0.6rem 20px; font-size:0.72rem; text-transform:uppercase; letter-spacing: 1px; }

        /* Main layout */
        /* Use page-level left padding so the fixed sidebar doesn't overlap content */
        body { padding-left: var(--sidebar-width); transition: padding-left .25s ease; }
        .main-content { margin-left: 0; min-height:100vh; position:relative; z-index:2; }
        .top-navbar { background: white; padding: .9rem 1.25rem; box-shadow: 0 1px 6px rgba(15,23,42,0.06); display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:90; }
        .page-title { font-size:1.25rem; font-weight:700; margin:0; color:var(--dark-color); }
        .page-breadcrumb { color:var(--muted); font-size:0.88rem; }

        .content-area { padding: 1.5rem; }

        /* Themed alerts, toasts and modal styling (brown & white theme) */
        .alert {
            background: linear-gradient(180deg, #ffffff, #fbf8f6);
            border: 1px solid rgba(122,75,43,0.08);
            border-left: 6px solid rgba(122,75,43,0.14);
            color: var(--dark-color);
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(15,23,42,0.04);
        }
        .alert .fa-check-circle, .alert .fa-exclamation-circle, .alert .fa-exclamation-triangle { margin-right:8px; }
        .alert-success { border-left-color: var(--success-color); }
        .alert-info { border-left-color: var(--info-color); }
        /* Strong brown background for critical/danger alerts */
        .alert-danger {
            background: linear-gradient(180deg, var(--primary-color), var(--primary-dark));
            color: #fff;
            border-left-color: rgba(0,0,0,0.12);
            border: 0; /* remove subtle border so brown fills cleanly */
        }
        /* Icon colors to match theme; danger icons white for contrast */
        .alert .fa-check-circle, .alert .fa-exclamation-circle, .alert .fa-exclamation-triangle { margin-right:8px; color: var(--primary-color); }
        .alert-danger .fa-check-circle, .alert-danger .fa-exclamation-circle, .alert-danger .fa-exclamation-triangle { color: #fff; }
        .alert .btn-close { background: rgba(255,255,255,0.08); border-radius:6px; opacity:0.95; }

        .toast {
            background: #ffffff;
            border: 1px solid rgba(122,75,43,0.08);
            color: var(--dark-color);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(15,23,42,0.06);
        }

        .modal-content {
            border-radius: 12px;
            border: 1px solid rgba(122,75,43,0.06);
        }
        /* Toast notifications (professional brown & white) */
        #toastContainer { position: fixed; right: 1.25rem; top: 1.25rem; z-index: 2200; display:flex; flex-direction:column; gap:0.75rem; }
        /* Toast header (count + view all) */
        #toastHeader { position: fixed; right: 1.25rem; top: 0.6rem; z-index: 2210; display:flex; align-items:center; gap:8px; }
        #toastHeader .toast-badge { background: var(--primary-color); color:#fff; padding:6px 10px; border-radius:999px; font-weight:700; box-shadow: 0 6px 18px rgba(15,23,42,0.12); }
        #toastHeader .toast-controls { background: rgba(255,255,255,0.95); border-radius:8px; padding:6px 8px; border:1px solid rgba(122,75,43,0.06); box-shadow: 0 6px 18px rgba(15,23,42,0.06); }
        .app-toast { min-width: 340px; max-width: 420px; background: var(--card-bg); border-radius: 12px; padding: 14px 16px; box-shadow: 0 16px 40px rgba(15,23,42,0.12); border: 1px solid rgba(122,75,43,0.06); display:flex; gap:12px; align-items:flex-start; transform: translateX(12px); opacity:0; }
        .app-toast.toast-success { border-left: 6px solid var(--success-color); }
        .app-toast.toast-info { border-left: 6px solid var(--info-color); }
        .app-toast.toast-danger { background: linear-gradient(180deg, var(--primary-color), var(--primary-dark)); color: #fff; border-left: none; }
        .app-toast .toast-icon { width:44px; height:44px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background: linear-gradient(180deg, rgba(122,75,43,0.08), rgba(122,75,43,0.04)); color: var(--primary-dark); font-size:18px; }
        .app-toast.toast-success .toast-icon { background: linear-gradient(180deg, rgba(16,185,129,0.12), rgba(16,185,129,0.06)); color: var(--success-color); }
        .app-toast.toast-info .toast-icon { background: linear-gradient(180deg, rgba(59,130,246,0.12), rgba(59,130,246,0.06)); color: var(--info-color); }
        .app-toast.toast-danger .toast-icon { background: rgba(255,255,255,0.12); color:#fff; }
        .app-toast .toast-body { flex:1; }
        .app-toast .toast-title { font-weight:800; margin-bottom:4px; color:var(--primary-dark); }
        .app-toast.toast-danger .toast-title { color: #fff; }
        .app-toast .toast-message { color: rgba(43,43,43,0.85); }
        .app-toast.toast-danger .toast-message { color: rgba(255,255,255,0.92); }
        .app-toast .toast-close { background: transparent; border:0; color:inherit; font-size:18px; cursor:pointer; margin-left:8px; opacity:0.9; }
        .app-toast:focus { outline:2px solid rgba(122,75,43,0.12); }

        /* Animated enter/exit for toasts */
        @keyframes toastIn { from { transform: translateX(12px); opacity:0; } to { transform: translateX(0); opacity:1; } }
        @keyframes toastOut { from { transform: translateX(0); opacity:1; } to { transform: translateX(12px); opacity:0; } }
        .app-toast.toast-show { animation: toastIn 320ms cubic-bezier(.2,.9,.2,1) forwards; }
        .app-toast.toast-hide { animation: toastOut 240ms ease forwards; }

        /* Toast list modal */
        .toast-list-item { display:flex; gap:12px; align-items:flex-start; padding:10px; border-bottom:1px solid rgba(230,238,246,0.9); }
        .toast-list-item:last-child { border-bottom:0; }

        /* Loading overlay (coffee) */
        #loadingOverlay { position: fixed; inset: 0; display:none; align-items:center; justify-content:center; background: rgba(11,8,6,0.45); z-index: 3000; }
        /* Extra-large loading popup */
        #loadingOverlay .loader-box { background: #fff; padding:36px 40px; border-radius:16px; display:flex; gap:28px; align-items:center; box-shadow: 0 40px 100px rgba(15,23,42,0.32); border: 1px solid rgba(122,75,43,0.06); min-width: 760px; }
        #loadingOverlay .loader-text { font-weight:800; color:var(--primary-dark); font-size:1.25rem; }
        #loadingOverlay .loader-box div > div { color: var(--muted); font-size:0.98rem; }
        /* Coffee GIF sizing (extra large) */
        #loadingOverlay .coffee { width:240px; height:240px; object-fit:contain; }
        /* Progress bar under loading box */
        #loadingOverlay .progress-wrap { width:100%; margin-top:12px; }
        #loadingOverlay .progress { width:100%; height:8px; background: linear-gradient(90deg, rgba(122,75,43,0.06), rgba(122,75,43,0.03)); border-radius:6px; overflow:hidden; }
        #loadingOverlay .progress .bar { width:0%; height:100%; background: linear-gradient(90deg, var(--primary-color), var(--primary-dark)); transition: width 220ms linear; }
        /* Steam animation (kept for GIF fallback) */
        .steam { transform-origin: center bottom; animation: steamUp 1800ms infinite ease-in-out; opacity:0; }
        .steam.s2 { animation-delay: 300ms; }
        .steam.s3 { animation-delay: 600ms; }
        @keyframes steamUp {
            0% { transform: translateY(8px) scaleY(0.85); opacity: 0; }
            30% { opacity: 0.6; }
            100% { transform: translateY(-18px) scaleY(1.05); opacity: 0; }
        }

        @media (max-width: 1024px) {
            #loadingOverlay .loader-box { min-width: 560px; padding:28px 30px; gap:18px; }
            #loadingOverlay .coffee { width:180px; height:180px; }
            #loadingOverlay .loader-text { font-size:1.05rem; }
        }

        @media (max-width: 768px) {
            #loadingOverlay .loader-box { min-width: 420px; padding:22px 24px; gap:14px; }
            #loadingOverlay .coffee { width:140px; height:140px; }
            #loadingOverlay .loader-text { font-size:1rem; }
        }

        @media (max-width: 420px) {
            #loadingOverlay .loader-box { min-width: 300px; padding:14px 16px; gap:10px; flex-direction:column; align-items:center; }
            #loadingOverlay .coffee { width:110px; height:110px; }
            #loadingOverlay .loader-text { font-size:0.98rem; text-align:center; }
        }

        /* User dropdown (top-right) */
        .user-dropdown { display:flex; align-items:center; gap:10px; cursor:pointer; }
        .user-info { display:flex; flex-direction:column; text-align:right; margin-right:8px; }
        .user-name { font-weight:700; font-size:0.95rem; color:var(--dark-color); }
        .user-role { font-size:0.78rem; color:var(--muted); margin-top:2px; }
        .user-avatar { width:40px; height:40px; border-radius:50%; background:var(--primary-color); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; box-shadow: 0 4px 10px rgba(15,23,42,0.08); }
        @media (max-width: 768px) { .user-info { display:none; } .user-avatar { width:36px; height:36px; } }

        /* Cards */
        .card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: 0 6px 18px rgba(15,23,42,0.04); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card:hover { box-shadow: 0 12px 32px rgba(15,23,42,0.1); transform: translateY(-2px); }
        .card-header { background: transparent; border-bottom: 1px solid var(--border); padding: .9rem 1rem; font-weight:700; }
        .card-body { padding: 1.1rem; }

        /* Page content fade-in animation */
        .content-area { animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards; opacity: 0; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Buttons */
        .btn { border-radius: 8px; font-weight:600; padding: .5rem .9rem; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border: none; color: #fff; box-shadow: 0 4px 12px rgba(122, 75, 43, 0.3); }
        .btn-primary:hover { filter:brightness(1.1); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(122, 75, 43, 0.4); }
        .btn-primary:active { transform: translateY(0); box-shadow: 0 2px 8px rgba(122, 75, 43, 0.3); }
        .btn-outline-primary { color: var(--primary-color); border: 1px solid rgba(122,75,43,0.2); transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-outline-primary:hover { background: var(--primary-color); color: #fff; border-color: var(--primary-color); transform: translateY(-1px); }
        .btn-success { box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-success:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); }
        .btn-warning { box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-warning:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4); }
        .btn-danger { box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-danger:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4); }

        /* Tables */
        .table { background: transparent; }
        .table th { font-weight:700; color: #475569; font-size:0.82rem; border-bottom: 1px solid var(--border); padding: .8rem; vertical-align: middle; }
        .table td { padding: .8rem; border-bottom: 1px solid rgba(230,238,246,0.8); vertical-align: middle; }
        .table-hover tbody tr:hover { background: rgba(122,75,43,0.03); }
        .table-responsive { border-radius: 8px; }

        /* Forms */
        .form-control, .form-select { border: 1px solid var(--border); border-radius: 8px; padding: .5rem .7rem; min-height:38px; vertical-align:middle; }
        .form-control:focus, .form-select:focus { outline: none; box-shadow: 0 6px 18px rgba(122,75,43,0.06); border-color: rgba(122,75,43,0.18); }

        /* Form layout helpers */
        .form-row { display:flex; gap:1rem; align-items:center; margin-bottom:0.9rem; }
        .form-row .form-group { flex:1; min-width:0; }
        .form-row .form-label { min-width:140px; margin-bottom:0; display:inline-block; color:var(--muted); font-weight:600; }
        .form-actions { display:flex; gap:0.5rem; justify-content:flex-end; margin-top:.5rem; }

        /* Fallback for narrow screens */
        @media (max-width: 768px) {
            .form-row { flex-direction:column; align-items:stretch; }
            .form-row .form-label { min-width:0; }
            .form-actions { justify-content:flex-start; }
        }

        /* Card grid for dashboards & summary pages */
        .card-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; align-items:start; }
        .card-grid .card { height:100%; display:flex; flex-direction:column; }
        .card-grid .card .card-body { flex:1; }

        /* Pagination */
        .pagination { gap: .35rem; }
        .page-link { border-radius: 8px; border: 1px solid #e6eef6; color: var(--primary-dark); }
        .page-item.active .page-link { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }

        /* Responsive tweaks */
        @media (max-width: 992px) { 
            .sidebar { width: 70px; }
            body { padding-left: 70px; }
            .sidebar .sidebar-brand h4, .sidebar .nav-link span { display:none; } 
        }

        @media (max-width: 768px) {
            .top-navbar {
                align-items: flex-start;
                gap: .85rem;
                padding: .8rem 1rem;
            }

            .page-title {
                font-size: 1.05rem;
                line-height: 1.25;
            }

            .page-breadcrumb {
                display: block;
                max-width: 72vw;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .content-area { padding: 1rem; }

            .card-header {
                align-items: flex-start !important;
                flex-direction: column;
                gap: .75rem;
            }

            .card-header .btn,
            .card-header .btn-group,
            .card-header form {
                width: 100%;
            }

            .card-body { padding: .9rem; }

            .table-responsive {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table {
                min-width: 720px;
                white-space: nowrap;
            }

            form.row > .col-auto,
            form.row > [class*="col-"] {
                width: 100%;
                flex: 0 0 100%;
            }

            form.row .btn,
            form.row .btn-link,
            form.row .form-control,
            form.row .form-select {
                width: 100%;
            }

            .btn-group {
                display: flex;
                flex-wrap: wrap;
                gap: .4rem;
            }

            .btn-group > .btn {
                border-radius: 8px !important;
                flex: 1 1 auto;
            }

            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }

            #toastContainer {
                left: 1rem;
                right: 1rem;
                top: .85rem;
            }

            #toastHeader {
                left: 1rem;
                right: 1rem;
                justify-content: flex-end;
            }

            .app-toast {
                min-width: 0;
                max-width: none;
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            body {
                padding-left: 0;
                padding-bottom: 74px;
            }

            .sidebar {
                top: auto;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 64px;
                overflow-x: auto;
                overflow-y: hidden;
                padding: 0 .35rem;
                box-shadow: 0 -8px 24px rgba(39, 33, 29, 0.18);
            }

            .sidebar-brand,
            .sidebar-heading,
            .sidebar-divider {
                display: none;
            }

            .sidebar-nav {
                display: flex;
                align-items: center;
                gap: .25rem;
                height: 100%;
                padding: 0;
                min-width: max-content;
            }

            .sidebar-nav .nav-item {
                margin: 0;
            }

            .sidebar-nav .nav-link {
                width: 58px;
                height: 54px;
                justify-content: center;
                padding: .45rem;
                gap: 0;
                border-radius: 12px;
            }

            .sidebar-nav .nav-link i {
                width: auto;
                font-size: 1.1rem;
            }

            .sidebar-nav .nav-link span {
                display: none;
            }

            .sidebar-nav .nav-link.active {
                border-left: 0;
                box-shadow: inset 0 0 0 1px rgba(255,255,255,0.16);
            }

            .sidebar-nav .nav-link:hover {
                transform: none;
            }

            .main-content {
                min-width: 0;
            }

            .top-navbar {
                position: sticky;
                top: 0;
            }

            .content-area {
                padding: .85rem;
            }

            .card {
                border-radius: 8px;
            }

            .modal-dialog {
                margin: .75rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
            }
        }

    </style>

    @stack('styles')
</head>
<body>
    <div id="app">
        @auth
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-img d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.1); border-radius: 10px; width: 64px; height: 64px; padding: 8px; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;" onclick="document.getElementById('logoModal') && new bootstrap.Modal(document.getElementById('logoModal')).show()">
                    <i class="fas fa-coffee" style="font-size: 32px; color: #fff;"></i>
                </div>
                <h4>{{ config('app.name', 'The Dosage Drip System') }}</h4>
            </div>

            <div class="sidebar-nav">
                <div class="sidebar-heading">Main Menu</div>
                
                @if(auth()->user()->isAdmin())
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                @endif

                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                        <i class="fas fa-cash-register"></i>
                        <span>POS System</span>
                    </a>
                </div>

                @if(auth()->user()->isAdmin())
                <div class="sidebar-divider"></div>
                <div class="sidebar-heading">Management</div>

                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                        <i class="fas fa-mug-hot"></i>
                        <span>Products</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                        <i class="fas fa-boxes"></i>
                        <span>Inventory</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.sales') }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <i class="fas fa-users-cog"></i>
                        <span>Manage Staff</span>
                    </a>
                </div>
                @endif

                <div class="sidebar-divider"></div>
                <div class="sidebar-heading">Account</div>

                <div class="nav-item">
                    <a class="nav-link" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <div>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                    <small class="page-breadcrumb">@yield('breadcrumb', 'Welcome back, ' . auth()->user()->name)</small>
                </div>

                <div class="dropdown">
                    <div class="user-dropdown" data-bs-toggle="dropdown">
                        <div class="user-info">
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                        <div class="user-avatar">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                            @else
                                {{ substr(auth()->user()->name, 0, 1) }}
                            @endif
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        @if(auth()->user()->isAdmin())
                            <li><a class="dropdown-item py-2" href="{{ route('dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a></li>
                        @endif
                        <li><a class="dropdown-item py-2" href="{{ route('pos.index') }}"><i class="fas fa-cash-register me-2"></i> POS System</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-none" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <script>window.__FLASH_SUCCESS = @json(session('success'));</script>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-none" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <script>window.__FLASH_ERROR = @json(session('error'));</script>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> 
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
        @else
        @yield('guest-content')
        @endauth
    </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Logo modal -->
        <div class="modal fade" id="logoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-transparent border-0">
                    <div class="modal-body text-center p-0">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>

        <script>
                document.addEventListener('DOMContentLoaded', function(){
                        var el = document.querySelector('.sidebar-brand .brand-img');
                        if(!el) return;
                        el.addEventListener('click', function(e){
                                var modalEl = document.getElementById('logoModal');
                                if(modalEl){
                                        var modal = new bootstrap.Modal(modalEl);
                                        modal.show();
                                }
                        });
                });
        </script>
        <!-- Themed confirmation modal (replaces native confirm dialogs) -->
        <style>
            /* Larger confirm modal for readability */
            #appConfirmModal .modal-content { border-radius:12px; }
            #appConfirmModal .modal-body { padding:20px 22px; }
            #appConfirmModal .confirm-icon { width:72px; height:72px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:linear-gradient(180deg,rgba(122,75,43,0.06),rgba(122,75,43,0.03)); }
            #appConfirmModal #appConfirmTitle { font-size:1.25rem; font-weight:800; color:var(--primary-dark); }
            #appConfirmModal #appConfirmMessage { font-size:1.05rem; color:var(--muted); margin-top:6px; }
            #appConfirmModal .modal-footer { border-top:0; padding:14px 18px; }
            #appConfirmModal .modal-footer .btn { padding:.6rem .95rem; font-size:1rem; border-radius:8px; }
            @media (max-width:768px){ #appConfirmModal .modal-dialog { max-width:92%; } #appConfirmModal .confirm-icon{width:56px;height:56px;} #appConfirmModal #appConfirmTitle{font-size:1.05rem;} #appConfirmModal #appConfirmMessage{font-size:0.98rem;} }
        </style>

        <div class="modal fade" id="appConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-body">
                        <div style="display:flex;gap:18px;align-items:flex-start;">
                            <div class="confirm-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 9v4" stroke="#7A4B2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 17h.01" stroke="#7A4B2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div style="flex:1;">
                                <div id="appConfirmTitle">Please confirm</div>
                                <div id="appConfirmMessage">Are you sure?</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal" id="appConfirmCancel">Cancel</button>
                        <button type="button" class="btn btn-primary" id="appConfirmOk">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function(){
                // Promise-based confirm modal that matches theme
                var confirmModalEl = document.getElementById('appConfirmModal');
                var confirmModalInstance = confirmModalEl ? new bootstrap.Modal(confirmModalEl, { backdrop: 'static', keyboard: false }) : null;
                var confirmResolve = null;
                function appConfirm(message, opts){
                    opts = opts || {};
                    if(!confirmModalInstance) return Promise.resolve(window.confirm(message));
                    var title = opts.title || 'Please confirm';
                    var okText = opts.okText || 'OK';
                    var cancelText = opts.cancelText || 'Cancel';
                    document.getElementById('appConfirmTitle').textContent = title;
                    document.getElementById('appConfirmMessage').textContent = message || '';
                    document.getElementById('appConfirmOk').textContent = okText;
                    document.getElementById('appConfirmCancel').textContent = cancelText;
                    return new Promise(function(resolve){
                        confirmResolve = resolve;
                        confirmModalInstance.show();
                    });
                }

                // wire buttons
                if(document.getElementById('appConfirmOk')){
                    document.getElementById('appConfirmOk').addEventListener('click', function(){ if(confirmResolve) confirmResolve(true); confirmResolve = null; confirmModalInstance.hide(); });
                }
                if(document.getElementById('appConfirmCancel')){
                    document.getElementById('appConfirmCancel').addEventListener('click', function(){ if(confirmResolve) confirmResolve(false); confirmResolve = null; confirmModalInstance.hide(); });
                }

                // Global helper
                window.appConfirm = appConfirm;

                // Intercept clicks on elements with `data-confirm` attribute
                document.addEventListener('click', function(e){
                    var el = e.target.closest('[data-confirm]');
                    if(!el) return;
                    e.preventDefault();
                    var msg = el.getAttribute('data-confirm') || 'Are you sure?';
                    var title = el.getAttribute('data-confirm-title') || undefined;
                    var okText = el.getAttribute('data-confirm-ok') || undefined;
                    var cancelText = el.getAttribute('data-confirm-cancel') || undefined;
                    appConfirm(msg, { title: title, okText: okText, cancelText: cancelText }).then(function(yes){
                        if(!yes) return;
                        // proceed with action
                        if(el.tagName.toLowerCase()==='a'){
                            var href = el.getAttribute('href');
                            if(href) window.location.href = href;
                            return;
                        }
                        // if it's a button inside a form, submit the form
                        if(el.tagName.toLowerCase()==='button' || (el.getAttribute('type') && el.getAttribute('type').toLowerCase()==='submit')){
                            var form = el.closest('form');
                            if(form) return form.submit();
                        }
                        // if element has data-target-form selector, submit that
                        var targetForm = el.getAttribute('data-target-form');
                        if(targetForm){
                            var f = document.querySelector(targetForm);
                            if(f && f.tagName && f.tagName.toLowerCase()==='form') f.submit();
                        }
                    });
                }, true);
            })();
        </script>

        @stack('scripts')
        <!-- Toast header + container -->
        <div id="toastHeader" aria-hidden="false" style="display:none;">
            <div class="toast-badge" id="toastCount">0</div>
            <div class="toast-controls"><a href="#" id="toastViewAll">View all</a></div>
        </div>
        <div id="toastContainer" aria-live="polite" aria-atomic="true"></div>

        <!-- Loading overlay (coffee) -->
        <div id="loadingOverlay" role="status" aria-hidden="true">
            <div class="loader-box">
                <img src="{{ asset('images/coffee.gif') }}" alt="Loading" class="coffee" />
                <div>
                    <div class="loader-text">Loading — please wait</div>
                    <div style="font-size:0.85rem;color:var(--muted)">This may take a few seconds</div>
                    <div class="progress-wrap" aria-hidden="false"><div class="progress"><div class="bar" id="loadingProgressBar"></div></div></div>
                </div>
            </div>
        </div>

        <script>
            (function(){
                // Toast helper (professional: enter/exit animation, hover-pause, ARIA)
                window.__APP_TOASTS = window.__APP_TOASTS || [];
                function updateToastHeader(){
                    var header = document.getElementById('toastHeader');
                    var count = document.getElementById('toastCount');
                    if(!header || !count) return;
                    var n = window.__APP_TOASTS.length;
                    if(n>0){ header.style.display = 'flex'; count.textContent = n; } else { header.style.display = 'none'; }
                }

                function showToast(type, title, message, timeout){
                    timeout = typeof timeout === 'number' ? timeout : 5000;
                    var container = document.getElementById('toastContainer');
                    if(!container) return;
                    var t = document.createElement('div');
                    t.setAttribute('role','status');
                    t.setAttribute('aria-live','polite');
                    t.tabIndex = 0;
                    t.className = 'app-toast toast-' + (type || 'info');
                    // Inline SVG icons for sharp rendering
                    var svgIcon = '';
                    if(type==='success'){
                        svgIcon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="#2F855A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    } else if(type==='danger'){
                        svgIcon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94A2 2 0 0 0 22.18 18L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="#fff" stroke-width="0" fill="#fff" opacity="0.06"/><path d="M12 9v4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 17h.01" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    } else {
                        svgIcon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 16h-1v-4h-1" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 8h.01" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    }
                    t.innerHTML = '<div class="toast-icon">' + svgIcon + '</div>' +
                                  '<div class="toast-body"><div class="toast-title">' + (title || '') + '</div><div class="toast-message">' + (message || '') + '</div></div>' +
                                  '<button class="toast-close" aria-label="close">&times;</button>';
                    container.insertBefore(t, container.firstChild);
                    // store toast in history for view-all
                    window.__APP_TOASTS.unshift({ type: type||'info', title: title||'', message: message||'', time: (new Date()).toISOString() });
                    updateToastHeader();
                    // show animation
                    requestAnimationFrame(function(){ t.classList.add('toast-show'); t.style.opacity = ''; t.style.transform = ''; });

                    var start = Date.now();
                    var remaining = timeout;
                    var timer = null;
                    function hide(){
                        if(!t) return;
                        t.classList.remove('toast-show');
                        t.classList.add('toast-hide');
                        // remove after animation
                        setTimeout(function(){ if(t && t.parentNode) t.parentNode.removeChild(t); }, 260);
                    }
                    function startTimer(){ timer = setTimeout(hide, remaining); start = Date.now(); }
                    // close handler
                    t.querySelector('.toast-close').addEventListener('click', function(){ clearTimeout(timer); hide(); });
                    // pause on hover / focus
                    t.addEventListener('mouseenter', function(){ if(timer){ clearTimeout(timer); remaining -= (Date.now() - start); } });
                    t.addEventListener('mouseleave', function(){ startTimer(); });
                    t.addEventListener('focus', function(){ if(timer){ clearTimeout(timer); remaining -= (Date.now() - start); } });
                    t.addEventListener('blur', function(){ startTimer(); });

                    // start
                    startTimer();
                }

                // View all toast list
                document.getElementById('toastViewAll').addEventListener('click', function(e){
                    e.preventDefault();
                    var html = '';
                    (window.__APP_TOASTS || []).forEach(function(item){
                        var ic = item.type==='success' ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="#2F855A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : (item.type==='danger' ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 9v4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 17h.01" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 16h-1v-4h-1" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 8h.01" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
                        html += '<div class="toast-list-item"> <div style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:rgba(122,75,43,0.04);">'+ic+'</div><div><div style="font-weight:700">'+item.title+'</div><div style="opacity:.85">'+item.message+'</div><div style="font-size:12px;color:var(--muted);margin-top:6px">'+new Date(item.time).toLocaleString()+'</div></div></div>';
                    });
                    var win = window.open('','_blank');
                    // fallback: open a new small window with list
                    if(win){
                        win.document.write('<html><head><title>Notifications</title><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif;padding:16px;max-width:700px">'+html+'</body></html>');
                        win.document.close();
                    }
                });

                // Expose globally
                window.appShowToast = showToast;

                // Loading overlay helpers with progress bar
                var loadingTimer = null; var loadingProgressTimer = null; var loadingProgress = 0;
                function setLoadingProgress(p){
                    loadingProgress = Math.max(0, Math.min(100, p));
                    var bar = document.getElementById('loadingProgressBar'); if(bar) bar.style.width = loadingProgress + '%';
                }
                function startProgressAuto(){
                    // slowly increase progress toward 85%
                    clearInterval(loadingProgressTimer);
                    loadingProgressTimer = setInterval(function(){
                        if(loadingProgress < 85){ setLoadingProgress(loadingProgress + Math.random()*6 + 2); }
                    }, 420);
                }
                function stopProgressAuto(){ clearInterval(loadingProgressTimer); loadingProgressTimer = null; }
                function showLoading(){
                    var el = document.getElementById('loadingOverlay'); if(!el) return;
                    // small delay to avoid flashing for quick loads
                    loadingTimer = setTimeout(function(){ el.style.display = 'flex'; el.setAttribute('aria-hidden','false'); setLoadingProgress(6); startProgressAuto(); }, 180);
                }
                function hideLoading(){
                    var el = document.getElementById('loadingOverlay'); if(!el) return;
                    if(loadingTimer) { clearTimeout(loadingTimer); loadingTimer = null; }
                    // finish progress
                    stopProgressAuto(); setLoadingProgress(100);
                    setTimeout(function(){ if(el){ el.style.display = 'none'; el.setAttribute('aria-hidden','true'); setLoadingProgress(0); } }, 300);
                }
                window.appShowLoading = showLoading;
                window.appHideLoading = hideLoading;

                // Show loading on form submit and same-origin link clicks (except with data-no-loading)
                document.addEventListener('click', function(e){
                    var a = e.target.closest('a');
                    if(!a) return;
                    var href = a.getAttribute('href');
                    var target = a.getAttribute('target');
                    var no = a.getAttribute('data-no-loading');
                    if(!href || href.startsWith('#') || target==='__blank' || no!==null) return;
                    // only same-origin navigations
                    try{
                        var url = new URL(href, window.location.href);
                        if(url.origin === window.location.origin){ showLoading(); }
                    }catch(err){}
                }, true);

                document.addEventListener('submit', function(e){
                    var f = e.target;
                    if(f && f.tagName && f.tagName.toLowerCase()==='form'){
                        if(f.hasAttribute('data-no-loading')) return;
                        showLoading();
                    }
                }, true);

                // Wrap fetch to show loader if request takes longer than 500ms
                if(window.fetch){
                    var _fetch = window.fetch;
                    window.fetch = function(){
                        var timer = setTimeout(function(){ showLoading(); }, 500);
                        return _fetch.apply(this, arguments).then(function(res){ clearTimeout(timer); hideLoading(); return res; }).catch(function(err){ clearTimeout(timer); hideLoading(); throw err; });
                    };
                }

                // If jQuery present, hook into ajax events
                if(window.jQuery){
                    jQuery(document).on('ajaxStart', function(){ showLoading(); });
                    jQuery(document).on('ajaxStop ajaxError', function(){ hideLoading(); });
                }

                // Show flash session messages as toasts on load (blade outputs will create global variables)
                document.addEventListener('DOMContentLoaded', function(){
                    try{
                        if(window.__FLASH_SUCCESS){ showToast('success','', window.__FLASH_SUCCESS); }
                        if(window.__FLASH_ERROR){ showToast('danger','', window.__FLASH_ERROR); }
                        if(window.__FLASH_INFO){ showToast('info','', window.__FLASH_INFO); }
                    }catch(e){}
                });
            })();
        </script>
</body>
</html>
