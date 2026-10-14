/* Smart Tea Factory stored procedures */
USE tea_factory_db;

DROP PROCEDURE IF EXISTS sp_create_production_batch;
DROP PROCEDURE IF EXISTS sp_adjust_tea_leaf_stock;
DROP PROCEDURE IF EXISTS sp_adjust_material_stock;
DROP PROCEDURE IF EXISTS sp_acknowledge_alert;
DROP PROCEDURE IF EXISTS sp_get_batch_report;

DELIMITER $$

CREATE PROCEDURE sp_create_production_batch(
    IN p_batch_code VARCHAR(10),
    IN p_created_by INT UNSIGNED,
    IN p_production_date DATE,
    IN p_leaf_weight DECIMAL(10,2),
    IN p_expected_output DECIMAL(10,2)
)
BEGIN
    INSERT INTO production_batch
        (batch_code, created_by, production_date, total_leaf_weight, expected_output, batch_status)
    VALUES
        (p_batch_code, p_created_by, p_production_date, p_leaf_weight, p_expected_output, 'Planned');
    SELECT LAST_INSERT_ID() AS batch_id;
END$$

CREATE PROCEDURE sp_adjust_tea_leaf_stock(
    IN p_tea_leaf_id INT UNSIGNED,
    IN p_change DECIMAL(10,2)
)
BEGIN
    DECLARE v_stock DECIMAL(10,2);
    SELECT current_stock INTO v_stock
    FROM tea_leaf_inventory WHERE tea_leaf_id=p_tea_leaf_id FOR UPDATE;
    IF v_stock IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Tea leaf record not found';
    ELSEIF v_stock + p_change < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Insufficient tea leaf stock';
    END IF;
    UPDATE tea_leaf_inventory SET current_stock=current_stock+p_change
    WHERE tea_leaf_id=p_tea_leaf_id;
END$$

CREATE PROCEDURE sp_adjust_material_stock(
    IN p_material_id INT UNSIGNED,
    IN p_change DECIMAL(10,2)
)
BEGIN
    DECLARE v_stock DECIMAL(10,2);
    SELECT current_stock INTO v_stock
    FROM other_material WHERE material_id=p_material_id FOR UPDATE;
    IF v_stock IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Material record not found';
    ELSEIF v_stock + p_change < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Insufficient material stock';
    END IF;
    UPDATE other_material SET current_stock=current_stock+p_change
    WHERE material_id=p_material_id;
END$$

CREATE PROCEDURE sp_acknowledge_alert(
    IN p_alert_id BIGINT UNSIGNED,
    IN p_user_id INT UNSIGNED
)
BEGIN
    UPDATE alert
    SET alert_status='Acknowledged', acknowledged_by=p_user_id, acknowledged_at=NOW()
    WHERE alert_id=p_alert_id AND alert_status='Active';
END$$

CREATE PROCEDURE sp_get_batch_report(IN p_batch_code VARCHAR(10))
BEGIN
    SELECT pb.batch_code, pb.production_date, pb.batch_status,
           ps.stage_name, m.machine_name, pp.start_time, pp.end_time,
           pp.input_quantity, pp.output_quantity, pp.waste_quantity, pp.process_status
    FROM production_batch pb
    LEFT JOIN production_process pp ON pp.batch_id=pb.batch_id
    LEFT JOIN production_stage ps ON ps.stage_id=pp.stage_id
    LEFT JOIN machine m ON m.machine_id=pp.machine_id
    WHERE pb.batch_code=p_batch_code
    ORDER BY ps.sequence_number;
END$$

DELIMITER ;
