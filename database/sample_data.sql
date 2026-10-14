/*==============================================================
  Project  : Smart Tea Factory Production Monitoring and
             Inventory Management System
  File     : sample_data.sql
  Purpose  : Insert development and testing data
==============================================================*/

USE tea_factory_db;


/*==============================================================
  1. USERS
==============================================================*/

/*
  Development login password for all sample users: password

  This is a bcrypt password hash that can later be checked in PHP
  using password_verify().
*/

INSERT INTO users (
    user_code,
    username,
    password_hash,
    full_name,
    role,
    email,
    phone,
    account_status
)
VALUES
(
    'USR001',
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi',
    'Nimal Perera',
    'Administrator',
    'admin@teafactory.lk',
    '0712345678',
    'Active'
),
(
    'USR002',
    'production.manager',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi',
    'Kasun Silva',
    'Production Manager',
    'production@teafactory.lk',
    '0723456789',
    'Active'
),
(
    'USR003',
    'quality.officer',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi',
    'Tharushi Fernando',
    'Quality Officer',
    'quality@teafactory.lk',
    '0764567890',
    'Active'
),
(
    'USR004',
    'operator01',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi',
    'Saman Kumara',
    'Machine Operator',
    'operator01@teafactory.lk',
    '0755678901',
    'Active'
),
(
    'USR005',
    'store.keeper',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi',
    'Dilani Jayasinghe',
    'Store Keeper',
    'store@teafactory.lk',
    '0776789012',
    'Active'
);


/*==============================================================
  2. USER ACTIVITY LOG
==============================================================*/

INSERT INTO user_activity_log (
    user_id,
    activity_type,
    activity_description,
    ip_address
)
VALUES
(
    1,
    'LOGIN',
    'Administrator logged into the system',
    '127.0.0.1'
),
(
    2,
    'LOGIN',
    'Production manager logged into the system',
    '192.168.1.10'
),
(
    3,
    'LOGIN',
    'Quality officer logged into the system',
    '192.168.1.11'
),
(
    5,
    'INVENTORY_VIEW',
    'Store keeper viewed the inventory records',
    '192.168.1.12'
);


/*==============================================================
  3. SUPPLIERS
==============================================================*/

INSERT INTO supplier (
    supplier_code,
    supplier_name,
    estate_name,
    contact_person,
    phone,
    email,
    address,
    district,
    supplier_status
)
VALUES
(
    'SUP001',
    'Hill Country Tea Suppliers',
    'Green Valley Estate',
    'Sunil Wijesinghe',
    '0711122233',
    'greenvalley@example.com',
    'Nuwara Eliya Road, Nanu Oya',
    'Nuwara Eliya',
    'Active'
),
(
    'SUP002',
    'Uva Fresh Leaf Suppliers',
    'Silver Hill Estate',
    'Ruwan Bandara',
    '0722233344',
    'silverhill@example.com',
    'Badulla Road, Bandarawela',
    'Badulla',
    'Active'
),
(
    'SUP003',
    'Southern Tea Growers',
    'Ruhuna Tea Estate',
    'Chaminda Perera',
    '0753344455',
    'ruhunatea@example.com',
    'Akuressa Road, Matara',
    'Matara',
    'Active'
);


/*==============================================================
  4. TEA LEAF INVENTORY
==============================================================*/

INSERT INTO tea_leaf_inventory (
    tea_leaf_code,
    supplier_id,
    leaf_type,
    quality_grade,
    received_quantity,
    current_stock,
    unit,
    received_date,
    storage_location,
    stock_status
)
VALUES
(
    'LEAF001',
    1,
    'Fresh Tea Leaves',
    'A',
    1000.00,
    850.00,
    'kg',
    '2026-07-20',
    'Raw Material Store A',
    'Available'
),
(
    'LEAF002',
    2,
    'Fresh Tea Leaves',
    'B',
    800.00,
    620.00,
    'kg',
    '2026-07-21',
    'Raw Material Store A',
    'Available'
),
(
    'LEAF003',
    3,
    'Fresh Tea Leaves',
    'A',
    1200.00,
    950.00,
    'kg',
    '2026-07-22',
    'Raw Material Store B',
    'Available'
);


