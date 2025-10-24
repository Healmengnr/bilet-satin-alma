<?php
require_once '../config/config.php';

if (!isLoggedIn() || (!hasRole('admin') && !hasRole('company_admin'))) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$trip_id = $_GET['id'] ?? '';

if (empty($trip_id)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz sefer ID']);
    exit;
}

try {
    $pdo = getDBConnection();
    $user = getCurrentUser();
    
    if (hasRole('company_admin')) {
        $stmt = $pdo->prepare("SELECT company_id FROM Trips WHERE id = ?");
        $stmt->execute([$trip_id]);
        $trip_company_id = $stmt->fetchColumn();
        
        if ($trip_company_id !== $user['company_id']) {
            echo json_encode(['success' => false, 'message' => 'Bu sefer için yetkiniz yok']);
            exit;
        }
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            u.full_name,
            u.email,
            t.total_price,
            t.status,
            t.created_at,
            GROUP_CONCAT(bs.seat_number ORDER BY bs.seat_number) as seats
        FROM Tickets t
        LEFT JOIN User u ON t.user_id = u.id
        LEFT JOIN Booked_Seats bs ON t.id = bs.ticket_id
        WHERE t.trip_id = ?
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$trip_id]);
    $tickets = $stmt->fetchAll();
    
    foreach ($tickets as &$ticket) {
        $ticket['seats'] = $ticket['seats'] ? explode(',', $ticket['seats']) : [];
    }
    
    echo json_encode([
        'success' => true,
        'tickets' => $tickets
    ]);
    
} catch (Exception $e) {
    error_log('Trip tickets error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sistem hatası oluştu']);
}
?>
