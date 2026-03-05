-- ============================================================
-- HOSTEL ALLOCATION & MANAGEMENT SYSTEM (HAMS)
-- views.sql — Run FIFTH
-- ============================================================

USE hams;

-- ============================================================
-- VIEW 1: Available Rooms (with hostel and type info)
-- ============================================================
DROP VIEW IF EXISTS view_available_rooms;
CREATE VIEW view_available_rooms AS
SELECT
    R.room_id,
    R.room_number,
    H.hostel_name,
    H.location,
    RT.type_name,
    RT.capacity,
    R.occupied_count,
    (RT.capacity - R.occupied_count) AS beds_available,
    RT.monthly_fee
FROM Room R
JOIN Hostel   H  ON R.hostel_id    = H.hostel_id
JOIN RoomType RT ON R.room_type_id = RT.room_type_id
WHERE R.occupied_count < RT.capacity;

-- ============================================================
-- VIEW 2: Student Full Details (currently active allocations)
-- ============================================================
DROP VIEW IF EXISTS view_student_details;
CREATE VIEW view_student_details AS
SELECT
    S.student_id,
    S.full_name,
    S.email,
    S.phone,
    S.department,
    H.hostel_name,
    R.room_number,
    RT.type_name,
    RT.monthly_fee,
    A.check_in_date,
    A.status AS allocation_status,
    fn_total_paid(S.student_id) AS total_paid
FROM Student S
JOIN Allocation A  ON S.student_id    = A.student_id
JOIN Room       R  ON A.room_id       = R.room_id
JOIN Hostel     H  ON R.hostel_id     = H.hostel_id
JOIN RoomType   RT ON R.room_type_id  = RT.room_type_id
WHERE A.status = 'ACTIVE';

-- ============================================================
-- VIEW 3: Pending Maintenance Requests (with room & student)
-- ============================================================
DROP VIEW IF EXISTS view_pending_maintenance;
CREATE VIEW view_pending_maintenance AS
SELECT
    MR.request_id,
    S.full_name        AS reported_by,
    H.hostel_name,
    R.room_number,
    MR.issue_details,
    MR.status,
    MR.request_date
FROM MaintenanceRequest MR
JOIN Student S ON MR.student_id = S.student_id
JOIN Room    R ON MR.room_id    = R.room_id
JOIN Hostel  H ON R.hostel_id   = H.hostel_id
WHERE MR.status != 'COMPLETED'
ORDER BY MR.request_date DESC;

-- ============================================================
-- VIEW 4: Payment Overview
-- ============================================================
DROP VIEW IF EXISTS view_payment_overview;
CREATE VIEW view_payment_overview AS
SELECT
    P.payment_id,
    S.full_name,
    S.email,
    P.amount,
    P.payment_date,
    PM.method_name,
    P.status
FROM Payment      P
JOIN Student      S  ON P.student_id = S.student_id
JOIN PaymentMethod PM ON P.method_id  = PM.method_id
ORDER BY P.payment_date DESC;
