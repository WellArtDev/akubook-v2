# Scenario 02: Sales Order Flow - Overview

**Project:** AkuBook  
**Scenario:** 02 - Sari's Sales Order Flow  
**Created:** 2026-05-13  
**Status:** ✅ Complete - All 3 pages designed

---

## Scenario Summary

**User:** Sari (Finance Admin)  
**Goal:** Verify that Sales Order → Invoice → Journal Entry auto-posted correctly  
**Success Metric:** 95%+ auto-posting rate achieved, Finance Admin only reviews (no manual entry)

---

## Pages Designed

### Page 02.1: Accounting Dashboard
**Purpose:** Daily overview with auto-posting status and review queue  
**Key Features:**
- Auto-posting success rate (Today/Week/Month)
- Target indicator (95%+ achieved)
- Review queue (5 most recent entries)
- Quick stats (AR, AP, Cash)
- Quick actions

**User Flow:** Sari starts her day → sees 100% auto-posting today → reviews pending entries

---

### Page 02.2: Journal Entry List
**Purpose:** Filterable list of auto-generated entries with transaction chains  
**Key Features:**
- Advanced filters (date, status, source, amount)
- Search by entry/source IDs
- Inline entry preview with debit/credit
- Transaction chain visualization
- Pagination (5 entries per page)

**User Flow:** Sari clicks "Lihat Semua" → filters by date → scans entries → selects one to review

---

### Page 02.3: Journal Entry Detail
**Purpose:** Complete entry verification with audit trail and approval actions  
**Key Features:**
- Full entry information
- Visual transaction chain (SO → DO → INV → JE)
- Detailed entry lines with sub-info
- Complete audit trail
- Review actions (Mark Reviewed / Approve & Lock / Reject)
- Additional actions (Print, Email, View in GL)

**User Flow:** Sari reviews entry details → checks transaction chain → verifies amounts → marks as reviewed

---

## Design Patterns Established

### 1. Auto-Posting Visualization
- **Success Indicators:** Green checkmarks, percentage badges
- **Target Display:** Prominent "95%+ achieved" message
- **Status Cards:** Today/Week/Month breakdown

### 2. Transaction Chain
- **Horizontal Timeline:** SO → DO → INV → JE with arrows
- **Timestamps:** Each step shows when it occurred
- **Linked Documents:** Click to view source documents
- **Trigger Info:** Shows what triggered auto-posting

### 3. Entry Display
- **Inline Preview:** Quick debit/credit view in list
- **Detailed Table:** Full entry lines with sub-info
- **Balance Indicator:** Green checkmark if balanced
- **Account Details:** Code + Name + Context

### 4. Review Workflow
- **Three-Stage:** Pending Review → Reviewed → Approved & Locked
- **Optional Notes:** Add comments during review
- **Audit Trail:** Every action logged with timestamp
- **Permissions:** Role-based access to approve/reject

### 5. Navigation
- **Breadcrumbs:** Clear path back to list
- **Contextual Links:** Entry IDs, source documents all linked
- **Quick Actions:** Print, Email, View in GL
- **Filter Persistence:** Maintains filter state on back navigation

---

## Technical Specifications

### APIs Required
1. **Auto-Posting Stats API:** GET `/api/accounting/auto-posting-stats`
2. **Review Queue API:** GET `/api/accounting/journal-entries?status=pending_review`
3. **Quick Stats API:** GET `/api/accounting/quick-stats`
4. **Entry List API:** GET `/api/accounting/journal-entries` (with filters)
5. **Entry Detail API:** GET `/api/accounting/journal-entries/{id}`
6. **Review Action API:** POST `/api/accounting/journal-entries/{id}/review`

### Data Models
- **Journal Entry:** ID, posted_at, type, status, source, lines, totals
- **Transaction Chain:** Array of source documents with timestamps
- **Audit Trail:** Array of actions with timestamps and users
- **Entry Line:** account_code, account_name, debit, credit, sub_info

### Permissions
- **View Dashboard:** Finance Admin, Finance Manager
- **View Entries:** Finance Admin, Finance Manager
- **Mark Reviewed:** Finance Admin
- **Approve & Lock:** Finance Admin + Manager
- **Reject:** Finance Admin + Manager

---

## Success Metrics

### User Success (Sari)
- ✅ Sees 95%+ auto-posting rate immediately
- ✅ Identifies entries needing review in < 10 seconds
- ✅ Reviews single entry in < 2 minutes
- ✅ Feels confident system is working correctly

### Business Success
- ✅ 95%+ transactions auto-post to journal
- ✅ Finance Admin spends < 5 minutes on dashboard review
- ✅ Finance Admin reviews 10+ entries per hour
- ✅ Clear audit trail for compliance

---

## Edge Cases Handled

### Dashboard
- No pending reviews → Show success message
- Below 95% target → Show warning indicator
- Failed auto-posting → Show separate failed count

### Entry List
- No results → Show empty state with filter suggestions
- Unbalanced entry → Red indicator, cannot approve
- Long transaction chain → Truncate with hover/expand

### Entry Detail
- Unbalanced entry → Disable approve button, show warning
- Missing transaction chain → Show message, allow review
- Already reviewed → Show reviewer info, disable buttons
- Locked entry → Show lock icon, disable all edits

---

## Design Decisions

### Why This Flow Works

**Dashboard First:**
- Sari sees success immediately (100% today)
- Monthly target (95%) prominently displayed
- Review queue is actionable, not just informational

**List View:**
- Inline preview reduces need to open every entry
- Transaction chain provides context
- Filters support common workflows

**Detail View:**
- Complete context before approving
- Transaction chain proves auto-posting worked
- Audit trail supports compliance
- Clear approval workflow prevents errors

### What Makes This Different

**vs Manual Entry Systems:**
- Auto-posting eliminates 95%+ of manual work
- Finance Admin reviews instead of creates
- Transaction chain shows automation working

**vs Other ERP Systems:**
- Prominent success metrics (95%+ target)
- Visual transaction chain (not just text)
- Three-stage review workflow (not binary approve/reject)
- Inline entry preview (not just list of IDs)

---

## Next Steps

### For Development
1. Implement auto-posting engine (backend)
2. Create journal entry APIs
3. Build dashboard with real-time stats
4. Implement entry list with filters
5. Build entry detail with transaction chain
6. Add review workflow with permissions

### For Testing
1. Test auto-posting accuracy (95%+ target)
2. Verify transaction chain display
3. Test review workflow permissions
4. Validate audit trail logging
5. Test filter combinations
6. Verify balance calculations

### For Documentation
1. Auto-posting rules documentation
2. Review workflow guide
3. Audit trail requirements
4. Permission matrix
5. API documentation

---

## Files Created

```
02-sales-order-flow/
├── 02-sales-order-flow.md (scenario definition)
├── 02.1-accounting-dashboard/
│   └── 02.1-accounting-dashboard.md
├── 02.2-journal-entry-list/
│   └── 02.2-journal-entry-list.md
└── 02.3-journal-entry-detail/
    └── 02.3-journal-entry-detail.md
```

---

## Time Estimate

**Design Time:** ~60 minutes (autonomous design)  
**Development Time:** ~40 hours (3 pages + APIs + auto-posting engine)  
**Testing Time:** ~8 hours (functional + integration + user acceptance)

---

_Scenario 02: Sales Order Flow - Complete 2026-05-13_
