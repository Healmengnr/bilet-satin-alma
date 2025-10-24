<?php
require_once '../config/config.php';

if (!isLoggedIn() || (!hasRole('admin') && !hasRole('company_admin'))) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu']);
    exit;
}

$ticket_id = $_POST['ticket_id'] ?? '';

if (empty($ticket_id)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz bilet ID']);
    exit;
}

try {
    $pdo = getDBConnection();
    $user = getCurrentUser();
    
    $stmt = $pdo->prepare("
        SELECT t.*, tr.company_id, tr.departure_time
        FROM Tickets t
        LEFT JOIN Trips tr ON t.trip_id = tr.id
        WHERE t.id = ?
    ");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Bilet bulunamadı']);
        exit;
    }
    
    if (hasRole('company_admin')) {
        if ($ticket['company_id'] !== $user['company_id']) {
            echo json_encode(['success' => false, 'message' => 'Bu bilet için yetkiniz yok']);
            exit;
        }
    }
    
    if ($ticket['status'] === 'cancelled') {
        echo json_encode(['success' => false, 'message' => 'Bilet zaten iptal edilmiş']);
        exit;
    }
    
    $departure_time = new DateTime($ticket['departure_time']);
    $current_time = new DateTime();
    $time_diff = $departure_time->getTimestamp() - $current_time->getTimestamp();
    
    if ($time_diff < 3600) {
        echo json_encode(['success' => false, 'message' => 'Sefer saatinden 1 saat öncesine kadar iptal edilebilir']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("UPDATE Tickets SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$ticket_id]);
        
        $stmt = $pdo->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$ticket['total_price'], $ticket['user_id']]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Bilet başarıyla iptal edildi ve ücret iade edildi'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Cancel ticket admin error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sistem hatası oluştu']);
}
?>
