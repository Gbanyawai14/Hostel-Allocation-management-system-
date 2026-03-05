-- ============================================================
-- HOSTEL ALLOCATION & MANAGEMENT SYSTEM (HAMS)
-- schema.sql — Run this FIRST in MySQL Workbench
-- ============================================================

DROP DATABASE IF EXISTS hams;
CREATE DATABASE hams;
USE hams;

-- ============================================================
-- TABLE 1: UserAccount (Role-Based Access)
-- ============================================================
CREATE TABLE UserAccount (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('ADMIN','WARDEN','STUDENT') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE 2: Hostel
-- ============================================================
CREATE TABLE Hostel (
    hostel_id   INT AUTO_INCREMENT PRIMARY KEY,
    hostel_name VARCHAR(100) NOT NULL,
    location    VARCHAR(150),
    total_rooms INT DEFAULT 0
);

-- ============================================================
-- TABLE 3: RoomType
-- ============================================================
CREATE TABLE RoomType (
    room_type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name    VARCHAR(50),
    capacity     INT NOT NULL,
    monthly_fee  DECIMAL(10,2) NOT NULL
);

-- ============================================================
-- TABLE 4: Room
-- ============================================================
CREATE TABLE Room (
    room_id       INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id     INT,
    room_type_id  INT,
    room_number   VARCHAR(20),
    occupied_count INT DEFAULT 0,
    FOREIGN KEY (hostel_id)    REFERENCES Hostel(hostel_id)   ON DELETE CASCADE,
    FOREIGN KEY (room_type_id) REFERENCES RoomType(room_type_id)
);

-- ============================================================
-- TABLE 5: Student
-- ============================================================
CREATE TABLE Student (
    student_id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT,
    full_name         VARCHAR(100) NOT NULL,
    email             VARCHAR(100) UNIQUE NOT NULL,
    phone             VARCHAR(20),
    department        VARCHAR(100),
    registration_date DATE DEFAULT (CURDATE()),
    FOREIGN KEY (user_id) REFERENCES UserAccount(user_id)
);

-- ============================================================
-- TABLE 6: Warden
-- ============================================================
CREATE TABLE Warden (
    warden_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT,
    full_name VARCHAR(100) NOT NULL,
    phone     VARCHAR(20),
    hostel_id INT,
    FOREIGN KEY (user_id)   REFERENCES UserAccount(user_id),
    FOREIGN KEY (hostel_id) REFERENCES Hostel(hostel_id)
);

-- ============================================================
-- TABLE 7: Allocation
-- ============================================================
CREATE TABLE Allocation (
    allocation_id  INT AUTO_INCREMENT PRIMARY KEY,
    student_id     INT,
    room_id        INT,
    check_in_date  DATE,
    check_out_date DATE,
    status         ENUM('ACTIVE','CHECKED_OUT') DEFAULT 'ACTIVE',
    FOREIGN KEY (student_id) REFERENCES Student(student_id),
    FOREIGN KEY (room_id)    REFERENCES Room(room_id)
);

-- ============================================================
-- TABLE 8: PaymentMethod
-- ============================================================
CREATE TABLE PaymentMethod (
    method_id   INT AUTO_INCREMENT PRIMARY KEY,
    method_name VARCHAR(50) NOT NULL
);

-- ============================================================
-- TABLE 9: Payment
-- ============================================================
CREATE TABLE Payment (
    payment_id   INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT,
    amount       DECIMAL(10,2) NOT NULL,
    payment_date DATE DEFAULT (CURDATE()),
    method_id    INT,
    status       ENUM('PAID','PENDING') DEFAULT 'PAID',
    FOREIGN KEY (student_id) REFERENCES Student(student_id),
    FOREIGN KEY (method_id)  REFERENCES PaymentMethod(method_id)
);

-- ============================================================
-- TABLE 10: MaintenanceRequest (JSON – Advanced Feature)
-- ============================================================
CREATE TABLE MaintenanceRequest (
    request_id   INT AUTO_INCREMENT PRIMARY KEY,
    room_id      INT,
    student_id   INT,
    issue_details JSON,
    status       ENUM('PENDING','IN_PROGRESS','COMPLETED') DEFAULT 'PENDING',
    request_date DATE DEFAULT (CURDATE()),
    FOREIGN KEY (room_id)    REFERENCES Room(room_id),
    FOREIGN KEY (student_id) REFERENCES Student(student_id)
);

-- ============================================================
-- TABLE 11: VisitorLog
-- ============================================================
CREATE TABLE VisitorLog (
    visitor_id   INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT,
    visitor_name VARCHAR(100) NOT NULL,
    visit_date   DATE DEFAULT (CURDATE()),
    entry_time   TIME,
    exit_time    TIME,
    FOREIGN KEY (student_id) REFERENCES Student(student_id)
);

-- ============================================================
-- TABLE 12: Staff
-- ============================================================
CREATE TABLE Staff (
    staff_id  INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    role      VARCHAR(50),
    phone     VARCHAR(20)
);

-- ============================================================
-- TABLE 13: AuditLog
-- ============================================================
CREATE TABLE AuditLog (
    audit_id    INT AUTO_INCREMENT PRIMARY KEY,
    table_name  VARCHAR(50),
    action_type VARCHAR(50),
    action_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT
);

-- ============================================================
-- TABLE 14: Bed (Advanced – tracks individual beds in room)
-- ============================================================
CREATE TABLE Bed (
    bed_id     INT AUTO_INCREMENT PRIMARY KEY,
    room_id    INT,
    bed_label  VARCHAR(10) NOT NULL,
    is_occupied TINYINT(1) DEFAULT 0,
    FOREIGN KEY (room_id) REFERENCES Room(room_id)
);

-- ============================================================
-- INDEXES (Performance + Required for Week 13)
-- ============================================================
CREATE INDEX idx_student_allocation ON Allocation(student_id);
CREATE INDEX idx_payment_date       ON Payment(payment_date);
CREATE INDEX idx_room_hostel        ON Room(hostel_id);
