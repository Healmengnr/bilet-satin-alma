<?php
require_once '../config/config.php';

if (!isLoggedIn() || !hasRole('user')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu']);
    exit;
}

$amount = $_POST['amount'] ?? '';

if (empty($amount)) {
    echo json_encode(['success' => false, 'message' => 'Miktar alanını doldurun']);
    exit;
}

$amount = (float)$amount;
if ($amount < 1 || $amount > 10000) {
    echo json_encode(['success' => false, 'message' => 'Miktar 1-10000 TL arasında olmalıdır']);
    exit;
}

try {
    $pdo = getDBConnection();
    $user = getCurrentUser();
    
    $stmt = $pdo->prepare("SELECT balance FROM User WHERE id = ?");
    $stmt->execute([$user['id']]);
    $currentBalance = $stmt->fetchColumn();
    
    if ($currentBalance === false) {
        echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı']);
        exit;
    }
    
    $newBalance = $currentBalance + $amount;
    
    $stmt = $pdo->prepare("UPDATE User SET balance = ? WHERE id = ?");
    $result = $stmt->execute([$newBalance, $user['id']]);
    
    if ($result) {
        $message = sprintf(
            '%s TL kredi eklendi. Yeni bakiye: %s TL',
            number_format($amount, 2),
            number_format($newBalance, 2)
        );
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'new_balance' => $newBalance
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kredi eklenirken hata oluştu']);
    }
    
} catch (Exception $e) {
    error_log('Add credit error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sistem hatası oluştu']);
}
?>
