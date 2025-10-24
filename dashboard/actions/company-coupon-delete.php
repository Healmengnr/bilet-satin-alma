<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('company_admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$pdo = getDBConnection();
$user = getCurrentUser();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? $_GET['id'] ?? '';
    
    if ($id === '') {
        $error = 'Geçersiz kupon ID';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT used_count FROM Coupons WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $user['company_id']]);
            $coupon = $stmt->fetch();
            
            if (!$coupon) {
                $error = 'Kupon bulunamadı';
            } else if ($coupon['used_count'] > 0) {
                $error = 'Bu kupon kullanıldığı için silinemez';
            } else {
                $stmt = $pdo->prepare("DELETE FROM Coupons WHERE id = ? AND company_id = ?");
                $stmt->execute([$id, $user['company_id']]);
                echo json_encode(['success' => true, 'message' => 'Kupon silindi', 'reload' => 'company-coupons']);
                exit;
            }
        } catch (Exception $e) {
            $error = 'Silme sırasında hata: ' . $e->getMessage();
        }
    }
}

echo json_encode(['success' => false, 'message' => $error]);
