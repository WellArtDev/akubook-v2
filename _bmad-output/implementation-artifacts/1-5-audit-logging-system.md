# Story 1.5: Audit Logging System

**Epic:** 1 - Core System Setup & Infrastructure  
**Story ID:** 1.5  
**Story Key:** 1-5-audit-logging-system  
**Status:** ready-for-dev  
**Created:** 2026-05-14

---

## User Story

**As a** system administrator  
**I want** a comprehensive audit logging system that tracks all critical operations  
**So that** we have complete traceability for compliance and security purposes

---

## Business Context

AkuBook adalah ERP system yang handle sensitive financial data. Audit trail adalah requirement critical untuk compliance, security, dan troubleshooting. Story ini implement comprehensive audit logging untuk semua critical operations.

**Business Value:**
- Compliance dengan audit requirements
- Security monitoring dan threat detection
- Troubleshooting dan debugging
- User activity tracking
- Data change history

**Success Impact:**
- Complete audit trail untuk semua critical operations
- Compliance requirements terpenuhi
- Security incidents dapat di-trace
- User actions dapat di-review
- Data changes dapat di-rollback

---

## Acceptance Criteria

### AC1: Audit Log Model and Service Created
**Given** audit_logs table exists  
**When** audit logging system is implemented  
**Then**
- `AuditLog` model created with relationships
- `AuditService` created for logging operations
- Helper methods for common audit operations
- Automatic user/IP/user-agent capture
- JSON serialization for old/new values

### AC2: Model Events Automatically Logged
**Given** Eloquent models with audit trait  
**When** model is created/updated/deleted  
**Then**
- Event automatically logged to audit_logs
- Old values captured (for update/delete)
- New values captured (for create/update)
- User ID captured if authenticated
- IP address and user agent captured

### AC3: Authentication Events Logged
**Given** user performs auth actions  
**When** login/logout/failed attempt occurs  
**Then**
- Login success logged
- Login failure logged (with reason)
- Logout logged
- Password change logged
- Email verification logged

### AC4: Critical Business Operations Logged
**Given** user performs critical operations  
**When** operation completes  
**Then**
- Journal entry posting logged
- Financial report generation logged
- Master data changes logged
- Permission changes logged
- Configuration changes logged

### AC5: Audit Log Viewer Created
**Given** audit logs exist  
**When** admin views audit log page  
**Then**
- Paginated list of audit logs
- Filter by user, date range, event type
- Search by description
- View old/new values comparison
- Export to CSV/Excel

---

## Technical Requirements

### Existing Infrastructure
- ✅ `audit_logs` table created (migration exists)
- ✅ Columns: user_id, auditable_type, auditable_id, event, old_values, new_values, ip_address, user_agent, timestamps
- ✅ Foreign key to users table
- ✅ Composite index on auditable_type + auditable_id

### Components to Create

#### 1. AuditLog Model
```php
// app/Models/AuditLog.php
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }
}
```

#### 2. Auditable Trait
```php
// app/Traits/Auditable.php
trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            AuditService::log($model, 'created');
        });

        static::updated(function ($model) {
            AuditService::log($model, 'updated', $model->getOriginal());
        });

        static::deleted(function ($model) {
            AuditService::log($model, 'deleted', $model->getAttributes());
        });
    }
}
```

#### 3. AuditService
```php
// app/Services/AuditService.php
class AuditService
{
    public static function log(
        $auditable,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues ?? $auditable->getAttributes(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logAuth(string $event, ?User $user = null, ?string $reason = null)
    {
        // Log authentication events
    }

    public static function logCustom(string $event, string $description, array $data = [])
    {
        // Log custom events
    }
}
```

#### 4. Audit Log Controller & Page
```php
// app/Http/Controllers/AuditLogController.php
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->event, fn($q) => $q->where('event', $request->event))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(50);

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['user_id', 'event', 'date_from', 'date_to']),
        ]);
    }
}
```

---

## Developer Context

### Current State Analysis
**Existing Infrastructure:**
- ✅ `audit_logs` table created (Story 1.1)
- ✅ Migration ran successfully
- ✅ Table structure supports polymorphic relationships
- ✅ JSON columns for old/new values
- ✅ IP address and user agent columns

**What This Story Actually Needs:**
Since table already exists, this story focuses on:
1. **Model & Service** - Create AuditLog model and AuditService
2. **Trait** - Create Auditable trait for automatic logging
3. **Integration** - Apply trait to critical models
4. **Auth Logging** - Log authentication events
5. **Viewer** - Create audit log viewer page

### Critical Implementation Notes

#### 🚨 MUST DO:
1. **Create AuditLog Model**
   - Define fillable fields
   - Cast JSON columns to array
   - Define user relationship
   - Define polymorphic auditable relationship

2. **Create AuditService**
   - Static method for logging model events
   - Static method for logging auth events
   - Static method for logging custom events
   - Automatic capture of user, IP, user agent

3. **Create Auditable Trait**
   - Boot method to register event listeners
   - Listen to created, updated, deleted events
   - Capture old values on update/delete
   - Call AuditService to log

4. **Apply Trait to Critical Models**
   - User model
   - Branch, Department, Position, Warehouse
   - Account, FiscalPeriod
   - JournalEntry, JournalEntryLine
   - Customer, Supplier
   - Item

