<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    exit('Yetkisiz erişim');
}

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM Coupons WHERE company_id IS NULL ORDER BY created_at DESC");
$coupons = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Toplam <?php echo count($coupons); ?> sistem kuponu</h6>
    <button class="btn btn-success btn-sm" onclick="showModal('coupon-new')">
        <i class="bi bi-plus"></i> Yeni Kupon
    </button>
</div>

<?php if (empty($coupons)): ?>
    <div class="text-center text-muted py-4">
        <i class="bi bi-ticket-perforated fs-1"></i>
        <p class="mt-2">Henüz sistem kuponu bulunmuyor.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Kupon Kodu</th>
                    <th>İndirim (%)</th>
                    <th>Kullanım</th>
                    <th>Son Kullanma</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $coupon): ?>
                <tr>
                    <td>
                        <strong><?php echo escape($coupon['code']); ?></strong>
                    </td>
                    <td><?php echo $coupon['discount']; ?>%</td>
                    <td><?php echo $coupon['used_count']; ?> / <?php echo $coupon['usage_limit']; ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($coupon['expire_date'])); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $coupon['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo $coupon['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="showModal('coupon-edit', '<?php echo $coupon['id']; ?>')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('coupon', '<?php echo $coupon['id']; ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
