#!/bin/bash

# Fix Seeder Autoload Issue
# This script ensures the seeder class is properly autoloaded

echo "=========================================="
echo "Fixing Seeder Autoload Issue"
echo "=========================================="
echo ""

cd "$(dirname "$0")" || exit

# Step 1: Verify seeder file exists
echo "Step 1: Checking seeder file..."
if [ -f "packages/Crm/database/seeders/CrmRolePermissionSeeder.php" ]; then
    echo "✅ Seeder file exists"
else
    echo "❌ Seeder file NOT found at: packages/Crm/database/seeders/CrmRolePermissionSeeder.php"
    exit 1
fi
echo ""

# Step 2: Check namespace in seeder file
echo "Step 2: Verifying namespace..."
if grep -q "namespace Packages\\\\Crm\\\\database\\\\seeders;" packages/Crm/database/seeders/CrmRolePermissionSeeder.php; then
    echo "✅ Namespace is correct: Packages\\Crm\\database\\seeders"
else
    echo "⚠️  Warning: Namespace might be incorrect"
    echo "   Expected: namespace Packages\\Crm\\database\\seeders;"
    grep "namespace" packages/Crm/database/seeders/CrmRolePermissionSeeder.php | head -1
fi
echo ""

# Step 3: Regenerate autoloader
echo "Step 3: Regenerating Composer autoloader..."
composer dump-autoload
if [ $? -eq 0 ]; then
    echo "✅ Autoloader regenerated"
else
    echo "❌ Failed to regenerate autoloader"
    exit 1
fi
echo ""

# Step 4: Verify class can be loaded
echo "Step 4: Testing if class can be loaded..."
php artisan tinker <<EOF
\$seederClass = 'Packages\\\\Crm\\\\database\\\\seeders\\\\CrmRolePermissionSeeder';
if (class_exists(\$seederClass)) {
    echo "✅ Class can be loaded: \$seederClass\n";
    \$seeder = new \$seederClass();
    echo "✅ Seeder instance created successfully\n";
} else {
    echo "❌ Class cannot be loaded: \$seederClass\n";
    echo "   Try running: composer dump-autoload\n";
    
    // Try to require the file directly
    \$filePath = base_path('packages/Crm/database/seeders/CrmRolePermissionSeeder.php');
    if (file_exists(\$filePath)) {
        echo "   File exists at: \$filePath\n";
        require_once \$filePath;
        if (class_exists(\$seederClass)) {
            echo "✅ Class loaded after require_once\n";
        } else {
            echo "❌ Class still not found after require_once\n";
        }
    } else {
        echo "❌ File not found at: \$filePath\n";
    }
}
exit
EOF
echo ""

# Step 5: Clear caches
echo "Step 5: Clearing caches..."
php artisan optimize:clear
echo "✅ Caches cleared"
echo ""

echo "=========================================="
echo "Fix Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "  1. Try registering again: https://crm.goafli.com/crm/register"
echo "  2. If it still fails, check: storage/logs/laravel.log"
echo ""

