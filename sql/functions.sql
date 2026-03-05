 -- ============================================================
-- HOSTEL ALLOCATION & MANAGEMENT SYSTEM (HAMS)
-- functions.sql — Run SECOND (after schema.sql)
-- ============================================================

USE hams;

DELIMITER $$

-- ============================================================
-- FUNCTION 1: Calculate Total Paid by a Student
-- ============================================================
DROP FUNCTION IF EXISTS fn_total_paid$$
CREATE FUNCTION fn_total_paid(sid INT)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE total DECIMAL(10,2);
    SELECT IFNULL(SUM(amount), 0)
    INTO   total
    FROM   Payment
    WHERE  student_id = sid AND status = 'PAID';
    RETURN total;
END$$

-- ============================================================
-- FUNCTION 2: Check Room Availability (returns TRUE if space)
-- ============================================================
DROP FUNCTION IF EXISTS fn_room_available$$
CREATE FUNCTION fn_room_available(rid INT)
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE cap INT DEFAULT 0;
    DECLARE occ INT DEFAULT 0;

    SELECT RT.capacity, R.occupied_count
    INTO   cap, occ
    FROM   Room R
    JOIN   RoomType RT ON R.room_type_id = RT.room_type_id
    WHERE  R.room_id = rid;

    RETURN occ < cap;
END$$

-- ============================================================
-- FUNCTION 3: Occupancy Rate for a Hostel (percentage)
-- ============================================================
DROP FUNCTION IF EXISTS fn_occupancy_rate$$
CREATE FUNCTION fn_occupancy_rate(hid INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
    DECLARE total    INT DEFAULT 0;
    DECLARE occupied INT DEFAULT 0;

    SELECT COUNT(*)          INTO total    FROM Room WHERE hostel_id = hid;
    SELECT IFNULL(SUM(occupied_count),0) INTO occupied FROM Room WHERE hostel_id = hid;

    IF total = 0 THEN RETURN 0; END IF;
    RETURN (occupied / total) * 100;
END$$

DELIMITER ;
