# Changelog Versions

## 2023-11-01
- Se agrega comando para eliminar el duplicado de animales en los lotes.

## 2023-10-30
- Se incrementa el rate-limit de las apis a 1000 por minuto.

## 2023-10-27
### Added
- Se agrega aumento de rate-limit para las apis.
### Fixed
- Se corrige consulta de animales activos para obtener los lotes.

## 2023-10-25
### Added
- Se agrega comando para actualizar las fotos de los animales al nuevo dominio.

## v0.9.1
### Fixed
- Se omiten los animales vendidos en el conteo de animales en los lotes.

## v0.9.0
### Added
- Se agrega consumo de reportes legacy para animales por lote. 
- Se agrega consumo de reportes legacy para hembras por lote.
- Se omiten los animales eliminados en la carga de lotes.


## v0.8.0
### Added
- Se agrega comando para cargar a los lotes a la nueva estructura de datos.
- Se corrige borrado de cache en la api de obtener lote por animal id.

## v0.7.3
### Fixed
- Se corrige validacion de tipo de subscripcion.

## v0.7.2
### Fixed
- Se actualiza el tiempo de cache para obtener la información de un suscripcion a 5 minutos.

## v0.7.1
### Fixed
- Se corrige validación de acceso en la API de subscription para usuarios owner de una finca.

## v0.7.0
### Added
- Se agrega informacion adicional en la estadistica de suscripciones para mostrar renovaciones, vencimientos y % de renovacion.

## v0.6.0
### Added
- Se agrega api CRUD para el manejo de lotes de animales.
- Se agrega api CRUD para el relacionamiento de animales con los lotes.
- Se agrega api para obtener el lot dado un animal id.

## v0.5.0
### Added
- Se agrega api de visualización de estadisticas y metricas.

## v0.4.0
### Added
- Se agrega endpoint para validar la membresia de un usuario y el nivel de acceso a una finca.

## v0.3.0
### Added
- Se agrega comando para enviar notificaciones push con OneSignal como recordatorio para el vencimiento de suscripciones.

## v0.2.0
### Added
- Se agrega endpoint para actualizar atributos de un animal.

## v0.1.0
### Added
- Se agrega endpoint para eliminar invitación de usuario.
