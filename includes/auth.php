<?php
/**
 * Kimlik Doğrulama Fonksiyonları
 */

/**
 * Kullanıcı giriş yap
 */
function loginUser($username, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['company_id'] = $user['company_id'];
        
        return true;
    }
    
    return false;
}

/**
 * Kullanıcı kayıt ol
 */
function registerUser($data) {
    $pdo = getDBConnection();
    
    // Kullanıcı adı ve email kontrolü
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$data['username'], $data['email']]);
    if ($stmt->fetch()) {
        return false; // Kullanıcı zaten mevcut
    }
    
    // Yeni kullanıcı oluştur
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, phone, role, credit) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $data['username'],
        $data['email'],
        hashPassword($data['password']),
        $data['full_name'],
        $data['phone'] ?? null,
        $data['role'] ?? 'user',
        DEFAULT_CREDIT
    ]);
    
    return $result;
}

/**
 * Kullanıcı çıkış yap
 */
function logoutUser() {
    session_destroy();
    session_start();
}

/**
 * Mevcut kullanıcı bilgilerini al
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Kullanıcı kredisi güncelle
 */
function updateUserCredit($userId, $amount) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET credit = credit + ? WHERE id = ?");
    return $stmt->execute([$amount, $userId]);
}

/**
 * Kullanıcı kredisi kontrol et
 */
function checkUserCredit($userId, $amount) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT credit FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    return $user && $user['credit'] >= $amount;
}

/**
 * Yetki kontrolü
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Bu sayfaya erişmek için giriş yapmalısınız.');
        redirect('/login.php');
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        setFlashMessage('error', 'Bu işlem için yetkiniz bulunmuyor.');
        redirect('/');
    }
}

function requireAdmin() {
    requireRole('admin');
}

function requireFirmaAdmin() {
    if (!hasRole('admin') && !hasRole('firma_admin')) {
        requireRole('firma_admin');
    }
}
?>
