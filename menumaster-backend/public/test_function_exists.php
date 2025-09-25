<?php
// Test if requireAdmin function exists

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/App/Utils/AuthHelpers.php';

echo "Testing function existence:\n";
echo "function_exists('requireAdmin'): " . (function_exists('requireAdmin') ? 'YES' : 'NO') . "\n";
echo "class_exists('App\\Utils\\AuthHelpers'): " . (class_exists('App\\Utils\\AuthHelpers') ? 'YES' : 'NO') . "\n";

if (function_exists('requireAdmin')) {
    echo "✅ requireAdmin function is available\n";
} else {
    echo "❌ requireAdmin function is NOT available\n";
}

// Test calling the function without proper auth (should throw exception)
try {
    requireAdmin();
    echo "❌ Function call succeeded (unexpected)\n";
} catch (Exception $e) {
    echo "✅ Function call threw exception as expected: " . $e->getMessage() . "\n";
}