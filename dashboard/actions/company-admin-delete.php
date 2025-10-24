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

$id = $_POST['id'] ?? $_GET['id'] ?? '';
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM User WHERE id = ? AND role = 'company_admin'");
    $stmt->execute([$id]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        throw new Exception('Firma admini bulunamadı');
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Trips WHERE company_id = ?");
    $stmt->execute([$admin['company_id']]);
    $tripCount = $stmt->fetchColumn();
    
    if ($tripCount > 0) {
        throw new Exception('Bu firma admininin yönettiği seferler bulunmaktadır. Önce seferleri siliniz.');
    }

    $stmt = $pdo->prepare("DELETE FROM User WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Firma admini silindi']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
