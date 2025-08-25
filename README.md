# Client Manager (PHP + MySQL) — WAMP Starter Kit

Requisitos:
- WAMP (Windows)
- PHP 8+ y MySQL/MariaDB
- Visual Studio Code

## Instalación rápida
1) Copia la carpeta `client_manager` a `C:\wamp64\www\client_manager` (o a tu directorio `www`/`htdocs`).
2) Inicia WAMP (servicios Apache y MySQL).
3) Abre `http://localhost/phpmyadmin` e **importa** `sql/schema.sql` para crear DB/tablas y datos de ejemplo.
4) Abre `config/db.php` y ajusta las credenciales de MySQL si es necesario.
5) En tu navegador ve a: `http://localhost/client_manager/public/`
   - Admin: **admin@example.com** / **admin123**
   - Cliente: **cliente1@example.com** / **cliente123**

## Estructura de carpetas
- config/db.php .................. Conexión a MySQL (mysqli + UTF-8)
- includes/auth.php .............. Control de sesión y helpers de roles
- includes/header.php ............ Header con Home, Clientes General y buscador
- actions/login.php .............. Procesa login
- actions/client_save.php ........ Crea/edita cliente (solo Admin)
- actions/client_delete.php ...... Elimina cliente (solo Admin)
- actions/payment_save.php ....... Agrega pago (solo Admin)
- public/index.php ............... Login
- public/dashboard.php ........... Dashboard: gráfico (Chart.js) con % pagados / al día / en retraso. Click → lista filtrada
- public/clients.php ............. Lista de **todos** los clientes + buscador + botón “Historial”
- public/client_form.php ......... Alta/edición de cliente
- public/payment_form.php ........ Alta de pago
- public/client_detail.php ....... Historial del cliente + datos completos + mapa Leaflet con geolocalización editable (solo Admin)
- assets/css/style.css ........... Estilos básicos
- assets/js/app.js ............... JS básico (buscador, acciones)
- sql/schema.sql ................. Esquema SQL + datos demo (2 usuarios, 3 clientes y pagos de ejemplo)

## Notas
- Seguridad: contraseñas con `password_hash()` / `password_verify()`.
- Roles: `admin` puede crear/editar/eliminar; `client` solo lectura (tablas, historial).
- Gráfico: Chart.js (CDN). Click en un segmento → `clients.php?status=PAID|ONTIME|LATE`
- Mapa: Leaflet (OpenStreetMap). Click en el mapa posiciona el marcador. Botón “Guardar ubicación” guarda lat/lng en MySQL.
- SQL: la “situación” de pago se calcula por consulta (pagado si `paid_at` no es NULL y `paid_at <= due_date`; al día si `CURDATE() <= due_date` y no pagado; en retraso si `CURDATE() > due_date` y no pagado).

## Siguientes pasos (opcionales)
- Paginación y exportación a CSV/Excel.
- Validaciones extra (servidor/cliente).
- Permisos más finos por acción.
- Captura de ubicación desde móvil con geolocalización del navegador.
