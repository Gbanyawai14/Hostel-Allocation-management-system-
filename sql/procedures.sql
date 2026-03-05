-- ============================================================
-- HOSTEL ALLOCATION & MANAGEMENT SYSTEM (HAMS)
-- procedures.sql — Run THIRD (after functions.sql)
-- ============================================================
 
USE hams;

DELIMITER $$

-- ============================================================
-- PROCEDURE 1: Allocate Room to Student (with Transaction)
-- ============================================================
DROP PROCEDURE IF EXISTS proc_allocate_room$$
CREATE PROCEDURE proc_allocate_room(
    IN  sid INT,
    IN  rid INT,
    OUT result VARCHAR(100)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET result = 'ERROR: Transaction failed.';
    END;

    START TRANSACTION;

    -- Check if student already has active allocation
    IF EXISTS (
        SELECT 1 FROM Allocation
        WHERE student_id = sid AND status = 'ACTIVE'
    ) THEN
        ROLLBACK;
        SET result = 'ERROR: Student already has an active room.';
    ELSEIF NOT fn_room_available(rid) THEN
        ROLLBACK;
        SET result = 'ERROR: Room is full.';
    ELSE
        INSERT INTO Allocation (student_id, room_id, check_in_date)
        VALUES (sid, rid, CURDATE());

        UPDATE Room
        SET    occupied_count = occupied_count + 1
        WHERE  room_id = rid;

        COMMIT;
        SET result = 'SUCCESS: Room allocated successfully.';
    END IF;
END$$

-- ============================================================
-- PROCEDURE 2: Checkout Student
-- ============================================================
DROP PROCEDURE IF EXISTS proc_checkout_student$$
CREATE PROCEDURE proc_checkout_student(
    IN  sid INT,
    OUT result VARCHAR(100)
)
BEGIN
    DECLARE rid INT DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET result = 'ERROR: Checkout failed.';
    END;

    START TRANSACTION;

    SELECT room_id INTO rid
    FROM   Allocation
    WHERE  student_id = sid AND status = 'ACTIVE'
    LIMIT  1;

    IF rid IS NULL THEN
        ROLLBACK;
        SET result = 'ERROR: No active allocation found.';
    ELSE
        UPDATE Allocation
        SET    status = 'CHECKED_OUT',
               check_out_date = CURDATE()
        WHERE  student_id = sid AND status = 'ACTIVE';

        UPDATE Room
        SET    occupied_count = GREATEST(occupied_count - 1, 0)
        WHERE  room_id = rid;

        COMMIT;
        SET result = 'SUCCESS: Student checked out successfully.';
    END IF;
END$$

-- ============================================================
-- PROCEDURE 3: Add Payment
-- ============================================================
DROP PROCEDURE IF EXISTS proc_add_payment$$
CREATE PROCEDURE proc_add_payment(
    IN sid    INT,
    IN amt    DECIMAL(10,2),
    IN mid    INT,
    IN pstatus ENUM('PAID','PENDING')
)
BEGIN
    INSERT INTO Payment (student_id, amount, payment_date, method_id, status)
    VALUES (sid, amt, CURDATE(), mid, pstatus);
END$$

-- ============================================================
-- PROCEDURE 4: Report Maintenance Issue (with JSON)
-- ============================================================
DROP PROCEDURE IF EXISTS proc_report_maintenance$$
CREATE PROCEDURE proc_report_maintenance(
    IN rid         INT,
    IN sid         INT,
    IN category    VARCHAR(50),
    IN description TEXT,
    IN priority    VARCHAR(20)
)
BEGIN
    DECLARE issue_json JSON;
    SET issue_json = JSON_OBJECT(
        'category',    category,
        'description', description,
        'priority',    priority
    );

    INSERT INTO MaintenanceRequest (room_id, student_id, issue_details, request_date)
    VALUES (rid, sid, issue_json, CURDATE());
END$$

DELIMITER ;
