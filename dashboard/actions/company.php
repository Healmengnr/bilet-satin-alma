<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$pdo = getDBConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');

    if ($name === '' || $contact_email === '' || $contact_phone === '') {
        $error = 'Tüm alanları doldurun';
    } else {
        try {
            if ($id) {
                // Güncelleme
                $stmt = $pdo->prepare("UPDATE Bus_Company SET name=?, contact_email=?, contact_phone=? WHERE id=?");
                $stmt->execute([$name, $contact_email, $contact_phone, $id]);
                $message = 'Firma güncellendi';
            } else {
                // Yeni ekleme
                $stmt = $pdo->prepare("INSERT INTO Bus_Company (id, name, contact_email, contact_phone) VALUES (?, ?, ?, ?)");
                $stmt->execute([generateUUID(), $name, $contact_email, $contact_phone]);
                $message = 'Firma eklendi';
            }
            
            echo json_encode(['success' => true, 'message' => $message, 'reload' => 'companies']);
            exit;
        } catch (Exception $e) {
            $error = 'Kayıt sırasında hata: ' . $e->getMessage();
        }
    }
}

echo json_encode(['success' => false, 'message' => $error]);
