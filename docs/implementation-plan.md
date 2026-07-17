# Plan de implementación del LIS HISLIS

## Enfoque general

La implementación se realizará por fases. No se debe intentar construir todo el LIS en una sola iteración. Cada fase debe entregar una base funcional, probada y auditable antes de continuar.

Reglas operativas por fase:

1. Antes de modificar código se deben listar cambios, archivos probables, migraciones y riesgos.
2. Cada fase debe incluir pruebas para los flujos afectados.
3. Deben ejecutarse migraciones en entorno de prueba cuando existan migraciones.
4. Deben ejecutarse pruebas automatizadas.
5. No se deben ocultar errores.
6. No se debe avanzar a la siguiente fase sin autorización explícita.
7. No se debe modificar producción.

## Fase 0: análisis y arquitectura

Entregables:

- Auditoría del repositorio.
- Arquitectura objetivo.
- Modelo de datos propuesto.
- Diagrama ER Mermaid.
- Reglas de negocio.
- Plan por fases.
- Riesgos y supuestos.

Archivos:

- `docs/architecture.md`
- `docs/database-design.md`
- `docs/business-rules.md`
- `docs/implementation-plan.md`

No incluye migraciones, modelos, controladores, vistas ni rutas funcionales.

## Fase 1: base del sistema

Objetivo:

- Configuración general.
- Sedes.
- Áreas LIS.
- Usuarios.
- Roles.
- Permisos.
- Auditoría.
- Catálogos básicos.

Entregables:

- Migraciones de sedes, áreas, accesos y auditoría.
- Seeders de roles/permisos/catálogos base.
- Policies iniciales.
- Pruebas de permisos y auditoría.

Riesgos:

- Conflicto entre tabla `areas` actual y áreas LIS.
- Duplicidad entre permisos route-based y Policies.

## Fase 2: pacientes y clientes

Objetivo:

- Pacientes normalizados.
- Médicos solicitantes.
- Empresas, clínicas y convenios.
- Tarifarios.
- Prevención de duplicados.

Entregables:

- Migraciones complementarias.
- Form Requests.
- Services/Actions de creación y actualización.
- Pruebas de duplicados y permisos.

## Fase 3: catálogo de exámenes

Objetivo:

- Exámenes.
- Perfiles.
- Muestras.
- Principios analíticos.
- Métodos.
- Equipos.
- Unidades.
- Rangos.

Entregables:

- Catálogo normalizado.
- Relaciones examen-área, examen-muestra, examen-método.
- Rangos por condiciones.
- Pruebas de método predeterminado y rangos.

## Fase 4: constructor de plantillas

Objetivo:

- Versiones de plantillas.
- Secciones.
- Campos.
- Opciones.
- Reglas condicionales.
- Vista previa.
- Publicación.

Entregables:

- UI Blade + Bootstrap + Alpine.js.
- Versionado de plantillas.
- Restricción de edición de plantillas publicadas usadas.
- Pruebas de opciones, publicación y versionado.

Riesgos:

- Validación de reglas condicionales sin ejecutar código arbitrario.

## Fase 5: órdenes y muestras

Objetivo:

- Órdenes de laboratorio.
- Precios.
- Perfiles.
- Estados.
- Muestras.
- Etiquetas.
- Trazabilidad.

Entregables:

- Correlativos.
- Creación transaccional de órdenes.
- Asociación de muestras a exámenes.
- Pruebas de cálculo de totales y estados.

## Fase 6: resultados

Objetivo:

- Captura dinámica.
- Validaciones.
- Rangos.
- Banderas.
- Resultados críticos.
- Aprobación.
- Correcciones.
- Reportes.

Entregables:

- Almacenamiento híbrido JSON + filas normalizadas.
- Flujo de validación técnica/profesional.
- Correcciones auditadas.
- Versiones de informe.

## Fase 7: microbiología

Objetivo:

- Cultivos.
- Aislamientos.
- Microorganismos.
- Antibiogramas.

Entregables:

- Estructuras especializadas para microbiología.
- Reportes microbiológicos.
- Pruebas de cultivos positivos, sin crecimiento y antibiogramas.

## Fase 8: inventario

Objetivo:

- Productos.
- Categorías.
- Unidades.
- Almacenes.
- Áreas.
- Lotes.
- Entradas.
- Salidas.
- Transferencias.
- Kardex.
- FEFO.

Entregables:

