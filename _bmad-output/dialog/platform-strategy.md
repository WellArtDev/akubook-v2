# Platform Strategy: AkuBook

**Date:** 2026-05-12

---

## Platform Overview

AkuBook uses a **multi-platform strategy** with API-first architecture to support web, mobile, and future integrations from a single backend.

---

## Primary Platforms (MVP)

### 1. Web Application

**Technology Stack:**
- **Framework:** Laravel 11+ (Breeze starter kit)
- **Frontend:** Blade templates + Livewire (or Alpine.js)
- **Styling:** Tailwind CSS
- **JavaScript:** Alpine.js (minimal, progressive enhancement)

**Target Devices:**
- **Desktop:** Primary (1920×1080 and above)
- **Tablet:** Secondary (768×1024)
- **Mobile browser:** Tertiary (375×667 and above)

**Design Approach:**
- **Desktop-first design:** Optimized for complex workflows
- **Responsive down to mobile:** Simple tasks accessible on mobile browser

**Primary Users:**
- Finance Admin / Accounting (desktop)
- HRD Manager (desktop)
- Purchasing Manager (desktop)
- Sales Team (desktop/tablet)
- Warehouse Staff (tablet)

**Features:**
- Full ERP functionality
- Dashboard with customizable widgets
- Accounting (GL, AP/AR, Bank reconciliation)
- Sales (Quotation, SO, Invoice, Delivery)
- Purchasing (PR, PO, Receiving, Payment)
- Inventory (Multi-warehouse, Stock movements, COGS)
- HRM (Employee data, Payroll, Leave management)
- Attendance (Admin view, Reports, Configuration)
- Reports (Financial, Operational, Custom)
- Settings (Company, Users, Roles, Modules)

**Interaction Models:**
- **Primary:** Mouse + keyboard
- **Secondary:** Touch (tablet/mobile)
- **Accessibility:** WCAG AA compliance (screen readers, keyboard navigation)

**Offline Functionality:**
- **Always-online** (on-premise deployment = LAN always available)
- No offline mode required for web app

### 2. Mobile Native Application

**Technology Stack:**
- **Platform:** iOS + Android
- **Approach:** Cross-platform (React Native or Flutter — to be decided)
- **Rationale:** Single codebase, faster development, near-native performance

**Target Devices:**
- **Smartphones:** iOS 14+ and Android 8+ (minimum)
- **Screen sizes:** 375×667 (iPhone SE) to 428×926 (iPhone Pro Max)

**Design Approach:**
- **Mobile-first design:** Optimized for touch and small screens
- **Native UI patterns:** Follow iOS Human Interface Guidelines and Material Design

**Primary Users:**
- General Staff (attendance, leave, payslip)
- Field workers (sales, delivery)
- Managers (approvals, notifications)

**Features (MVP):**
- **Attendance:**
  - Clock in/out (geo-location + face recognition)
  - View attendance history
  - Submit attendance corrections
- **Leave Management:**
  - Submit leave requests
  - View leave balance
  - Track approval status
- **Payslip:**
  - View current and historical payslips
  - Download PDF
- **Notifications:**
  - Push notifications for approvals, announcements
  - In-app notification center
- **Profile:**
  - View personal information
  - Update contact details (limited)

**Interaction Models:**
- **Primary:** Touch gestures (tap, swipe, pinch)
- **Biometric:** Face ID / Touch ID for authentication
- **Camera:** Face recognition for attendance
- **GPS:** Geo-location for attendance

**Offline Functionality:**
- **Offline mode for attendance:**
  - Clock in/out stored locally
  - Sync when internet available
  - Conflict resolution (server timestamp wins)
- **Read-only offline:**
  - View cached payslips
  - View cached leave balance
- **No offline write** (except attendance)

**Native Device Features:**
- **Camera:** Face recognition attendance
- **GPS:** Geo-location attendance (with accuracy threshold)
- **Push notifications:** Real-time alerts
- **Biometric authentication:** Face ID / Touch ID
- **Local storage:** Offline data cache
- **Background sync:** Attendance data sync

