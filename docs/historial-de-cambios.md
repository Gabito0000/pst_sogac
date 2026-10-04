# Historial de cambios (bitácora)

Módulo **independiente** del historial de solicitudes. El historial cuenta las solicitudes y su
recorrido por los estados; la bitácora cuenta todo lo que se modifica en el sistema, con su autor,
la pantalla desde la que salió y los valores antes y después.

Juntarlos en una sola pantalla haría que ninguna se entendiera, así que son sectores separados,
con tablas separadas y rutas separadas.

- **Rutas**: `admin.cambios.index` (`/admin/cambios`) y `admin.cambios.exportar` (`/admin/cambios/exportar`)
- **Acceso**: solo administradores (middleware `admin` del grupo `/admin`; no se repite la comprobación de rol)
- **Vistas**: `resources/views/admin/cambios/index.blade.php` + `resources/views/partials/cambios/diff.blade.php`
- **Servicios**: `App\Services\RegistroCambios` (escritura) y `App\Services\HistorialCambiosService` (lectura)

## Por qué una tabla aparte

`historial_estado_solicitudes` solo existe para las solicitudes y solo se escribe cuando un
administrador las mueve de estado. No registra altas de trámites, requisitos, preguntas frecuentes
ni chats. La bitácora necesita eso y además quién lo hizo, así que va a `historial_cambios`
(prefijo `hcm_`).

## Cómo se escribe: automático y sin tocar los controladores

El trait `App\Models\Concerns\RegistraCambios` se engancha a los eventos `created`, `updated` y
`deleted` de Eloquent. Está aplicado a `Solicitud`, `TipoSolicitud`, `Requisito`,
`PreguntasFrecuentes` y `HiloChat`.

Se eligió así para que no importe quién escribe en la base: si alguien aprueba una solicitud desde
el panel, corrige un requisito o edita una pregunta, el cambio queda registrado sin que ningún
método tenga que acordarse de hacerlo. También evita el fallo clásico de olvidar el registro en una
ruta nueva.

`RegistroCambios::$activo` es un interruptor global que los seeders y las pruebas apagan para poder
fabricar datos sin llenar la bitácora.

### Los valores se guardan ya traducidos

En el momento de escribir, las claves foráneas se resuelven a su nombre y los booleanos a "Sí"/"No".
Guardar `sol_eso_id: 1 → 2` no sirve ni para entenderlo ahora ni dentro de un año; además, una
bitácora con identificadores deja de tener sentido cuando el registro al que apunta ya no existe.

Las etiquetas legibles de cada columna (`ETIQUETAS`) y los resolutores de claves foráneas
(`RESOLUTORES`) están en `RegistroCambios`.

Dos detalles que hubo que cuidar:

- **La comparación de "ha cambiado" se hace sobre el valor entero, nunca sobre el texto recortado.**
  Los valores se guardan con un límite de 200 caracteres, así que comparar los valores ya
  truncados descartaba como "sin cambios" los motivos o respuestas largas editados solo al final.
- **Cuando el recorte traga la diferencia**, el valor nuevo añade su final entre corchetes para que
  la tabla de diferencias enseñe qué se editó en vez de dos celdas iguales.

Los nombres de claves foráneas resueltos **no se cachean**: una caché estática se filtraría entre
pruebas y devolvería un nombre viejo en procesos de larga duración (Octane, colas).

## Tabla `historial_cambios`

| Columna | Contenido |
| --- | --- |
| `hcm_id` | Clave |
| `hcm_usu_id` | Quién hizo el cambio. `nullOnDelete`: si se borra un usuario, su rastro sigue legible |
| `hcm_entidad` / `hcm_entidad_id` | Qué registro se tocó (nombre de clase + clave primaria) |
| `hcm_sol_id` | Solicitud relacionada, para poder mostrarle al estudiante su propia bitácora |
| `hcm_accion` | `creo`, `actualizo` o `elimino` |
| `hcm_resumen` | Una línea legible: "Actualizó Estado" |
| `hcm_cambios` | JSON con la lista de `{campo, anterior, nuevo}` |
| `hcm_ruta`, `hcm_ip`, `hcm_navegador` | Contexto de desde dónde salió |
| `hcm_fecha` | Cuándo |

## Listado y filtros

20 cambios por página. Filtros por texto libre (`q`, busca en resumen, IP y nombre del autor),
entidad, acción, autor y rango de fechas.

Dos detalles:

- El listado usa `leftJoin('usuarios')`: los cambios sin autor (cargas de consola, datos iniciales)
  se muestran igual, y con un join interior desaparecían del listado sin dejar rastro.
- Un rango de fechas invertido se avisa en pantalla en vez de devolver una lista vacía sin
  explicación.

Las tarjetas de arriba (total, altas, ediciones, bajas) cuentan sobre toda la bitácora, no sobre el
filtro, igual que en el panel de estadísticas.

## Exportación a CSV

`/admin/cambios/exportar` respeta exactamente los filtros del listado. Un cambio con varias
diferencias genera una fila por campo, porque en una hoja de cálculo es la única forma de no perder
nada. Va con BOM UTF-8 para que Excel muestre bien los acentos. El recorrido usa `cursor()` y está
limitado a 5000 filas: una exportación sin techo puede tumbar el servidor cuando la bitácora lleve
meses recogiendo datos.

## Corrección relacionada

`PreguntasFrecuentesService::update()` y `delete()` usaban actualizaciones y borrados masivos.
Las consultas de masas no disparan eventos de Eloquent, así que **las ediciones de preguntas
frecuentes no se registraban**. Ahora operate sobre la instancia.

## Datos de demostración

`HistorialCambiosDemoSeeder` reconstruye la bitácora a partir de las solicitudes existentes y de su
historial de estados. Hace falta porque `DatabaseSeeder` corre con `WithoutModelEvents`, y sin
eventos el trait no se dispara durante la siembra. El seeder borra `historial_cambios` antes de
rellenarla, así que se puede volver a ejecutar sin duplicar. Va registrado el último en
`DatabaseSeeder`.