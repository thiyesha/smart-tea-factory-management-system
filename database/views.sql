/*==============================================================
  Project  : Smart Tea Factory Production Monitoring and
             Inventory Management System
  File     : views.sql
  Purpose  : Create reporting and application views
==============================================================*/

USE tea_factory_db;


/*==============================================================
  1. TEA LEAF INVENTORY DETAILS
==============================================================*/

CREATE OR REPLACE VIEW vw_tea_leaf_inventory_details AS
SELECT
    tli.tea_leaf_id,
    tli.tea_leaf_code,
    tli.leaf_type,
    tli.quality_grade,
    tli.received_quantity,
    tli.current_stock,
    tli.unit,
    tli.received_date,
    tli.storage_location,
    tli.stock_status,

    s.supplier_id,
    s.supplier_code,
    s.supplier_name,
    s.estate_name,
    s.phone AS supplier_phone,
    s.district

FROM tea_leaf_inventory AS tli

INNER JOIN supplier AS s
    ON tli.supplier_id = s.supplier_id;


/*==============================================================
  2. PRODUCTION BATCH DETAILS
==============================================================*/

CREATE OR REPLACE VIEW vw_production_batch_details AS
SELECT
    pb.batch_id,
    pb.batch_code,
    pb.production_date,
    pb.total_leaf_weight,
    pb.expected_output,
    pb.batch_status,
    pb.remarks,

    u.user_id AS created_by_user_id,
    u.user_code AS created_by_user_code,
    u.full_name AS created_by_name,
    u.role AS created_by_role,

    pb.created_at,
    pb.updated_at

FROM production_batch AS pb

INNER JOIN users AS u
    ON pb.created_by = u.user_id;


/*==============================================================
  3. PRODUCTION PROCESS DETAILS
==============================================================*/

CREATE OR REPLACE VIEW vw_production_process_details AS
SELECT
    pp.process_id,

    pb.batch_id,
    pb.batch_code,
    pb.production_date,

    ps.stage_id,
    ps.stage_code,
    ps.stage_name,
    ps.sequence_number,
    ps.standard_duration_minutes,

    m.machine_id,
    m.machine_code,
    m.machine_name,
    m.machine_status,

    pp.start_time,
    pp.end_time,
    pp.duration_minutes,
    pp.input_quantity,
    pp.output_quantity,
    pp.waste_quantity,
    pp.process_status,
    pp.remarks

FROM production_process AS pp

INNER JOIN production_batch AS pb
    ON pp.batch_id = pb.batch_id

INNER JOIN production_stage AS ps
    ON pp.stage_id = ps.stage_id

INNER JOIN machine AS m
    ON pp.machine_id = m.machine_id;


/*==============================================================
  4. ACTIVE ALERT DETAILS
==============================================================*/

CREATE OR REPLACE VIEW vw_active_alert_details AS
SELECT
    a.alert_id,
    a.alert_type,
    a.severity,
    a.alert_message,
    a.generated_at,
    a.alert_status,

    s.sensor_id,
    s.sensor_code,
    s.sensor_name,
    s.sensor_type,
    s.unit,

    m.machine_id,
    m.machine_code,
    m.machine_name,
    m.location AS machine_location,

    a.acknowledged_by,
    u.full_name AS acknowledged_by_name,
    a.acknowledged_at

FROM alert AS a

INNER JOIN sensor AS s
    ON a.sensor_id = s.sensor_id

INNER JOIN machine AS m
    ON s.machine_id = m.machine_id

LEFT JOIN users AS u
    ON a.acknowledged_by = u.user_id

WHERE a.alert_status IN ('Active', 'Acknowledged');


/*==============================================================
  5. FINISHED PRODUCT INVENTORY
==============================================================*/

CREATE OR REPLACE VIEW vw_finished_product_inventory AS
SELECT
    fp.finished_product_id,
    fp.quantity,
    fp.manufacture_date,
    fp.expiry_date,
    fp.warehouse_location,
    fp.stock_status,

    tp.product_id,
    tp.product_code,
    tp.product_name,
    tp.tea_grade,
    tp.package_weight,
    tp.package_unit,
    tp.selling_price,

    pb.batch_id,
    pb.batch_code,
    pb.production_date

FROM finished_product AS fp

INNER JOIN tea_product AS tp
    ON fp.product_id = tp.product_id

INNER JOIN production_batch AS pb
    ON fp.batch_id = pb.batch_id;