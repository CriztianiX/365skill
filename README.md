# Sistema Backend de SkyLink

## Resumen
API interna y servicio de streaming en tiempo real para gestionar reservas, pasajeros y notificaciones del sistema. Construido con Laravel (API/CRUD), Node.js (streaming WebSocket), MySQL (base de datos) y Redis (pub/sub).

## Requisitos
- docker
- docker-compose

Las instrucciones de prueba hacen un alto uso de docker/docker-compose para poder
distribuir la prueba con las menores dependencias posibles.

## Instrucciones para Correr el Proyecto
1. Clona el repositorio: `git clone https://github.com/CriztianiX/365skill`
2. No se necesita configurar nada, pero si desea modificar los valores por defecto edita `project/.env` y configura:
   ```env
   DB_CONNECTION=mariadb
   DB_HOST=mariadb
   DB_PORT=3306
   DB_DATABASE=skylink
   DB_USERNAME=admin
   REDIS_HOST=redis
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   REDIS_CLIENT=predis
   ```
Desde ahora en adelante todos los comandos deben ser ejecutados desde dentro del directorio "project"
Cambie al directorio "project"
```
cd project
```

3. Instala las dependencias de Laravel.
```
docker run --rm --interactive --tty --volume $PWD:/app composer install
```

4. Instale las dependencias de node
```
docker run --volume $PWD:/app --workdir /app --entrypoint npm  node:24.9.0 install
```

5. Ejecuta inicie el stack. 
```
docker-compose up
```
Esto puede tomar un tiempo, espere a ver en la consola:
```
laravel_1                 | laravel 22:08:09.64 INFO  ==> ** Laravel setup finished! **
laravel_1                 |
laravel_1                 | laravel 22:08:09.66 INFO  ==> ** Starting Laravel project **
laravel_1                 |
laravel_1                 |    INFO  Server running on [http://0.0.0.0:8000].
laravel_1                 |
laravel_1                 |   Press Ctrl+C to stop the server
laravel_1                 |
```

5. Obtenga el ID del contenedor corriendo Laravel y aplique las migraciones:
```
docker ps
CONTAINER ID   IMAGE                    COMMAND                  CREATED         STATUS              PORTS                              NAMES
329bc7a9d389   bitnami/laravel:latest   "/opt/bitnami/script…"   9 minutes ago   Up About a minute   3000/tcp, 0.0.0.0:8000->8000/tcp   365assist_laravel_1
5c838a7470f7   bitnami/mariadb:latest   "/opt/bitnami/script…"   25 hours ago    Up About a minute   0.0.0.0:23306->3306/tcp            365assist_mariadb_1

docker exec -it 329bc7a9d389 /bin/bash
root@329bc7a9d389:/app# php artisan migrate
root@329bc7a9d389:/app# php artisan db:seed

```

7. Simula eventos desde dentro de la aplicacion Laravel. Se ejecuta cada 5 segundos en un bucle.
```
docker exec -it 329bc7a9d389 /bin/bash
php artisan reservations:simulate-events
```

9. Simula el dashboard. Ejecute lo siguiente para imprimir actualizaciones WebSocket en la consola.
```
docker run --volume $PWD:/app --network 365test_network --workdir /app node:24.9.0 notificator-viewer.js
```

## Justificación Técnica de la Arquitectura
- **Node.js con Socket.io**: Ideal para streaming en tiempo real mediante WebSocket. Es ligero y escalable para difundir eventos a clientes conectados. En producción se podrìa usar poxa/pusher o algo similar.
- **Redis Pub/Sub**: Eficiente para la comunicación entre servicios. Evita la sobrecarga de sondeo a la base de datos y permite una arquitectura desacoplada con alta capacidad de procesamiento de eventos.
- **Índices en la Base de Datos**: Se utilizan índices en columnas como `flight_number`, `departure_time` y `status` en la tabla `reservations` para optimizar consultas de filtrado. Por ejemplo, un `EXPLAIN` en `SELECT * FROM reservations WHERE status = 'PENDING' AND departure_time > '2025-01-01'` muestra un acceso tipo "ref" en lugar de un escaneo completo, reduciendo significativamente el tiempo de consulta.
- **Escalabilidad**: Los servicios están modularizados en servicios de docker, permitiendo escalar instancias de Node.js detrás de un balanceador de carga. Redis soporta pub/sub distribuido para manejar múltiples suscriptores.

