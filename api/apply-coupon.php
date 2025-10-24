<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$couponCode = $input['coupon_code'] ?? '';
$tripId = $input['trip_id'] ?? '';

if (empty($couponCode) || empty($tripId)) {
    echo json_encode(['success' => false, 'message' => 'Eksik parametreler']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM Coupons 
        WHERE code = ? AND status = 'active' 
        AND expire_date > datetime('now') 
        AND used_count < usage_limit
    ");
    $stmt->execute([$couponCode]);
    $coupon = $stmt->fetch();
    
    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş kupon']);
        exit;
    }
    
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM User_Coupons 
            WHERE user_id = ? AND coupon_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $coupon['id']]);
        $usedCount = $stmt->fetchColumn();
        
        if ($usedCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Bu kuponu daha önce kullandınız']);
            exit;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Kupon başarıyla uygulandı! %' . $coupon['discount'] . ' indirim kazandınız.',
        'discount_percentage' => $coupon['discount']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Kupon uygulanırken hata oluştu']);
}
?>
