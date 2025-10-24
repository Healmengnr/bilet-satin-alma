<?php

$dbPath = __DIR__ . '/../database/bilet_otomasyonu.sqlite';
define('DB_PATH', $dbPath);

$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

function getDBConnection() {
    try {
        if (!file_exists(DB_PATH)) {
            touch(DB_PATH);
            chmod(DB_PATH, 0666);
        }
        
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die('Veritabanı bağlantı hatası: ' . $e->getMessage() . ' (Dosya: ' . DB_PATH . ')');
    }
}

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function createTables() {
    $pdo = getDBConnection();
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS Bus_Company (
            id TEXT PRIMARY KEY,
            name TEXT UNIQUE NOT NULL,
            logo_path TEXT,
            contact_email TEXT,
            contact_phone TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // User tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS User (
            id TEXT PRIMARY KEY,
            full_name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            role TEXT NOT NULL CHECK(role IN ('user', 'company_admin', 'admin')),
            password TEXT NOT NULL,
            company_id TEXT,
            balance REAL DEFAULT 800,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES Bus_Company(id)
        )
    ");
    
    // Trips tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS Trips (
            id TEXT PRIMARY KEY,
            company_id TEXT NOT NULL,
            destination_city TEXT NOT NULL,
            arrival_time DATETIME NOT NULL,
            departure_time DATETIME NOT NULL,
            departure_city TEXT NOT NULL,
            price INTEGER NOT NULL,
            capacity INTEGER NOT NULL CHECK(capacity IN (25, 35, 41)),
            created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES Bus_Company(id)
        )
    ");
    
    // Tickets tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS Tickets (
            id TEXT PRIMARY KEY,
            trip_id TEXT NOT NULL,
            user_id TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active', 'cancelled', 'expired')),
            total_price INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (trip_id) REFERENCES Trips(id),
            FOREIGN KEY (user_id) REFERENCES User(id)
        )
    ");
    
    // Booked_Seats tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS Booked_Seats (
            id TEXT PRIMARY KEY,
            ticket_id TEXT NOT NULL,
            seat_number INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES Tickets(id)
        )
    ");
    
    // Coupons tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS Coupons (
            id TEXT PRIMARY KEY,
            code TEXT NOT NULL,
            discount REAL NOT NULL,
            usage_limit INTEGER NOT NULL,
            expire_date DATETIME NOT NULL,
            status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active','inactive')),
            used_count INTEGER NOT NULL DEFAULT 0,
            company_id TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES Bus_Company(id)
        )
    ");
    
    // User_Coupons tablosu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS User_Coupons (
            id TEXT PRIMARY KEY,
            coupon_id TEXT NOT NULL,
            user_id TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (coupon_id) REFERENCES Coupons(id),
            FOREIGN KEY (user_id) REFERENCES User(id)
        )
    ");
}

createTables();
?>