/*==============================================================
  5. OTHER MATERIALS
==============================================================*/

INSERT INTO other_material (
    material_code,
    material_name,
    category,
    unit,
    current_stock,
    minimum_stock,
    storage_location,
    material_status
)
VALUES
(
    'MAT001',
    '100 g Tea Pouches',
    'Packaging',
    'pieces',
    5000.00,
    1000.00,
    'Packaging Store A',
    'Available'
),
(
    'MAT002',
    '250 g Tea Boxes',
    'Packaging',
    'pieces',
    3000.00,
    500.00,
    'Packaging Store A',
    'Available'
),
(
    'MAT003',
    'Machine Lubricant',
    'Maintenance',
    'litres',
    50.00,
    10.00,
    'Maintenance Store',
    'Available'
),
(
    'MAT004',
    'Cleaning Detergent',
    'Cleaning',
    'litres',
    30.00,
    8.00,
    'Cleaning Store',
    'Available'
),
(
    'MAT005',
    'Diesel Fuel',
    'Fuel',
    'litres',
    500.00,
    100.00,
    'Fuel Storage Area',
    'Available'
);


/*==============================================================
  6. TEA PRODUCT CATALOGUE
==============================================================*/

INSERT INTO tea_product (
    product_code,
    product_name,
    tea_grade,
    package_weight,
    package_unit,
    selling_price,
    description,
    product_status
)
VALUES
(
    'PRD001',
    'Premium BOP Tea 100 g',
    'BOP',
    100.00,
    'g',
    450.00,
    'Premium broken orange pekoe black tea',
    'Active'
),
(
    'PRD002',
    'Classic OP Tea 250 g',
    'OP',
    250.00,
    'g',
    950.00,
    'Orange pekoe loose-leaf black tea',
    'Active'
),
(
    'PRD003',
    'Premium BOPF Tea 500 g',
    'BOPF',
    500.00,
    'g',
    1750.00,
    'Strong and finely processed black tea',
    'Active'
);
/*==============================================================
  7. PRODUCTION STAGES
==============================================================*/

INSERT INTO production_stage (
    stage_code,
    stage_name,
    sequence_number,
    standard_duration_minutes,
    description,
    stage_status
)
VALUES
(
    'STG001',
    'Withering',
    1,
    720,
    'Fresh tea leaves are spread out to reduce their moisture content',
    'Active'
),
(
    'STG002',
    'Rolling',
    2,
    45,
    'Withered leaves are rolled to break their cell structure',
    'Active'
),
(
    'STG003',
    'Fermentation',
    3,
    120,
    'Rolled leaves are oxidized under controlled conditions',
    'Active'
),
(
    'STG004',
    'Drying',
    4,
    30,
    'Fermented tea leaves are dried to stop oxidation',
    'Active'
),
(
    'STG005',
    'Sorting',
    5,
    60,
    'Dried tea is separated according to particle size and grade',
    'Active'
),
(
    'STG006',
    'Packaging',
    6,
    90,
    'Finished tea is measured and packed for storage or sale',
    'Active'
);


/*==============================================================
  8. MACHINES
==============================================================*/

INSERT INTO machine (
    machine_code,
    machine_name,
    machine_type,
    manufacturer,
    model_number,
    installation_date,
    location,
    machine_status
)
VALUES
(
    'MAC001',
    'Withering Trough 01',
    'Withering Machine',
    'TeaTech Engineering',
    'WT-1000',
    '2022-01-15',
    'Withering Section',
    'Running'
),
(
    'MAC002',
    'Tea Roller 01',
    'Rolling Machine',
    'Marshall Tea Machinery',
    'TR-500',
    '2021-06-10',
    'Rolling Section',
    'Running'
),
(
    'MAC003',
    'Fermentation Unit 01',
    'Fermentation Machine',
    'TeaTech Engineering',
    'FU-300',
    '2022-03-20',
    'Fermentation Section',
    'Running'
),
(
    'MAC004',
    'Tea Dryer 01',
    'Drying Machine',
    'Lanka Industrial Systems',
    'TD-700',
    '2020-09-12',
    'Drying Section',
    'Running'
),
(
    'MAC005',
    'Tea Sorting Machine 01',
    'Sorting Machine',
    'Colombo Engineering',
    'TS-450',
    '2023-02-18',
    'Sorting Section',
    'Idle'
),
(
    'MAC006',
    'Tea Packaging Machine 01',
    'Packaging Machine',
    'PackTech Lanka',
    'TP-250',
    '2023-07-25',
    'Packaging Section',
    'Idle'
);


