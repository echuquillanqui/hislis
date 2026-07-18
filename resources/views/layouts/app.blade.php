<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- 1. Obtención dinámica de configuración --}}
    @php $setting = \App\Models\Setting::first(); @endphp
    <title>{{ $setting->hospital_name ?? config('app.name', 'HIS-LIS') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { --main-blue: #004a99; --bg-gray: #f4f7fa; }
        body { background-color: var(--bg-gray); font-family: 'Nunito', sans-serif; }
        
        .navbar-hosp {
            background-color: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1020;
            padding: 0.6rem 1rem;
        }

        .nav-link {
            color: #475569 !important;
            font-weight: 600;
            padding: 0.6rem 1.1rem !important;
            border-radius: 8px;
            transition: 0.2s;
        }

        .nav-link:hover, .nav-item.show .nav-link {
            color: var(--main-blue) !important;
            background-color: #f0f7ff;
        }

        .nav-link i { margin-right: 6px; color: var(--main-blue); }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        .user-pill {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 50px;
            padding: 4px 12px;
        }

        /* Configuración global Toastr */
        #toast-container > .toast { opacity: 1 !important; }
    </style>
</head>
<body>
    <div id="app">
        @auth
        <nav class="navbar navbar-expand-lg navbar-light navbar-hosp shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold text-primary" href="{{ url('/home') }}">
                    <i class="fa-solid fa-hospital-user me-2"></i> {{ $setting->hospital_name ?? 'CENTRO MÉDICO' }}
                </a>
                
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        
                        {{-- Módulo HIS: Solo SuperAdmin, Admin, Recepcion y Medico --}}
                        @hasanyrole('SUPERADMIN|ADMINISTRADOR|RECEPCION|MEDICO|super-admin|administrador|recepcion|tecnico-laboratorio|responsable-area|profesional-validador')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-hospital-user"></i> HIS (Pacientes)
                            </a>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="{{ route('patients.index') }}"><i class="fa-solid fa-user-plus me-2"></i> Admisión de Pacientes</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-calendar-check me-2"></i> Agenda de Citas</a></li>
                                <li><a class="dropdown-item" href="{{ route('areas.index') }}"><i class="fa-solid fa-layer-group me-2"></i> Gestión de Áreas</a></li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('templates.*') ? 'bg-primary text-white' : '' }}" 
                                    href="{{ route('templates.index') }}">
                                        <i class="fas fa-file-medical me-2"></i> Plantillas de Historias
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('bundles.*') ? 'bg-primary text-white' : '' }}" 
                                    href="{{ route('bundles.index') }}">
                                        <i class="fas fa-layer-group me-2"></i> Paquetes y Perfiles
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('vouchers.index') }}" 
                                    class="nav-link {{ request()->routeIs('vouchers.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-invoice-dollar me-2 {{ request()->routeIs('vouchers.*') ? 'text-white' : 'text-primary' }}"></i>
                                            <span>Ventas y Vouchers</span>
                                        </div>
                                    </a>
                                </li>

                                <li class="nav-item ms-3 small">
                                    <a href="{{ route('vouchers.create') }}" class="nav-link text-muted">
                                        <i class="fas fa-plus-circle me-1"></i> Generar Orden
                                    </a>
                                </li>
                                

                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('services.*') ? 'active bg-primary text-white' : '' }}" 
                                    href="{{ route('services.index') }}">
                                        <i class="fas fa-concierge-bell me-2"></i>
                                        <span>Asignar Plantilla</span>
                                    </a>
                                </li>
                                {{-- Enlace a Usuarios --}}
                                <li>
                                    <a class="dropdown-item" href="{{ route('users.index') }}">
                                        <i class="fa-solid fa-users text-success me-2"></i> Gestión de Personal
                                    </a>
                                </li>

                                {{-- Enlace a Roles y Permisos --}}
                                <li>
                                    <a class="dropdown-item" href="{{ route('roles.index') }}">
                                        <i class="fa-solid fa-shield-halved text-danger me-2"></i> Roles y Permisos
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-file-medical me-2"></i> Atención Médica</a></li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('attentions.index') ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-dark' }}" 
                                    href="{{ route('attentions.index') }}" 
                                    style="border-radius: 0; border-left: 4px solid {{ request()->routeIs('attentions.index') ? '#0d6efd' : 'transparent' }};">
                                        <div class="d-flex align-items-center">
                                            <i class="fa-solid fa-desktop me-2 {{ request()->routeIs('attentions.index') ? 'text-white' : 'text-primary' }}"></i>
                                            <span>MONITOR GLOBAL</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endhasanyrole

                        {{-- Módulo LIS: Solo SuperAdmin, Admin y Laboratorio --}}
                        @hasanyrole('SUPERADMIN|ADMINISTRADOR|LABORATORIO|super-admin|administrador|tecnico-laboratorio|responsable-area|profesional-validador|microbiologia')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('specialty_labs.*', 'lab_exams.*') ? 'active bg-primary text-white' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-microscope {{ request()->routeIs('specialty_labs.*', 'lab_exams.*') ? 'text-white' : '' }}"></i> LIS (Laboratorio)
                            </a>
                            <ul class="dropdown-menu shadow">

                                <li>
                                    <a class="dropdown-item py-2 {{ request()->routeIs('specialty_labs.*') ? 'bg-primary text-white' : '' }}" href="{{ route('specialty_labs.index') }}">
                                        <i class="fa-solid fa-microscope me-2 {{ request()->routeIs('specialty_labs.*') ? 'text-white' : 'text-muted' }}"></i> Especialidades
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item py-2 {{ request()->routeIs('lab_exams.*') ? 'bg-primary text-white' : '' }}" href="{{ route('lab_exams.index') }}">
                                        <i class="fa-solid fa-list-check me-2 {{ request()->routeIs('lab_exams.*') ? 'text-white' : 'text-muted' }}"></i> Catálogo de Exámenes
                                    </a>
                                </li>
                                <li><a class="dropdown-item py-2" href="{{ route('inventory.index') }}#productos"><i class="fa-solid fa-prescription-bottle-medical me-2 text-muted"></i> Insumos y reactivos</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('inventory.index') }}#kardex"><i class="fa-solid fa-clipboard-list me-2 text-muted"></i> Kardex de consumos</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-vial me-2"></i> Toma de Muestras</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-flask me-2"></i> Ingreso de Resultados</a></li>
                            </ul>
                        </li>
                        @endhasanyrole

                        {{-- Módulo Caja: Solo SuperAdmin, Admin y Cajero --}}
                        @hasanyrole('SUPERADMIN|ADMINISTRADOR|CAJERO|super-admin|administrador|caja')
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="fa-solid fa-cash-register"></i> Ventas/Caja</a>
                        </li>
                        @endhasanyrole

                        {{-- Módulo Logística: Solo SuperAdmin, Admin --}}
                        @hasanyrole('SUPERADMIN|ADMINISTRADOR|super-admin|administrador|almacen|compras')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-boxes-stacked"></i> Logística
                            </a>
                            <ul class="dropdown-menu shadow">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('inventory.*', 'monthly-inventory-counts.*') ? 'bg-primary text-white' : '' }}" href="{{ route('inventory.index') }}">
                                        <i class="fa-solid fa-warehouse me-2"></i> Inventario General
                                    </a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('inventory.index') }}#productos"><i class="fa-solid fa-box-open me-2"></i> Productos e Insumos</a></li>
                                <li><a class="dropdown-item" href="{{ route('inventory.index') }}#lotes"><i class="fa-solid fa-vials me-2"></i> Lotes y Saldos</a></li>
                                <li><a class="dropdown-item" href="{{ route('inventory.index') }}#kardex"><i class="fa-solid fa-clipboard-list me-2"></i> Kardex</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('monthly-inventory-counts.*') ? 'bg-primary text-white' : '' }}" href="{{ route('monthly-inventory-counts.index') }}"><i class="fa-solid fa-calendar-check me-2"></i> Inventarios mensuales</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-truck-ramp-box me-2"></i> Proveedores</a></li>
                            </ul>
                        </li>
                        @endhasanyrole
                    </ul>

                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item dropdown user-pill">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center p-0" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-user-doctor"></i>
                                </div>
                                <div class="text-start">
                                    <span class="d-block small fw-bold text-dark lh-1">{{ Auth::user()->username }}</span>
                                    <small class="text-muted" style="font-size: 0.65rem;">{{ Auth::user()->roles->first()->name ?? 'Staff' }}</small>
                                </div>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end mt-2 shadow border-0">
                                <a class="dropdown-item" href="#"><i class="fa-solid fa-id-card me-2"></i> Mi Perfil</a>
                                <a class="dropdown-item" href="{{ route('settings.index') }}"><i class="fa-solid fa-gears me-2"></i> Configuración</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fa-solid fa-power-off me-2"></i> Cerrar Sesión
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        @endauth

        <main class="{{ !Auth::check() ? '' : 'py-4 container-fluid px-lg-5' }}">
            @yield('content')
        </main>
    </div>

    {{-- 7. Manejo global de notificaciones de sesión --}}
    <script type="module">
        $(document).ready(function() {
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-bottom-right",
            };

            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @endif
        });
    </script>
</body>
</html>