---

## API-First Architecture (CRITICAL)

### Backend API (Laravel)

**Architecture:**
- **RESTful API:** JSON responses, standard HTTP methods
- **Authentication:** Laravel Sanctum (token-based)
- **Authorization:** Laravel Policies + Gates (RBAC)
- **Versioning:** `/api/v1/` prefix (future-proof)
- **Rate limiting:** Per-user, per-endpoint
- **Documentation:** OpenAPI/Swagger spec

**API Consumers:**
- Web application (primary)
- Mobile application (primary)
- Future integrations (third-party, custom apps)
- Webhooks (outbound notifications)

**Why API-First:**
- **Decoupling:** Frontend and backend independent
- **Multi-platform:** Single backend, multiple clients
- **Scalability:** API can scale independently
- **Extensibility:** Easy to add new clients
- **Testing:** API testable independently

**API Design Principles:**
- **Resource-oriented:** `/api/v1/sales-orders`, `/api/v1/employees`
- **Consistent naming:** Plural nouns, kebab-case
- **Standard HTTP status codes:** 200, 201, 400, 401, 403, 404, 422, 500
- **Pagination:** Cursor-based for large datasets
- **Filtering:** Query parameters (`?status=pending&date_from=2026-01-01`)
- **Sorting:** Query parameter (`?sort=-created_at`)
- **Field selection:** Sparse fieldsets (`?fields=id,name,email`)
- **Relationships:** Eager loading control (`?include=customer,items`)

**API Security:**
- **Authentication:** Token-based (Sanctum)
- **Authorization:** RBAC (every endpoint checks permissions)
- **Input validation:** Form Requests for all inputs
- **Rate limiting:** Prevent abuse
- **CORS:** Configured for web/mobile origins
- **HTTPS:** Required in production

---

## Backend Integrations (MVP)

### 1. Wablas (WhatsApp Gateway)

**Purpose:**
- Send notifications via WhatsApp
- Built-in integration (not third-party add-on)

**Integration Point:**
- Backend API (Laravel)
- Event-driven architecture (Laravel Events → Queue → Wablas API)

**Notification Types:**

**To Employees:**
- Leave request approved/rejected
- Payslip available (with download link)
- Overtime request approved/rejected
- Company announcements
- Shift schedule changes

**To Customers:**
- Invoice sent (with payment link)
- Payment received confirmation
- Delivery status (surat jalan on the way)
- Order confirmation

**To Suppliers:**
- Purchase order confirmation
- Payment scheduled notification
- Delivery schedule reminder

**Technical Implementation:**
- **Trigger:** Laravel Events (e.g., `LeaveRequestApproved`)
- **Queue:** Laravel Queue (async processing)
- **API:** Wablas REST API
- **Template:** Message templates with variables
- **Retry:** Automatic retry on failure (3 attempts)
- **Logging:** All sent messages logged for audit

**Configuration:**
- Wablas API credentials (env variables)
- Message templates (database or config)
- Notification preferences (per user, per event type)
- Opt-out mechanism (users can disable certain notifications)

**Direction:**
- **One-way:** Send only (no receive/chatbot in MVP)
- **Future:** Two-way (receive replies, chatbot for simple queries)

### 2. ZKTeco (Attendance Hardware)

**Purpose:**
- Integrate with existing fingerprint/face recognition devices
- Leverage client's hardware investment

**Integration Point:**
- Backend API (Laravel)
- ZKTeco SDK or API (depending on device model)

**Data Flow:**
- **ZKTeco device** → **Backend API** → **Attendance module** → **Database**

**Sync Methods:**
- **Real-time push:** Device pushes attendance events to API (if supported)
- **Scheduled pull:** Backend pulls attendance data from device (every 5-15 minutes)
- **Manual sync:** Admin triggers sync on-demand