## Justificación de Patrones de Diseño Elegidos
- **Patrón Pub/Sub (Publicador/Suscriptor)**:
  - **Justificación**: Laravel publica eventos en el canal `reservation-events` de Redis, y Node.js se suscribe para difundirlos a clientes WebSocket. Este patrón desacopla los componentes (API y servicio de streaming), facilitando la escalabilidad (p. ej., agregar más instancias de Node.js) y reduciendo la latencia al evitar sondeo. Es ideal para notificaciones en tiempo real, como cambios de estado de reservas.
  - **Ventajas**: Permite que múltiples consumidores (dashboards, clientes) reciban actualizaciones sin afectar el sistema principal. Redis asegura un manejo eficiente de eventos de alto volumen.
- **No se usó CQRS**:
  - **Justificación**: El sistema no requiere una separación compleja entre lecturas y escrituras en esta etapa. El patrón de repositorio utilizado permite manejar tanto lecturas como escrituras con modelos Eloquent, manteniendo simplicidad. En el futuro, CQRS podría implementarse si se necesita optimizar consultas complejas o separar bases de datos para lecturas y escrituras.
- **Principios SOLID**:
  - **S (Responsabilidad Única)**: Cada clase tiene una sola responsabilidad. Por ejemplo, `ReservationService` maneja la lógica de dominio, mientras que `ReservationRepository` se encarga de la persistencia.
  - **O (Abierto/Cerrado)**: La arquitectura permite extensiones (p. ej., nuevas estrategias para manejar estados) sin modificar el código existente.
  - **I (Segregación de Interfaces)**: `ReservationRepositoryInterface` define un contrato claro, permitiendo cambiar la implementación (p. ej., de Eloquent a otro ORM).
  - **D (Inversión de Dependencias)**: Las dependencias se inyectan mediante constructores (p. ej., `ReservationService` recibe `ReservationRepositoryInterface`).
- **Patrón Repositorio**:
  - **Justificación**: Separa la lógica de acceso a datos de la lógica de negocio, facilitando pruebas unitarias y posibles cambios en la capa de persistencia (p. ej., cambiar MySQL por MongoDB). Mejora la mantenibilidad y permite mockear datos en pruebas.
- **Observer (Implícito)**:
  - **Justificación**: Aunque no se implementa explícitamente un patrón Observer, el flujo pub/sub actúa de manera similar: el servicio Node.js "observa" los eventos publicados por Laravel a través de Redis, reaccionando a cambios de estado en tiempo real.

## Notas Adicionales
- **Ejecutar Pruebas**:
  - Laravel: `php artisan test`
  - Node.js: `npm test` (usa Jest para pruebas de integración).
- **Documentación API**: La especificación OpenAPI se genera automáticamente y puede integrarse con herramientas como Scribe o Swagger.
La documentacion se encuentra dentro de project/resources/swagger/openapi.json
- **Diagrama ERD** (Mini):
  ```
  reservations
  - id (PK)
  - flight_number (string, indexed)
  - departure_time (datetime, indexed)
  - status (enum: PENDING, CONFIRMED, CANCELLED, CHECKED_IN, indexed)
  - created_at
  - updated_at

  passengers
  - id (PK)
  - name (string)
  - reservation_id (FK to reservations.id, indexed)
  - created_at
  - updated_at

  notifications
  - id (PK)
  - event_type (string, indexed)
  - data (json)
  - created_at
  - updated_at
  ```
  Relaciones: Uno-a-Muchos (una reserva tiene muchos pasajeros). Las notificaciones registran eventos.

---

Este README proporciona una guía clara para configurar y entender el proyecto, junto con justificaciones técnicas sólidas para las decisiones de arquitectura y patrones. Si necesitas ajustes o más detalles (p. ej., en la configuración de Redis o pruebas), ¡avísame!
