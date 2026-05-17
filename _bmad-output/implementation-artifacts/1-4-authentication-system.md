# Story 1.4: Authentication System

**Epic:** 1 - Core System Setup & Infrastructure  
**Story ID:** 1.4  
**Story Key:** 1-4-authentication-system  
**Status:** ready-for-dev  
**Created:** 2026-05-14

---

## User Story

**As a** system user  
**I want** a secure authentication system with branch assignment and Indonesian localization  
**So that** I can securely access the AkuBook ERP system with proper organizational context

---

## Business Context

AkuBook adalah multi-branch ERP system. Authentication harus support branch assignment, role-based access, dan Indonesian language. Story ini enhance existing Breeze auth dengan AkuBook-specific requirements.

**Business Value:**
- Secure user authentication
- Branch-level data isolation
- Indonesian language support
- Role-based access control foundation
- Audit trail untuk login activities

**Success Impact:**
- Users dapat login dengan branch context
- UI dalam Bahasa Indonesia
- Login activities ter-log untuk compliance
- Foundation untuk RBAC implementation

---

## Acceptance Criteria

### AC1: Branch Assignment on Registration
**Given** user registers new account  
**When** registration form is submitted  
**Then**
- Branch selection dropdown available
- Branch is required field
- User assigned to selected branch
- Branch stored in `users.branch_id`
- Branch relationship loaded on login

### AC2: Indonesian Localization
**Given** authentication pages loaded  
**When** user views login/register pages  
**Then**
- All labels in Bahasa Indonesia
- Error messages in Bahasa Indonesia
- Validation messages in Bahasa Indonesia
- Success messages in Bahasa Indonesia
- Email templates in Bahasa Indonesia

### AC3: Enhanced Login with Branch Context
**Given** user logs in  
**When** authentication succeeds  
**Then**
- User's branch loaded and available
- Branch name displayed in header
- Branch context available in all requests
- Session includes branch information
- Audit log records login with branch

### AC4: Login Activity Audit Trail
**Given** user performs auth actions  
**When** login/logout/failed attempt occurs  
**Then**
- Event logged to `audit_logs` table
- IP address recorded
- User agent recorded
- Timestamp recorded
- Action type recorded (login/logout/failed)

### AC5: Password Security Enhanced
**Given** user sets password  
**When** password is validated  
**Then**
- Minimum 8 characters
- Must contain uppercase letter
- Must contain lowercase letter
- Must contain number
- Must contain special character
- Password strength indicator shown

---

## Technical Requirements

### Existing Auth Setup (Laravel Breeze)

**Controllers (9):**
- `AuthenticatedSessionController` - Login/logout
- `RegisteredUserController` - Registration
- `PasswordResetLinkController` - Forgot password
- `NewPasswordController` - Reset password
- `PasswordController` - Update password
- `ConfirmablePasswordController` - Confirm password
- `EmailVerificationPromptController` - Email verification prompt
- `EmailVerificationNotificationController` - Resend verification
- `VerifyEmailController` - Verify email

**Routes:**
- `/register` - Registration
- `/login` - Login
- `/logout` - Logout
- `/forgot-password` - Password reset request
- `/reset-password/{token}` - Password reset
- `/verify-email` - Email verification
- `/confirm-password` - Password confirmation

**Pages (React):**
- `Pages/Auth/Login.jsx`
- `Pages/Auth/Register.jsx`
- `Pages/Auth/ForgotPassword.jsx`
- `Pages/Auth/ResetPassword.jsx`
- `Pages/Auth/VerifyEmail.jsx`
- `Pages/Auth/ConfirmPassword.jsx`

### Enhancements Needed

#### 1. Branch Assignment
```php
// RegisteredUserController.php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'branch_id' => $request->branch_id, // NEW
]);
```

#### 2. Indonesian Translations
```php
// lang/id/auth.php
return [
    'failed' => 'Kredensial tidak cocok dengan data kami.',
    'password' => 'Password yang Anda masukkan salah.',
    'throttle' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam :seconds detik.',
];
```