/*==============================================================
  9. SENSORS
==============================================================*/

INSERT INTO sensor (
    sensor_code,
    machine_id,
    sensor_name,
    sensor_type,
    unit,
    installation_date,
    sensor_status
)
VALUES
(
    'SEN001',
    1,
    'Withering Temperature Sensor',
    'Temperature',
    'Celsius',
    '2022-01-15',
    'Active'
),
(
    'SEN002',
    1,
    'Withering Humidity Sensor',
    'Humidity',
    'Percent',
    '2022-01-15',
    'Active'
),
(
    'SEN003',
    2,
    'Roller Speed Sensor',
    'Speed',
    'RPM',
    '2021-06-10',
    'Active'
),
(
    'SEN004',
    2,
    'Roller Vibration Sensor',
    'Vibration',
    'mm/s',
    '2021-06-10',
    'Active'
),
(
    'SEN005',
    3,
    'Fermentation Temperature Sensor',
    'Temperature',
    'Celsius',
    '2022-03-20',
    'Active'
),
(
    'SEN006',
    3,
    'Fermentation Humidity Sensor',
    'Humidity',
    'Percent',
    '2022-03-20',
    'Active'
),
(
    'SEN007',
    4,
    'Dryer Temperature Sensor',
    'Temperature',
    'Celsius',
    '2020-09-12',
    'Active'
),
(
    'SEN008',
    4,
    'Dryer Vibration Sensor',
    'Vibration',
    'mm/s',
    '2020-09-12',
    'Active'
);
/*==============================================================
  10. PRODUCTION BATCHES
==============================================================*/

INSERT INTO production_batch (
    batch_code,
    created_by,
    production_date,
    total_leaf_weight,
    expected_output,
    batch_status,
    remarks
)
VALUES
(
    'BAT001',
    2,
    '2026-07-23',
    150.00,
    32.00,
    'Completed',
    'Production completed successfully'
),
(
    'BAT002',
    2,
    '2026-07-25',
    180.00,
    40.00,
    'In Progress',
    'Batch currently undergoing fermentation'
),
(
    'BAT003',
    2,
    '2026-07-28',
    200.00,
    45.00,
    'Planned',
    'Production scheduled for the next working day'
);


/*==============================================================
  11. TEA LEAVES USED IN BATCHES
==============================================================*/

INSERT INTO batch_tea_leaf (
    batch_id,
    tea_leaf_id,
    quantity_used
)
VALUES
(
    1,
    1,
    150.00
),
(
    2,
    2,
    180.00
),
(
    3,
    3,
    200.00
);


/*==============================================================
  12. OTHER MATERIALS USED IN BATCHES
==============================================================*/

INSERT INTO batch_other_material (
    batch_id,
    material_id,
    quantity_used
)
VALUES
(
    1,
    1,
    320.00
),
(
    1,
    5,
    25.00
),
(
    2,
    5,
    20.00
);


/*==============================================================
  13. PRODUCTION PROCESSES — BATCH 1
==============================================================*/

