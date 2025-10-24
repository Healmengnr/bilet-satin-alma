<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

requireLogin();

$input = json_decode(file_get_contents('php://input'), true);
$ticketId = $input['ticket_id'] ?? '';

if (empty($ticketId)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz bilet ID']);
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        SELECT t.*, tr.departure_time 
        FROM Tickets t
        JOIN Trips tr ON t.trip_id = tr.id
        WHERE t.id = ? AND t.user_id = ? AND t.status = 'active'
    ");
    $stmt->execute([$ticketId, $_SESSION['user_id']]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        throw new Exception('Bilet bulunamadı veya iptal edilemez');
    }
    
    $departureTimestamp = strtotime($ticket['departure_time']);
    $currentTimestamp = time();
    $timeDifference = $departureTimestamp - $currentTimestamp;
    
    if ($timeDifference <= (CANCELLATION_HOURS * 3600)) {
        throw new Exception('Bilet sefer saatinden 1 saat öncesine kadar iptal edilebilir');
    }
    
    $stmt = $pdo->prepare("UPDATE Tickets SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$ticketId]);
    
    $stmt = $pdo->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$ticket['total_price'], $_SESSION['user_id']]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bilet başarıyla iptal edildi ve ücret iade edildi'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
