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
    $id = $_POST['id'] ?? '';
    $departure_city = trim($_POST['departure_city'] ?? '');
    $destination_city = trim($_POST['destination_city'] ?? '');
    $departure_time = $_POST['departure_time'] ?? '';
    $arrival_time = $_POST['arrival_time'] ?? '';
    $price = (int)($_POST['price'] ?? 0);
    $capacity = (int)($_POST['capacity'] ?? 0);

    // Validasyon
    if ($departure_city === '' || $destination_city === '' || $departure_time === '' || $arrival_time === '' || $price <= 0 || $capacity <= 0) {
        $error = 'Tüm alanları doğru şekilde doldurun';
    } else if ($departure_city === $destination_city) {
        $error = 'Kalkış ve varış şehri aynı olamaz';
    } else if (!in_array($capacity, [25, 35, 41])) {
        $error = 'Kapasite 25, 35 veya 41 olmalıdır';
    } else if (strtotime($arrival_time) <= strtotime($departure_time)) {
        $error = 'Varış saati kalkış saatinden sonra olmalıdır';
    } else if (strtotime($departure_time) <= time()) {
        $error = 'Kalkış saati geçmiş bir tarih olamaz';
    } else {
        try {
            if ($id) {
                // Güncelleme - Biletli seferleri kontrol et
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Booked_Seats bs JOIN Tickets t ON bs.ticket_id = t.id WHERE t.trip_id = ?");
                $stmt->execute([$id]);
                $bookedSeats = $stmt->fetchColumn();
                
                if ($bookedSeats > 0) {
                    $error = 'Bu seferde biletli yolcular bulunmaktadır. Sadece fiyat ve saat bilgileri güncellenebilir.';
                } else {
                    $stmt = $pdo->prepare("UPDATE Trips SET departure_city=?, destination_city=?, departure_time=?, arrival_time=?, price=?, capacity=? WHERE id=? AND company_id=?");
                    $stmt->execute([$departure_city, $destination_city, $departure_time, $arrival_time, $price, $capacity, $id, $user['company_id']]);
                    $message = 'Sefer güncellendi';
                }
            } else {
                // Yeni ekleme
                $stmt = $pdo->prepare("INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity, created_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([generateUUID(), $user['company_id'], $departure_city, $destination_city, $departure_time, $arrival_time, $price, $capacity, date('Y-m-d H:i:s')]);
                $message = 'Sefer oluşturuldu';
            }
            
            if (!isset($error) || $error === '') {
                echo json_encode(['success' => true, 'message' => $message, 'reload' => 'company-trips']);
                exit;
            }
        } catch (Exception $e) {
            $error = 'Kayıt sırasında hata: ' . $e->getMessage();
        }
    }
}

echo json_encode(['success' => false, 'message' => $error]);
