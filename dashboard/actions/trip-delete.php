<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('company_admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$pdo = getDBConnection();
$user = getCurrentUser();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? $_GET['id'] ?? '';
    
    if ($id === '') {
        $error = 'Geçersiz sefer ID';
    } else {
        try {
            // Seferin biletli yolcuları var mı kontrol et
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Booked_Seats bs JOIN Tickets t ON bs.ticket_id = t.id WHERE t.trip_id = ?");
            $stmt->execute([$id]);
            $bookedSeats = $stmt->fetchColumn();
            
            if ($bookedSeats > 0) {
                $error = 'Bu seferde biletli yolcular bulunmaktadır. Sefer silinemez.';
            } else {
                // Seferi sil
                $stmt = $pdo->prepare("DELETE FROM Trips WHERE id = ? AND company_id = ?");
                $stmt->execute([$id, $user['company_id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Sefer silindi', 'reload' => 'company-trips']);
                    exit;
                } else {
                    $error = 'Sefer bulunamadı';
                }
            }
        } catch (Exception $e) {
            $error = 'Silme sırasında hata: ' . $e->getMessage();
        }
    }
}

echo json_encode(['success' => false, 'message' => $error]);
