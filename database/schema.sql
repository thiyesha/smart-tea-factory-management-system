/*==============================================================
  Project  : Smart Tea Factory Production Monitoring and
             Inventory Management System
  Database : tea_factory_db
  DBMS     : MySQL
==============================================================*/


/*==============================================================
  1. CREATE DATABASE
==============================================================*/

DROP DATABASE IF EXISTS tea_factory_db;

CREATE DATABASE tea_factory_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tea_factory_db;


/*==============================================================
  2. USERS TABLE
==============================================================*/

CREATE TABLE users (
    user_id INT UNSIGNED AUTO_INCREMENT,
    user_code VARCHAR(10) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,

    role ENUM(
        'Administrator',
        'Production Manager',
        'Quality Officer',
        'Machine Operator',
        'Store Keeper'
    ) NOT NULL,

    email VARCHAR(100),
    phone VARCHAR(20),

    account_status ENUM(
        'Active',
        'Inactive'
    ) NOT NULL DEFAULT 'Active',

    last_login_at DATETIME NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_users
        PRIMARY KEY (user_id),

    CONSTRAINT uq_users_user_code
        UNIQUE (user_code),

    CONSTRAINT uq_users_username
        UNIQUE (username),

    CONSTRAINT uq_users_email
        UNIQUE (email)
) ENGINE = InnoDB;


/*==============================================================
  3. USER ACTIVITY LOG TABLE
==============================================================*/

