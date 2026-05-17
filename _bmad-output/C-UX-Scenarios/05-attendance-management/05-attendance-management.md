# 05: Budi's Attendance Management

**Project:** AkuBook  
**Created:** 2026-05-12  
**Method:** Whiteport Design Studio (WDS)

---

## Transaction (Q1)

**What this scenario covers:**
Daily: Employees clock in/out via mobile → HRD monitors real-time attendance → approves leave requests

---

## Business Goal (Q2)

**Goal:** 🚀 SECONDARY: Real-Time Attendance Visibility  
**Objective:** HRD sees real-time attendance across all cabang, fast leave approval (< 1 hour)

---

## User & Situation (Q3)

**Persona:** Budi (HRD Manager, SECONDARY) + Employees  
**Situation:** Morning, Budi checking who's in/late/absent across 5 cabang. Employee (via mobile) submitted leave request, waiting for approval.

---

## Driving Forces (Q4)

**Hope:** Real-time attendance across all cabang, late employees flagged automatically, leave requests approved in minutes (not WhatsApp chaos).

**Worry:** Late/absent employees undetected until month-end, leave requests pile up in WhatsApp, no visibility into attendance patterns.

---

## Device & Starting Point (Q5 + Q6)

**Device:** Desktop (HRD), Mobile (employees)  
**Entry:** Daily routine — morning attendance check (HRD), throughout day for clock in/out and leave requests (employees).

---

## Best Outcome (Q7)

**User Success:**
Employees clock in/out easily via mobile (face recognition), leave requests approved in minutes. Budi monitors real-time attendance, late employees flagged. No WhatsApp chaos.

**Business Success:**
Real-time attendance data (no month-end surprises), proactive attendance management, employee satisfaction (fast leave approval), accurate payroll input.

---

## Shortest Path (Q8)

1. **Attendance (Clock In/Out - Mobile)** — Employee opens mobile app, uses face recognition to clock in
2. **Attendance (List - Desktop)** — Budi sees real-time attendance dashboard, 5 cabang overview, late employees flagged
3. **Leave Request (Create - Mobile)** — Employee submits leave request via mobile app
4. **Leave Request (List - Desktop)** — Budi sees leave request notification, reviews balance, approves in one click ✓

---

## Trigger Map Connections

**Persona:** Budi (HRD Manager, SECONDARY)

**Driving Forces Addressed:**
- ✅ **Want:** Real-time attendance visibility, fast leave approval
- ❌ **Fear:** Late/absent undetected, WhatsApp chaos, no visibility

**Business Goal:** 🚀 SECONDARY: Real-time attendance → proactive management → accurate payroll

---

## Scenario Steps

| Step | Folder | Purpose | Exit Action |
|------|--------|---------|-------------|
| 05.1 | `05.1-attendance-clock-mobile/` | Employee clocks in via mobile | Face recognition → clock in |
| 05.2 | `05.2-attendance-list-desktop/` | HRD monitors real-time attendance | View attendance dashboard |
| 05.3 | `05.3-leave-request-create-mobile/` | Employee submits leave request | Submit leave request |
| 05.4 | `05.4-leave-request-list-desktop/` | HRD approves leave request | Click "Approve" ✓ |

---

_Scenario 05: Budi's Attendance Management_
