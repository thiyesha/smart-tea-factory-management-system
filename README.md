# Smart Tea Factory Management System

A role-based PHP and MySQL application for managing tea-factory production,
inventory, quality control, machines, IoT sensor readings, alerts and
maintenance.

## Requirements

- XAMPP (Apache, PHP 8.1+ and MySQL/MariaDB)
- MySQL Workbench is optional
- PHP extensions: PDO and `pdo_mysql`

## Installation

1. Copy the `smart_tea_factory` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP.
3. In MySQL Workbench, run the scripts in this exact order:
   1. `database/schema.sql`
   2. `database/sample_data.sql`
   3. `database/views.sql`
   4. `database/triggers.sql`
   5. `database/procedures.sql`
4. Edit `config/database.php` if the MySQL account has a password.
5. If your folder or web address differs, update `BASE_URL` in
   `config/app.php`.
6. Visit `http://localhost/smart_tea_factory/public/login.php`.

All sample accounts use the development password `password`:

- `admin`
- `production.manager`
- `quality.officer`
- `operator01`
- `store.keeper`

Change these passwords before using the application outside a local
development environment.

## Roles

- Administrator: users, all operational modules, reports and audit log
- Production Manager: production, machines, alerts, maintenance and reports
- Quality Officer: quality inspections and reports
- Machine Operator: machines, sensor readings, alerts and maintenance
- Store Keeper: suppliers, raw materials, products, finished stock and reports

## Main folders

- `config/`: application, database and session configuration
- `database/`: schema, development data, views, triggers and procedures
- `public/`: browser-accessible pages and assets
- `src/`: authentication, authorization, CSRF, activity and CRUD metadata
- `templates/`: shared application layout

## Automatic sensor simulation

Open **Machines and Sensors → Live Readings**. The simulator starts
automatically and creates one reading per active sensor every five seconds.
It intentionally generates an occasional abnormal value so alert creation,
acknowledgement and resolution can be demonstrated. The simulator runs while
the monitoring page is open.

## Connecting a physical ESP32 or other IoT device

1. Change `SENSOR_API_KEY` in `config/app.php` to a long random secret.
2. Send an HTTP `POST` request to:
   `http://YOUR-PC/smart_tea_factory/public/api/ingest.php`
3. Include the HTTP header `X-API-Key` with that secret.
4. Send a JSON body such as:

```json
{
  "sensor_code": "SNS001",
  "value": 73.25
}
```

The `sensor_code` must belong to an active record in the `sensor` table.
The same threshold and alert service is used for both simulated and physical
readings.

## Security included

PDO prepared statements, bcrypt password verification, CSRF tokens,
session-ID regeneration, server-side role permissions, escaped HTML output,
POST-only state changes and activity logging are included.

## Resetting the database

Running `schema.sql` deletes and recreates `tea_factory_db`. Only do this when
you intend to remove its existing data. Then rerun the other four scripts in
the installation order above.
