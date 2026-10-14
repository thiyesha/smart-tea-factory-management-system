/*==============================================================
  Project  : Smart Tea Factory Production Monitoring and
             Inventory Management System
  File     : triggers.sql
  Purpose  : Automate validation and stock-status updates
==============================================================*/

USE tea_factory_db;


/*==============================================================
  REMOVE OLD TRIGGERS BEFORE RECREATING THEM
==============================================================*/

DROP TRIGGER IF EXISTS trg_tea_leaf_status_before_insert;
DROP TRIGGER IF EXISTS trg_tea_leaf_status_before_update;

DROP TRIGGER IF EXISTS trg_material_status_before_insert;
DROP TRIGGER IF EXISTS trg_material_status_before_update;

DROP TRIGGER IF EXISTS trg_finished_product_before_insert;
DROP TRIGGER IF EXISTS trg_quality_control_before_insert;
DROP TRIGGER IF EXISTS trg_maintenance_before_insert;


DELIMITER $$


/*==============================================================
  1. SET TEA-LEAF STOCK STATUS BEFORE INSERT
==============================================================*/

CREATE TRIGGER trg_tea_leaf_status_before_insert
BEFORE INSERT ON tea_leaf_inventory
FOR EACH ROW
BEGIN
    IF NEW.current_stock = 0 THEN
        SET NEW.stock_status = 'Out of Stock';

    ELSEIF NEW.current_stock <= (NEW.received_quantity * 0.20) THEN
        SET NEW.stock_status = 'Low Stock';

    ELSE
        SET NEW.stock_status = 'Available';
    END IF;
END$$


/*==============================================================
  2. UPDATE TEA-LEAF STOCK STATUS AUTOMATICALLY
==============================================================*/

CREATE TRIGGER trg_tea_leaf_status_before_update
BEFORE UPDATE ON tea_leaf_inventory
FOR EACH ROW
BEGIN
    IF NEW.current_stock = 0 THEN
        SET NEW.stock_status = 'Out of Stock';

    ELSEIF NEW.current_stock <= (NEW.received_quantity * 0.20) THEN
        SET NEW.stock_status = 'Low Stock';

    ELSE
        SET NEW.stock_status = 'Available';
    END IF;
END$$


/*==============================================================
  3. SET MATERIAL STATUS BEFORE INSERT
==============================================================*/

CREATE TRIGGER trg_material_status_before_insert
BEFORE INSERT ON other_material
FOR EACH ROW
BEGIN
    IF NEW.material_status <> 'Inactive' THEN

        IF NEW.current_stock = 0 THEN
            SET NEW.material_status = 'Out of Stock';

        ELSEIF NEW.current_stock <= NEW.minimum_stock THEN
            SET NEW.material_status = 'Low Stock';

        ELSE
            SET NEW.material_status = 'Available';
        END IF;

    END IF;
END$$


/*==============================================================
  4. UPDATE MATERIAL STATUS AUTOMATICALLY
==============================================================*/

CREATE TRIGGER trg_material_status_before_update
BEFORE UPDATE ON other_material
FOR EACH ROW
BEGIN
    IF NEW.material_status <> 'Inactive' THEN

        IF NEW.current_stock = 0 THEN
            SET NEW.material_status = 'Out of Stock';

        ELSEIF NEW.current_stock <= NEW.minimum_stock THEN
            SET NEW.material_status = 'Low Stock';

        ELSE
            SET NEW.material_status = 'Available';
        END IF;

    END IF;
END$$


/*==============================================================
  5. ALLOW FINISHED PRODUCTS ONLY FROM COMPLETED BATCHES
==============================================================*/

CREATE TRIGGER trg_finished_product_before_insert
BEFORE INSERT ON finished_product
FOR EACH ROW
BEGIN
    DECLARE selected_batch_status VARCHAR(20);

    SELECT batch_status
    INTO selected_batch_status
    FROM production_batch
    WHERE batch_id = NEW.batch_id;

    IF selected_batch_status <> 'Completed' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
            'Finished products can only be recorded for completed batches';
    END IF;
END$$


/*==============================================================
  6. VALIDATE QUALITY-CONTROL MOISTURE PERCENTAGE
==============================================================*/

CREATE TRIGGER trg_quality_control_before_insert
BEFORE INSERT ON quality_control
FOR EACH ROW
BEGIN
    IF NEW.moisture_content IS NOT NULL
       AND (
           NEW.moisture_content < 0
           OR NEW.moisture_content > 100
       ) THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
            'Moisture content must be between 0 and 100 percent';
    END IF;
END$$


/*==============================================================
  7. VALIDATE MAINTENANCE DATES
==============================================================*/

CREATE TRIGGER trg_maintenance_before_insert
BEFORE INSERT ON maintenance
FOR EACH ROW
BEGIN
    IF NEW.next_maintenance_date IS NOT NULL
       AND NEW.next_maintenance_date < NEW.maintenance_date THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
            'Next maintenance date cannot be before maintenance date';
    END IF;
END$$


DELIMITER ;