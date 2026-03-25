# Revisión del sistema HISLIS (parte por parte)

> Fecha de revisión: 2026-03-24 (UTC)

## 1) Base del proyecto y dependencias

### Observación
- El proyecto está sobre Laravel con estructura estándar y módulos de negocio clínico bien separados por controladores/modelos.

### Qué mejorar
1. Definir una **política de versiones** (PHP, Laravel, Node) en README y CI.
2. Agregar validación automática de seguridad de dependencias (Composer audit y npm audit en CI).

---

## 2) Rutas y organización de dominio

### Observación
- Existen múltiples controladores bajo `app/Http/Controllers` para pacientes, vouchers, triage, laboratorio, etc.

### Qué mejorar
1. Migrar gradualmente a **rutas por dominio** (por ejemplo, `routes/admin/*.php`).
2. Introducir **Form Requests** para validar entrada en cada flujo crítico (pacientes, triage, vouchers, resultados).
3. Documentar permisos esperados por endpoint para reducir errores de autorización.

---

## 3) Modelo de datos (migraciones)

### Observación
- Hay buena cobertura de entidades clínicas y administrativas en migraciones.

### Qué mejorar
1. Reforzar integridad con más **índices y claves únicas** (p. ej. identificadores de paciente, correlativos de voucher).
2. Estandarizar nombres de campos para fechas clínicas y estados.
3. Añadir `softDeletes` en entidades sensibles donde aplique (si la política del negocio lo requiere).

---

## 4) Seguridad y permisos

### Observación
- Se usa `spatie/laravel-permission`, lo cual es una base adecuada para RBAC.

### Qué mejorar
1. Completar una **matriz de permisos por rol** y mantenerla versionada.
2. Agregar pruebas de autorización (quién puede ver/editar/eliminar).
3. Revisar protección de rutas de impresión/tickets para evitar exposición accidental de datos sensibles.

---

## 5) Capa de presentación (Blade)

### Observación
- Vistas Blade administrativas organizadas por módulo.

### Qué mejorar
1. Estandarizar componentes reutilizables (tablas, formularios, alertas, modales).
2. Mejorar accesibilidad (labels asociados, foco en modales, contraste, mensajes de error).
3. Añadir estados vacíos y loading consistentes para UX clínica.

---

## 6) Calidad operativa (logs, monitoreo, errores)

### Observación
- Configuración base de logging presente.

### Qué mejorar
1. Definir **logs estructurados** para eventos clínicos críticos (creación de atención, actualización de triage, emisión de voucher).
2. Incorporar trazabilidad por usuario (`user_id`) y correlación de request.
3. Crear un panel mínimo de métricas operativas (errores, tiempos de respuesta, colas si se usan).

---

## 7) Pruebas automatizadas

### Observación
- El proyecto tiene estructura de tests, pero por defecto parece plantilla inicial.

### Qué mejorar
1. Crear pruebas feature para flujos críticos:
   - registrar paciente,
   - generar voucher,
   - registrar triage,
   - registrar resultado de laboratorio.
2. Añadir pruebas de regresión para permisos.
3. Configurar cobertura mínima y ejecución en CI por pull request.

---

## 8) DevEx y despliegue

### Observación
- Falta documentación operativa específica del sistema más allá del esqueleto framework.

### Qué mejorar
1. Mantener README orientado al dominio HISLIS (hecho en este cambio).
2. Añadir checklist de despliegue (migraciones, cachés, colas, backups).
3. Crear ambiente de staging con datos anonimizados para QA funcional.

---

## Plan de mejora priorizado (90 días)

### Prioridad Alta (0–30 días)
- Implementar Form Requests en flujos críticos.
- Pruebas feature básicas de pacientes/vouchers/triage.
- Cerrar matriz de permisos por rol.

### Prioridad Media (31–60 días)
- Índices y restricciones adicionales en DB.
- Unificación de componentes Blade.
- Logs estructurados en eventos críticos.

### Prioridad Baja (61–90 días)
- Métricas operativas y tablero.
- Hardening avanzado de UX/accesibilidad.
- Refinamiento de arquitectura por dominios.

---

## Resultado esperado

Con este plan, HISLIS debería mejorar en:
- confiabilidad operativa,
- seguridad de acceso,
- trazabilidad clínica,
- velocidad de evolución del equipo.
