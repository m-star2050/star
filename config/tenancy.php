<?php

return [
    'database' => [
        'connection' => env('TENANT_CONNECTION', 'tenant'),
        'manager_connection' => env('TENANT_MANAGER_CONNECTION', env('DB_CONNECTION', 'mysql')),
        'template_path' => env('TENANT_TEMPLATE_PATH', 'database/tenant/migrations'),
        'seeder' => env('TENANT_SEEDER', 'Database\\Seeders\\Tenant\\TenantDatabaseSeeder'),
        'prefix' => env('TENANT_DATABASE_PREFIX', 'real_estate_'),
    ],
    'paths' => [
        'clients_root' => public_path('realestate/clients'),
        'global_root' => public_path('realestate/global'),
        'storage_root' => public_path('realestate/storage'),
        'templates_root' => resource_path('views/realestate'),
    ],
    'super_admin' => [
        'email' => env('TENANT_SUPER_ADMIN_EMAIL', 'admin@afli.ae'),
        'password' => env('TENANT_SUPER_ADMIN_PASSWORD', 'ChangeMe123'),
    ],
];

