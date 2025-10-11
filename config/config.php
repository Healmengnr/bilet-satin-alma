<?php
/**
 * Genel Konfigürasyon Dosyası
 */

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Site ayarları
define('SITE_NAME', 'Bilet Otomasyonu');
define('SITE_URL', 'http://localhost:8080');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Güvenlik ayarları
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 dakika

// Bilet ayarları
define('CANCELLATION_HOURS', 1); // İptal için son 1 saat
define('DEFAULT_CREDIT', 100.00); // Yeni kullanıcıya verilen kredi

// Veritabanı konfigürasyonunu dahil et
require_once __DIR__ . '/database.php';

// Yardımcı fonksiyonları dahil et
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
?>
