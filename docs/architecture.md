# Arquitectura del LIS HISLIS

## Fase 0: análisis del repositorio

Fecha de análisis: 2026-07-17.

Este documento define la arquitectura objetivo para evolucionar el proyecto HISLIS hacia un LIS — Laboratory Information System — modular, seguro, auditable y ampliable, usando Laravel 10, Blade, Bootstrap 5, MySQL 8 y una organización de monolito modular.

## Estado actual del proyecto

El repositorio ya contiene una aplicación Laravel 10 con estructura estándar. La aplicación usa `laravel/ui` para autenticación tradicional, Blade para vistas, Bootstrap 5 mediante Vite y `spatie/laravel-permission` para roles y permisos.

Estructura actual relevante:

- `app/Http/Controllers`: controladores administrativos, pacientes, vouchers, laboratorio, plantillas, triaje y usuarios.
- `app/Models`: modelos Eloquent para pacientes, áreas, usuarios, vouchers, resultados, inventario básico y productos.
- `database/migrations`: migraciones iniciales para usuarios, permisos, pacientes, áreas, exámenes, vouchers, resultados e inventario básico.
- `resources/views/admin`: vistas Blade administrativas.
- `tests`: pruebas PHPUnit de ejemplo.
- `docs/SISTEMA_REVIEW.md`: revisión previa del sistema.

## Hallazgos principales

1. Existe una base funcional HIS/LIS inicial, pero todavía no cubre el alcance clínico, financiero, inventario, caja, auditoría y dashboard requerido.
2. La aplicación ya usa Laravel 10, PHP compatible, Blade, Bootstrap 5 y Spatie Permission, por lo que no se requiere reemplazar el stack.
3. La estructura actual es Laravel clásica; no existe todavía separación por dominios en `app/Domains`.
4. Los controladores concentran la lógica de negocio. Se recomienda migrar gradualmente a Form Requests, Actions, Services, Policies y Events.
5. `AuthServiceProvider` no registra Policies ni Gates personalizados todavía.
6. `RoutePermissionMiddleware` implementa autorización por nombre de ruta usando Spatie Permission, pero se debe complementar con Policies para operaciones de dominio.
7. El modelo actual de laboratorio es simple: `lab_exams`, `templates`, `order_items` y `lab_results` no cubren versionado de plantillas, métodos, rangos complejos, resultados normalizados ni validaciones.
8. El inventario actual usa `products`, `warehouses`, `inventories` y `stock_movements`, pero no modela kardex inmutable completo, lotes FEFO, saldos transaccionales, costos históricos ni consumos por examen.
9. No existe auditoría integral para eventos clínicos, financieros, inventario y configuración.
10. Las pruebas existentes son ejemplos básicos; se requiere una suite por flujos críticos.

## Arquitectura objetivo

Se recomienda un monolito modular sobre Laravel. El sistema seguirá siendo una sola aplicación y una sola base de datos, pero con separación por dominios para reducir acoplamiento.

Estructura objetivo gradual:

```text
app/
├── Domains/
│   ├── Administration/
│   ├── Users/
│   ├── Patients/
│   ├── Customers/
│   ├── Catalog/
│   ├── LaboratoryOrders/
│   ├── Samples/
│   ├── Templates/
│   ├── Results/
│   ├── Microbiology/
│   ├── Inventory/
│   ├── Purchasing/
│   ├── Cash/
│   ├── Finance/
│   ├── Reporting/
│   ├── Dashboard/
│   └── Audit/
├── Http/
├── Models/
└── Providers/
```

## Organización interna por dominio

Cada dominio podrá contener, según necesidad:

```text
app/Domains/{Domain}/
├── Actions/
├── Data/
├── Events/
├── Jobs/
├── Listeners/
├── Models/
├── Notifications/
├── Policies/
├── Services/
├── Support/
└── ViewModels/
```

Reglas de uso:

- `Actions`: una operación de negocio concreta, por ejemplo `CreateLaboratoryOrder`, `ValidateResult`, `ConfirmExamConsumption`.
- `Services`: lógica reutilizable y coordinadores de dominio.
- `Data`: DTOs simples para transportar datos validados.
- `Policies`: autorización por entidad y operación.
- `Events` y `Listeners`: procesos desacoplados, como auditoría, notificaciones o generación de reportes.
- `Jobs`: tareas pesadas o diferibles, como generación de PDFs, agregados de dashboard y notificaciones.
- `ViewModels`: preparación de datos para Blade sin cargar controladores.

## Estrategia de adopción gradual

No se recomienda mover todo el código existente a `app/Domains` en una sola fase. La transición debe ser evolutiva:

