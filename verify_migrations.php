<?php
/**
 * Quick Migration Verification Script
 * Upload to server and run: php verify_migrations.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "==========================================\n";
echo "Migration Verification\n";
echo "==========================================\n\n";

// Get migrator
$migrator = app('migrator');

// Get all migration paths
$paths = array_merge(
    [database_path('migrations')],
    $migrator->paths()
);

echo "Migration Paths:\n";
foreach ($paths as $path) {
    if (is_dir($path)) {
        $count = count(glob($path . '/*.php'));
        echo "  ✅ $path ($count files)\n";
    } else {
        echo "  ❌ $path (NOT FOUND)\n";
    }
}
echo "\n";

// Get all migration files
$files = $migrator->getMigrationFiles($paths);

echo "Discovered Migrations (" . count($files) . " total):\n";
foreach ($files as $file) {
    echo "  - " . basename($file) . "\n";
}
echo "\n";

// Check if CRM migrations are included
$crmMigrations = array_filter($files, function($file) {
    return strpos($file, 'packages/Crm') !== false || strpos($file, 'crm_') !== false;
});

if (count($crmMigrations) > 0) {
    echo "CRM Package Migrations Found (" . count($crmMigrations) . "):\n";
    foreach ($crmMigrations as $file) {
        echo "  ✅ " . basename($file) . "\n";
    }
} else {
    echo "❌ NO CRM Package Migrations Found!\n";
    echo "   This means the service provider migrations are not being loaded.\n";
}
echo "\n";

// Check database connection
try {
    $dbName = DB::connection()->getDatabaseName();
    echo "Database: $dbName ✅\n";
} catch (Exception $e) {
    echo "Database: ❌ " . $e->getMessage() . "\n";
}
echo "\n";

echo "==========================================\n";
echo "To fix: Run 'php artisan migrate --force'\n";
echo "==========================================\n";

