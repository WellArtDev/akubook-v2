# Scenario 05: Attendance Management

**User:** HRD Admin / Employee  
**Priority:** MEDIUM (Payroll dependency)  
**Frequency:** Daily  
**Success Metric:** Attendance synced in real-time, 100% accuracy

---

## Scenario Goal

HRD Admin monitors employee attendance with real-time sync from ZKTeco devices, and employees can view their own attendance records.

---

## User Context

**Who:** HRD Admin (Budi) monitoring attendance, Employees checking their records

**When:** Daily (morning check-in, evening check-out)

**Why:** Track employee presence, calculate overtime, prepare payroll data

**Current Pain (from Accurate):** Manual attendance entry, no device integration, error-prone, time-consuming

---

## Sunshine Path (Happy Flow)

### Step 1: Real-Time Attendance Sync

**Page:** Attendance Dashboard

**User Action (HRD Admin):**
- Opens Attendance module
- Sees real-time attendance status

**System Shows:**
- Today's attendance summary:
  - Present: 145/150 employees
  - Late: 3 employees
  - Absent: 2 employees
  - On Leave: 5 employees
- Live sync status: "Last synced 2 minutes ago"
- Branch breakdown (5 branches)

**User Input:**
- Reviews attendance status
- Clicks "View Details"

**System Response:**
- Shows detailed attendance list
- Auto-refreshes every 5 minutes

**Next:** Review individual records

---

### Step 2: Review Attendance Records

**Page:** Attendance List

**User Action:**
- Filters by date/branch/department
- Searches for specific employee

**System Shows:**
- Attendance table:
  - Employee name, branch, check-in time, check-out time, hours worked
  - Status indicators (present/late/absent/leave)
  - Overtime hours (if applicable)

**User Input:**
- Clicks employee row to view details
- Edits attendance if needed (manual correction)

**System Response:**
- Shows employee attendance detail
- Allows manual adjustment with reason
- Logs audit trail

**Next:** Approve overtime or leave

---

### Step 3: Approve Overtime

**Page:** Overtime Approval

**User Action:**
- Sees "3 overtime requests pending"
- Clicks to review

**System Shows:**
- Overtime request list:
  - Employee: Andi Wijaya
  - Date: 2026-05-10
  - Hours: 3 hours
  - Reason: "Project deadline"
  - Status: Pending

**User Input:**
- Reviews overtime request
- Clicks "Approve" or "Reject"

**System Response:**
- Updates overtime status
- Calculates overtime pay
- Notifies employee
- Adds to payroll calculation

**Next:** Done (overtime approved)

---

### Step 4: Employee Self-Service (View Attendance)

**Page:** Employee Portal

**User Action (Employee):**
- Logs in to employee portal
- Clicks "My Attendance"

**System Shows:**
- Personal attendance calendar (current month)
- Daily records:
  - Date, check-in, check-out, hours worked
  - Status (present/late/absent/leave)
  - Overtime hours
- Monthly summary:
  - Days worked: 20/22
  - Late days: 1
  - Overtime hours: 8 hours

**User Input:**
- Reviews attendance records
- Clicks "Request Leave" or "Report Issue"

**System Response:**
- Shows leave request form or issue report form

**Next:** Submit leave request (if needed)

---

### Step 5: Leave Request

**Page:** Leave Request Form

**User Action (Employee):**
- Fills leave request form:
  - Leave type (annual/sick/emergency)
  - Start date, end date
  - Reason

**User Input:**
- Submits leave request

**System Response:**
- Creates leave request
- Sends notification to HRD Admin
- Shows "Request submitted" confirmation

**Next:** Wait for approval

---

### Step 6: Approve Leave Request

**Page:** Leave Approval

**User Action (HRD Admin):**
- Sees "5 leave requests pending"
- Clicks to review

