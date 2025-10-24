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
    $id = $_POST['id'] ?? '';
    $code = trim($_POST['code'] ?? '');
    $discount = (float)($_POST['discount'] ?? 0);
    $usage_limit = (int)($_POST['usage_limit'] ?? 0);
    $expire_date = $_POST['expire_date'] ?? '';
    $status = isset($_POST['status']) ? 'active' : 'inactive';

    if ($code === '' || $discount <= 0 || $discount > 100 || $usage_limit <= 0 || $expire_date === '') {
        $error = 'Tüm alanları doğru şekilde doldurun';
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM Coupons WHERE code = ? AND id != ?");
            $checkStmt->execute([$code, $id]);
            if ($checkStmt->fetch()) {
                $error = 'Bu kupon kodu zaten kullanılıyor';
            } else {
                if ($id) {
                    $stmt = $pdo->prepare("UPDATE Coupons SET code=?, discount=?, usage_limit=?, expire_date=?, status=? WHERE id=? AND company_id = ?");
                    $stmt->execute([$code, $discount, $usage_limit, $expire_date, $status, $id, $user['company_id']]);
                    $message = 'Kupon güncellendi';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO Coupons (id, code, discount, usage_limit, expire_date, status, company_id, used_count) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
                    $stmt->execute([generateUUID(), $code, $discount, $usage_limit, $expire_date, $status, $user['company_id']]);
                    $message = 'Kupon oluşturuldu';
                }
                
                echo json_encode(['success' => true, 'message' => $message, 'reload' => 'company-coupons']);
                exit;
            }
        } catch (Exception $e) {
            $error = 'Kayıt sırasında hata: ' . $e->getMessage();
        }
    }
}

echo json_encode(['success' => false, 'message' => $error]);