**Technical Implementation:**
- **ZKTeco SDK:** PHP library or REST API client
- **Device discovery:** Auto-detect devices on network (on-premise)
- **Data mapping:** Device user ID → Employee ID
- **Conflict resolution:** Device timestamp is source of truth
- **Logging:** All sync operations logged

**Configuration:**
- Device IP addresses (multiple devices supported)
- Sync schedule (cron job)
- Employee mapping (device user ID → employee ID)
- Attendance rules (late threshold, overtime calculation)

**Supported Devices:**
- ZKTeco fingerprint readers
- ZKTeco face recognition terminals
- ZKTeco access control systems

**Fallback:**
- If ZKTeco not available, use mobile app (geo + face recognition)
- Manual attendance entry (admin override)

---

## Platform Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     USERS & DEVICES                         │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐    │
│  │   Desktop    │  │    Tablet    │  │  Smartphone  │    │
│  │  (Browser)   │  │  (Browser)   │  │ (Native App) │    │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘    │
│         │                  │                  │             │
└─────────┼──────────────────┼──────────────────┼─────────────┘
          │                  │                  │
          └──────────────────┴──────────────────┘
                             │
                             ▼
          ┌──────────────────────────────────────┐
          │         LARAVEL API (Backend)        │
          │  ┌────────────────────────────────┐  │
          │  │  RESTful API (JSON)            │  │
          │  │  - Authentication (Sanctum)    │  │
          │  │  - Authorization (RBAC)        │  │
          │  │  - Validation (Form Requests)  │  │
          │  │  - Rate Limiting               │  │
          │  └────────────────────────────────┘  │
          │                                      │
          │  ┌────────────────────────────────┐  │
          │  │  Business Logic (Services)     │  │
          │  │  - Accounting                  │  │
          │  │  - Sales / Purchasing          │  │
          │  │  - Inventory                   │  │
          │  │  - HRM / Payroll               │  │
          │  │  - Attendance                  │  │
          │  └────────────────────────────────┘  │
          │                                      │
          │  ┌────────────────────────────────┐  │
          │  │  Event System (Laravel Events) │  │
          │  │  - Cross-module communication  │  │
          │  │  - Notification triggers       │  │
          │  │  - Audit logging               │  │
          │  └────────────────────────────────┘  │
          │                                      │
          │  ┌────────────────────────────────┐  │
          │  │  Queue System (Laravel Queue)  │  │
          │  │  - Async processing            │  │
          │  │  - Notification sending        │  │
          │  │  - Report generation           │  │
          │  │  - Data import/export          │  │
          │  └────────────────────────────────┘  │
          └──────────────┬───────────────────────┘
                         │
          ┌──────────────┴───────────────────────┐
          │                                      │
          ▼                                      ▼
