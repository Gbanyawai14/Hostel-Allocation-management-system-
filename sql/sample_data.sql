-- ============================================================
-- HOSTEL ALLOCATION & MANAGEMENT SYSTEM (HAMS)
-- sample_data.sql — Run SIXTH (last)
-- ============================================================

USE hams;

-- ============================================================
-- UserAccount (passwords are MD5 of 'password123')
-- ============================================================
INSERT INTO UserAccount (username, password, role) VALUES
('admin',      MD5('admin123'),    'ADMIN'),
('warden1',    MD5('warden123'),   'WARDEN'),
('warden2',    MD5('warden123'),   'WARDEN'),
('student001', MD5('student123'),  'STUDENT'),
('student002', MD5('student123'),  'STUDENT'),
('student003', MD5('student123'),  'STUDENT'),
('student004', MD5('student123'),  'STUDENT'),
('student005', MD5('student123'),  'STUDENT');

-- ============================================================
-- Hostel
-- ============================================================
INSERT INTO Hostel (hostel_name, location, total_rooms) VALUES
('Al-Hikmah Block A', 'North Campus', 0),
('Al-Hikmah Block B', 'South Campus', 0),
('Ar-Razi Block',     'East Campus',  0);

-- ============================================================
-- RoomType
-- ============================================================
INSERT INTO RoomType (type_name, capacity, monthly_fee) VALUES
('Single',   1, 350.00),
('Double',   2, 250.00),
('Triple',   3, 200.00),
('Quad',     4, 175.00);

-- ============================================================
-- Room (trigger will auto-increment hostel total_rooms)
-- ============================================================
INSERT INTO Room (hostel_id, room_type_id, room_number, occupied_count) VALUES
(1, 1, 'A101', 0),
(1, 2, 'A102', 1),
(1, 2, 'A103', 2),
(1, 3, 'A104', 0),
(2, 1, 'B101', 1),
(2, 2, 'B102', 0),
(2, 4, 'B103', 3),
(3, 1, 'C101', 0),
(3, 2, 'C102', 1),
(3, 3, 'C103', 2);

-- ============================================================
-- Staff
-- ============================================================
INSERT INTO Staff (full_name, role, phone) VALUES
('Ahmad Yusuf',    'Maintenance',  '0123456789'),
('Nurul Ain',      'Security',     '0198765432'),
('Faridah Hassan', 'Cleaning',     '0112233445');

-- ============================================================
-- Warden
-- ============================================================
INSERT INTO Warden (user_id, full_name, phone, hostel_id) VALUES
(2, 'Ustaz Hafiz Razak', '0111234567', 1),
(3, 'Puan Siti Hajar',   '0119876543', 2);

-- ============================================================
-- Student
-- ============================================================
INSERT INTO Student (user_id, full_name, email, phone, department, registration_date) VALUES
(4, 'Muhammad Amadu',     'amadu@student.edu.my',   '0134455667', 'Computer Science', '2024-01-10'),
(5, 'Fatimah Zahra',      'fatimah@student.edu.my', '0145566778', 'Engineering',       '2024-01-11'),
(6, 'Ismail Ibrahim',     'ismail@student.edu.my',  '0156677889', 'Medicine',          '2024-01-12'),
(7, 'Khadijah Osman',     'khadijah@student.edu.my','0167788990', 'Law',               '2024-01-13'),
(8, 'Yusuf Al-Qaradawi',  'yusuf@student.edu.my',   '0178899001', 'Shariah',           '2024-01-14');

-- ============================================================
-- PaymentMethod
-- ============================================================
INSERT INTO PaymentMethod (method_name) VALUES
('Cash'),
('Online Transfer'),
('Credit Card'),
('Debit Card');

-- ============================================================
-- Allocation (manually set — bypasses trigger for sample data)
-- ============================================================
INSERT INTO Allocation (student_id, room_id, check_in_date, status) VALUES
(1, 2, '2024-01-15', 'ACTIVE'),
(2, 3, '2024-01-16', 'ACTIVE'),
(3, 3, '2024-01-17', 'ACTIVE'),
(4, 5, '2024-01-18', 'ACTIVE'),
(5, 9, '2024-01-19', 'ACTIVE');

-- ============================================================
-- Payment
-- ============================================================
INSERT INTO Payment (student_id, amount, payment_date, method_id, status) VALUES
(1, 250.00, '2024-02-01', 2, 'PAID'),
(1, 250.00, '2024-03-01', 2, 'PAID'),
(2, 250.00, '2024-02-01', 1, 'PAID'),
(3, 250.00, '2024-02-01', 3, 'PAID'),
(4, 350.00, '2024-02-01', 2, 'PAID'),
(5, 250.00, '2024-02-01', 4, 'PENDING');

-- ============================================================
-- MaintenanceRequest (with JSON)
-- ============================================================
INSERT INTO MaintenanceRequest (room_id, student_id, issue_details, status, request_date) VALUES
(2, 1, JSON_OBJECT('category','Plumbing','description','Leaking tap in bathroom','priority','High'), 'PENDING', '2024-02-10'),
(3, 2, JSON_OBJECT('category','Electrical','description','Ceiling fan not working','priority','Medium'), 'IN_PROGRESS', '2024-02-12'),
(5, 4, JSON_OBJECT('category','Furniture','description','Broken bed frame','priority','Low'), 'COMPLETED', '2024-02-08');

-- ============================================================
-- VisitorLog
-- ============================================================
INSERT INTO VisitorLog (student_id, visitor_name, visit_date, entry_time, exit_time) VALUES
(1, 'Amina Amadu',   '2024-02-15', '10:00:00', '12:00:00'),
(2, 'Hassan Zahra',  '2024-02-16', '14:00:00', '16:30:00'),
(3, 'Musa Ibrahim',  '2024-02-17', '09:00:00', '11:00:00');

-- ============================================================
-- AuditLog (seed entry)
-- ============================================================
INSERT INTO AuditLog (table_name, action_type, description) VALUES
('System', 'INIT', 'HAMS database initialized with sample data.');
