<?php

function loginUser($email, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['balance'] = $user['balance'];
        
        return true;
    }
    
    return false;
}

function registerUser($data) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM User WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return false;
    }
    $user_id = generateUUID();
    $stmt = $pdo->prepare("
        INSERT INTO User (id, full_name, email, password, role, company_id, balance) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $user_id,
        $data['full_name'],
        $data['email'],
        hashPassword($data['password']),
        $data['role'] ?? 'user',
        $data['company_id'] ?? null,
        800.00
    ]);
    
    return $result;
}

function logoutUser() {
    session_destroy();
    session_start();
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM User WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function updateUserBalance($user_id, $amount) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
    return $stmt->execute([$amount, $user_id]);
}

function checkUserBalance($user_id, $amount) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT balance FROM User WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    return $user && $user['balance'] >= $amount;
}

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

function requireCompanyAdmin() {
    if (!hasRole('admin') && !hasRole('company_admin')) {
        requireRole('company_admin');
    }
}
?>
