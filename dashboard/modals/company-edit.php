<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    exit('Yetkisiz erişim');
}

$pdo = getDBConnection();
$id = $_GET['id'] ?? '';
if (!$id) {
    exit('Geçersiz ID');
}

$stmt = $pdo->prepare("SELECT * FROM Bus_Company WHERE id = ?");
$stmt->execute([$id]);
$company = $stmt->fetch();

if (!$company) {
    exit('Firma bulunamadı');
}
?>

<form id="companyEditForm" action="javascript:void(0)" method="post">
    <input type="hidden" name="id" value="<?php echo escape($company['id']); ?>">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Firma Adı</label>
            <input type="text" name="name" class="form-control" required value="<?php echo escape($company['name']); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">E-posta</label>
            <input type="email" name="contact_email" class="form-control" required value="<?php echo escape($company['contact_email']); ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Telefon</label>
            <input type="tel" name="contact_phone" class="form-control" required value="<?php echo escape($company['contact_phone']); ?>">
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check"></i> Güncelle
        </button>
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">
            <i class="bi bi-x"></i> İptal
        </button>
    </div>
</form>
