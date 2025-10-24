<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

requireLogin();

$input = json_decode(file_get_contents('php://input'), true);
$tripId = $input['trip_id'] ?? '';
$selectedSeats = $input['selected_seats'] ?? [];
$couponCode = $input['coupon_code'] ?? null;

if (empty($tripId) || empty($selectedSeats)) {
    echo json_encode(['success' => false, 'message' => 'Eksik parametreler']);
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("SELECT * FROM Trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        throw new Exception('Sefer bulunamadı');
    }
    
    foreach ($selectedSeats as $sn) {
        if (!is_int($sn) && !ctype_digit((string)$sn)) {
            throw new Exception('Geçersiz koltuk numarası');
        }
        $sn = (int)$sn;
        if ($sn < 1 || $sn > (int)$trip['capacity']) {
            throw new Exception('Koltuk numarası kapasite dışında');
        }
    }

    $placeholders = str_repeat('?,', count($selectedSeats) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT bs.seat_number 
        FROM Booked_Seats bs
        JOIN Tickets t ON bs.ticket_id = t.id
        WHERE t.trip_id = ? AND t.status = 'active' AND bs.seat_number IN ($placeholders)
    ");
    $stmt->execute(array_merge([$tripId], $selectedSeats));
    $occupiedSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($occupiedSeats)) {
        throw new Exception('Seçilen koltuklardan bazıları dolu: ' . implode(', ', $occupiedSeats));
    }
    
    $user = getCurrentUser();
    $totalPrice = $trip['price'] * count($selectedSeats);
    
    $discountAmount = 0;
    if ($couponCode) {
        $stmt = $pdo->prepare("
            SELECT * FROM Coupons 
            WHERE code = ? AND status = 'active' 
            AND expire_date > datetime('now') 
            AND used_count < usage_limit
        ");
        $stmt->execute([$couponCode]);
        $coupon = $stmt->fetch();
        
        if ($coupon) {
            $discountAmount = $totalPrice * ($coupon['discount'] / 100);
            $totalPrice -= $discountAmount;
            
            $stmt = $pdo->prepare("
                INSERT INTO User_Coupons (id, user_id, coupon_id) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([generateUUID(), $user['id'], $coupon['id']]);
            
            $stmt = $pdo->prepare("UPDATE Coupons SET used_count = used_count + 1 WHERE id = ?");
            $stmt->execute([$coupon['id']]);
        }
    }
    
    if ($user['balance'] < $totalPrice) {
        throw new Exception('Yetersiz bakiye');
    }
    
    $ticketId = generateUUID();
    $stmt = $pdo->prepare("
        INSERT INTO Tickets (id, trip_id, user_id, status, total_price) 
        VALUES (?, ?, ?, 'active', ?)
    ");
    $stmt->execute([$ticketId, $tripId, $user['id'], $totalPrice]);
    
    foreach ($selectedSeats as $seatNumber) {
        $seatId = generateUUID();
        $stmt = $pdo->prepare("
            INSERT INTO Booked_Seats (id, ticket_id, seat_number) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$seatId, $ticketId, $seatNumber]);
    }
    
    $stmt = $pdo->prepare("UPDATE User SET balance = balance - ? WHERE id = ?");
    $stmt->execute([$totalPrice, $user['id']]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bilet başarıyla satın alındı!',
        'ticket_id' => $ticketId
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
