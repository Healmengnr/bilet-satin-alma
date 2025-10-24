<?php
require_once '../config/config.php';

if (!isLoggedIn() || !hasRole('company_admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$customerId = $_GET['customer_id'] ?? '';

if (empty($customerId)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz müşteri ID']);
    exit;
}

try {
    $pdo = getDBConnection();
    $user = getCurrentUser();
    
    $stmt = $pdo->prepare("
        SELECT t.*, tr.departure_city, tr.destination_city, tr.departure_time, tr.arrival_time,
               bc.name as company_name, u.full_name, u.email
        FROM Tickets t
        JOIN Trips tr ON t.trip_id = tr.id
        JOIN Bus_Company bc ON tr.company_id = bc.id
        JOIN User u ON t.user_id = u.id
        WHERE t.user_id = ? AND tr.company_id = ? AND t.status IN ('active', 'cancelled')
        ORDER BY t.created_at DESC
    ");
    
    $stmt->execute([$customerId, $user['company_id']]);
    $tickets = $stmt->fetchAll();
    
    foreach ($tickets as &$ticket) {
        $stmt = $pdo->prepare("
            SELECT seat_number FROM Booked_Seats 
            WHERE ticket_id = ? 
            ORDER BY seat_number
        ");
        $stmt->execute([$ticket['id']]);
        $ticket['seats'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $tickets
    ]);
    
} catch (Exception $e) {
    error_log('Customer tickets API error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sistem hatası oluştu']);
}
?>
