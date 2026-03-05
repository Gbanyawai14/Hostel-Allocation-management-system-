-- ============================================================
-- HOSTEL ALLOCATION & MANAGEMENT SYSTEM (HAMS)
 -- triggers.sql — Run FOURTH
-- ============================================================

USE hams;

DELIMITER $$

-- ============================================================
-- TRIGGER 1: BEFORE INSERT on Allocation — Prevent Overbooking
-- ============================================================
DROP TRIGGER IF EXISTS trg_prevent_over_allocation$$
CREATE TRIGGER trg_prevent_over_allocation
BEFORE INSERT ON Allocation
FOR EACH ROW
BEGIN
    IF NOT fn_room_available(NEW.room_id) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Room is at full capacity. Allocation denied.';
    END IF;
END$$

-- ============================================================
-- TRIGGER 2: AFTER INSERT on Allocation — Audit Log
-- ============================================================
DROP TRIGGER IF EXISTS trg_audit_allocation$$
CREATE TRIGGER trg_audit_allocation
AFTER INSERT ON Allocation
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (table_name, action_type, description)
    VALUES (
        'Allocation',
        'INSERT',
        CONCAT('Room ID ', NEW.room_id, ' allocated to Student ID ', NEW.student_id,
               ' on ', NEW.check_in_date)
    );
END$$

-- ============================================================
-- TRIGGER 3: AFTER INSERT on Payment — Audit Log
-- ============================================================
DROP TRIGGER IF EXISTS trg_audit_payment$$
CREATE TRIGGER trg_audit_payment
AFTER INSERT ON Payment
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (table_name, action_type, description)
    VALUES (
        'Payment',
        'INSERT',
        CONCAT('Payment of ', NEW.amount, ' recorded for Student ID ',
               NEW.student_id, ' on ', NEW.payment_date)
    );
END$$

-- ============================================================
-- TRIGGER 4: AFTER UPDATE on Allocation — Log Checkout
-- ============================================================
DROP TRIGGER IF EXISTS trg_audit_checkout$$
CREATE TRIGGER trg_audit_checkout
AFTER UPDATE ON Allocation
FOR EACH ROW
BEGIN
    IF NEW.status = 'CHECKED_OUT' AND OLD.status = 'ACTIVE' THEN
        INSERT INTO AuditLog (table_name, action_type, description)
        VALUES (
            'Allocation',
            'CHECKOUT',
            CONCAT('Student ID ', NEW.student_id,
                   ' checked out from Room ID ', NEW.room_id,
                   ' on ', NEW.check_out_date)
        );
    END IF;
END$$

-- ============================================================
-- TRIGGER 5: BEFORE INSERT on Room — Sync Hostel total_rooms
-- ============================================================
DROP TRIGGER IF EXISTS trg_increment_hostel_rooms$$
CREATE TRIGGER trg_increment_hostel_rooms
AFTER INSERT ON Room
FOR EACH ROW
BEGIN
    UPDATE Hostel
    SET total_rooms = total_rooms + 1
    WHERE hostel_id = NEW.hostel_id;
END$$

DELIMITER ;
