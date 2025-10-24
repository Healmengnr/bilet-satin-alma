<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if (!hasRole('admin')) {
    die('Yetkisiz erişim');
}

$pdo = getDBConnection();

$companies = $pdo->query("SELECT id, name FROM Bus_Company ORDER BY name")->fetchAll();
?>

<form id="companyAdminNewForm" action="javascript:void(0)" method="post">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Şifre</label>
            <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="col-md-6">
            <label class="form-label">Firma</label>
            <select name="company_id" class="form-select" required>
                <option value="">Firma Seçin</option>
                <?php foreach ($companies as $company): ?>
                    <option value="<?= htmlspecialchars($company['id']) ?>">
                        <?= htmlspecialchars($company['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Firma Admini Oluştur
        </button>
    </div>
</form>
