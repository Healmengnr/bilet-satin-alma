<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$pdo = getDBConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $code = trim($_POST['code'] ?? '');
    $discount = (float)($_POST['discount'] ?? 0);
    $usage_limit = (int)($_POST['usage_limit'] ?? 0);
    $expire_date = $_POST['expire_date'] ?? '';
    $status = isset($_POST['status']) ? 'active' : 'inactive';

    // Validasyon
    if ($code === '' || $discount <= 0 || $discount > 100 || $usage_limit <= 0 || $expire_date === '') {
        $error = 'Tüm alanları doğru şekilde doldurun';
    } else {
        try {
            // Kupon kodu benzersizlik kontrolü
            $checkStmt = $pdo->prepare("SELECT id FROM Coupons WHERE code = ? AND id != ?");
            $checkStmt->execute([$code, $id]);
            if ($checkStmt->fetch()) {
                $error = 'Bu kupon kodu zaten kullanılıyor';
            } else {
                if ($id) {
                    // Güncelleme
                    $stmt = $pdo->prepare("UPDATE Coupons SET code=?, discount=?, usage_limit=?, expire_date=?, status=? WHERE id=? AND company_id IS NULL");
                    $stmt->execute([$code, $discount, $usage_limit, $expire_date, $status, $id]);
                    $message = 'Kupon güncellendi';
                } else {
                    // Yeni ekleme
                    $stmt = $pdo->prepare("INSERT INTO Coupons (id, code, discount, usage_limit, expire_date, status, company_id, used_count) VALUES (?, ?, ?, ?, ?, ?, NULL, 0)");
                    $stmt->execute([generateUUID(), $code, $discount, $usage_limit, $expire_date, $status]);
                    $message = 'Kupon oluşturuldu';
                }
                
                echo json_encode(['success' => true, 'message' => $message, 'reload' => 'coupons']);
                exit;
            }
        } catch (Exception $e) {
            $error = 'Kayıt sırasında hata: ' . $e->getMessage();
        }
    }
}

echo json_encode(['success' => false, 'message' => $error]);
