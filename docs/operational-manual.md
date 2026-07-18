# Manual operativo HISLIS

## Seguridad y accesos

- Revisar usuarios activos, roles y permisos antes de cada salida a producción.
- Mantener cuentas nominales; no usar usuarios compartidos para caja, laboratorio ni administración.
- Validar que los roles de gerencia y auditoría solo tengan permisos de consulta o dashboard cuando no operen flujos transaccionales.

## Respaldo y recuperación

- Ejecutar respaldo completo de base de datos antes de migraciones.
- Conservar al menos una copia externa cifrada y una copia local reciente.
- Probar restauración en un entorno no productivo antes de declarar listo un despliegue mayor.

## Operación crítica

- Caja: abrir una sola sesión por cajero y sede; cerrar con arqueo documentado.
- Resultados: toda corrección requiere motivo trazable y nueva versión de informe.
- Inventario: registrar entradas, salidas y transferencias con kardex inmutable; cerrar inventarios mensuales después del conteo físico completo.
- Finanzas: no registrar movimientos en periodos cerrados salvo reapertura autorizada.

## Monitoreo gerencial

- Revisar diariamente órdenes, ingresos, egresos, rentabilidad operativa, stock bajo e inventarios pendientes.
- Exportar KPIs en CSV para auditorías y cierres administrativos.