**System Shows:**
- Leave request list:
  - Employee: Siti Nurhaliza
  - Leave type: Annual Leave
  - Dates: May 15-17 (3 days)
  - Reason: "Family vacation"
  - Remaining leave balance: 12 days

**User Input:**
- Reviews leave request
- Clicks "Approve" or "Reject"

**System Response:**
- Updates leave status
- Deducts from leave balance
- Notifies employee
- Blocks attendance for leave dates

**Next:** Done (leave approved)

---

## Pages/Screens Needed

1. **Attendance Dashboard** - Real-time attendance summary
2. **Attendance List** - Detailed attendance records
3. **Overtime Approval** - Review and approve overtime
4. **Employee Portal** - Self-service attendance view
5. **Leave Request Form** - Employee leave request
6. **Leave Approval** - HRD Admin leave approval

---

## Data Models Required

### Tables

**attendance_records**
- id, employee_id, date, check_in, check_out
- hours_worked, overtime_hours, status (present/late/absent/leave)
- synced_from_device, device_id, created_at, updated_at

**overtime_requests**
- id, employee_id, date, hours, reason
- status (pending/approved/rejected), approved_by, approved_at
- created_at, updated_at

**leave_requests**
- id, employee_id, leave_type, start_date, end_date, days
- reason, status (pending/approved/rejected)
- approved_by, approved_at, created_at, updated_at

**leave_balances**
- id, employee_id, leave_type, total_days, used_days, remaining_days
- year, created_at, updated_at

**attendance_devices**
- id, company_id, branch_id, device_type (zkteco/fingerprint)
- device_id, ip_address, status (active/inactive)
- last_sync_at, created_at, updated_at

---

## Integration Requirements

**ZKTeco Device Integration:**
- Real-time attendance sync via ZKTeco SDK
- Support for fingerprint and face recognition
- Auto-sync every 5 minutes
- Manual sync option
- Device status monitoring

**Sync Logic:**
- Fetch attendance data from ZKTeco device
- Match employee by device user ID
- Create attendance record
- Calculate hours worked
- Detect overtime (>8 hours/day)
- Update attendance dashboard

---

## Acceptance Criteria

**Functional:**
- ✅ Attendance syncs from ZKTeco in real-time
- ✅ Overtime auto-detected (>8 hours/day)
- ✅ Leave requests submitted by employees
- ✅ Leave approved by HRD Admin
- ✅ Attendance records editable (with audit trail)
- ✅ Employee self-service portal

**Performance:**
- ✅ Attendance sync completes in <5 minutes
- ✅ Dashboard loads in <2 seconds
- ✅ Real-time updates every 5 minutes

**Security:**
- ✅ Employees can only view their own attendance
- ✅ HRD Admin can view all attendance
- ✅ Audit trail for manual adjustments
- ✅ Leave balance validation

**UX:**
- ✅ Clear status indicators (present/late/absent)
- ✅ Real-time sync status
- ✅ One-click overtime approval
- ✅ Mobile-friendly employee portal

---

## Design Notes

**Tone:**
- Efficient, transparent (daily routine)
- Clear status indicators (green/yellow/red)
- Helpful guidance for first-time users
- "Attendance synced" confirmation

**UX Principles:**
- Real-time sync (no manual entry)
- Auto-detection (overtime, late)
- Self-service (employee empowerment)
- One-click approval (fast processing)

**Mobile Consideration:**
- Employee portal mobile-friendly
- HRD Admin desktop-only (complex management)

---

## Related Scenarios

- **04: Payroll Processing** - Attendance data source
- **10: Profile & Help** - Employee self-service portal

---

## Accurate Feature Parity

**Accurate Attendance includes:**
- Manual attendance entry
- Leave management
- Overtime tracking

**AkuBook Enhancement:**
- ZKTeco device integration (Accurate doesn't have this)
- Real-time sync (Accurate manual)
- Employee self-service portal (Accurate limited)
- Auto-overtime detection (Accurate manual)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
