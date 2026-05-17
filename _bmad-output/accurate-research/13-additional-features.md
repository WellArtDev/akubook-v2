# 13. Additional Features (Fitur Tambahan)

## Overview

Additional Features in Accurate Online include security enhancements (2FA), AI-powered accounting assistance (Ailita), and advanced tax features (PPN 12% adjustment mechanism). These features enhance security, automate accounting tasks, and ensure tax compliance.

**Priority**: Security (2FA) → Tax (PPN) → AI (phased)  
**Complexity**: Medium - 2FA setup, AI training, PPN calculation rules

---

## Security Features

### 1. Two-Factor Authentication (2FA)

**Function**: Add extra security layer using Google Authenticator  
**Key Capabilities**:
- QR code scan setup
- Google Authenticator app integration
- Backup codes for recovery
- Per-user activation
- Deactivation methods (admin override, backup codes)

**Setup Process**:
1. Navigate to Settings → Security → 2FA
2. Scan QR code with Google Authenticator
3. Enter 6-digit code to verify
4. Save backup codes securely
5. 2FA active - required on every login

**Deactivation**:
- **Method 1**: Admin can deactivate for user
- **Method 2**: User enters backup code
- **Method 3**: Contact Accurate support

**Best Practices**:
- Enable for all admin users
- Store backup codes securely (password manager)
- Test deactivation process before rollout
- Document recovery procedures

**Implementation Notes**:
- 2FA rollout strategy: Admin first → Finance → All users
- Training: 15-minute session per user
- Support: Dedicated helpdesk for 2FA issues

---

## AI Features

### 2. Ailita (AI Akuntansi)

**Function**: AI-powered accounting assistant for report analysis and insights  
**Key Capabilities**:
- Trend analysis (revenue, expenses, profit over time)
- Anomaly detection (unusual transactions, spikes, drops)
- Financial ratio calculation (liquidity, profitability, efficiency)
- Branch/unit analysis (multi-location comparison)
- Natural language Q&A (ask questions, get answers)

**How to Use**:
1. Open any report (e.g., Laba/Rugi, Neraca)
2. Click AI logo at top of report
3. Ask questions in natural language
4. Get instant insights and analysis

**Example Questions**:
- "Why did revenue drop in March?"
- "Which branch has highest profit margin?"
- "What's our current ratio?"
- "Show me top 5 expense categories"
- "Compare Q1 vs Q2 performance"

**AI Capabilities**:
- **Trend Analysis**: Identify patterns over time
- **Anomaly Detection**: Flag unusual transactions
- **Ratio Calculation**: Auto-calculate financial ratios
- **Branch Analysis**: Compare multi-location performance
- **Q&A**: Answer accounting questions

**Powered By**: ChatGPT integration (OpenAI)

**Accuracy Factors**:
- Data quality (garbage in, garbage out)
- Training data (learns from your transactions)
- Question clarity (specific questions get better answers)

**Implementation Notes**:
- AI accuracy improves over time (more data = better insights)
- Review AI suggestions before acting
- Use for analysis, not decision-making (human judgment required)

---

## Tax Features

### 3. PPN 12% Adjustment Mechanism

**Function**: Handle PPN rate change from 11% to 12% (effective 2026)  
**Key Capabilities**:
- Auto-adjust PPN rate based on transaction date
- DPP (Dasar Pengenaan Pajak) configuration
- XML export for e-Faktur
- Edge case handling (late entries, mixed rates)

**8-Step Process**:

**Step 1: Backup Database**
- Full backup before any changes
- Test restore process
- Store backup securely

**Step 2: Update PPN Rate**
- Navigate to Settings → Tax → PPN Rate
- Change from 11% to 12%
- Effective date: 2026-01-01

**Step 3: Configure DPP**
- DPP = Dasar Pengenaan Pajak (tax base)
- Options: Include/Exclude PPN in price
- Set per item or globally

**Step 4: Test Transactions**
- Create test invoice with 12% PPN
- Verify calculation: DPP × 12% = PPN
- Check journal entries

**Step 5: Train Users**
- 30-minute training session
- Focus on date-based rate selection
- Document edge cases

**Step 6: Monitor Transactions**
- Review all invoices for correct rate
- Flag errors immediately
- Correct before e-Faktur generation

**Step 7: Export XML**
- Generate e-Faktur XML
- Verify PPN rate in XML
- Upload to DJP portal

**Step 8: Reconcile**
- Monthly PPN reconciliation
- Compare system vs e-Faktur
- Resolve discrepancies

**Edge Cases**:

**Late Entry (Invoice Date < 2026, Entry Date ≥ 2026)**:
- Use 11% PPN (based on invoice date, not entry date)
- Manual override if system defaults to 12%

**Mixed Rates (Same Invoice, Different Items)**:
- Not supported - split into 2 invoices
- Invoice 1: Items with 11% PPN
- Invoice 2: Items with 12% PPN

**Partial Payment (Invoice 11%, Payment 12%)**:
- PPN rate locked at invoice date
- Payment rate doesn't affect PPN

**Critical Checks**:
1. **Transaction Date**: System uses transaction date, not entry date
2. **Manual Override**: User can override rate (risk of error)
3. **E-Faktur Sync**: Verify rate matches e-Faktur XML
4. **Audit Trail**: Log all rate changes

**Implementation Notes**:
- Accurate doesn't auto-restrict PPN rates - **user responsible** for correct rate selection per transaction date
- Training critical: Users must understand date-based rate logic
- Monthly reconciliation: Compare system PPN vs e-Faktur to catch errors

---

## Priority for AkuBook MVP

### Phase 1 (Must Have):
1. **2FA** - Security foundation for multi-user system

### Phase 2 (Should Have):
2. **PPN 12%** - Tax compliance (effective 2026)

### Phase 3 (Nice to Have):
3. **Ailita AI** - Advanced analytics (phased rollout)

---

## Technical Notes

### 2FA Implementation
- **Library**: Google Authenticator compatible (TOTP)
- **Backup Codes**: 10 single-use codes per user
- **Session**: 2FA required on every login (no "remember device")
- **Admin Override**: Admin can deactivate 2FA for locked-out users

### Ailita AI Implementation
- **API**: ChatGPT integration (OpenAI)
- **Data Privacy**: Anonymized data sent to AI (no customer names)
- **Rate Limits**: API call limits (check OpenAI pricing)
- **Fallback**: Manual analysis if AI unavailable

### PPN 12% Implementation
- **Rate Table**: Store historical rates (11% before 2026, 12% after)
- **Date Logic**: Transaction date determines rate
- **Override**: Allow manual override with audit trail
- **Validation**: Alert if rate doesn't match transaction date

---

## Common Pitfalls

### 2FA
1. **Lost Phone**: No backup codes = locked out (admin override required)
2. **Wrong Time**: Phone time must sync with server (TOTP requirement)
3. **Multiple Devices**: Each device needs separate setup

### Ailita AI
1. **Over-Reliance**: AI suggestions need human review
2. **Data Quality**: Poor data = poor insights
3. **Privacy**: Don't share sensitive data in questions

### PPN 12%
1. **Wrong Rate**: User selects 12% for 2025 transaction (should be 11%)
2. **Late Entry**: System defaults to 12% for old invoice (should be 11%)
3. **No Reconciliation**: Errors accumulate without monthly checks

---

**Source**: Accurate Online Help Documentation (https://help.accurate.id/product/fitur-tambahan/)  
**Last Updated**: May 2026  
**Compliance**: Indonesian tax regulations (PPN 12% effective 2026)
