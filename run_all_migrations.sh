#!/bin/bash

# Complete Migration Script for Laravel CRM
# This script ensures all migrations are discovered and run

echo "=========================================="
echo "Laravel CRM - Complete Migration Setup"
echo "=========================================="
echo ""

cd "$(dirname "$0")" || exit

# Step 1: Clear all caches (CRITICAL - ensures service providers reload)
echo "Step 1: Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "✅ Caches cleared"
echo ""

# Step 2: Verify service provider
echo "Step 2: Verifying service providers..."
php artisan tinker <<EOF
\$providers = require 'bootstrap/providers.php';
if (in_array('Packages\\\\Crm\\\\Providers\\\\CrmServiceProvider', \$providers)) {
    echo "✅ CrmServiceProvider is registered\n";
} else {
    echo "❌ CrmServiceProvider is NOT registered\n";
}
exit
EOF
echo ""

# Step 3: Check migration discovery
echo "Step 3: Checking migration discovery..."
php artisan migrate:status 2>&1 | head -20
echo ""

# Step 4: Show what migrations will run
echo "Step 4: Listing all migration files..."
echo "Main migrations:"
ls -1 database/migrations/*.php 2>/dev/null | wc -l | xargs echo "   Total:"
ls -1 database/migrations/*.php 2>/dev/null
echo ""
echo "CRM package migrations:"
ls -1 packages/Crm/database/migrations/*.php 2>/dev/null | wc -l | xargs echo "   Total:"
ls -1 packages/Crm/database/migrations/*.php 2>/dev/null
echo ""

# Step 5: Run migrations with verbose output
echo "Step 5: Running migrations..."
echo "----------------------------------------"
php artisan migrate --force -v
MIGRATION_EXIT_CODE=$?
echo "----------------------------------------"
echo ""

if [ $MIGRATION_EXIT_CODE -eq 0 ]; then
    echo "✅ Migrations completed successfully"
else
    echo "❌ Migrations failed with exit code: $MIGRATION_EXIT_CODE"
    exit 1
fi

# Step 6: Verify tables were created
echo ""
echo "Step 6: Verifying tables..."
php artisan tinker <<EOF
\$requiredTables = [
    'users',
    'roles', 
    'permissions',
    'crm_contacts',
    'crm_leads',
    'crm_tasks',
    'crm_pipelines',
    'crm_files',
    'crm_reports'
];

\$missing = [];
\$existing = [];

foreach (\$requiredTables as \$table) {
    if (\Illuminate\Support\Facades\Schema::hasTable(\$table)) {
        \$existing[] = \$table;
    } else {
        \$missing[] = \$table;
    }
}

echo "✅ Existing tables (" . count(\$existing) . "):\n";
foreach (\$existing as \$table) {
    echo "   - \$table\n";
}

if (!empty(\$missing)) {
    echo "\n❌ Missing tables (" . count(\$missing) . "):\n";
    foreach (\$missing as \$table) {
        echo "   - \$table\n";
    }
} else {
    echo "\n✅ All required tables exist!\n";
}
exit
EOF
echo ""

# Step 7: Seed roles and permissions
echo "Step 7: Seeding roles and permissions..."
php artisan tinker <<EOF
try {
    \$seeder = new \Packages\Crm\database\seeders\CrmRolePermissionSeeder();
    \$seeder->run();
    echo "✅ Roles and permissions seeded successfully\n";
} catch (\Exception \$e) {
    echo "⚠️  Warning: " . \$e->getMessage() . "\n";
    echo "Stack trace:\n" . \$e->getTraceAsString() . "\n";
}
exit
EOF
echo ""

# Step 8: Final status
echo "=========================================="
echo "Migration Setup Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "  1. Register: https://crm.goafli.com/crm/register"
echo "  2. Login: https://crm.goafli.com/crm/login"
echo "  3. Access workspace after login"
echo ""

