#!/bin/bash

# CRM Module Integration Script
# Run this script after uploading the CRM package to public_html/packages/Crm

echo "Starting CRM Module Integration..."

# Navigate to Laravel root directory
cd "$(dirname "$0")" || exit

# Step 1: Dump autoload
echo "Step 1: Regenerating Composer autoload..."
composer dump-autoload

# Step 2: Run migrations
echo "Step 2: Running database migrations..."
php artisan migrate

# Step 3: Clear all caches
echo "Step 3: Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Step 4: Verify routes
echo "Step 4: Verifying CRM routes..."
php artisan route:list | grep crm

echo ""
echo "✅ CRM Module Integration Complete!"
echo ""
echo "Test the integration by visiting:"
echo "  - https://yourdomain.com/crm"
echo "  - https://yourdomain.com/crm/files"
echo ""