INSERT INTO production_process (
    batch_id,
    stage_id,
    machine_id,
    start_time,
    end_time,
    duration_minutes,
    input_quantity,
    output_quantity,
    waste_quantity,
    process_status,
    remarks
)
VALUES
(
    1,
    1,
    1,
    '2026-07-23 06:00:00',
    '2026-07-23 18:00:00',
    720,
    150.00,
    95.00,
    5.00,
    'Completed',
    'Withering completed under normal conditions'
),
(
    1,
    2,
    2,
    '2026-07-23 18:15:00',
    '2026-07-23 19:00:00',
    45,
    95.00,
    92.00,
    2.00,
    'Completed',
    'Rolling completed successfully'
),
(
    1,
    3,
    3,
    '2026-07-23 19:15:00',
    '2026-07-23 21:15:00',
    120,
    92.00,
    90.00,
    1.00,
    'Completed',
    'Fermentation colour developed correctly'
),
(
    1,
    4,
    4,
    '2026-07-23 21:30:00',
    '2026-07-23 22:00:00',
    30,
    90.00,
    34.00,
    1.00,
    'Completed',
    'Tea dried to the required moisture level'
),
(
    1,
    5,
    5,
    '2026-07-23 22:15:00',
    '2026-07-23 23:15:00',
    60,
    34.00,
    32.00,
    1.00,
    'Completed',
    'Tea sorted into the required grade'
),
(
    1,
    6,
    6,
    '2026-07-24 08:00:00',
    '2026-07-24 09:30:00',
    90,
    32.00,
    32.00,
    0.00,
    'Completed',
    'Tea packed into 100 g pouches'
);


/*==============================================================
  14. PRODUCTION PROCESSES — BATCH 2
==============================================================*/

INSERT INTO production_process (
    batch_id,
    stage_id,
    machine_id,
    start_time,
    end_time,
    duration_minutes,
    input_quantity,
    output_quantity,
    waste_quantity,
    process_status,
    remarks
)
VALUES
(
    2,
    1,
    1,
    '2026-07-25 06:00:00',
    '2026-07-25 18:00:00',
    720,
    180.00,
    115.00,
    5.00,
    'Completed',
    'Withering completed'
),
(
    2,
    2,
    2,
    '2026-07-25 18:15:00',
    '2026-07-25 19:00:00',
    45,
    115.00,
    111.00,
    2.00,
    'Completed',
    'Rolling completed'
),
(
    2,
    3,
    3,
    '2026-07-25 19:15:00',
    NULL,
    NULL,
    111.00,
    NULL,
    0.00,
    'Running',
    'Fermentation currently in progress'
);


/*==============================================================
  15. PRODUCTION ACTIVITY LOGS
==============================================================*/

INSERT INTO user_activity_log (
    user_id,
    activity_type,
    activity_description,
    ip_address
)
VALUES
(
    2,
    'CREATE_BATCH',
    'Created production batch BAT001',
    '192.168.1.10'
),
(
    2,
    'COMPLETE_BATCH',
    'Marked production batch BAT001 as completed',
    '192.168.1.10'
),
(
    2,
    'CREATE_BATCH',
    'Created production batch BAT002',
    '192.168.1.10'
),
(
    4,
    'UPDATE_PROCESS',
    'Updated the fermentation process of batch BAT002',
    '192.168.1.15'
);
/*==============================================================
  16. SENSOR DATA
==============================================================*/

INSERT INTO sensor_data (
    sensor_id,
    reading_value,
    reading_time
)
VALUES
-- Withering temperature
(
    1,
    27.500,
    '2026-07-25 08:00:00'
),
(
    1,
    28.200,
    '2026-07-25 10:00:00'
),

-- Withering humidity
(
    2,
    72.500,
    '2026-07-25 08:00:00'
),
(
    2,
    69.800,
    '2026-07-25 10:00:00'
),

-- Roller speed
(
    3,
    45.000,
    '2026-07-25 18:20:00'
),
(
    3,
    48.000,
    '2026-07-25 18:40:00'
),

-- Roller vibration
(
    4,
    3.200,
    '2026-07-25 18:20:00'
),
(
    4,
    12.500,
    '2026-07-25 18:45:00'
),

-- Fermentation temperature
(
    5,
    26.500,
    '2026-07-25 19:30:00'
),
(
    5,
    27.200,
    '2026-07-25 20:00:00'
),

-- Fermentation humidity
(
    6,
    85.000,
    '2026-07-25 19:30:00'
),
(
    6,
    92.000,
    '2026-07-25 20:00:00'
),

-- Dryer temperature
(
    7,
    94.500,
    '2026-07-23 21:35:00'
),
(
    7,
    115.000,
    '2026-07-23 21:45:00'
),

-- Dryer vibration
(
    8,
    4.100,
    '2026-07-23 21:35:00'
),
(
    8,
    4.500,
    '2026-07-23 21:50:00'
);


