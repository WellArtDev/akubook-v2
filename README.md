# AkuBook ERP

Comprehensive ERP system untuk medium enterprises di Indonesia.

## Tech Stack

- **Backend:** Laravel 13.8 + PHP 8.3
- **Frontend:** React 18.2 + Inertia.js 2.0
- **Database:** PostgreSQL 17
- **Build Tool:** Vite 8.0
- **Styling:** TailwindCSS 3.2
- **Authentication:** Laravel Breeze + Sanctum
- **RBAC:** Spatie Laravel Permission

## Prerequisites

- PHP 8.3+
- PostgreSQL 17
- Node.js 18+ & npm
- Composer 2+

## Installation

### 1. Clone Repository

```bash
git clone <repository-url>
cd akubook-dev
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database

Edit `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=akubook
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 5. Create Database

```sql
CREATE DATABASE akubook;
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Build Frontend Assets

```bash
npm run build
```

## Development

### Start Development Server

```bash
composer dev
```

This starts 4 services concurrently:
- Laravel server (port 8000)
- Queue worker
- Log viewer (Pail)
- Vite dev server (HMR)

### Run Tests

```bash
composer test
```

### Code Style

```bash
./vendor/bin/pint
```

## Project Structure

```
akubook-dev/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   └── Providers/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   ├── Layouts/
│   │   ├── Pages/
│   │   └── app.jsx
│   └── css/
├── routes/
│   ├── web.php
│   └── api.php
└── tests/
    ├── Feature/
    └── Unit/
```

## Features

### Phase 1 - Foundation
- ✅ Core System Setup
- ✅ User Management & RBAC
- ✅ Organization Structure (Branches, Departments, Warehouses)

### Phase 2 - Accounting
- ✅ Chart of Accounts
- ✅ Fiscal Periods
- ✅ Journal Entries
- ✅ Financial Reports

### Phase 3 - Sales & Purchasing
- 🚧 Customer Management
- 🚧 Sales Orders
- 🚧 Supplier Management
- 🚧 Purchase Orders

### Phase 4 - Inventory
- 📋 Item Master
- 📋 Stock Tracking
- 📋 Inventory Valuation

### Phase 5+ - HRM, Payroll, Tax, Fixed Assets
- 📋 Employee Management
- 📋 Attendance (ZKTeco Integration)
- 📋 Payroll Processing
- 📋 Tax Management
- 📋 Fixed Assets

## Authentication

Default authentication routes:
- `/login` - Login page
- `/register` - Registration page
- `/dashboard` - Dashboard (authenticated)

## Database Schema

Key tables:
- `users` - User accounts
- `roles`, `permissions` - RBAC (Spatie)
- `branches`, `departments`, `warehouses` - Organization
- `accounts`, `fiscal_periods` - Accounting
- `journal_entries`, `journal_entry_lines` - Transactions
- `customers`, `sales_orders` - Sales
- `suppliers`, `purchase_orders` - Purchasing
- `audit_logs` - Audit trail

## Development Workflow

1. Create feature branch
2. Implement changes
3. Run tests: `composer test`
4. Fix code style: `./vendor/bin/pint`
5. Commit with conventional commits
6. Create pull request

## Troubleshooting

### Database Connection Issues
- Ensure PostgreSQL is running
- Check credentials in `.env`
- Verify database exists

### Vite Build Errors
- Clear cache: `npm run build`
- Check `resources/js/bootstrap.js` exists
- Verify `public/build/manifest.json` created

### Permission Issues
- Ensure `storage/` and `bootstrap/cache/` are writable
- Run: `chmod -R 775 storage bootstrap/cache`

## License

Proprietary - All rights reserved

## Support

For issues and questions, contact: WellArtDev