┌─────────────────────┐              ┌─────────────────────┐
│  EXTERNAL SERVICES  │              │     DATABASE        │
├─────────────────────┤              ├─────────────────────┤
│                     │              │                     │
│  ┌───────────────┐  │              │  PostgreSQL         │
│  │ Wablas API    │  │              │  - Companies        │
│  │ (WhatsApp)    │  │              │  - Users / Roles    │
│  └───────────────┘  │              │  - Accounting       │
│                     │              │  - Sales / Purchase │
│  ┌───────────────┐  │              │  - Inventory        │
│  │ ZKTeco SDK    │  │              │  - HRM / Payroll    │
│  │ (Attendance)  │  │              │  - Attendance       │
│  └───────────────┘  │              │  - Audit Logs       │
│                     │              │                     │
└─────────────────────┘              └─────────────────────┘
```

---

## Device Priority & Design Strategy

### Desktop-First (Web App)

**Rationale:**
- Primary users (Finance, HRD, Purchasing) work on desktop
- Complex workflows require large screens
- Data entry intensive (keyboard + mouse optimal)
- Multi-window/tab workflows common

**Design Implications:**
- **Layout:** Multi-column layouts, sidebars, data tables
- **Navigation:** Top nav + sidebar (always visible)
- **Forms:** Multi-step forms, inline validation, keyboard shortcuts
- **Tables:** Sortable, filterable, paginated, bulk actions
- **Dashboards:** Multiple widgets, drag-and-drop customization

**Responsive Strategy:**
- **Tablet:** Collapse sidebar to hamburger, simplify layouts
- **Mobile:** Stack columns, hide non-essential elements, focus on viewing (not editing)

### Mobile-First (Native App)

**Rationale:**
- Staff users primarily on mobile
- Attendance requires mobile (geo + camera)
- Simple tasks (view payslip, request leave)
- Push notifications critical

**Design Implications:**
- **Layout:** Single column, card-based, bottom navigation
- **Navigation:** Bottom tab bar (4-5 main sections)
- **Forms:** One field per screen, large touch targets, native pickers
- **Lists:** Swipe actions, pull-to-refresh, infinite scroll
- **Biometric:** Face ID / Touch ID for quick login

**Native Patterns:**
- **iOS:** Follow Human Interface Guidelines (SF Symbols, native controls)
- **Android:** Follow Material Design (FAB, bottom sheets, snackbars)

---

## Interaction Models

### Web Application

**Primary: Mouse + Keyboard**
- Click, hover, right-click context menus
- Keyboard shortcuts (Cmd/Ctrl + S to save, Cmd/Ctrl + K for search)
- Tab navigation, Enter to submit
- Drag-and-drop (dashboard widgets, file uploads)

**Secondary: Touch (Tablet/Mobile)**
- Tap, long-press, swipe
- Touch-friendly buttons (44×44px minimum)
- No hover-dependent interactions
- Pinch-to-zoom (images, charts)

**Accessibility:**
- **Screen readers:** ARIA labels, semantic HTML, skip links
- **Keyboard navigation:** All interactive elements focusable, visible focus indicators
- **Color contrast:** WCAG AA (4.5:1 for text, 3:1 for UI components)
- **Text scaling:** Support browser zoom up to 200%

### Mobile Application

**Primary: Touch**
- Tap, double-tap, long-press
- Swipe (navigate, dismiss, reveal actions)
- Pinch-to-zoom (images)
- Pull-to-refresh (lists)

**Biometric:**
- Face ID / Touch ID for authentication
- Quick unlock (no password re-entry)
- Fallback to PIN/password

**Camera:**
- Face recognition for attendance (front camera)
- Document scanning (future: expense receipts)

**GPS:**
- Geo-location for attendance (accuracy threshold: 50m)
- Map view for field staff locations (future)

---

## Offline Functionality

### Web Application

**Strategy:** Always-online (no offline mode)

**Rationale:**
- On-premise deployment = LAN always available
- Complex workflows require real-time data
- Multi-user collaboration needs live updates
- Offline sync complexity not justified for web

**Graceful Degradation:**
- Show clear error messages when connection lost
- Prevent data loss (save drafts locally, warn before navigation)
- Retry failed requests automatically

### Mobile Application

**Strategy:** Offline-first for attendance, read-only for viewing

**Offline Capabilities:**

**Attendance (Read + Write):**
- Clock in/out stored locally (SQLite)
- Sync when internet available (background sync)
- Conflict resolution: Server timestamp wins
- Queue failed syncs, retry automatically

**Viewing (Read-Only):**
- Cached payslips (last 3 months)
- Cached leave balance
- Cached company announcements
- No offline editing (except attendance)

**Sync Strategy:**
- **Foreground sync:** When app opened, pull latest data
- **Background sync:** Periodic sync (every 15 minutes if data pending)
- **Manual sync:** Pull-to-refresh
- **Conflict resolution:** Server is source of truth

**Offline Indicators:**
- Clear offline badge in UI
- Sync status indicator (syncing, synced, failed)
- Pending actions count (e.g., "2 attendance records pending sync")

---

## Native Device Features (Mobile)

### Camera

**Use Cases:**
- **Face recognition attendance:** Front camera, real-time detection
- **Document scanning (future):** Expense receipts, invoices

**Implementation:**
- Native camera API (React Native Camera or Flutter Camera)
- Face detection library (ML Kit for Android, Vision for iOS)
- Image compression before upload
- Privacy: Camera access permission, clear user consent

### GPS

**Use Cases:**
- **Geo-location attendance:** Verify employee at work location
- **Field staff tracking (future):** Real-time location for managers

**Implementation:**
- Native location API (React Native Geolocation or Flutter Geolocator)
- Accuracy threshold: 50m (reject if accuracy > 50m)
- Battery optimization: Only request location during clock in/out
- Privacy: Location permission, clear user consent, data retention policy

### Push Notifications

**Use Cases:**
- Leave request approved/rejected
- Payslip available
- Company announcements
- Shift reminders

**Implementation:**
- Firebase Cloud Messaging (FCM) for Android
- Apple Push Notification Service (APNs) for iOS
- Backend: Laravel Notification system + FCM/APNs
- User preferences: Enable/disable per notification type

### Biometric Authentication

**Use Cases:**
- Quick login (no password re-entry)
- Secure access to sensitive data (payslip)

**Implementation:**
- Native biometric API (React Native Biometrics or Flutter Local Auth)
- Fallback to PIN/password
- Biometric data never leaves device (OS-level security)

### Local Storage

**Use Cases:**
- Offline data cache (attendance, payslips, leave balance)
- User preferences (theme, language)
- Authentication token (secure storage)

**Implementation:**
- SQLite for structured data (attendance records)
- Secure storage for sensitive data (token, biometric keys)
- Clear cache on logout

---

## Platform Rationale

### Why Web + Mobile (Not Web-Only)

**Web-Only Limitations:**
- No native device features (camera, GPS, biometric)
- Poor mobile UX for simple tasks (attendance, leave)
- No offline mode (mobile data unreliable in Indonesia)
- No push notifications (web push limited on iOS)

**Mobile-Only Limitations:**
- Complex workflows difficult on small screens
- Data entry intensive tasks (accounting, inventory)
- Multi-window workflows impossible
- App store approval delays

**Web + Mobile Benefits:**
- **Best of both worlds:** Complex workflows on web, simple tasks on mobile
- **User choice:** Use device that fits task
- **Wider reach:** Desktop users + mobile-only users
- **Future-proof:** API supports future platforms (tablet app, desktop app, integrations)

### Why API-First

**Benefits:**
- **Decoupling:** Frontend and backend independent (easier to maintain)
- **Multi-platform:** Single backend, multiple clients (web, mobile, future)
- **Scalability:** API can scale independently (load balancer, caching)
- **Extensibility:** Easy to add new clients (third-party integrations)
- **Testing:** API testable independently (Postman, automated tests)
- **Team collaboration:** Frontend and backend teams can work in parallel

**Trade-offs:**
- **Initial complexity:** More upfront architecture work
- **Network dependency:** Web app requires API calls (vs server-side rendering)
- **Mitigation:** Use Laravel Livewire for server-side rendering where needed

### Why Cross-Platform Mobile (Not Native)

**Benefits:**
- **Single codebase:** iOS + Android from one codebase (faster development)
- **Solo dev friendly:** One developer can handle both platforms
- **Faster iteration:** Changes deploy to both platforms simultaneously
- **Cost-effective:** No need for separate iOS and Android developers

**Trade-offs:**
- **Performance:** Slightly slower than native (acceptable for business app)
- **Platform limitations:** Some native features harder to access
- **Mitigation:** Use well-maintained libraries (React Native or Flutter)

**Technology Choice (To Be Decided):**
- **React Native:** JavaScript, large ecosystem, mature
- **Flutter:** Dart, fast performance, growing ecosystem
- **Decision criteria:** Developer familiarity, library availability, performance requirements

---

## Future Platform Plans

### Phase 2 (Post-MVP)

**Desktop Application (Optional):**
- Electron-based desktop app (Windows/Mac/Linux)
- Use case: Offline-first for on-premise users with unreliable internet
- Rationale: Only if demand exists (web app sufficient for most)

**Tablet-Optimized App (Optional):**
- Dedicated tablet app (iPadOS, Android tablets)
- Use case: Warehouse operations, field sales
- Rationale: Only if tablet usage significant (responsive web may suffice)

**Smartwatch App (Future):**
- Apple Watch / Wear OS app
- Use case: Quick attendance check-in, notifications
- Rationale: Low priority (mobile app sufficient)

**Third-Party Integrations:**
- Zapier integration (connect to 1000+ apps)
- API marketplace (partners build integrations)
- Webhooks (outbound notifications to external systems)

---

## Design & Development Implications

### Web Application

**Development Stack:**
- Laravel 11+ (backend + frontend)
- Blade templates (server-side rendering)
- Livewire (reactive components without JavaScript)
- Alpine.js (minimal JavaScript for interactions)
- Tailwind CSS (utility-first styling)

**Design System:**
- Component library (buttons, forms, tables, modals)
- Consistent spacing, typography, colors
- Responsive breakpoints (mobile, tablet, desktop)
- Dark mode support (future)

**Performance:**
- Server-side rendering (fast initial load)
- Lazy loading (images, charts, large tables)
- Caching (Redis for dashboard widgets, reports)
- CDN for static assets (CSS, JS, images)

### Mobile Application

**Development Stack:**
- React Native or Flutter (to be decided)
- State management (Redux/MobX or Provider/Riverpod)
- Navigation (React Navigation or Flutter Navigator)
- API client (Axios or Dio)
- Local storage (SQLite + Secure Storage)

**Design System:**
- Native UI components (iOS and Android)
- Platform-specific patterns (bottom nav on iOS, FAB on Android)
- Consistent branding (colors, typography, icons)
- Accessibility (VoiceOver, TalkBack support)

**Performance:**
- Lazy loading (screens, images)
- Image optimization (compression, caching)
- Background sync (efficient battery usage)
- Crash reporting (Sentry or Firebase Crashlytics)

### API Development

**Development Stack:**
- Laravel API Resources (JSON transformation)
- Laravel Sanctum (authentication)
- Laravel Policies (authorization)
- Laravel Form Requests (validation)
- Laravel Queue (async processing)

**Documentation:**
- OpenAPI/Swagger spec (auto-generated)
- Postman collection (for testing)
- API versioning (`/api/v1/`)

**Testing:**
- Unit tests (Services, Models)
- Feature tests (API endpoints)
- Integration tests (cross-module workflows)
- Load testing (100+ concurrent users)

---

## Platform Strategy Summary

**MVP Platforms:**
1. **Web Application:** Desktop-first, responsive, full ERP features
2. **Mobile Native App:** Mobile-first, attendance + leave + payslip + notifications

**Backend:**
- **API-First:** RESTful API (Laravel)
- **Integrations:** Wablas (WhatsApp), ZKTeco (Attendance hardware)

**Architecture:**
- **Decoupled:** Frontend and backend independent
- **Multi-platform:** Single backend, multiple clients
- **Event-driven:** Cross-module communication via Laravel Events
- **Queue-based:** Async processing for notifications, reports

**Device Priority:**
- **Desktop:** Primary (complex workflows)
- **Mobile:** Primary (attendance, simple tasks)
- **Tablet:** Secondary (warehouse, field ops)

**Offline:**
- **Web:** Always-online
- **Mobile:** Offline-first for attendance, read-only for viewing

**Future:**
- Desktop app (optional)
- Tablet app (optional)
- Third-party integrations (API marketplace, Zapier)
