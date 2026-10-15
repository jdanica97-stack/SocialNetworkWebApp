<?php
// tests/bootstrap.php
// PHPUnit Test Suite Bootstrap for SMCC Connect

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Ensure testing environment
putenv('APP_ENV=testing');
if (!getenv('DB_NAME')) {
    putenv('DB_NAME=social_app_test');
}

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Base application constants
if (!defined('BASE_URL')) {
    define('BASE_URL', '/SocialNetworkWebApp');
}

// Load core helpers
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/helpers/ErrorHandler.php';

// Run test database migration if needed
require_once __DIR__ . '/setup_test_db.php';

// Ensure database connection is pointing to social_app_test
require_once __DIR__ . '/../config/database.php';
Database::disconnect();
$testConn = Database::connect();

// Safety Verification: Confirm we are NEVER connected to production database
$activeDb = $testConn->query("SELECT DATABASE()")->fetchColumn();
if ($activeDb === 'social_app') {
    die("CRITICAL TEST SECURITY ERROR: Tests attempted to connect to production database 'social_app'! Aborting test suite execution.\n");
}

echo "PHPUnit Bootstrap: Successfully initialized isolated test database '{$activeDb}'.\n";
