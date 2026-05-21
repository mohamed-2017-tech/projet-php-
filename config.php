<?php
// Database configuration. Update values or set environment variables in production.
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'patisserie';

// Optional: fail early if required in production
// if (getenv('APP_ENV') === 'production' && (empty($user) || $user === 'root')) {
//     error_log('Please configure database credentials for production.');
// }