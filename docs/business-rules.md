# Reglas de negocio del LIS HISLIS

## Principios generales

1. Toda operación clínica, financiera o de inventario debe ser trazable.
2. Los registros confirmados no se eliminan ni modifican silenciosamente.
3. Las correcciones se realizan mediante versiones, estados o movimientos compensatorios.
4. Las operaciones sensibles se ejecutan en transacciones.
5. El sistema debe validar permisos, sede y área autorizada del usuario.
6. Los importes monetarios se guardan con `DECIMAL` y moneda principal `PEN`.
7. La zona horaria operativa será `America/Lima`.
8. No se deben guardar contraseñas, tokens ni secretos en auditoría.

## Seguridad, roles y permisos

Roles mínimos:

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

Reglas:

1. Un usuario solo accede a sedes autorizadas.
2. Un usuario solo accede a áreas autorizadas.
3. El superadministrador puede administrar configuración global.
4. Los permisos clínicos, financieros e inventario deben ser granulares.
5. Las Policies deben validar no solo el permiso, sino también la sede, área y estado del registro.

## Pacientes

1. Un paciente debe tener tipo y número de documento cuando aplique.
2. La prevención de duplicados debe considerar reglas configurables.
3. La edad se calcula a partir de la fecha de nacimiento; no debe almacenarse como dato fijo salvo snapshot clínico cuando sea necesario.
4. No se deben usar datos reales de pacientes en seeders.
5. La edición de datos identificatorios debe auditar valores anteriores y nuevos.

## Clientes, médicos y procedencias

1. Una orden puede asociarse a paciente particular, empresa, clínica, convenio o procedencia.
2. Los médicos solicitantes deben ser catálogo separado de usuarios internos.
3. Los tarifarios por cliente deben versionarse o conservar precio histórico en la orden.
4. El cambio de precio o descuento debe requerir permiso y auditoría.

## Catálogo de exámenes

1. Un examen puede pertenecer a varias áreas.
2. Un examen puede aceptar varios tipos de muestra.
3. Un examen puede tener múltiples métodos.
4. El método define principio analítico, equipo, unidad, decimales, tipo de resultado y rangos.
5. Los principios analíticos no deben repetirse como texto libre.
6. Los rangos de referencia pueden depender de sexo, edad, gestación, sede, equipo, unidad y condiciones especiales.

## Plantillas dinámicas

1. Una plantilla publicada no debe modificarse directamente si ya fue usada.
2. Todo cambio sobre una plantilla publicada debe crear una nueva versión.
3. Cada orden-examen debe guardar la versión exacta de plantilla utilizada.
4. Los tipos `select`, `radio`, `multiselect` y `checkbox_group` deben tener opciones configurables.
5. `select` y `radio` permiten una sola opción predeterminada.
6. `multiselect` y `checkbox_group` permiten varias opciones predeterminadas.
7. Las reglas condicionales no pueden ejecutar PHP, SQL ni código arbitrario.
8. Los campos calculados deben usar un evaluador seguro con operaciones permitidas.

## Órdenes de laboratorio

Estados sugeridos:

- Registrada.
- Pendiente de pago.
- Pagada parcialmente.
- Pagada.
- Muestra pendiente.
- Muestra tomada.
- Muestra recibida.
- En proceso.
- Parcialmente completada.
- Completada.
- Validada.
- Entregada.
- Cancelada.

Reglas:

1. La orden debe tener correlativo único por sede y serie si aplica.
2. Los precios, descuentos y totales se congelan en la orden.
3. Los descuentos requieren permiso y motivo.
4. Una orden puede contener exámenes individuales y perfiles.
5. Una orden puede tener múltiples muestras.
6. Una muestra puede servir a varios exámenes.
7. La cancelación debe conservar auditoría y motivo.
8. No se consume inventario al crear la orden.

## Muestras

1. Toda muestra debe tener trazabilidad de toma, recepción, rechazo y nueva toma si aplica.
2. El rechazo requiere motivo catalogado, usuario y fecha.
3. La recepción debe registrar usuario receptor, fecha y temperatura cuando corresponda.
4. Las etiquetas y códigos de barras deben relacionarse con la orden y la muestra.

## Resultados

Estados sugeridos:

- Pendiente.
- En proceso.
- Resultado ingresado.
- Validación técnica.
- Validación profesional.
- Aprobado.
- Reportado.
- Corregido.
- Cancelado.

Reglas:

