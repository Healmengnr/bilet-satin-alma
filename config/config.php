<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Istanbul');

define('SITE_NAME', 'HealmeGO');
define('SITE_URL', 'http://localhost:8080');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

define('CANCELLATION_HOURS', 1);
define('DEFAULT_CREDIT', 100.00);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
?>
