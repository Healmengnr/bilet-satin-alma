<?php

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

function formatTime($time, $format = 'H:i') {
    return date($format, strtotime($time));
}

function formatPrice($price) {
    return number_format($price, 2, ',', '.') . ' ₺';
}

function formatSeatNumber($seatNumber) {
    return sprintf('%02d', $seatNumber);
}

function canCancelTicket($departureDate, $departureTime) {
    $departureDateTime = $departureDate . ' ' . $departureTime;
    $departureTimestamp = strtotime($departureDateTime);
    $currentTimestamp = time();
    $timeDiff = $departureTimestamp - $currentTimestamp;
    
    return $timeDiff > (CANCELLATION_HOURS * 3600);
}

function generateCouponCode($length = 8) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9+\-\s()]+$/', $phone);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect404() {
    http_response_code(404);
    header('Location: /404.php');
    exit;
}

function redirect403() {
    http_response_code(403);
    header('Location: /403.php');
    exit;
}

function pageNotFound() {
    redirect404();
}

function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}
?>
