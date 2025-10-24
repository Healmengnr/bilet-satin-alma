<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    exit('Yetkisiz erişim');
}

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM Bus_Company ORDER BY name");
$companies = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Toplam <?php echo count($companies); ?> firma</h6>
    <button class="btn btn-success btn-sm" onclick="showModal('company-new')">
        <i class="bi bi-plus"></i> Yeni Firma
    </button>
</div>

<?php if (empty($companies)): ?>
    <div class="text-center text-muted py-4">
        <i class="bi bi-building fs-1"></i>
        <p class="mt-2">Henüz firma bulunmuyor.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Firma Adı</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $company): ?>
                <tr>
                    <td>
                        <strong><?php echo escape($company['name']); ?></strong>
                    </td>
                    <td><?php echo escape($company['contact_email']); ?></td>
                    <td><?php echo escape($company['contact_phone']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="showModal('company-edit', '<?php echo $company['id']; ?>')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('company', '<?php echo $company['id']; ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
