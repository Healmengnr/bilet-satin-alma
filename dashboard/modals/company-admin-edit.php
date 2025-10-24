<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if (!hasRole('admin')) {
    die('Yetkisiz erişim');
}

$pdo = getDBConnection();

$id = $_GET['id'] ?? '';
if (!$id) {
    die('Geçersiz ID');
}

$stmt = $pdo->prepare("SELECT * FROM User WHERE id = ? AND role = 'company_admin'");
$stmt->execute([$id]);
$admin = $stmt->fetch();

if (!$admin) {
    die('Firma admini bulunamadı');
}

$companies = $pdo->query("SELECT id, name FROM Bus_Company ORDER BY name")->fetchAll();
?>

<form id="companyAdminEditForm" action="javascript:void(0)" method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($admin['id']) ?>">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($admin['full_name']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($admin['email']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Yeni Şifre (Boş bırakırsanız değişmez)</label>
            <input type="password" name="password" class="form-control" minlength="6">
        </div>
        <div class="col-md-6">
            <label class="form-label">Firma</label>
            <select name="company_id" class="form-select" required>
                <option value="">Firma Seçin</option>
                <?php foreach ($companies as $company): ?>
                    <option value="<?= htmlspecialchars($company['id']) ?>" 
                            <?= $company['id'] === $admin['company_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($company['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Güncelle
        </button>
    </div>
</form>