1. El resultado debe conservar orden, examen, método, equipo, unidad, rango y plantilla usada.
2. El resultado completo se guarda como JSON estructurado.
3. Los valores consultables se guardan además en filas normalizadas.
4. Un resultado aprobado no se edita silenciosamente.
5. Toda corrección conserva valor anterior, nuevo valor, motivo, usuario y fecha.
6. La corrección genera nueva versión de informe y marca el informe como corregido.
7. Los valores críticos deben disparar alerta o notificación auditable.
8. Validar y aprobar resultados requiere permisos diferenciados.

## Microbiología

1. Microbiología no debe resolverse únicamente con JSON genérico.
2. Los cultivos deben registrar estado de crecimiento, recuento y observaciones.
3. Un cultivo puede tener uno o varios aislamientos.
4. Cada aislamiento se asocia a microorganismo identificado cuando aplique.
5. El antibiograma debe registrar antibiótico, interpretación, MIC y observaciones.
6. Deben existir estados para sin crecimiento, flora mixta, contaminación y cultivo positivo.

## Inventario

1. El kardex debe ser inmutable.
2. Los movimientos confirmados no se eliminan ni editan.
3. Las correcciones se hacen con movimientos compensatorios.
4. Debe existir saldo transaccional para no calcular stock sumando todo el kardex en cada pantalla.
5. Se debe impedir stock negativo salvo configuración excepcional y permiso especial.
6. Productos con vencimiento deben aplicar FEFO.
7. El costo histórico de movimientos no cambia si cambia el precio actual del producto.
8. Entradas, salidas, transferencias, ajustes y mermas requieren auditoría.
9. Los ajustes requieren motivo y permisos; algunos ajustes deben requerir aprobación.

## Consumo de reactivos por prueba

1. Cada examen y método puede tener receta de consumo teórico.
2. El consumo no ocurre al crear la orden.
3. El consumo se genera al confirmar procesamiento o corrida analítica.
4. La operación debe ser transaccional.
5. Debe aplicar FEFO sobre lotes utilizables.
6. Debe crear movimientos de inventario y asociarlos al orden-examen o corrida.
7. Debe impedir consumo duplicado con clave idempotente.
8. Reprocesar un examen crea un nuevo intento y consumo adicional.
9. El consumo real puede diferir del estimado, pero requiere motivo.
10. Cancelaciones posteriores deben generar reversión o merma, no borrar consumo original.

## Caja

1. No se puede registrar movimiento en caja cerrada.
2. Un cajero debe tener una sesión abierta para registrar cobros de caja.
3. Los pagos pueden ser parciales o mixtos.
4. Toda anulación o devolución conserva movimiento original y genera compensatorio.
5. El cierre calcula monto esperado, contado y diferencia.
6. Diferencias de caja requieren observación y, según monto, aprobación.
7. No se permite alterar caja cerrada sin proceso formal de reapertura auditada.

## Finanzas operativas

1. El módulo financiero registra ingresos y egresos operativos, no reemplaza contabilidad completa inicialmente.
2. Todo movimiento financiero debe tener tipo, categoría, sede, fecha, importe, medio de pago, documento y usuario.
3. Los periodos cerrados no se modifican directamente.
4. La reapertura de periodo requiere autorización y auditoría.
5. Las cuentas por cobrar deben reflejar órdenes con saldo pendiente y abonos.

## Inventario físico y cierre mensual

1. Un conteo físico debe tener fecha de corte, sede, almacén y alcance.
2. Debe definirse si bloquea movimientos o toma snapshot del stock.
3. Las diferencias generan ajustes solo tras aprobación.
4. El cierre mensual consolida stock inicial, entradas, salidas, consumo, mermas, vencimientos, ajustes, transferencias y stock final.

## Dashboard gerencial

1. El acceso al dashboard depende de permiso.
2. Las consultas deben filtrar por sede y área autorizada.
3. Los KPIs deben usar agregaciones optimizadas e índices.
4. Indicadores costosos deben cachearse o materializarse cuando sea necesario.
5. Deben existir vistas separadas: resumen ejecutivo, ventas y caja, finanzas, inventario, operaciones, calidad y rentabilidad.

## Auditoría

Eventos mínimos a auditar:

- Inicio de sesión.
- Cambios en pacientes.
- Creación y cancelación de órdenes.
- Cambios de precios y descuentos.
- Pagos, anulaciones y devoluciones.
- Apertura y cierre de caja.
- Resultados, validaciones y correcciones.
- Publicación de plantillas.
- Entradas, salidas, consumos, ajustes y transferencias.
- Inventarios físicos.
- Cierres mensuales.
- Cambios de configuración.

La auditoría debe omitir secretos, contraseñas y tokens.
