@extends('layouts.app')

@section('content')
@php
    // Intentamos obtener la configuración, si no existe creamos un objeto vacío
    $setting = \App\Models\Setting::first();
    $hospitalName = $setting->hospital_name ?? 'HIS-LIS System';
    $hospitalLogo = ($setting && $setting->logo_path) ? asset('storage/' . $setting->logo_path) : asset('images/default-hospital.jpg');
@endphp

<div class="container-fluid p-0" style="height: 100vh; background-color: #fff;">
    <div class="row g-0 h-100">
        <div class="col-md-6 d-none d-md-flex flex-column align-items-center justify-content-center text-white p-5 position-relative" 
             style="background: linear-gradient(135deg, #004a99 0%, #002d5f 100%);">
            
            <div class="text-center z-index-10">
                <div class="mb-4">
                    @if($setting && $setting->logo_path)
                        <img src="{{ $hospitalLogo }}" alt="Logo" class="img-fluid shadow-lg" style="max-height: 150px; border-radius: 15px;">
                    @else
                        <div class="bg-white p-4 rounded-circle d-inline-block shadow-lg mb-3">
                             <i class="bi bi-hospital-fill text-primary" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>

                <h1 class="fw-bold mt-2">{{ $hospitalName }}</h1>
                <p class="lead opacity-75">Plataforma de Gestión Médica e Inteligencia de Laboratorio</p>
            </div>

            <div class="mt-auto small opacity-50">
                © {{ date('Y') }} {{ $hospitalName }} • Sistema de Gestión Interna.
            </div>
        </div>

        <div class="col-md-6 d-flex align-items-center justify-content-center bg-white">
            <div class="col-10 col-sm-8 col-lg-7">
                <div class="mb-5">
                    <h2 class="fw-bold text-dark mb-2">Iniciar Sesión</h2>
                    <p class="text-muted">Ingrese sus credenciales para acceder al panel de {{ $hospitalName }}.</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="login" class="form-label small fw-bold text-secondary text-uppercase">Usuario o Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-person text-primary"></i></span>
                            <input id="login" type="text" class="form-control form-control-lg bg-light border-0 @error('login') is-invalid @enderror" 
                                   name="login" value="{{ old('login') }}" required autofocus placeholder="Nombre de usuario o correo">
                        </div>
                        @error('login') <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label small fw-bold text-secondary text-uppercase">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-lock text-primary"></i></span>
                            <input id="password" type="password" class="form-control form-control-lg bg-light border-0 @error('password') is-invalid @enderror" 
                                   name="password" required placeholder="••••••••">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">Mantener sesión</label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold py-3" 
                                style="border-radius: 10px; background-color: #004a99; border: none;">
                            INGRESAR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    nav.navbar { display: none !important; }
    main { padding: 0 !important; }
    .z-index-10 { z-index: 10; }
</style>
@endsection