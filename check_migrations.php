<?php
/**
 * Migration Discovery Diagnostic Script
 * Run: php check_migrations.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==========================================\n";
echo "Migration Discovery Diagnostic\n";
echo "==========================================\n\n";

// Check service providers
echo "1. Checking Service Providers:\n";
$providers = require __DIR__ . '/bootstrap/providers.php';
$crmProvider = 'Packages\Crm\Providers\CrmServiceProvider';
if (in_array($crmProvider, $providers)) {
    echo "   ✅ CrmServiceProvider is registered\n";
} else {
    echo "   ❌ CrmServiceProvider is NOT registered\n";
}
echo "\n";

// Check if service provider is loaded
echo "2. Checking if CrmServiceProvider is loaded:\n";
try {
    $serviceProvider = app()->getProvider($crmProvider);
    if ($serviceProvider) {
        echo "   ✅ CrmServiceProvider is loaded\n";
    } else {
        echo "   ❌ CrmServiceProvider is not loaded\n";
    }
} catch (Exception $e) {
    echo "   ⚠️  Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Check migration files in database/migrations
echo "3. Migration files in database/migrations:\n";
$mainMigrations = glob(__DIR__ . '/database/migrations/*.php');
foreach ($mainMigrations as $migration) {
    echo "   - " . basename($migration) . "\n";
}
echo "   Total: " . count($mainMigrations) . " files\n\n";

// Check migration files in packages/Crm/database/migrations
echo "4. Migration files in packages/Crm/database/migrations:\n";
$crmMigrations = glob(__DIR__ . '/packages/Crm/database/migrations/*.php');
foreach ($crmMigrations as $migration) {
    echo "   - " . basename($migration) . "\n";
}
echo "   Total: " . count($crmMigrations) . " files\n\n";

// Check what Laravel discovers
echo "5. Migrations discovered by Laravel:\n";
try {
    $migrator = app('migrator');
    $paths = $migrator->paths();
    echo "   Migration paths:\n";
    foreach ($paths as $path) {
        echo "   - $path\n";
    }
    echo "\n";
    
    $files = $migrator->getMigrationFiles($paths);
    echo "   Discovered migration files: " . count($files) . "\n";
    foreach ($files as $file) {
        echo "   - " . basename($file) . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Check database connection
echo "6. Database connection:\n";
try {
    DB::connection()->getPdo();
    echo "   ✅ Database connected\n";
    echo "   Database: " . DB::connection()->getDatabaseName() . "\n";
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "==========================================\n";
echo "Diagnostic Complete\n";
echo "==========================================\n";

