-- ============================================================
-- HOSTEL ALLOCATION & MANAGEMENT SYSTEM (HAMS)
-- queries.sql — Complex SQL Queries for Presentation (Week 13)
-- ============================================================

USE hams;

-- ============================================================
-- QUERY 1: Monthly Revenue Report
-- ============================================================
SELECT
    MONTHNAME(payment_date) AS month,
    YEAR(payment_date)      AS year,
    COUNT(*)                AS total_transactions,
    SUM(amount)             AS total_revenue
FROM Payment
WHERE status = 'PAID'
GROUP BY YEAR(payment_date), MONTH(payment_date)
ORDER BY year DESC, MONTH(payment_date);

-- ============================================================
-- QUERY 2: Occupancy Rate Per Hostel (using function)
-- ============================================================
SELECT
    H.hostel_id,
    H.hostel_name,
    H.location,
    COUNT(R.room_id)             AS total_rooms,
    SUM(R.occupied_count)        AS total_occupied,
    fn_occupancy_rate(H.hostel_id) AS occupancy_pct
FROM Hostel H
JOIN Room R ON H.hostel_id = R.hostel_id
GROUP BY H.hostel_id;

-- ============================================================
-- QUERY 3: Students With Highest Total Payment (Subquery)
-- ============================================================
SELECT
    S.full_name,
    S.email,
    fn_total_paid(S.student_id) AS total_paid
FROM Student S
WHERE fn_total_paid(S.student_id) = (
    SELECT MAX(sub.t)
    FROM (
        SELECT IFNULL(SUM(amount), 0) AS t
        FROM Payment
        WHERE status = 'PAID'
        GROUP BY student_id
    ) sub
);

-- ============================================================
-- QUERY 4: Students With Payment Dues (Pending)
-- ============================================================
SELECT
    S.student_id,
    S.full_name,
    S.email,
    P.amount      AS pending_amount,
    P.payment_date
FROM Student S
JOIN Payment P ON S.student_id = P.student_id
WHERE P.status = 'PENDING'
ORDER BY P.payment_date;

-- ============================================================
-- QUERY 5: Rooms Below Average Occupancy (Subquery)
-- ============================================================
SELECT
    R.room_number,
    H.hostel_name,
    RT.type_name,
    R.occupied_count,
    RT.capacity
FROM Room R
JOIN Hostel   H  ON R.hostel_id    = H.hostel_id
JOIN RoomType RT ON R.room_type_id = RT.room_type_id
WHERE R.occupied_count < (
    SELECT AVG(occupied_count) FROM Room
);

-- ============================================================
-- QUERY 6: Multi-Table Join — Full Student Room Info
-- ============================================================
SELECT
    S.student_id,
    S.full_name,
    S.department,
    H.hostel_name,
    R.room_number,
    RT.type_name,
    RT.monthly_fee,
    A.check_in_date,
    fn_total_paid(S.student_id) AS total_paid
FROM Student S
JOIN Allocation A  ON S.student_id   = A.student_id
JOIN Room       R  ON A.room_id      = R.room_id
JOIN Hostel     H  ON R.hostel_id    = H.hostel_id
JOIN RoomType   RT ON R.room_type_id = RT.room_type_id
WHERE A.status = 'ACTIVE'
ORDER BY H.hostel_name, R.room_number;

-- ============================================================
-- QUERY 7: Maintenance by Category (JSON extraction)
-- ============================================================
SELECT
    JSON_UNQUOTE(JSON_EXTRACT(issue_details, '$.category'))    AS category,
    JSON_UNQUOTE(JSON_EXTRACT(issue_details, '$.priority'))    AS priority,
    COUNT(*) AS total_requests,
    SUM(CASE WHEN status='COMPLETED' THEN 1 ELSE 0 END)        AS completed,
    SUM(CASE WHEN status='PENDING'   THEN 1 ELSE 0 END)        AS pending
FROM MaintenanceRequest
GROUP BY category, priority
ORDER BY total_requests DESC;

-- ============================================================
-- QUERY 8: Revenue Rollup by Hostel and Month
-- ============================================================
SELECT
    H.hostel_name,
    MONTHNAME(P.payment_date) AS month,
    SUM(P.amount)             AS revenue
FROM Payment P
JOIN Student    S  ON P.student_id = S.student_id
JOIN Allocation A  ON S.student_id = A.student_id AND A.status = 'ACTIVE'
JOIN Room       R  ON A.room_id    = R.room_id
JOIN Hostel     H  ON R.hostel_id  = H.hostel_id
WHERE P.status = 'PAID'
GROUP BY H.hostel_name, YEAR(P.payment_date), MONTH(P.payment_date)
WITH ROLLUP;

-- ============================================================
-- QUERY 9: Hostel-Wise Student Distribution
-- ============================================================
SELECT
    H.hostel_name,
    COUNT(DISTINCT A.student_id) AS total_students,
    COUNT(DISTINCT R.room_id)    AS occupied_rooms
FROM Hostel H
JOIN Room       R ON H.hostel_id = R.hostel_id
JOIN Allocation A ON R.room_id   = A.room_id AND A.status = 'ACTIVE'
GROUP BY H.hostel_name;

-- ============================================================
-- QUERY 10: Recent Audit Log
-- ============================================================
SELECT
    audit_id,
    table_name,
    action_type,
    action_time,
    description
FROM AuditLog
ORDER BY action_time DESC
LIMIT 20;
