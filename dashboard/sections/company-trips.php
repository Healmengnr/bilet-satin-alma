<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('company_admin')) {
    http_response_code(403);
    exit('Yetkisiz erişim');
}

$pdo = getDBConnection();
$user = getCurrentUser();
$stmt = $pdo->prepare("SELECT * FROM Trips WHERE company_id = ? ORDER BY departure_time DESC");
$stmt->execute([$user['company_id']]);
$trips = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Toplam <?php echo count($trips); ?> sefer</h6>
    <button class="btn btn-success btn-sm" onclick="showModal('trip-new')">
        <i class="bi bi-plus"></i> Yeni Sefer
    </button>
</div>

<?php if (empty($trips)): ?>
    <div class="text-center text-muted py-4">
        <i class="bi bi-bus-front fs-1"></i>
        <p class="mt-2">Henüz sefer bulunmuyor.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Güzergah</th>
                    <th>Kalkış</th>
                    <th>Varış</th>
                    <th>Fiyat</th>
                    <th>Kapasite</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $trip): ?>
                <tr style="cursor: pointer;" onclick="window.location.href='/trip-details.php?id=<?php echo $trip['id']; ?>'">
                    <td>
                        <strong><?php echo escape($trip['departure_city']); ?> → <?php echo escape($trip['destination_city']); ?></strong>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($trip['arrival_time'])); ?></td>
                    <td><?php echo formatPrice($trip['price']); ?></td>
                    <td><?php echo $trip['capacity']; ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); showModal('trip-edit', '<?php echo $trip['id']; ?>')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteItem('trip', '<?php echo $trip['id']; ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
