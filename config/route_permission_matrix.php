<?php

return [
    'critical_routes' => [
        'dashboard' => ['auth', 'verified'],
        'role-dashboard.index' => ['auth'],
        'governance-dashboard-v2.index' => ['auth'],
        'customers.index' => ['auth'],
        'suppliers.index' => ['auth'],
        'sales-orders.index' => ['auth'],
        'purchase-orders.index' => ['auth'],
        'customer-payments.index' => ['auth'],
        'supplier-payments.index' => ['auth'],
        'sensitive-alerts.index' => ['auth'],
        'compliance-export-packs.index' => ['auth'],
        'fiscal-periods.index' => ['auth', 'permission:manage-fiscal-periods'],
        'journal-entries.index' => ['auth', 'permission:manage-journal-entries'],
    ],
];
