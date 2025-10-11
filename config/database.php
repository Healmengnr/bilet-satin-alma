<?php
/**
 * Veritabanı Konfigürasyonu
 */

// Veritabanı dosya yolu
define('DB_PATH', __DIR__ . '/../database/bilet_otomasyonu.db');

// PDO bağlantısı oluştur
function getDBConnection() {
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die('Veritabanı bağlantı hatası: ' . $e->getMessage());
    }
}

// Veritabanı tablolarını oluştur
function createTables() {
    $pdo = getDBConnection();
    
    // Kullanıcılar tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            role ENUM('admin', 'firma_admin', 'user') DEFAULT 'user',
            credit DECIMAL(10,2) DEFAULT 0.00,
            company_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES companies(id)
        )
    ");
    
    // Firmalar tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS companies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            contact_email VARCHAR(100),
            contact_phone VARCHAR(20),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Seferler tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS trips (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            departure_city VARCHAR(100) NOT NULL,
            arrival_city VARCHAR(100) NOT NULL,
            departure_date DATE NOT NULL,
            departure_time TIME NOT NULL,
            arrival_time TIME NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            total_seats INTEGER DEFAULT 45,
            available_seats INTEGER DEFAULT 45,
            status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES companies(id)
        )
    ");
    
    // Biletler tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tickets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            trip_id INTEGER NOT NULL,
            seat_number INTEGER NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            discount_amount DECIMAL(10,2) DEFAULT 0.00,
            final_price DECIMAL(10,2) NOT NULL,
            coupon_code VARCHAR(50),
            status ENUM('active', 'cancelled', 'used') DEFAULT 'active',
            purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (trip_id) REFERENCES trips(id)
        )
    ");
    
    // Kuponlar tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS coupons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code VARCHAR(50) UNIQUE NOT NULL,
            discount_percentage INTEGER NOT NULL,
            max_usage INTEGER DEFAULT 100,
            used_count INTEGER DEFAULT 0,
            company_id INTEGER,
            valid_from DATE NOT NULL,
            valid_until DATE NOT NULL,
            status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES companies(id)
        )
    ");
}

// Veritabanı başlat
createTables();
?>
