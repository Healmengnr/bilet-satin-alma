<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    exit('Yetkisiz erişim');
}

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT t.*, bc.name as company_name FROM Trips t LEFT JOIN Bus_Company bc ON t.company_id = bc.id ORDER BY t.departure_time DESC");
$trips = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Toplam <?php echo count($trips); ?> sefer</h6>
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
                    <th>Firma</th>
                    <th>Güzergah</th>
                    <th>Kalkış</th>
                    <th>Varış</th>
                    <th>Fiyat</th>
                    <th>Kapasite</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $trip): ?>
                <tr style="cursor: pointer;" onclick="window.location.href='/trip-details.php?id=<?php echo $trip['id']; ?>'">
                    <td><?php echo escape($trip['company_name'] ?? 'Bilinmeyen'); ?></td>
                    <td>
                        <strong><?php echo escape($trip['departure_city']); ?> → <?php echo escape($trip['destination_city']); ?></strong>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($trip['arrival_time'])); ?></td>
                    <td><?php echo formatPrice($trip['price']); ?></td>
                    <td><?php echo $trip['capacity']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