#### 3. Audit Logging
```php
// After successful login
AuditLog::create([
    'user_id' => $user->id,
    'action' => 'login',
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

#### 4. Password Validation
```php
// RegisteredUserController.php
'password' => ['required', 'confirmed', Rules\Password::defaults()
    ->min(8)
    ->letters()
    ->mixedCase()
    ->numbers()
    ->symbols()
],
```

---

## Developer Context

### Current State Analysis
**Existing Auth (from Story 1.1):**
- ✅ Laravel Breeze 2.4 installed
- ✅ Sanctum 4.0 configured
- ✅ 9 auth controllers present
- ✅ Auth routes configured
- ✅ 6 React auth pages present
- ✅ User model with branch_id column

**What This Story Actually Needs:**
Since Breeze auth already setup, this story focuses on:
1. **Enhancement** - Add branch assignment to registration
2. **Localization** - Translate to Indonesian
3. **Audit** - Log authentication activities
4. **Security** - Enhance password requirements
5. **UX** - Improve auth pages with AkuBook branding

### Critical Implementation Notes

#### 🚨 MUST DO:
1. **Add Branch Selection to Registration**
   - Modify `Register.jsx` to include branch dropdown
   - Load branches from API endpoint
   - Validate branch_id is required
   - Store branch_id in users table

2. **Create Indonesian Translation Files**
   - Create `lang/id/auth.php`
   - Create `lang/id/passwords.php`
   - Create `lang/id/validation.php`
   - Update `.env` to set `APP_LOCALE=id`
   - Translate all auth-related strings

3. **Implement Audit Logging**
   - Create `AuditLog` model (if not exists)
   - Log successful logins
   - Log failed login attempts
   - Log logouts
   - Include IP, user agent, timestamp

4. **Enhance Password Validation**
   - Update password rules in controllers
   - Add password strength indicator to Register page
   - Show password requirements clearly
   - Validate on frontend and backend

5. **Update Auth Pages with AkuBook Branding**
   - Use AkuBook primary colors
   - Add AkuBook logo
   - Update page titles
   - Improve form layouts

#### ⚠️ MUST NOT DO:
1. **Don't Remove Breeze Controllers** - Extend, don't replace
2. **Don't Break Email Verification** - Keep it functional
3. **Don't Weaken Security** - Only strengthen password rules
4. **Don't Skip Audit Logging** - Critical for compliance
5. **Don't Hardcode Translations** - Use Laravel localization

#### 🔍 Verification Checklist:
- [ ] Branch dropdown shows in registration
- [ ] Branch is required and validated
- [ ] User assigned to branch on registration
- [ ] All auth pages in Indonesian
- [ ] Login activity logged to audit_logs
- [ ] Failed attempts logged
- [ ] Password validation enforced
- [ ] Password strength indicator works
- [ ] Auth pages use AkuBook branding
- [ ] Email verification still works

---

## Testing Requirements

### Unit Tests
- Test branch assignment on registration
- Test password validation rules
- Test audit log creation
- Test Indonesian translations loaded

### Feature Tests
- Test registration with branch
- Test login creates audit log
- Test failed login creates audit log
- Test logout creates audit log
- Test password validation rejects weak passwords

### Manual Testing
1. Visit `/register`
2. Verify branch dropdown present
3. Try register without branch - should fail
4. Register with branch - should succeed
5. Check `users` table - branch_id populated
6. Check `audit_logs` table - login recorded
7. Try weak password - should be rejected
8. Verify all text in Indonesian
9. Test email verification flow
10. Test password reset flow

---

## Definition of Done

- [ ] All acceptance criteria met
- [ ] All verification checklist items passed
- [ ] Branch assignment working
- [ ] Indonesian translations complete
- [ ] Audit logging implemented
- [ ] Password validation enhanced
- [ ] Auth pages branded
- [ ] All tests passing
- [ ] Email verification working
- [ ] Password reset working

---

## Dependencies

**Upstream:**
- Story 1.1: Laravel Application Setup (DONE - in review)
- Story 1.2: React + Inertia.js Frontend Setup (DONE - in review)
- Story 1.3: Database Schema Foundation (ready-for-dev)

**Downstream:**
- Story 1.5: Audit Logging System
- Story 2.1: Spatie Permission Integration
- All authenticated features depend on this

---

## Notes for Developer

### Project Context
- **Target:** Medium enterprises di Indonesia
- **Language:** Bahasa Indonesia first
- **Multi-branch:** Users belong to specific branch
- **Compliance:** Audit trail required
- **Security:** Strong password requirements

### Laravel Localization
```php
// Set locale in .env
APP_LOCALE=id
APP_FALLBACK_LOCALE=en

// Use in code
__('auth.failed')
trans('passwords.reset')
```

### Audit Log Model
```php
// app/Models/AuditLog.php
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Branch Relationship
```php
// app/Models/User.php
public function branch()
{
    return $this->belongsTo(Branch::class);
}
```

### Common Pitfalls to Avoid
1. **Forgetting to Load Branch** - Eager load on login
2. **Missing Translations** - Check all error messages
3. **Audit Log Failures** - Don't block auth if logging fails
4. **Password Rules Too Strict** - Balance security vs usability
5. **Breaking Email Verification** - Test thoroughly

### Learnings from Story 1.1, 1.2, 1.3
- PostgreSQL configured and working
- React components with JSDoc documentation
- TailwindCSS with AkuBook branding
- 21 migrations including branches, audit_logs
- Build time: 1.02s

---

## Resources

### Official Documentation
- Laravel Authentication: https://laravel.com/docs/13.x/authentication
- Laravel Localization: https://laravel.com/docs/13.x/localization
- Laravel Breeze: https://laravel.com/docs/13.x/starter-kits#breeze
- Password Validation: https://laravel.com/docs/13.x/validation#rule-password

### Indonesian Translation
- Laravel Lang: https://github.com/Laravel-Lang/lang
- Indonesian Locale: https://github.com/Laravel-Lang/lang/tree/main/locales/id

### Project-Specific
- Product Brief: `_bmad-output/planning-artifacts/product-brief.md`
- Sprint Status: `_bmad-output/implementation-artifacts/sprint-status.yaml`
- Story 1.1: `_bmad-output/implementation-artifacts/1-1-laravel-application-setup.md`
- Story 1.2: `_bmad-output/implementation-artifacts/1-2-react-inertiajs-frontend-setup.md`
- Story 1.3: `_bmad-output/implementation-artifacts/1-3-database-schema-foundation.md`

---

**Story Created:** 2026-05-14  
**Ready for Development:** Yes  
**Estimated Effort:** 5-7 hours (enhancement + localization + audit + testing)
