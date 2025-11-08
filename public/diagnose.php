<?php
/**
 * Laravel Route Diagnostic Script
 * Upload this file to public_html/public/diagnose.php
 * Access via: https://crm.goafli.com/diagnose.php
 */

echo "<h1>Laravel Route Diagnostic</h1>";
echo "<hr>";

// Check if Laravel is bootstrapped
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "<h2>✅ Laravel Bootstrapped Successfully</h2>";
    
    // Check routes
    echo "<h2>Registered Routes (filtered by 'register'):</h2>";
    echo "<pre>";
    $routes = Illuminate\Support\Facades\Route::getRoutes();
    $found = false;
    foreach ($routes as $route) {
        $uri = $route->uri();
        $methods = implode('|', $route->methods());
        $action = $route->getActionName();
        
        if (str_contains($uri, 'register') || str_contains($action, 'Register')) {
            echo $methods . "  " . $uri . "  ->  " . $action . "\n";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "No register routes found!\n";
    }
    echo "</pre>";
    
    // Check if view exists
    echo "<h2>View File Check:</h2>";
    $viewPath = __DIR__ . '/../resources/views/auth/register.blade.php';
    if (file_exists($viewPath)) {
        echo "<p>✅ Register view exists: " . $viewPath . "</p>";
    } else {
        echo "<p>❌ Register view NOT found: " . $viewPath . "</p>";
    }
    
    // Check service provider
    echo "<h2>Service Provider Check:</h2>";
    $providersFile = __DIR__ . '/../bootstrap/providers.php';
    if (file_exists($providersFile)) {
        $providers = require $providersFile;
        $crmProvider = 'Packages\Crm\Providers\CrmServiceProvider';
        if (in_array($crmProvider, $providers)) {
            echo "<p>✅ CrmServiceProvider is registered</p>";
        } else {
            echo "<p>❌ CrmServiceProvider is NOT registered</p>";
            echo "<pre>Registered providers:\n";
            print_r($providers);
            echo "</pre>";
        }
    } else {
        echo "<p>❌ providers.php not found</p>";
    }
    
    // Check .htaccess
    echo "<h2>.htaccess Check:</h2>";
    $rootHtaccess = __DIR__ . '/../.htaccess';
    $publicHtaccess = __DIR__ . '/.htaccess';
    
    if (file_exists($rootHtaccess)) {
        echo "<p>✅ Root .htaccess exists</p>";
        echo "<pre>" . htmlspecialchars(file_get_contents($rootHtaccess)) . "</pre>";
    } else {
        echo "<p>❌ Root .htaccess NOT found at: " . $rootHtaccess . "</p>";
    }
    
    if (file_exists($publicHtaccess)) {
        echo "<p>✅ Public .htaccess exists</p>";
    } else {
        echo "<p>❌ Public .htaccess NOT found</p>";
    }
    
    // Server info
    echo "<h2>Server Information:</h2>";
    echo "<pre>";
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "Script Filename: " . __FILE__ . "\n";
    echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "mod_rewrite: " . (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) ? 'Enabled' : 'Unknown') . "\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n\n";
    echo htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><small>Remove this file after diagnosis for security reasons.</small></p>";

