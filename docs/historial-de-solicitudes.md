# Historial de solicitudes

Sector del área del estudiante que sustituye a la antigua tabla plana que vivía en
`UserSolicitudController::historial`. Ahora es un módulo con listado filtrable y ficha
de detalle por trámite.

- **Rutas**: `user.historial.index` (`/user/historial`) y `user.historial.show` (`/user/historial/{solicitud}`)
- **Acceso**: requiere sesión. El listado se limita siempre al usuario autenticado; la ficha pasa por `SolicitudPolicy`
- **Vistas**: `resources/views/user/historial/index.blade.php`, `show.blade.php`
- **Servicio**: `App\Services\HistorialSolicitudesService` (10 por página)

## Listado

Filtros disponibles, todos combinables y reflejados en la URL:

| Filtro | Parámetro | Comportamiento |
| --- | --- | --- |
| Texto libre | `q` | Busca en el código de seguimiento y en el motivo detallado |
| Estado | `estado` | Se descarta si no existe en el catálogo, para no vaciar el listado sin explicación |
| Trámite | `tipo` | Se descarta si el `tsi_id` no existe |
| Rango de fechas | `desde`, `hasta` | Sobre `sol_fecha_creacion`; un rango invertido no da error, simplemente no devuelve nada |
| Orden | `orden` | `recientes` (por defecto) u `antiguas` |

Las cuatro tarjetas de arriba (total, pendientes, aprobadas, rechazadas) se calculan en SQL sobre
**todas** las solicitudes del estudiante, no sobre el resultado filtrado: el resumen responde
"cómo voy", no "qué coincide con el filtro".

## Ficha de detalle

`HistorialSolicitudesService::ficha()` carga en una llamada lo que la pantalla necesita:

- Datos del trámite (código, tipo, periodo, prioridad, fechas, motivo)
- Línea de tiempo de estados, desde `historial_estado_solicitudes`, incluida la entrada de alta
- Adjuntos y cita de validación
- **Cambios registrados**: los registros de `historial_cambios` de esa solicitud, es decir, la
  bitácora vista desde el estudiante

Si quien abre la ficha es un administrador, la pantalla muestra un aviso de que está viendo el
trámite de otra persona en modo consulta.

## Autorización

`SolicitudPolicy::view()` concede el acceso si el usuario es admin **o** si la solicitud es suya.
El trait `AuthorizesRequests` se añadió al `Controller` base para poder usar `$this->authorize()`
en el controlador.

Las consultas parten siempre de `sol_usu_id`: el filtro por estudiante no es una comodidad del
listado, es la garantía de que un alumno no lea los trámites de otro.

## Cambio de rutas

La ruta `user.solicitudes.historial` (en `/user/historial`) ya no existe y **no tiene redirección**.
Si hay enlaces guardados a esa URL habrá que apuntarlos a `user.historial.index`.