1. Mantener controladores, modelos y vistas actuales funcionando.
2. Introducir dominios nuevos para funcionalidades LIS no existentes.
3. Crear servicios/actions y usarlos desde controladores actuales cuando se refactoricen flujos existentes.
4. Agregar pruebas antes de reemplazar comportamiento legacy.
5. Migrar nombres y relaciones solo cuando exista cobertura suficiente.
6. Evitar eliminar tablas existentes hasta completar migraciones de datos y compatibilidad.

## Decisiones técnicas base

- Laravel 10 como framework principal.
- MySQL 8 como base de datos.
- Blade, Bootstrap 5 y Alpine.js para UI dinámica no SPA.
- PHPUnit como framework de pruebas, respetando el proyecto actual.
- `America/Lima` como zona horaria aplicativa.
- `PEN` como moneda principal.
- Spatie Permission se mantiene para RBAC.
- Policies y Gates complementarán Spatie para reglas de dominio.
- Jobs, Events, Listeners y Notifications se incorporarán donde aporten desacoplamiento.
- No usar React, Vue, Angular ni SPA.
- No instalar dependencias si Laravel resuelve la necesidad directamente.

## Seguridad y autorización

La seguridad debe combinar:

1. Autenticación Laravel UI actual.
2. Roles y permisos con Spatie Permission.
3. Policies para entidades sensibles.
4. Gates para capacidades transversales.
5. Acceso por sede y área mediante tablas pivote.
6. Auditoría en cada operación sensible.
7. Form Requests para validación consistente.
8. Protección CSRF en formularios Blade.
9. Escape por defecto de Blade.
10. Validación estricta de archivos y firmas.

Roles iniciales:

- Superadministrador.
- Administrador.
- Recepción.
- Caja.
- Flebotomista.
- Técnico de laboratorio.
- Responsable de área.
- Profesional validador.
- Microbiología.
- Almacén.
- Compras.
- Contabilidad.
- Gerencia.
- Auditor.
- Usuario de consulta.

## Auditoría transversal

Debe existir un dominio `Audit` con una tabla `audit_logs` para registrar:

- Usuario.
- Acción.
- Entidad.
- Identificador.
- Valores anteriores.
- Valores nuevos.
- IP.
- User agent.
- Fecha y hora.
- Motivo cuando aplique.

No se deben guardar contraseñas, tokens, secretos ni información sensible innecesaria.

## Patrones obligatorios para operaciones críticas

Operaciones críticas deben ejecutarse dentro de transacciones:

- Crear orden de laboratorio.
- Registrar pagos.
- Abrir y cerrar caja.
- Recibir o rechazar muestras.
- Ingresar, validar, aprobar o corregir resultados.
- Publicar plantillas.
- Registrar entradas, salidas, transferencias o ajustes de inventario.
- Confirmar consumo de materiales por examen o corrida.
- Cerrar periodos financieros.
- Cerrar inventarios físicos.

Cuando exista riesgo de concurrencia se deben usar bloqueos de filas con `lockForUpdate()`.

## Integración con frontend

El frontend debe continuar con Blade y Bootstrap 5. Alpine.js será recomendado para:

- Constructor de plantillas.
- Opciones dinámicas de campos select/radio/multiselect/checkbox_group.
- Reordenamiento simple de secciones/campos/opciones.
- Formularios condicionales.
- UI de pagos mixtos.
- UI de consumos reales vs estimados.

JavaScript plano se usará solo cuando Alpine.js no sea suficiente.

## Riesgos arquitectónicos

1. Refactorizar demasiadas entidades a la vez puede romper flujos actuales.
2. El inventario requiere especial cuidado en concurrencia, costos y FEFO.
3. Las correcciones de resultados e informes requieren versionado y auditoría estricta.
4. Los cierres financieros y de inventario bloquean modificaciones retroactivas y deben diseñarse antes de liberar caja/finanzas.
5. El dashboard puede generar consultas costosas si no se crean índices y agregados.
6. La autorización por nombre de ruta no sustituye Policies de dominio.
7. Las plantillas dinámicas deben evitar ejecución de código arbitrario guardado en base de datos.

## Supuestos

- El sistema será usado inicialmente por una organización con múltiples sedes.
- La facturación electrónica peruana no se implementará en las primeras fases salvo decisión explícita.
- Se conservará el proyecto actual y se evolucionará sin reescritura total.
- La base de datos será MySQL 8 en producción.
- No se introducirán datos reales de pacientes en seeders.

## Preguntas bloqueantes

1. ¿Los `vouchers` actuales deben seguir representando la orden principal, o se introducirá `laboratory_orders` como entidad clínica central y `vouchers` quedará como comprobante/cobro?
2. ¿La auditoría será implementación propia desde Fase 1 o se autoriza evaluar un paquete compatible con Laravel 10?
3. ¿Se requiere multiempresa o solo multisede?
4. ¿Facturación electrónica peruana será alcance temprano o posterior?
5. ¿Los datos actuales deben migrarse o el ambiente puede reiniciarse en desarrollo?
