<?php
require_once '../config/config.php';

if (!isLoggedIn() || !hasRole('company_admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

try {
    $pdo = getDBConnection();
    $user = getCurrentUser();
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            u.id,
            u.full_name,
            u.email,
            COUNT(t.id) as total_tickets,
            SUM(t.total_price) as total_spent,
            MAX(t.created_at) as last_purchase,
            GROUP_CONCAT(DISTINCT tr.departure_city || ' - ' || tr.destination_city) as routes
        FROM User u
        INNER JOIN Tickets t ON u.id = t.user_id
        INNER JOIN Trips tr ON t.trip_id = tr.id
        WHERE tr.company_id = ? AND t.status != 'cancelled'
        GROUP BY u.id, u.full_name, u.email
        ORDER BY last_purchase DESC
    ");
    
    $stmt->execute([$user['company_id']]);
    $customers = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $customers
    ]);
    
} catch (Exception $e) {
    error_log('Customers API error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sistem hatası oluştu']);
}
?>
