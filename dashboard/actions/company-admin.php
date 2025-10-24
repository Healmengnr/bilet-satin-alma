<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

if (!hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

try {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $company_id = $_POST['company_id'] ?? '';
    $id = $_POST['id'] ?? '';

    if (empty($full_name) || empty($email) || empty($company_id)) {
        throw new Exception('Tüm alanlar doldurulmalıdır');
    }

    if (!$id && empty($password)) {
        throw new Exception('Şifre gereklidir');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Geçerli bir e-posta adresi giriniz');
    }

    $stmt = $pdo->prepare("SELECT id FROM Bus_Company WHERE id = ?");
    $stmt->execute([$company_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Geçersiz firma');
    }

    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM User WHERE id = ? AND role = 'company_admin'");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        
        if (!$existing) {
            throw new Exception('Firma admini bulunamadı');
        }

        $stmt = $pdo->prepare("SELECT id FROM User WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Bu e-posta adresi zaten kullanılıyor');
        }

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE User SET full_name = ?, email = ?, password = ?, company_id = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $hashedPassword, $company_id, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE User SET full_name = ?, email = ?, company_id = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $company_id, $id]);
        }

        echo json_encode(['success' => true, 'message' => 'Firma admini güncellendi', 'reload' => 'company-admins']);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM User WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Bu e-posta adresi zaten kullanılıyor');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $newId = generateUUID();
        
        $stmt = $pdo->prepare("INSERT INTO User (id, full_name, email, password, role, company_id, balance) VALUES (?, ?, ?, ?, 'company_admin', ?, 0)");
        $stmt->execute([$newId, $full_name, $email, $hashedPassword, $company_id]);

        echo json_encode(['success' => true, 'message' => 'Firma admini oluşturuldu', 'reload' => 'company-admins']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
