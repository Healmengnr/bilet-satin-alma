<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    exit('Yetkisiz erişim');
}

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT u.*, bc.name as company_name FROM User u LEFT JOIN Bus_Company bc ON u.company_id = bc.id WHERE u.role = 'company_admin' ORDER BY u.full_name");
$admins = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Toplam <?php echo count($admins); ?> firma admini</h6>
    <button class="btn btn-success btn-sm" onclick="showModal('company-admin-new')">
        <i class="bi bi-plus"></i> Yeni Firma Admini
    </button>
</div>

<?php if (empty($admins)): ?>
    <div class="text-center text-muted py-4">
        <i class="bi bi-people fs-1"></i>
        <p class="mt-2">Henüz firma admini bulunmuyor.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Firma</th>
                    <th>Bakiye</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td>
                        <strong><?php echo escape($admin['full_name']); ?></strong>
                    </td>
                    <td><?php echo escape($admin['email']); ?></td>
                    <td><?php echo escape($admin['company_name'] ?? 'Atanmamış'); ?></td>
                    <td><?php echo formatPrice($admin['balance']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="showModal('company-admin-edit', '<?php echo $admin['id']; ?>')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('company-admin', '<?php echo $admin['id']; ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
