# HOSTEL ALLOCATION & MANAGEMENT SYSTEM (HAMS)
## Complete Setup Guide — XAMPP + MySQL Workbench

---

## ✅ STEP 1: Start XAMPP

1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Both should show green/running status

---

## ✅ STEP 2: Copy Project to XAMPP

Copy the entire `hams/` folder to:
```
C:\xampp\htdocs\hams\
```

Final path should be:
```
C:\xampp\htdocs\hams\index.php   ← should exist
C:\xampp\htdocs\hams\sql\        ← SQL files folder
C:\xampp\htdocs\hams\config\     ← db.php
```

---

## ✅ STEP 3: Import SQL Files (MySQL Workbench)

Open **MySQL Workbench** and connect to `localhost` (root, no password by default).

Run the SQL files **IN THIS EXACT ORDER**:

| Order | File | Purpose |
|-------|------|---------|
| 1 | `sql/schema.sql` | Creates database + 14 tables + indexes |
| 2 | `sql/functions.sql` | Creates 3 stored functions |
| 3 | `sql/procedures.sql` | Creates 4 stored procedures |
| 4 | `sql/triggers.sql` | Creates 5 triggers |
| 5 | `sql/views.sql` | Creates 4 views |
| 6 | `sql/sample_data.sql` | Inserts sample data |

### How to run each file:
1. In MySQL Workbench → **File → Open SQL Script**
2. Select the file
3. Click the ⚡ **lightning bolt** (Execute All)
4. Check the Output panel — no red errors

---

## ✅ STEP 4: Open in Browser

```
http://localhost/hams
```

---

## ✅ STEP 5: Login

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Warden | `warden1` | `warden123` |
| Student | `student001` | `student123` |

---

## 📁 Full Project Structure

```
hams/
│
├── config/
│   └── db.php                    ← Database connection
│
├── sql/
│   ├── schema.sql                ← Tables + indexes (run 1st)
│   ├── functions.sql             ← 3 stored functions (run 2nd)
│   ├── procedures.sql            ← 4 stored procedures (run 3rd)
│   ├── triggers.sql              ← 5 triggers (run 4th)
│   ├── views.sql                 ← 4 views (run 5th)
│   ├── sample_data.sql           ← Sample data (run 6th)
│   └── queries.sql               ← 10 complex queries for demo
│
├── admin/
│   ├── add_hostel.php            ← Add/view hostels
│   ├── add_room.php              ← Add/view rooms
│   ├── allocate_room.php         ← Allocate using proc_allocate_room
│   ├── checkout.php              ← Checkout using proc_checkout_student
│   └── reports.php               ← Analytics & reports
│
├── student/
│   ├── register.php              ← Register students
│   ├── payment.php               ← Add/view payments
│   └── view_allocation.php       ← View allocations
│
├── maintenance/
│   ├── report_issue.php          ← Report issue (JSON + procedure)
│   └── view_requests.php         ← View & update requests
│
├── auth/
│   ├── login.php                 ← Login page
│   └── logout.php                ← Logout
│
├── assets/
│   ├── css/style_inline.php      ← Shared CSS styles
│   └── nav_inline.php            ← Shared sidebar nav
│
└── index.php                     ← Dashboard (main page)
```

---

## 🗄️ Database Objects Summary

### 14 Tables (3NF Normalized)
`UserAccount` `Hostel` `RoomType` `Room` `Student` `Warden`
`Allocation` `PaymentMethod` `Payment` `MaintenanceRequest`
`VisitorLog` `Staff` `AuditLog` `Bed`

### 3 Stored Functions
| Function | Purpose |
|----------|---------|
| `fn_total_paid(student_id)` | Returns total paid by student |
| `fn_room_available(room_id)` | Returns TRUE if room has space |
| `fn_occupancy_rate(hostel_id)` | Returns occupancy % |

### 4 Stored Procedures (with Transactions)
| Procedure | Purpose |
|-----------|---------|
| `proc_allocate_room(sid, rid, OUT result)` | Allocates room with transaction |
| `proc_checkout_student(sid, OUT result)` | Checks out student with transaction |
| `proc_add_payment(sid, amt, mid, status)` | Records payment |
| `proc_report_maintenance(rid, sid, cat, desc, pri)` | Reports issue with JSON |

### 5 Triggers
| Trigger | Type | Purpose |
|---------|------|---------|
| `trg_prevent_over_allocation` | BEFORE INSERT | Prevents overbooking |
| `trg_audit_allocation` | AFTER INSERT | Logs allocations |
| `trg_audit_payment` | AFTER INSERT | Logs payments |
| `trg_audit_checkout` | AFTER UPDATE | Logs checkouts |
| `trg_increment_hostel_rooms` | AFTER INSERT | Keeps room count in sync |

### 4 Views
| View | Purpose |
|------|---------|
| `view_available_rooms` | Rooms with free beds |
| `view_student_details` | Active student + room info + fn_total_paid |
| `view_pending_maintenance` | Pending/in-progress requests |
| `view_payment_overview` | Full payment history |

### 3 Indexes
```sql
CREATE INDEX idx_student_allocation ON Allocation(student_id);
CREATE INDEX idx_payment_date       ON Payment(payment_date);
CREATE INDEX idx_room_hostel        ON Room(hostel_id);
```

---

## 📊 Week 13 Checklist

- [x] 14 Entities (10-15 required)
- [x] Normalization to 3NF
- [x] 3+ Stored Functions
- [x] 3+ Stored Procedures with Transactions
- [x] 3+ Triggers (BEFORE + AFTER + Audit)
- [x] 2+ Views
- [x] 2+ Indexes
- [x] 10+ Complex SQL Queries (queries.sql)
- [x] JSON column (advanced feature)
- [x] AuditLog system
- [x] Role-based access (Admin / Warden / Student)
- [x] Multi-table joins
- [x] Nested subqueries
- [x] GROUP BY + ROLLUP
- [x] Analytical queries (revenue, occupancy, distribution)
- [x] Full business workflow demonstration

---

## 🔧 Troubleshooting

**Problem:** Login page shows "Database connection failed"
- Make sure XAMPP MySQL is running
- Check `config/db.php` — password should be empty `''` for default XAMPP

**Problem:** Blank page
- Enable PHP errors: add `error_reporting(E_ALL); ini_set('display_errors', 1);` to top of any PHP file

**Problem:** Procedures/Functions won't run
- In MySQL Workbench, make sure you're running files individually (not all at once)
- Run schema.sql BEFORE functions.sql

**Problem:** "Function does not exist" error
- Make sure you ran functions.sql before procedures.sql and triggers.sql
