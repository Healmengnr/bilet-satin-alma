<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$trip_id = $_GET['trip_id'] ?? '';

if (empty($trip_id)) {
    echo json_encode(['error' => 'Geçersiz sefer ID']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT bs.seat_number 
        FROM Booked_Seats bs
        JOIN Tickets t ON bs.ticket_id = t.id
        WHERE t.trip_id = ? AND t.status = 'active'
    ");
    $stmt->execute([$trip_id]);
    $occupiedSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'occupied_seats' => $occupiedSeats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>
