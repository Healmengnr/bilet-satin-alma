<?php
/**
 * Yardımcı Fonksiyonlar
 */

/**
 * Güvenli şifre hash'leme
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Şifre doğrulama
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Güvenli çıktı (XSS koruması)
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Flash mesaj sistemi
 */
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

/**
 * Kullanıcı giriş yapmış mı kontrol et
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Kullanıcı rolü kontrol et
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Yönlendirme
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Tarih formatla
 */
function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

/**
 * Saat formatla
 */
function formatTime($time, $format = 'H:i') {
    return date($format, strtotime($time));
}

/**
 * Para formatla
 */
function formatPrice($price) {
    return number_format($price, 2, ',', '.') . ' ₺';
}

/**
 * Koltuk numarası formatla
 */
function formatSeatNumber($seatNumber) {
    return sprintf('%02d', $seatNumber);
}

/**
 * Bilet iptal edilebilir mi kontrol et
 */
function canCancelTicket($departureDate, $departureTime) {
    $departureDateTime = $departureDate . ' ' . $departureTime;
    $departureTimestamp = strtotime($departureDateTime);
    $currentTimestamp = time();
    $timeDifference = $departureTimestamp - $currentTimestamp;
    
    return $timeDifference > (CANCELLATION_HOURS * 3600); // 1 saat = 3600 saniye
}

/**
 * Rastgele kupon kodu oluştur
 */
function generateCouponCode($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

/**
 * Form validasyonu
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9+\-\s()]+$/', $phone);
}

/**
 * CSRF token oluştur
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrula
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
