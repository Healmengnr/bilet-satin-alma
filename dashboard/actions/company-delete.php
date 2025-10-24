<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$pdo = getDBConnection();
$id = $_GET['id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM Bus_Company WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Firma silindi']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Silme sırasında hata: ' . $e->getMessage()]);
}
