# Scenario 10: User Profile & Help System

**User:** All Users  
**Priority:** MEDIUM (User experience)  
**Frequency:** As needed  
**Success Metric:** Help found in <2 minutes

---

## Scenario Goal

Users manage their profiles, access help documentation, and get support when needed.

---

## User Context

**Who:** All users (employees, managers, admins)

**When:** Profile updates, password changes, help needed

**Why:** Maintain account security, access help resources, resolve issues

**Current Pain (from Accurate):** Limited help resources, no in-app guidance, slow support response

---

## Sunshine Path (Happy Flow)

### Step 1: View Profile

**Page:** User Profile

**User Action:**
- Clicks profile icon (top right)
- Selects "My Profile"

**System Shows:**
- Profile information:
  - Name: Sari Wijaya
  - Email: sari@distributor.com
  - Role: Finance Admin
  - Department: Finance
  - Branch: Jakarta HQ
  - Last login: 2026-05-13 08:30
- Profile photo
- Edit button

**User Input:**
- Reviews profile
- Clicks "Edit Profile"

**System Response:**
- Shows editable profile form

**Next:** Update profile

---

### Step 2: Update Profile

**Page:** Edit Profile

**User Action:**
- Updates profile information

**System Shows:**
- Editable fields:
  - Name
  - Email
  - Phone
  - Profile photo
  - Notification preferences

**User Input:**
- Updates phone number
- Uploads new profile photo
- Enables email notifications
- Clicks "Save Changes"

**System Response:**
- Validates inputs
- Updates profile
- Shows "Profile Updated" confirmation

**Next:** Done (profile updated)

---

### Step 3: Change Password

**Page:** Change Password

**User Action:**
- Clicks "Change Password" from profile menu

**System Shows:**
- Password change form:
  - Current password
  - New password
  - Confirm new password
- Password requirements:
  - Minimum 8 characters
  - At least 1 uppercase letter
  - At least 1 number
  - At least 1 special character

**User Input:**
- Enters current password
- Enters new password
- Confirms new password
- Clicks "Change Password"

**System Response:**
- Validates password requirements
- Updates password
- Logs out other sessions
- Shows "Password Changed" confirmation

**Next:** Done (password changed)

---

### Step 4: Access Help Center

**Page:** Help Center

**User Action:**
- Clicks "Help" icon (top right)
- Or presses F1 key

**System Shows:**
- Help center home:
  - Search bar
  - Popular topics:
    - How to create a sales order
    - How to process payroll
    - How to close a month
    - How to generate reports
  - Video tutorials
  - User guide (PDF)
  - Contact support

**User Input:**
- Searches for "sales order"

**System Response:**
- Shows search results:
  - How to create a sales order (article)
  - Sales order workflow (video)
  - Sales order FAQ
- Highlights matching keywords

**Next:** View help article

---

### Step 5: View Help Article

**Page:** Help Article

**User Action:**
- Clicks "How to create a sales order"

**System Shows:**
- Help article:
  - Title: How to Create a Sales Order
  - Step-by-step instructions with screenshots
  - Video tutorial (embedded)
  - Related articles
  - "Was this helpful?" feedback

**User Input:**
- Reads article
- Watches video
- Clicks "Yes, this was helpful"

**System Response:**
- Records feedback
- Shows "Thank you for your feedback"
- Suggests related articles

**Next:** Done (help found)

---

### Step 6: Contact Support

**Page:** Support Ticket

**User Action:**
- Clicks "Contact Support" from help center
- Or clicks "Report Issue" from any page

**System Shows:**
- Support ticket form:
  - Subject
  - Description
  - Category (Bug/Feature Request/Question)
  - Priority (Low/Medium/High)
  - Attachments (optional)

**User Input:**
- Fills ticket form:
  - Subject: "Cannot post sales invoice"
  - Description: "Error message when trying to post invoice INV-2026-05-001"
  - Category: Bug
  - Priority: High
  - Attaches screenshot
- Clicks "Submit Ticket"

**System Response:**
- Creates support ticket: TKT-2026-05-001
- Sends confirmation email
- Shows "Ticket Submitted" confirmation
- Estimated response time: 4 hours

**Next:** Wait for support response

---

## Pages/Screens Needed

1. **User Profile** - View profile information
2. **Edit Profile** - Update profile
3. **Change Password** - Change password
4. **Help Center** - Search and browse help
5. **Help Article** - View help content
6. **Support Ticket** - Submit support request

---

## Data Models Required

### Tables

**users**
- id, company_id, name, email, phone
- profile_photo_path, role_id, department_id, branch_id
- notification_preferences (JSON), last_login_at
- created_at, updated_at

**help_articles**
- id, title, content, category, tags
- video_url, views_count, helpful_count
- is_published, created_at, updated_at

**support_tickets**
- id, company_id, user_id, ticket_number
- subject, description, category, priority
- status (open/in_progress/resolved/closed)
- assigned_to, resolved_at, created_at, updated_at

**support_ticket_attachments**
- id, ticket_id, file_path, file_name, file_size
- created_at, updated_at

**support_ticket_comments**
- id, ticket_id, user_id, comment, is_internal
- created_at, updated_at

---

## Acceptance Criteria

**Functional:**
- ✅ View and edit profile
- ✅ Change password
- ✅ Search help articles
- ✅ View video tutorials
- ✅ Submit support tickets
- ✅ Track ticket status

**Performance:**
- ✅ Profile loads in <1 second
- ✅ Help search results in <2 seconds
- ✅ Ticket submission in <5 seconds

**Security:**
- ✅ Password requirements enforced
- ✅ Email verification for email changes
- ✅ Session logout on password change

**UX:**
- ✅ Contextual help (F1 key)
- ✅ Search autocomplete
- ✅ Video tutorials embedded
- ✅ Mobile-friendly help center

---

## Design Notes

**Tone:**
- Helpful, supportive (user assistance)
- Clear instructions (step-by-step)
- Friendly language (not technical jargon)

**UX Principles:**
- Contextual help (right place, right time)
- Search-first (find answers fast)
- Video tutorials (visual learning)
- Self-service (reduce support load)

**Mobile Consideration:**
- Profile management mobile-friendly
- Help center mobile-optimized

---

## Related Scenarios

- **01: Company Setup** - Initial user setup
- All scenarios - Help available everywhere

---

## Accurate Feature Parity

**Accurate Help includes:**
- User manual (PDF)
- Email support

**AkuBook Enhancement:**
- In-app help center (Accurate doesn't have this)
- Video tutorials (Accurate limited)
- Contextual help (Accurate doesn't have this)
- Support ticket system (Accurate email-only)

---

**Scenario Status:** ✅ Ready for Implementation  
**Next:** Design wireframes for 6 pages in this flow
