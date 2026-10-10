<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BioMed CRM & PPM Management')</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Prevent sidebar from having visible scrollbars while allowing smooth vertical scrolling */
        #sidebar, #sidebar nav {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        #sidebar::-webkit-scrollbar, #sidebar nav::-webkit-scrollbar {
            display: none;
        }

        @media print {
            .no-print, #sidebar, #mobile-header, #ppm-toast-container, #ppmNotificationPanel {
                display: none !important;
            }
            #main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
            .print-card {
                box-shadow: none !important;
                border: 1px solid #1f2937 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Header -->
    <header id="mobile-header" class="md:hidden bg-slate-900 text-white p-3.5 flex items-center justify-between no-print sticky top-0 z-50 shadow">
        <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center text-white font-bold text-sm">
                <i class="fa-solid fa-notes-medical"></i>
            </div>
            <span class="font-bold text-sm tracking-tight">BioMed CRM &amp; PPM</span>
        </div>
        <div class="flex items-center space-x-2">
            <!-- Mobile Notification Bell Trigger -->
            <button onclick="toggleNotificationDropdown()" class="relative p-1.5 text-slate-300 hover:text-white focus:outline-none">
                <i class="fa-solid fa-bell text-lg {{ ($navNotificationCount ?? 0) > 0 ? 'text-amber-400' : '' }}"></i>
                @if(($navNotificationCount ?? 0) > 0)
                    <span class="absolute top-0 right-0 flex h-3.5 w-3.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex items-center justify-center rounded-full h-3.5 w-3.5 bg-rose-600 text-[9px] font-bold text-white leading-none">
                            {{ $navNotificationCount }}
                        </span>
                    </span>
                @endif
            </button>
            <button onclick="toggleMobileSidebar()" class="p-1.5 text-slate-300 hover:text-white focus:outline-none">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
        </div>
    </header>

    <!-- Mobile Backdrop Overlay -->
    <div id="sidebar-overlay" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-30 hidden md:hidden"></div>

    <!-- Sidebar Navigation: Sticky Top-0, Screen Height, Never Scrolls with Content -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-60 bg-slate-900 text-white flex flex-col transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0 md:sticky md:top-0 md:h-screen md:shrink-0 no-print shadow-xl overflow-hidden">
        <!-- Logo / Branding -->
        <div class="px-4 py-3.5 border-b border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-teal-600 to-teal-400 flex items-center justify-center text-white shadow text-sm">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div>
                    <h2 class="font-bold text-sm text-white tracking-wide leading-tight">BioMed CRM</h2>
                    <p class="text-[10px] text-teal-400 font-semibold tracking-wider uppercase">PPM Field Portal</p>
                </div>
            </div>
            <button onclick="toggleMobileSidebar()" class="md:hidden text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Navigation Links: Smooth vertical scrolling for smaller screens -->
        <nav class="flex-grow px-2.5 py-2 space-y-1 overflow-y-auto min-h-0">
            <div class="px-2 pt-1 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                PPM &amp; Installation
            </div>

            <a href="{{ route('ppm.index') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('ppm.index') && !request('ppm_status') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <i class="fa-solid fa-table-list w-5 text-sm {{ request()->routeIs('ppm.index') && !request('ppm_status') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>All PPM Records</span>
                </div>
            </a>

            <!-- PPM Due Today Link -->
            <a href="{{ route('ppm.index', ['ppm_status' => 'due_today']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request('ppm_status') === 'due_today' ? 'bg-amber-600 text-white shadow-xs' : 'text-amber-300 hover:bg-slate-800 hover:text-amber-200' }}">
                <div class="flex items-center">
                    <i class="fa-solid fa-bell w-5 text-sm {{ ($navDueToday->count() ?? 0) > 0 ? 'text-amber-400 animate-pulse' : 'text-slate-400' }}"></i>
                    <span>PPM Due Today</span>
                </div>
                @if(($navDueToday->count() ?? 0) > 0)
                    <span class="bg-amber-500 text-slate-900 text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">
                        {{ $navDueToday->count() }}
                    </span>
                @endif
            </a>

            <!-- PPM Overdue Link -->
            <a href="{{ route('ppm.index', ['ppm_status' => 'overdue']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request('ppm_status') === 'overdue' ? 'bg-rose-700 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <i class="fa-solid fa-triangle-exclamation w-5 text-sm text-rose-400"></i>
                    <span>Overdue PPM</span>
                </div>
                @if(($navOverdue->count() ?? 0) > 0)
                    <span class="bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                        {{ $navOverdue->count() }}
                    </span>
                @endif
            </a>

            <a href="{{ route('ppm.create') }}" 
               class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('ppm.create') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-circle-plus w-5 text-sm {{ request()->routeIs('ppm.create') ? 'text-white' : 'text-teal-400' }}"></i>
                <span>New Service Form</span>
            </a>

            <div class="px-2 pt-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                Master Data (Dynamic)
            </div>

            <a href="{{ route('customers.index') }}" 
               class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('customers.*') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-hospital-user w-5 text-sm {{ request()->routeIs('customers.*') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Customers / Hospitals</span>
            </a>

            <a href="{{ route('products.index') }}" 
               class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('products.*') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-boxes-stacked w-5 text-sm {{ request()->routeIs('products.*') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Products &amp; Equipment</span>
            </a>

            @can('manage-users')
                <div class="px-2 pt-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    System Administration
                </div>
                <a href="{{ route('users.index') }}" 
                   class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-user-shield w-5 text-sm {{ request()->routeIs('users.*') ? 'text-white' : 'text-indigo-400' }}"></i>
                    <span>User Accounts &amp; Roles</span>
                </a>
            @endcan

            <div class="px-2 pt-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                Exports &amp; Reports Hub
            </div>

            <a href="{{ route('reports.index') }}" 
               class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('reports.index') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-chart-pie w-5 text-sm {{ request()->routeIs('reports.index') ? 'text-white' : 'text-teal-400' }}"></i>
                <span>Reports &amp; Analytics Hub</span>
            </a>

            <a href="{{ route('reports.customer') }}" 
               class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('reports.customer') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-hospital-user w-5 text-sm {{ request()->routeIs('reports.customer') ? 'text-white' : 'text-teal-400' }}"></i>
                <span>Customer-Wise Report</span>
            </a>

            <a href="{{ route('reports.product') }}" 
               class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('reports.product') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-boxes-stacked w-5 text-sm {{ request()->routeIs('reports.product') ? 'text-white' : 'text-blue-400' }}"></i>
                <span>Product-Wise Report</span>
            </a>

            <a href="{{ route('reports.export') }}" 
               class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <i class="fa-solid fa-file-csv w-5 text-sm text-emerald-400"></i>
                <span>Export CSV Sheet</span>
            </a>
        </nav>

        <!-- Compact Server Status Footer -->
        <div class="px-3 py-2 border-t border-slate-800 mt-auto">
            <div class="bg-slate-800/90 rounded-lg px-2.5 py-1.5 border border-slate-700/60 flex items-center justify-between text-xs">
                <div class="flex items-center space-x-1.5 font-mono text-[11px] text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>{{ request()->getHttpHost() }}</span>
                </div>
                <span class="text-[10px] font-medium text-slate-400">BioMed CRM</span>
            </div>
        </div>
    </aside>

    <!-- Main Content Area: Fills Width Next to Sidebar Without Dead Space -->
    <div id="main-content" class="flex-1 flex flex-col min-w-0 min-h-screen">
        
        <!-- Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200 h-14 px-5 hidden md:flex items-center justify-between no-print sticky top-0 z-30 shadow-xs">
            <div class="flex items-center space-x-2 text-xs text-slate-500">
                <span class="font-medium text-slate-800">Hospital Equipment Field Operations</span>
                <span>/</span>
                <span class="text-teal-600 font-semibold">@yield('page_category', 'CRM & PPM Management')</span>
            </div>

            <div class="flex items-center space-x-3">
                <!-- PPM Notification Bell Dropdown Button -->
                <div class="relative" id="ppmNotificationContainer">
                    <button id="ppmBellBtn" onclick="toggleNotificationDropdown()" class="relative p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none" title="PPM Maintenance Notifications">
                        <i class="fa-solid fa-bell text-lg {{ ($navNotificationCount ?? 0) > 0 ? 'text-amber-500 animate-pulse' : 'text-slate-500' }}"></i>
                        
                        @if(($navNotificationCount ?? 0) > 0)
                            <span class="absolute top-1 right-1 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex items-center justify-center rounded-full h-4 w-4 bg-rose-600 text-[10px] font-bold text-white leading-none">
                                    {{ $navNotificationCount }}
                                </span>
                            </span>
                        @endif
                    </button>

                    <!-- Notification Flyout Dropdown -->
                    <div id="ppmNotificationPanel" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden text-left">
                        <div class="p-3.5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs tracking-wide">PPM Schedule Notifications</h4>
                                    <p class="text-[10px] text-slate-400">Date: {{ now()->format('d-M-Y') }}</p>
                                </div>
                            </div>
                            <button onclick="requestDesktopNotifications()" id="enableDesktopNotifBtn" class="text-[10px] bg-slate-800 hover:bg-slate-700 border border-slate-700 px-2 py-1 rounded text-teal-300 font-medium transition" title="Enable browser alerts">
                                <i class="fa-solid fa-desktop mr-1"></i> Alert
                            </button>
                        </div>

                        <!-- Notification Tabs -->
                        <div class="grid grid-cols-3 text-center border-b border-slate-100 bg-slate-50 text-[11px] font-bold p-1 gap-1">
                            <button onclick="showNotifTab('due_today')" id="tab_btn_due_today" class="py-1.5 rounded-lg text-amber-800 bg-amber-100 font-bold transition">
                                Today ({{ $navDueToday->count() ?? 0 }})
                            </button>
                            <button onclick="showNotifTab('overdue')" id="tab_btn_overdue" class="py-1.5 rounded-lg text-slate-600 hover:text-rose-700 transition">
                                Overdue ({{ $navOverdue->count() ?? 0 }})
                            </button>
                            <button onclick="showNotifTab('upcoming')" id="tab_btn_upcoming" class="py-1.5 rounded-lg text-slate-600 hover:text-teal-700 transition">
                                7 Days ({{ $navUpcoming->count() ?? 0 }})
                            </button>
                        </div>

                        <!-- Tab Content Lists -->
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 text-xs">
                            <!-- Due Today Tab -->
                            <div id="tab_content_due_today" class="block">
                                @forelse($navDueToday ?? [] as $item)
                                    <a href="{{ route('ppm.show', $item) }}" class="block p-3 hover:bg-amber-50/70 transition group border-l-4 border-amber-500">
                                        <div class="flex items-start justify-between">
                                            <span class="font-bold text-slate-900 group-hover:text-amber-800">{{ $item->equipment_name ?: $item->product_description }}</span>
                                            <span class="text-[10px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded">DUE TODAY</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 mt-0.5"><i class="fa-solid fa-hospital mr-1 text-slate-400"></i>{{ $item->customer_name }}</p>
                                        <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                                            <span>PO: {{ $item->purchase_order_no ?: 'N/A' }}</span>
                                            <span class="text-teal-700 font-semibold group-hover:underline">View Sheet &rarr;</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-6 text-center text-slate-400">
                                        <i class="fa-solid fa-circle-check text-2xl text-emerald-500 mb-1"></i>
                                        <p class="font-semibold text-slate-600 text-xs">No PPM due today</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">All scheduled equipment checks are up-to-date.</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Overdue Tab -->
                            <div id="tab_content_overdue" class="hidden">
                                @forelse($navOverdue ?? [] as $item)
                                    <a href="{{ route('ppm.show', $item) }}" class="block p-3 hover:bg-rose-50/70 transition group border-l-4 border-rose-500">
                                        <div class="flex items-start justify-between">
                                            <span class="font-bold text-slate-900 group-hover:text-rose-800">{{ $item->equipment_name ?: $item->product_description }}</span>
                                            <span class="text-[10px] bg-rose-100 text-rose-800 font-bold px-1.5 py-0.5 rounded">
                                                Overdue ({{ optional($item->next_ppm_date)->format('d-M-Y') }})
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 mt-0.5"><i class="fa-solid fa-hospital mr-1 text-slate-400"></i>{{ $item->customer_name }}</p>
                                        <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                                            <span>Status: {{ $item->status ?: 'Pending' }}</span>
                                            <span class="text-rose-700 font-semibold group-hover:underline">Action Needed &rarr;</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-6 text-center text-slate-400">
                                        <i class="fa-solid fa-circle-check text-2xl text-emerald-500 mb-1"></i>
                                        <p class="font-semibold text-slate-600 text-xs">No overdue PPM records</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Upcoming Tab -->
                            <div id="tab_content_upcoming" class="hidden">
                                @forelse($navUpcoming ?? [] as $item)
                                    <a href="{{ route('ppm.show', $item) }}" class="block p-3 hover:bg-teal-50/70 transition group border-l-4 border-teal-500">
                                        <div class="flex items-start justify-between">
                                            <span class="font-bold text-slate-900 group-hover:text-teal-800">{{ $item->equipment_name ?: $item->product_description }}</span>
                                            <span class="text-[10px] bg-teal-100 text-teal-800 font-bold px-1.5 py-0.5 rounded">
                                                Due {{ optional($item->next_ppm_date)->format('d-M-Y') }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 mt-0.5"><i class="fa-solid fa-hospital mr-1 text-slate-400"></i>{{ $item->customer_name }}</p>
                                        <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                                            <span>PO: {{ $item->purchase_order_no ?: 'N/A' }}</span>
                                            <span class="text-teal-700 font-semibold group-hover:underline">View Form &rarr;</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-6 text-center text-slate-400">
                                        <i class="fa-solid fa-calendar text-2xl text-slate-300 mb-1"></i>
                                        <p class="font-semibold text-slate-600 text-xs">No PPM due in the next 7 days</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Dropdown Footer -->
                        <div class="p-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                            <a href="{{ route('ppm.index', ['ppm_status' => 'due_today']) }}" class="text-amber-700 hover:text-amber-900 font-semibold text-[11px]">
                                <i class="fa-solid fa-filter mr-1"></i> View Today Due ({{ $navDueToday->count() ?? 0 }})
                            </a>
                            <a href="{{ route('ppm.index') }}" class="text-slate-500 hover:text-slate-800 font-medium text-[11px]">
                                All Records &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                @can('create-records')
                    <a href="{{ route('ppm.create') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition">
                        <i class="fa-solid fa-plus mr-1.5"></i> New Form
                    </a>
                @endcan

                <!-- Authenticated User Profile & Logout -->
                @auth
                    <div class="flex items-center space-x-2 pl-2 border-l border-slate-200">
                        <div class="text-right text-xs hidden sm:block">
                            <p class="font-bold text-slate-900 leading-tight">{{ auth()->user()->name }}</p>
                            @if(auth()->user()->isAdmin())
                                <span class="bg-rose-100 text-rose-800 font-extrabold text-[9px] px-1.5 py-0.5 rounded uppercase">Super Admin</span>
                            @elseif(auth()->user()->isManager())
                                <span class="bg-amber-100 text-amber-800 font-extrabold text-[9px] px-1.5 py-0.5 rounded uppercase">Manager</span>
                            @elseif(auth()->user()->isTechnician())
                                <span class="bg-teal-100 text-teal-800 font-extrabold text-[9px] px-1.5 py-0.5 rounded uppercase">Technician</span>
                            @else
                                <span class="bg-blue-100 text-blue-800 font-extrabold text-[9px] px-1.5 py-0.5 rounded uppercase">Viewer</span>
                            @endif
                        </div>
                        
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Log Out">
                                <i class="fa-solid fa-right-from-bracket text-base"></i>
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </header>

        <!-- Main Body: Compact padding -->
        <main class="flex-grow p-4 sm:p-5 w-full">
            @if(session('success'))
                <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between no-print shadow-xs text-xs font-medium">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-base">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between no-print shadow-xs text-xs font-medium">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-base">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-3 px-5 text-center text-xs text-slate-500 no-print">
            <p>Biomedical Equipment CRM &amp; PPM Tracker &bull; Dynamic Master Data &amp; Field Records</p>
        </footer>
    </div>

    <!-- On-Screen Toast Notification Container -->
    <div id="ppm-toast-container" class="fixed top-5 right-5 z-50 space-y-2 pointer-events-none no-print"></div>

    <script>
        // Mobile Sidebar Toggle with Backdrop
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
                if (overlay) {
                    if (sidebar.classList.contains('-translate-x-full')) {
                        overlay.classList.add('hidden');
                    } else {
                        overlay.classList.remove('hidden');
                    }
                }
            }
        }

        // Toggle notification flyout dropdown
        function toggleNotificationDropdown() {
            const panel = document.getElementById('ppmNotificationPanel');
            if (panel) {
                panel.classList.toggle('hidden');
            }
        }

        // Close dropdown when clicked outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('ppmNotificationContainer');
            const panel = document.getElementById('ppmNotificationPanel');
            if (panel && container && !container.contains(e.target)) {
                panel.classList.add('hidden');
            }
        });

        // Tabs in Notification Dropdown
        function showNotifTab(tabName) {
            const tabs = ['due_today', 'overdue', 'upcoming'];
            tabs.forEach(t => {
                const content = document.getElementById('tab_content_' + t);
                const btn = document.getElementById('tab_btn_' + t);
                if (content && btn) {
                    if (t === tabName) {
                        content.classList.remove('hidden');
                        btn.className = 'py-1.5 rounded-lg text-slate-900 bg-white shadow-xs font-bold transition border border-slate-200';
                    } else {
                        content.classList.add('hidden');
                        btn.className = 'py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition';
                    }
                }
            });
        }

        // Self-contained Pleasant Web Audio Chime (No external audio files required)
        function playPpmChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5 note
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.12); // A5 note
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.4);
                osc.start();
                osc.stop(ctx.currentTime + 0.4);
            } catch (err) {
                // Ignore audio autoplay restrictions
            }
        }

        // Browser Native Desktop Notification
        function requestDesktopNotifications() {
            if (!("Notification" in window)) {
                alert("Your browser does not support desktop notifications.");
                return;
            }
            Notification.requestPermission().then(permission => {
                const btn = document.getElementById('enableDesktopNotifBtn');
                if (permission === "granted") {
                    if (btn) btn.innerHTML = '<i class="fa-solid fa-check mr-1 text-emerald-400"></i> Enabled';
                    new Notification("BioMed PPM Notifications", {
                        body: "Desktop alerts enabled! You will be notified when equipment PPM is due.",
                        icon: "https://cdn-icons-png.flaticon.com/512/2966/2966327.png"
                    });
                } else {
                    if (btn) btn.innerHTML = '<i class="fa-solid fa-xmark mr-1 text-rose-400"></i> Denied';
                }
            });
        }

        // Display Floating Toast Notification
        function showPpmToast(title, message, linkUrl = null, type = 'amber') {
            const container = document.getElementById('ppm-toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto transform transition-all duration-300 ease-out translate-y-2 opacity-0 p-4 rounded-xl shadow-xl border flex items-start gap-3 max-w-sm ${
                type === 'amber' ? 'bg-amber-50 border-amber-300 text-amber-950' : 'bg-rose-50 border-rose-300 text-rose-950'
            }`;

            toast.innerHTML = `
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 ${type === 'amber' ? 'bg-amber-500 text-white' : 'bg-rose-600 text-white'}">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div class="flex-1 text-xs">
                    <h5 class="font-bold text-sm leading-tight">${title}</h5>
                    <p class="mt-0.5 text-slate-700 leading-normal">${message}</p>
                    ${linkUrl ? `<a href="${linkUrl}" class="inline-block mt-2 text-[11px] font-bold ${type === 'amber' ? 'text-amber-700 hover:text-amber-900' : 'text-rose-700 hover:text-rose-900'} underline">View Record &rarr;</a>` : ''}
                </div>
                <button onclick="this.closest('div').remove()" class="text-slate-400 hover:text-slate-700 text-sm leading-none ml-1">&times;</button>
            `;

            container.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });

            // Auto dismiss after 7 seconds
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-4');
                setTimeout(() => toast.remove(), 350);
            }, 7000);
        }

        // Check and trigger notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            @if(($navDueToday->count() ?? 0) > 0)
                // Trigger toast notification for today's due PPM
                setTimeout(() => {
                    playPpmChime();
                    showPpmToast(
                        "🔔 PPM Due Today!",
                        "{{ $navDueToday->count() }} equipment(s) are scheduled for PPM maintenance today ({{ now()->format('d-M-Y') }}).",
                        "{{ route('ppm.index', ['ppm_status' => 'due_today']) }}",
                        "amber"
                    );

                    // If desktop notifications already permitted, trigger native browser alert
                    if ("Notification" in window && Notification.permission === "granted") {
                        new Notification("BioMed: PPM Due Today!", {
                            body: "{{ $navDueToday->count() }} equipment(s) scheduled for maintenance today: {{ $navDueToday->first()->customer_name }} ({{ $navDueToday->first()->equipment_name }}).",
                            icon: "https://cdn-icons-png.flaticon.com/512/2966/2966327.png"
                        });
                    }
                }, 800);
            @elseif(($navOverdue->count() ?? 0) > 0)
                setTimeout(() => {
                    showPpmToast(
                        "⚠️ Overdue PPM Alert",
                        "{{ $navOverdue->count() }} equipment(s) have passed their scheduled PPM date.",
                        "{{ route('ppm.index', ['ppm_status' => 'overdue']) }}",
                        "rose"
                    );
                }, 1000);
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>
