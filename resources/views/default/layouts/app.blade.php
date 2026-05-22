<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $data['page_title'] ?? 'Analytics') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Figtree', sans-serif;
            background: #f8fafc;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Sidebar ─────────────────────────────── */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #1e293b;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 300;
            transition: transform 0.25s ease;
        }
        .sidebar a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; color: #94a3b8; text-decoration: none;
            border-radius: 6px; margin: 2px 8px; font-size: 0.9rem;
        }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: #f1f5f9; }
        .sidebar-brand {
            padding: 18px 20px; color: #f1f5f9; font-weight: 700;
            font-size: 1.05rem; border-bottom: 1px solid #334155;
            margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;
        }
        .sidebar-close {
            display: none;
            background: none; border: none; cursor: pointer;
            color: #94a3b8; padding: 4px; border-radius: 4px;
        }
        .sidebar-close:hover { color: #f1f5f9; background: #334155; }
        .sidebar-section {
            padding: 6px 20px; font-size: 0.7rem; font-weight: 600;
            text-transform: uppercase; color: #475569; letter-spacing: 0.08em; margin-top: 12px;
        }
        .sidebar-footer { border-top: 1px solid #334155; padding: 12px 8px; margin-top: auto; }
        .sidebar-footer form { margin: 0; }
        .sidebar-footer button {
            width: 100%; margin-top: 4px; background: transparent; color: #94a3b8;
            justify-content: flex-start; gap: 10px; padding: 10px 12px;
            display: flex; align-items: center; border: none; cursor: pointer;
            border-radius: 6px; font-size: 0.9rem;
        }
        .sidebar-footer button:hover { background: #334155; color: #f1f5f9; }

        /* ── Backdrop ────────────────────────────── */
        .sidebar-backdrop {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 200;
        }
        .sidebar-backdrop.open { display: block; }

        /* ── Main wrapper ────────────────────────── */
        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar-spacer {
            width: 240px;
            flex-shrink: 0;
        }
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* ── Topbar ──────────────────────────────── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 20px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky; top: 0; z-index: 100;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-title { font-weight: 600; color: #1e293b; font-size: 0.95rem; }
        .topbar-email { font-size: 0.8rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
        .hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            color: #475569; padding: 6px; border-radius: 6px;
            flex-shrink: 0;
        }
        .hamburger:hover { background: #f1f5f9; }

        /* ── Content ─────────────────────────────── */
        .content { padding: 24px 20px; }

        /* ── Utilities ───────────────────────────── */
        .breadcrumb {
            display: flex; flex-wrap: wrap; gap: 4px; align-items: center;
            font-size: 0.82rem; color: #64748b; margin-bottom: 18px;
        }
        .breadcrumb a { color: #3b82f6; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb span + span::before { content: '/'; margin-right: 4px; color: #cbd5e1; }

        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; }

        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 8px; font-size: 0.875rem;
            font-weight: 500; border: none; cursor: pointer; text-decoration: none;
            white-space: nowrap;
        }
        .btn-primary { background: #3b82f6; color: #fff; }
        .btn-primary:hover { background: #2563eb; color: #fff; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-danger { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fecaca; }
        .btn-sm { padding: 5px 12px; font-size: 0.8rem; }

        .form-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 9px 12px; border: 1px solid #d1d5db;
            border-radius: 8px; font-size: 0.9rem; background: #fff; color: #1e293b;
        }
        .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .form-group { margin-bottom: 16px; }

        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table { width: 100%; border-collapse: collapse; font-size: 0.875rem; min-width: 540px; }
        .table th { text-align: left; padding: 10px 14px; font-weight: 600; color: #475569; border-bottom: 2px solid #e2e8f0; }
        .table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; color: #374151; vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: #f8fafc; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 0.875rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
        .alert-info    { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

        .input-group { display: flex; gap: 8px; }
        .input-group .form-control { flex: 1; min-width: 0; }

        /* Site cards */
        .site-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px; }
        .site-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; display: flex; flex-direction: column; gap: 14px; }
        .site-card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .site-card-name { font-weight: 700; color: #1e293b; font-size: 1rem; }
        .site-card-url { font-size: 0.78rem; color: #64748b; margin-top: 2px; word-break: break-all; }
        .site-card-stat { display: flex; align-items: baseline; gap: 6px; }
        .site-card-stat-value { font-size: 2rem; font-weight: 800; color: #1e293b; line-height: 1; }
        .site-card-stat-label { font-size: 0.8rem; color: #64748b; }
        .site-card-chart { height: 90px; }
        .site-card-error { color: #dc2626; font-size: 0.82rem; background: #fee2e2; border-radius: 6px; padding: 8px 12px; }

        .empty-state { text-align: center; padding: 48px 20px; color: #64748b; }
        .empty-state h3 { font-size: 1.1rem; font-weight: 600; color: #1e293b; margin-bottom: 8px; }

        #alert-container { position: fixed; top: 66px; right: 16px; z-index: 9999; width: min(320px, calc(100vw - 32px)); }

        /* ── Mobile (≤ 768px) ────────────────────── */
        @media (max-width: 480px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0,0,0,0.25);
            }
            .sidebar-close { display: flex; align-items: center; justify-content: center; }
            .sidebar-spacer { display: none; }
            .hamburger { display: flex; }
            .content { padding: 16px; }
            .topbar { padding: 0 14px; }
            .topbar-email { display: none; }
            .site-grid { grid-template-columns: 1fr; }
            .card { padding: 16px; }
            .card[style*="max-width"] { max-width: 100% !important; }
            .input-group { flex-wrap: wrap; }
        }

        @media (min-width: 481px) {
            body { flex-direction: row; }
        }
    </style>
</head>
<body>

{{-- Backdrop (mobile only) --}}
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

<div class="layout-wrapper">

    {{-- Sidebar spacer (desktop) --}}
    <div class="sidebar-spacer"></div>

    {{-- Sidebar --}}
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            {{ config('app.name') }}
            <button class="sidebar-close" id="sidebar-close" aria-label="Close menu">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="sidebar-section">Main</div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        @can('view sites')
        <a href="{{ route('sites.index') }}" class="{{ request()->routeIs('sites.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
            Sites
        </a>
        @endcan

        <div class="sidebar-footer">
            <a href="{{ route('profile.edit') }}">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ auth()->user()->name }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </nav>

    {{-- Main --}}
    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" id="hamburger" aria-label="Open menu">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="topbar-title">{{ $data['page_title'] ?? '' }}</span>
            </div>
            <div class="topbar-email">{{ auth()->user()->email }}</div>
        </div>

        <div class="content">
            <div id="alert-container"></div>

            @foreach(['success','error','warning','info'] as $type)
                @if(session($type))
                    <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }}">{{ session($type) }}</div>
                @endif
            @endforeach

            @if(!empty($data['breadcrumbs']))
            <div class="breadcrumb">
                @foreach($data['breadcrumbs'] as $crumb)
                <span>
                    @if(!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    @else
                        {{ $crumb['label'] }}
                    @endif
                </span>
                @endforeach
            </div>
            @endif

            @yield('content')
        </div>
    </div>

</div>{{-- /.layout-wrapper --}}

<script>
toastr.options = { positionClass: 'toast-top-right', timeOut: 4000, closeButton: true };
@if(session('success')) toastr.success("{{ addslashes(session('success')) }}"); @endif
@if(session('error'))   toastr.error("{{ addslashes(session('error')) }}"); @endif
@if(session('warning')) toastr.warning("{{ addslashes(session('warning')) }}"); @endif
@if(session('info'))    toastr.info("{{ addslashes(session('info')) }}"); @endif

// Off-canvas sidebar
(function () {
    const sidebar   = document.getElementById('sidebar');
    const backdrop  = document.getElementById('sidebar-backdrop');
    const hamburger = document.getElementById('hamburger');
    const closeBtn  = document.getElementById('sidebar-close');

    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    hamburger.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    backdrop.addEventListener('click', closeSidebar);

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });
})();
</script>

@stack('scripts')
</body>
</html>