5. **Create Audit Log Viewer**
   - Controller with index method
   - React page with filters
   - Pagination (50 per page)
   - Filter by user, event, date range
   - Show old/new values comparison
   - Export functionality (optional)

#### ⚠️ MUST NOT DO:
1. **Don't Log Everything** - Only critical operations
2. **Don't Log Passwords** - Exclude sensitive fields
3. **Don't Block Operations** - Audit logging should never fail main operation
4. **Don't Store Large Objects** - Limit JSON size
5. **Don't Forget Performance** - Use queues for heavy logging

#### 🔍 Verification Checklist:
- [ ] AuditLog model created with relationships
- [ ] AuditService created with helper methods
- [ ] Auditable trait created
- [ ] Trait applied to critical models
- [ ] Model changes automatically logged
- [ ] Auth events logged
- [ ] Audit log viewer page created
- [ ] Filters work correctly
- [ ] Old/new values displayed
- [ ] Performance acceptable

---

## Testing Requirements

### Unit Tests
- Test AuditLog model relationships
- Test AuditService log methods
- Test Auditable trait event listeners
- Test JSON serialization

### Feature Tests
- Test model create logs audit entry
- Test model update logs old/new values
- Test model delete logs audit entry
- Test auth events logged
- Test audit log viewer filters
- Test pagination works

### Manual Testing
1. Create a new user - check audit_logs table
2. Update user - verify old/new values captured
3. Delete user - verify deletion logged
4. Login - verify login logged
5. Logout - verify logout logged
6. Visit `/audit-logs` page
7. Test filters (user, event, date)
8. Verify old/new values display correctly
9. Test pagination
10. Check performance with 1000+ logs

---

## Definition of Done

- [ ] All acceptance criteria met
- [ ] All verification checklist items passed
- [ ] AuditLog model created
- [ ] AuditService created
- [ ] Auditable trait created
- [ ] Trait applied to critical models
- [ ] Auth events logged
- [ ] Audit log viewer created
- [ ] All tests passing
- [ ] Performance acceptable

---

## Dependencies

**Upstream:**
- Story 1.1: Laravel Application Setup (DONE - in review)
- Story 1.2: React + Inertia.js Frontend Setup (DONE - in review)
- Story 1.3: Database Schema Foundation (ready-for-dev)
- Story 1.4: Authentication System (ready-for-dev)

**Downstream:**
- All future stories benefit from audit logging
- Compliance requirements depend on this
- Security monitoring depends on this

---

## Notes for Developer

### Project Context
- **Compliance:** Audit trail required for financial ERP
- **Security:** Track all critical operations
- **Performance:** Use queues for heavy logging
- **Privacy:** Don't log sensitive data (passwords, tokens)

### Models to Audit (Priority)
**High Priority:**
- User (authentication, profile changes)
- JournalEntry, JournalEntryLine (financial transactions)
- Account, FiscalPeriod (accounting setup)
- Branch, Department (organizational structure)

**Medium Priority:**
- Customer, Supplier (master data)
- SalesOrder, PurchaseOrder (transactions)
- Item (inventory)

**Low Priority:**
- Cache, Job (system tables)

### Sensitive Fields to Exclude
```php
// In Auditable trait
protected $auditExclude = [
    'password',
    'remember_token',
    'api_token',
    'two_factor_secret',
];
```

### Performance Considerations
```php
// For heavy operations, use queues
dispatch(function () use ($model, $event, $oldValues) {
    AuditService::log($model, $event, $oldValues);
})->afterResponse();
```

### Common Pitfalls to Avoid
1. **Logging Too Much** - Only critical operations
2. **Blocking Main Thread** - Use queues for heavy logging
3. **Circular Dependencies** - Don't audit AuditLog model itself
4. **Missing Context** - Always capture user, IP, user agent
5. **Poor Performance** - Index audit_logs table properly

### Learnings from Story 1.1-1.4
- PostgreSQL configured and working
- React components with JSDoc
- TailwindCSS with AkuBook branding
- 21 migrations including audit_logs
- Auth system with Breeze

---

## Resources

### Official Documentation
- Laravel Events: https://laravel.com/docs/13.x/events
- Laravel Eloquent Events: https://laravel.com/docs/13.x/eloquent#events
- Laravel Queues: https://laravel.com/docs/13.x/queues
- Polymorphic Relationships: https://laravel.com/docs/13.x/eloquent-relationships#polymorphic-relationships

### Audit Packages (Reference)
- Laravel Auditing: https://github.com/owen-it/laravel-auditing
- Spatie Activity Log: https://github.com/spatie/laravel-activitylog

### Project-Specific
- Product Brief: `_bmad-output/planning-artifacts/product-brief.md`
- Sprint Status: `_bmad-output/implementation-artifacts/sprint-status.yaml`
- Story 1.1: `_bmad-output/implementation-artifacts/1-1-laravel-application-setup.md`
- Story 1.2: `_bmad-output/implementation-artifacts/1-2-react-inertiajs-frontend-setup.md`
- Story 1.3: `_bmad-output/implementation-artifacts/1-3-database-schema-foundation.md`
- Story 1.4: `_bmad-output/implementation-artifacts/1-4-authentication-system.md`

---

**Story Created:** 2026-05-14  
**Ready for Development:** Yes  
**Estimated Effort:** 6-8 hours (model + service + trait + viewer + testing)

**🎉 NOTE: This is the LAST story in Epic 1 - Core System Setup & Infrastructure!**