CREATE TABLE user_activity_log (
    activity_id BIGINT UNSIGNED AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,

    activity_type VARCHAR(50) NOT NULL,
    activity_description VARCHAR(255) NOT NULL,

    ip_address VARCHAR(45),
    activity_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_user_activity_log
        PRIMARY KEY (activity_id),

    CONSTRAINT fk_activity_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;


/*==============================================================
  4. INDEXES
==============================================================*/

CREATE INDEX idx_activity_user_id
    ON user_activity_log(user_id);

CREATE INDEX idx_activity_time
    ON user_activity_log(activity_time);

/*==============================================================
  5. SUPPLIER TABLE
==============================================================*/

CREATE TABLE supplier (
    supplier_id INT UNSIGNED AUTO_INCREMENT,
    supplier_code VARCHAR(10) NOT NULL,
    supplier_name VARCHAR(100) NOT NULL,
    estate_name VARCHAR(100) NOT NULL,

    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address VARCHAR(255),
    district VARCHAR(50),

    supplier_status ENUM(
        'Active',
        'Inactive'
    ) NOT NULL DEFAULT 'Active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_supplier
        PRIMARY KEY (supplier_id),

    CONSTRAINT uq_supplier_code
        UNIQUE (supplier_code),

    CONSTRAINT uq_supplier_email
        UNIQUE (email)
) ENGINE = InnoDB;


/*==============================================================
  6. TEA LEAF INVENTORY TABLE
==============================================================*/

CREATE TABLE tea_leaf_inventory (
    tea_leaf_id INT UNSIGNED AUTO_INCREMENT,
    tea_leaf_code VARCHAR(10) NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,

    leaf_type VARCHAR(50) NOT NULL,
    quality_grade VARCHAR(30),

    received_quantity DECIMAL(10,2) NOT NULL,
    current_stock DECIMAL(10,2) NOT NULL,

    unit ENUM(
        'kg',
        'g',
        'ton'
    ) NOT NULL DEFAULT 'kg',

    received_date DATE NOT NULL,
    storage_location VARCHAR(100),

    stock_status ENUM(
        'Available',
        'Low Stock',
        'Out of Stock'
    ) NOT NULL DEFAULT 'Available',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_tea_leaf_inventory
        PRIMARY KEY (tea_leaf_id),

    CONSTRAINT uq_tea_leaf_code
        UNIQUE (tea_leaf_code),

    CONSTRAINT fk_tea_leaf_supplier
        FOREIGN KEY (supplier_id)
        REFERENCES supplier(supplier_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_tea_leaf_received_quantity
        CHECK (received_quantity > 0),

    CONSTRAINT chk_tea_leaf_current_stock
        CHECK (
            current_stock >= 0
            AND current_stock <= received_quantity
        )
) ENGINE = InnoDB;


/*==============================================================
  7. OTHER MATERIAL TABLE
==============================================================*/

CREATE TABLE other_material (
    material_id INT UNSIGNED AUTO_INCREMENT,
    material_code VARCHAR(10) NOT NULL,
    material_name VARCHAR(100) NOT NULL,

    category ENUM(
        'Packaging',
        'Maintenance',
        'Fuel',
        'Cleaning',
        'Utility',
        'Other'
    ) NOT NULL,

    unit VARCHAR(20) NOT NULL,

    current_stock DECIMAL(10,2) NOT NULL DEFAULT 0,
    minimum_stock DECIMAL(10,2) NOT NULL DEFAULT 0,

    storage_location VARCHAR(100),

    material_status ENUM(
        'Available',
        'Low Stock',
        'Out of Stock',
        'Inactive'
    ) NOT NULL DEFAULT 'Available',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_other_material
        PRIMARY KEY (material_id),

    CONSTRAINT uq_material_code
        UNIQUE (material_code),

    CONSTRAINT chk_material_current_stock
        CHECK (current_stock >= 0),

    CONSTRAINT chk_material_minimum_stock
        CHECK (minimum_stock >= 0)
) ENGINE = InnoDB;


/*==============================================================
  8. TEA PRODUCT TABLE
==============================================================*/

CREATE TABLE tea_product (
    product_id INT UNSIGNED AUTO_INCREMENT,
    product_code VARCHAR(10) NOT NULL,
    product_name VARCHAR(100) NOT NULL,

    tea_grade VARCHAR(30) NOT NULL,

    package_weight DECIMAL(8,2) NOT NULL,

    package_unit ENUM(
        'g',
        'kg'
    ) NOT NULL DEFAULT 'g',

    selling_price DECIMAL(10,2) NOT NULL,

    description TEXT,

    product_status ENUM(
        'Active',
        'Inactive'
    ) NOT NULL DEFAULT 'Active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_tea_product
        PRIMARY KEY (product_id),

    CONSTRAINT uq_product_code
        UNIQUE (product_code),

    CONSTRAINT chk_product_package_weight
        CHECK (package_weight > 0),

    CONSTRAINT chk_product_selling_price
        CHECK (selling_price >= 0)
) ENGINE = InnoDB;


/*==============================================================
  9. INVENTORY INDEXES
==============================================================*/

CREATE INDEX idx_tea_leaf_supplier_id
    ON tea_leaf_inventory(supplier_id);

CREATE INDEX idx_tea_leaf_received_date
    ON tea_leaf_inventory(received_date);

CREATE INDEX idx_tea_leaf_stock_status
    ON tea_leaf_inventory(stock_status);

CREATE INDEX idx_material_category
    ON other_material(category);

CREATE INDEX idx_material_status
    ON other_material(material_status);

CREATE INDEX idx_product_tea_grade
    ON tea_product(tea_grade);    

/*==============================================================
  10. PRODUCTION STAGE TABLE
==============================================================*/

CREATE TABLE production_stage (
    stage_id INT UNSIGNED AUTO_INCREMENT,
    stage_code VARCHAR(10) NOT NULL,
    stage_name VARCHAR(50) NOT NULL,
    sequence_number INT UNSIGNED NOT NULL,
    standard_duration_minutes INT UNSIGNED,
    description TEXT,

    stage_status ENUM(
        'Active',
        'Inactive'
    ) NOT NULL DEFAULT 'Active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_production_stage
        PRIMARY KEY (stage_id),

    CONSTRAINT uq_production_stage_code
        UNIQUE (stage_code),

    CONSTRAINT uq_production_stage_name
        UNIQUE (stage_name),

    CONSTRAINT uq_production_stage_sequence
        UNIQUE (sequence_number),

    CONSTRAINT chk_production_stage_sequence
        CHECK (sequence_number > 0),

    CONSTRAINT chk_production_stage_duration
        CHECK (
            standard_duration_minutes IS NULL
            OR standard_duration_minutes >= 0
        )
) ENGINE = InnoDB;


/*==============================================================
  11. MACHINE TABLE
==============================================================*/

CREATE TABLE machine (
    machine_id INT UNSIGNED AUTO_INCREMENT,
    machine_code VARCHAR(10) NOT NULL,
    machine_name VARCHAR(100) NOT NULL,
    machine_type VARCHAR(100) NOT NULL,

    manufacturer VARCHAR(100),
    model_number VARCHAR(50),
    installation_date DATE,
    location VARCHAR(100),

    machine_status ENUM(
        'Running',
        'Idle',
        'Maintenance',
        'Out of Service'
    ) NOT NULL DEFAULT 'Idle',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_machine
        PRIMARY KEY (machine_id),

    CONSTRAINT uq_machine_code
        UNIQUE (machine_code)
) ENGINE = InnoDB;


/*==============================================================
  12. PRODUCTION BATCH TABLE
==============================================================*/

CREATE TABLE production_batch (
    batch_id INT UNSIGNED AUTO_INCREMENT,
    batch_code VARCHAR(10) NOT NULL,
    created_by INT UNSIGNED NOT NULL,

    production_date DATE NOT NULL,
    total_leaf_weight DECIMAL(10,2) NOT NULL,
    expected_output DECIMAL(10,2),

    batch_status ENUM(
        'Planned',
        'In Progress',
        'Completed',
        'Cancelled'
    ) NOT NULL DEFAULT 'Planned',

    remarks VARCHAR(255),

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_production_batch
        PRIMARY KEY (batch_id),

    CONSTRAINT uq_production_batch_code
        UNIQUE (batch_code),

    CONSTRAINT fk_production_batch_user
        FOREIGN KEY (created_by)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_production_batch_leaf_weight
        CHECK (total_leaf_weight > 0),

    CONSTRAINT chk_production_batch_expected_output
        CHECK (
            expected_output IS NULL
            OR expected_output >= 0
        )
) ENGINE = InnoDB;


/*==============================================================
  13. PRODUCTION PROCESS TABLE
==============================================================*/

CREATE TABLE production_process (
    process_id BIGINT UNSIGNED AUTO_INCREMENT,
    batch_id INT UNSIGNED NOT NULL,
    stage_id INT UNSIGNED NOT NULL,
    machine_id INT UNSIGNED NOT NULL,

    start_time DATETIME,
    end_time DATETIME,
    duration_minutes INT UNSIGNED,

    input_quantity DECIMAL(10,2) NOT NULL,
    output_quantity DECIMAL(10,2),
    waste_quantity DECIMAL(10,2) NOT NULL DEFAULT 0,

    process_status ENUM(
        'Pending',
        'Running',
        'Completed',
        'Interrupted'
    ) NOT NULL DEFAULT 'Pending',

    remarks VARCHAR(255),

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_production_process
        PRIMARY KEY (process_id),

    CONSTRAINT fk_production_process_batch
        FOREIGN KEY (batch_id)
        REFERENCES production_batch(batch_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_production_process_stage
        FOREIGN KEY (stage_id)
        REFERENCES production_stage(stage_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_production_process_machine
        FOREIGN KEY (machine_id)
        REFERENCES machine(machine_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_production_process_batch_stage
        UNIQUE (batch_id, stage_id),

    CONSTRAINT chk_production_process_duration
        CHECK (
            duration_minutes IS NULL
            OR duration_minutes >= 0
        ),

    CONSTRAINT chk_production_process_input
        CHECK (input_quantity >= 0),

    CONSTRAINT chk_production_process_output
        CHECK (
            output_quantity IS NULL
            OR output_quantity >= 0
        ),

    CONSTRAINT chk_production_process_waste
        CHECK (waste_quantity >= 0),

    CONSTRAINT chk_production_process_time
        CHECK (
            end_time IS NULL
            OR start_time IS NULL
            OR end_time >= start_time
        ),

    CONSTRAINT chk_production_process_quantities
        CHECK (
            output_quantity IS NULL
            OR output_quantity + waste_quantity <= input_quantity
        )
) ENGINE = InnoDB;


/*==============================================================
  14. PRODUCTION INDEXES
==============================================================*/

CREATE INDEX idx_production_stage_sequence
    ON production_stage(sequence_number);

CREATE INDEX idx_machine_status
    ON machine(machine_status);

CREATE INDEX idx_production_batch_created_by
    ON production_batch(created_by);

CREATE INDEX idx_production_batch_date
    ON production_batch(production_date);

CREATE INDEX idx_production_batch_status
    ON production_batch(batch_status);

CREATE INDEX idx_production_process_batch
    ON production_process(batch_id);

CREATE INDEX idx_production_process_stage
    ON production_process(stage_id);

CREATE INDEX idx_production_process_machine
    ON production_process(machine_id);

CREATE INDEX idx_production_process_status
    ON production_process(process_status);

/*==============================================================
  15. SENSOR TABLE
==============================================================*/

CREATE TABLE sensor (
    sensor_id INT UNSIGNED AUTO_INCREMENT,
    sensor_code VARCHAR(10) NOT NULL,
    machine_id INT UNSIGNED NOT NULL,

    sensor_name VARCHAR(100) NOT NULL,

    sensor_type ENUM(
        'Temperature',
        'Humidity',
        'Speed',
        'Vibration'
    ) NOT NULL,

    unit VARCHAR(20) NOT NULL,

    installation_date DATE,

    sensor_status ENUM(
        'Active',
        'Inactive',
        'Faulty'
    ) NOT NULL DEFAULT 'Active',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_sensor
        PRIMARY KEY (sensor_id),

    CONSTRAINT uq_sensor_code
        UNIQUE (sensor_code),

    CONSTRAINT fk_sensor_machine
        FOREIGN KEY (machine_id)
        REFERENCES machine(machine_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;


/*==============================================================
  16. SENSOR DATA TABLE
==============================================================*/

CREATE TABLE sensor_data (
    reading_id BIGINT UNSIGNED AUTO_INCREMENT,
    sensor_id INT UNSIGNED NOT NULL,

    reading_value DECIMAL(12,3) NOT NULL,

    reading_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_sensor_data
        PRIMARY KEY (reading_id),

    CONSTRAINT fk_sensor_data_sensor
        FOREIGN KEY (sensor_id)
        REFERENCES sensor(sensor_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;


/*==============================================================
  17. ALERT TABLE
==============================================================*/

CREATE TABLE alert (
    alert_id BIGINT UNSIGNED AUTO_INCREMENT,
    sensor_id INT UNSIGNED NOT NULL,

    alert_type VARCHAR(100) NOT NULL,

    severity ENUM(
        'Low',
        'Medium',
        'High',
        'Critical'
    ) NOT NULL,

    alert_message VARCHAR(255) NOT NULL,

    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    alert_status ENUM(
        'Active',
        'Acknowledged',
        'Resolved'
    ) NOT NULL DEFAULT 'Active',

    acknowledged_by INT UNSIGNED NULL,
    acknowledged_at DATETIME NULL,
    resolved_at DATETIME NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_alert
        PRIMARY KEY (alert_id),

    CONSTRAINT fk_alert_sensor
        FOREIGN KEY (sensor_id)
        REFERENCES sensor(sensor_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_alert_acknowledged_user
        FOREIGN KEY (acknowledged_by)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_alert_acknowledgement
        CHECK (
            acknowledged_at IS NULL
            OR acknowledged_by IS NOT NULL
        ),

    CONSTRAINT chk_alert_resolution
        CHECK (
            resolved_at IS NULL
            OR alert_status = 'Resolved'
        )
) ENGINE = InnoDB;


/*==============================================================
  18. IOT MONITORING INDEXES
==============================================================*/

CREATE INDEX idx_sensor_machine
    ON sensor(machine_id);

CREATE INDEX idx_sensor_type
    ON sensor(sensor_type);

CREATE INDEX idx_sensor_status
    ON sensor(sensor_status);

CREATE INDEX idx_sensor_data_sensor
    ON sensor_data(sensor_id);

CREATE INDEX idx_sensor_data_reading_time
    ON sensor_data(reading_time);

CREATE INDEX idx_alert_sensor
    ON alert(sensor_id);

CREATE INDEX idx_alert_severity
    ON alert(severity);

CREATE INDEX idx_alert_status
    ON alert(alert_status);

CREATE INDEX idx_alert_generated_at
    ON alert(generated_at);

CREATE INDEX idx_alert_acknowledged_by
    ON alert(acknowledged_by);        

/*==============================================================
  19. MAINTENANCE TABLE
==============================================================*/

CREATE TABLE maintenance (
    maintenance_id INT UNSIGNED AUTO_INCREMENT,
    machine_id INT UNSIGNED NOT NULL,
    performed_by INT UNSIGNED NOT NULL,

    maintenance_type ENUM(
        'Preventive',
        'Corrective',
        'Emergency'
    ) NOT NULL,

    maintenance_date DATE NOT NULL,

    description VARCHAR(255),

    maintenance_status ENUM(
        'Scheduled',
        'Completed',
        'Cancelled'
    ) NOT NULL DEFAULT 'Scheduled',

    next_maintenance_date DATE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_maintenance
        PRIMARY KEY (maintenance_id),

    CONSTRAINT fk_maintenance_machine
        FOREIGN KEY (machine_id)
        REFERENCES machine(machine_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_maintenance_user
        FOREIGN KEY (performed_by)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


/*==============================================================
  20. QUALITY CONTROL TABLE
==============================================================*/

CREATE TABLE quality_control (
    quality_id INT UNSIGNED AUTO_INCREMENT,

    batch_id INT UNSIGNED NOT NULL,

    inspected_by INT UNSIGNED NOT NULL,

    inspection_date DATE NOT NULL,

    moisture_content DECIMAL(5,2),

    colour VARCHAR(50),

    aroma VARCHAR(50),

    appearance VARCHAR(50),

    quality_status ENUM(
        'Passed',
        'Failed',
        'Pending'
    ) NOT NULL DEFAULT 'Pending',

    remarks VARCHAR(255),

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_quality_control
        PRIMARY KEY (quality_id),

    CONSTRAINT fk_quality_batch
        FOREIGN KEY (batch_id)
        REFERENCES production_batch(batch_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_quality_user
        FOREIGN KEY (inspected_by)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_moisture_content
        CHECK (
            moisture_content IS NULL
            OR moisture_content >= 0
        )
) ENGINE=InnoDB;


/*==============================================================
  21. FINISHED PRODUCT TABLE
==============================================================*/

CREATE TABLE finished_product (
    finished_product_id INT UNSIGNED AUTO_INCREMENT,

    product_id INT UNSIGNED NOT NULL,

    batch_id INT UNSIGNED NOT NULL,

    quantity DECIMAL(10,2) NOT NULL,

    manufacture_date DATE NOT NULL,

    expiry_date DATE,

    warehouse_location VARCHAR(100),

    stock_status ENUM(
        'Available',
        'Reserved',
        'Sold'
    ) NOT NULL DEFAULT 'Available',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_finished_product
        PRIMARY KEY (finished_product_id),

    CONSTRAINT fk_finished_product_product
        FOREIGN KEY (product_id)
        REFERENCES tea_product(product_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_finished_product_batch
        FOREIGN KEY (batch_id)
        REFERENCES production_batch(batch_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_finished_product_quantity
        CHECK (quantity >= 0),

    CONSTRAINT chk_finished_product_dates
        CHECK (
            expiry_date IS NULL
            OR expiry_date >= manufacture_date
        )
) ENGINE=InnoDB;


/*==============================================================
  22. QUALITY & MAINTENANCE INDEXES
==============================================================*/

CREATE INDEX idx_maintenance_machine
ON maintenance(machine_id);

CREATE INDEX idx_maintenance_date
ON maintenance(maintenance_date);

CREATE INDEX idx_quality_batch
ON quality_control(batch_id);

CREATE INDEX idx_quality_status
ON quality_control(quality_status);

CREATE INDEX idx_finished_product_batch
ON finished_product(batch_id);

CREATE INDEX idx_finished_product_product
ON finished_product(product_id);

CREATE INDEX idx_finished_product_status
ON finished_product(stock_status);

/*==============================================================
  23. BATCH TEA LEAF (BRIDGE TABLE)
==============================================================*/

CREATE TABLE batch_tea_leaf (
    batch_id INT UNSIGNED NOT NULL,
    tea_leaf_id INT UNSIGNED NOT NULL,

    quantity_used DECIMAL(10,2) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_batch_tea_leaf
        PRIMARY KEY (batch_id, tea_leaf_id),

    CONSTRAINT fk_batch_tea_leaf_batch
        FOREIGN KEY (batch_id)
        REFERENCES production_batch(batch_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_batch_tea_leaf_leaf
        FOREIGN KEY (tea_leaf_id)
        REFERENCES tea_leaf_inventory(tea_leaf_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_batch_tea_leaf_quantity
        CHECK (quantity_used > 0)
) ENGINE=InnoDB;


/*==============================================================
  24. BATCH OTHER MATERIAL (BRIDGE TABLE)
==============================================================*/

CREATE TABLE batch_other_material (
    batch_id INT UNSIGNED NOT NULL,
    material_id INT UNSIGNED NOT NULL,

    quantity_used DECIMAL(10,2) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_batch_other_material
        PRIMARY KEY (batch_id, material_id),

    CONSTRAINT fk_batch_material_batch
        FOREIGN KEY (batch_id)
        REFERENCES production_batch(batch_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_batch_material_material
        FOREIGN KEY (material_id)
        REFERENCES other_material(material_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_batch_material_quantity
        CHECK (quantity_used > 0)
) ENGINE=InnoDB;


/*==============================================================
  25. BRIDGE TABLE INDEXES
==============================================================*/

CREATE INDEX idx_batch_tea_leaf_leaf
ON batch_tea_leaf(tea_leaf_id);

CREATE INDEX idx_batch_material_material
ON batch_other_material(material_id);