/*==============================================================
  17. ALERTS
==============================================================*/

INSERT INTO alert (
    sensor_id,
    alert_type,
    severity,
    alert_message,
    generated_at,
    alert_status,
    acknowledged_by,
    acknowledged_at,
    resolved_at
)
VALUES
(
    4,
    'High Vibration',
    'High',
    'Tea Roller 01 vibration level exceeded the safe operating limit',
    '2026-07-25 18:45:00',
    'Acknowledged',
    4,
    '2026-07-25 18:47:00',
    NULL
),
(
    6,
    'High Humidity',
    'Medium',
    'Fermentation humidity exceeded the recommended level',
    '2026-07-25 20:00:00',
    'Active',
    NULL,
    NULL,
    NULL
),
(
    7,
    'High Temperature',
    'Critical',
    'Tea Dryer 01 temperature exceeded the critical safety limit',
    '2026-07-23 21:45:00',
    'Resolved',
    4,
    '2026-07-23 21:46:00',
    '2026-07-23 21:55:00'
);


/*==============================================================
  18. ALERT ACTIVITY LOGS
==============================================================*/

INSERT INTO user_activity_log (
    user_id,
    activity_type,
    activity_description,
    ip_address
)
VALUES
(
    4,
    'ACKNOWLEDGE_ALERT',
    'Acknowledged the high-vibration alert from Tea Roller 01',
    '192.168.1.15'
),
(
    4,
    'RESOLVE_ALERT',
    'Resolved the critical temperature alert from Tea Dryer 01',
    '192.168.1.15'
);
/*==============================================================
  19. MAINTENANCE RECORDS
==============================================================*/

INSERT INTO maintenance (
    machine_id,
    performed_by,
    maintenance_type,
    maintenance_date,
    description,
    maintenance_status,
    next_maintenance_date
)
VALUES
(
    4,
    4,
    'Emergency',
    '2026-07-23',
    'Inspected and recalibrated the dryer after the critical temperature alert',
    'Completed',
    '2026-08-23'
),
(
    2,
    4,
    'Corrective',
    '2026-07-26',
    'Inspected the tea roller and tightened components after excessive vibration',
    'Completed',
    '2026-08-26'
),
(
    6,
    4,
    'Preventive',
    '2026-07-29',
    'Scheduled inspection and lubrication of the packaging machine',
    'Scheduled',
    '2026-08-29'
);


/*==============================================================
  20. QUALITY CONTROL RECORDS
==============================================================*/

INSERT INTO quality_control (
    batch_id,
    inspected_by,
    inspection_date,
    moisture_content,
    colour,
    aroma,
    appearance,
    quality_status,
    remarks
)
VALUES
(
    1,
    3,
    '2026-07-24',
    3.20,
    'Bright reddish brown',
    'Fresh and strong',
    'Clean and evenly graded',
    'Passed',
    'Batch satisfied the required factory quality standards'
),
(
    2,
    3,
    '2026-07-25',
    NULL,
    NULL,
    NULL,
    NULL,
    'Pending',
    'Final inspection will be completed after production'
);


/*==============================================================
  21. FINISHED PRODUCT INVENTORY
==============================================================*/

INSERT INTO finished_product (
    product_id,
    batch_id,
    quantity,
    manufacture_date,
    expiry_date,
    warehouse_location,
    stock_status
)
VALUES
(
    1,
    1,
    32.00,
    '2026-07-24',
    '2028-07-24',
    'Finished Goods Warehouse A',
    'Available'
);


/*==============================================================
  22. FINAL ACTIVITY LOGS
==============================================================*/

INSERT INTO user_activity_log (
    user_id,
    activity_type,
    activity_description,
    ip_address
)
VALUES
(
    3,
    'QUALITY_CHECK',
    'Completed the quality inspection for batch BAT001',
    '192.168.1.11'
),
(
    4,
    'MAINTENANCE',
    'Completed emergency maintenance on Tea Dryer 01',
    '192.168.1.15'
),
(
    5,
    'FINISHED_STOCK',
    'Recorded 32 kg of finished product from batch BAT001',
    '192.168.1.12'
);