- Kardex inmutable.
- Saldos transaccionales.
- Bloqueos de fila.
- Prevención de stock negativo.
- Pruebas FEFO.

## Fase 9: consumo por prueba

Objetivo:

- Recetas.
- Consumo estimado.
- Consumo automático.
- Consumo real.
- Variaciones.
- Reprocesamientos.
- Reversiones.
- Costeo por prueba.

Entregables:

- `exam_consumable_requirements`.
- Intentos de procesamiento.
- Idempotencia de consumo.
- Integración con inventario.
- Pruebas de consumo duplicado y reprocesamiento.

## Fase 10: caja

Objetivo:

- Sesiones.
- Cobros.
- Pagos parciales.
- Pagos mixtos.
- Egresos.
- Arqueo.
- Cierre.

Entregables:

- Apertura/cierre de caja.
- Pagos asociados a órdenes.
- Movimientos compensatorios.
- Pruebas de diferencia de caja.

## Fase 11: finanzas operativas

Objetivo:

- Ingresos.
- Egresos.
- Categorías.
- Cuentas por cobrar.
- Cierres mensuales.

Entregables:

- Movimientos financieros.
- Periodos.
- Reglas de bloqueo por periodo cerrado.
- Pruebas de cierre y reapertura autorizada.

## Fase 12: inventarios mensuales

Objetivo:

- Conteos.
- Diferencias.
- Ajustes.
- Valorización.
- Cierre.

Entregables:

- Conteo físico.
- Snapshot o bloqueo según decisión.
- Ajustes aprobados.
- Reporte mensual.

## Fase 13: dashboard gerencial

Objetivo:

- KPIs.
- Gráficos.
- Comparaciones.
- Alertas.
- Rentabilidad.
- Exportaciones.

Entregables:

- Dashboard por pestañas.
- Consultas optimizadas.
- Cache o agregados cuando aplique.
- Pruebas de permisos y filtros.

## Fase 14: endurecimiento

Objetivo:

- Optimización.
- Seguridad.
- Pruebas integrales.
- Documentación.
- Respaldo.
- Procedimientos de despliegue.

Entregables:

- Revisión de seguridad.
- Índices finales.
- Pruebas E2E críticas.
- Manual operativo.
- Checklist de despliegue.

## Pruebas mínimas por flujo crítico

Se deben crear pruebas para:

- Creación de paciente.
- Prevención de duplicados.
- Creación de orden.
- Cálculo de totales.
- Pago parcial.
- Pago mixto.
- Apertura y cierre de caja.
- Diferencia de caja.
- Creación de plantilla.
- Versionado de plantilla.
- Campos select con opciones.
- Rangos según método.
- Registro de resultados.
- Validación.
- Corrección de resultados.
- Entrada de inventario.
- Transferencia.
- FEFO.
- Prevención de stock negativo.
- Consumo automático por examen.
- Prevención de consumo duplicado.
- Reprocesamiento.
- Reversión.
- Inventario físico.
- Ajuste autorizado.
- Cierre mensual.
- Restricciones de periodos cerrados.
- Permisos.
- Auditoría.

## Riesgos técnicos

1. Alcance amplio: requiere control estricto por fases.
2. Inventario y caja tienen alta sensibilidad transaccional.
3. Correcciones clínicas requieren trazabilidad legal y operativa.
4. Dashboard puede degradar rendimiento si consulta tablas operativas sin agregados.
5. Migración desde tablas actuales debe hacerse sin romper vistas/controladores existentes.
6. Plantillas dinámicas pueden volverse complejas si no se limita el motor de reglas.
7. Spatie Permission debe complementarse con Policies para evitar permisos demasiado genéricos.

## Supuestos

- Laravel 10 se mantiene.
- MySQL 8 será la base de datos objetivo.
- PHPUnit seguirá siendo el framework de pruebas.
- No se usará SPA.
- Se trabajará en español.
- La moneda principal será `PEN`.
- La zona horaria será `America/Lima`.

## Preguntas bloqueantes

1. ¿Los vouchers actuales serán reemplazados por órdenes de laboratorio o coexistirán como comprobantes?
2. ¿Se requiere facturación electrónica peruana dentro del alcance inicial?
3. ¿Se necesita multiempresa o solo multisede?
4. ¿Se autoriza paquete externo para auditoría si se justifica compatibilidad con Laravel 10?
5. ¿Se deben migrar datos existentes de desarrollo o se puede reiniciar la base durante fases tempranas?
