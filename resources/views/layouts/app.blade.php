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
                <a class="navbar-brand fw-bold text-primary" href="{{ route('home') }}">
                    <i class="fa-solid fa-hospital-user me-2"></i> {{ $setting->hospital_name ?? 'CENTRO MÉDICO' }}
                </a>
                
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navContent">
                    @include('layouts.partials.navigation')

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