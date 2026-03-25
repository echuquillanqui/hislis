# HISLIS

Sistema web clínico construido con **Laravel** para gestionar pacientes, atención, triage, laboratorios, vouchers, inventario y configuración administrativa.

## Estado actual

Se identificó que el README tenía marcadores de conflicto de Git (`<<<<<<<`, `=======`, `>>>>>>>`).
Este documento queda normalizado y enfocado al proyecto HISLIS.

## Módulos principales

- Gestión de pacientes.
- Atención y triage.
- Exámenes y resultados de laboratorio.
- Vouchers y tickets de atención.
- Inventario (productos, almacenes y movimientos de stock).
- Seguridad y permisos (roles/permisos con `spatie/laravel-permission`).

## Stack

- PHP / Laravel
- Blade + Vite
- MySQL/MariaDB (u otro motor compatible con Laravel)

## Puesta en marcha rápida

1. Instalar dependencias:
   ```bash
   composer install
   npm install
   ```
2. Configurar entorno:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Configurar base de datos en `.env` y ejecutar migraciones:
   ```bash
   php artisan migrate
   ```
4. (Opcional) Compilar assets para desarrollo:
   ```bash
   npm run dev
   ```
5. Levantar servidor local:
   ```bash
   php artisan serve
   ```

## Revisión del sistema

Se agregó una revisión técnica estructurada con mejoras priorizadas en:

- Arquitectura
- Seguridad
- Calidad de datos
- UX
- Observabilidad
- Pruebas y despliegue

Ver: [`docs/SISTEMA_REVIEW.md`](docs/SISTEMA_REVIEW.md).
