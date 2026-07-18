# Checklist de despliegue HISLIS

## Antes del despliegue

- [ ] Confirmar ventana de mantenimiento y responsables.
- [ ] Ejecutar pruebas automatizadas.
- [ ] Generar respaldo completo de base de datos y archivos subidos.
- [ ] Verificar variables `.env` sin credenciales de desarrollo.
- [ ] Revisar permisos de almacenamiento, caché y logs.

## Durante el despliegue

- [ ] Activar modo mantenimiento si aplica.
- [ ] Instalar dependencias con versiones bloqueadas.
- [ ] Ejecutar migraciones.
- [ ] Ejecutar seeders autorizados.
- [ ] Limpiar y reconstruir cachés de configuración, rutas y vistas.

## Después del despliegue

- [ ] Validar login, permisos, creación de paciente, orden, resultado, caja e inventario.
- [ ] Revisar dashboard gerencial y exportación CSV.
- [ ] Confirmar que no existan errores recientes en logs.
- [ ] Desactivar modo mantenimiento.
- [ ] Registrar versión desplegada, fecha, responsable y observaciones.
