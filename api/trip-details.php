<?php
require_once '../config/config.php';

$trip_id = $_GET['id'] ?? '';
$trip = null;
$error = '';

if (empty($trip_id)) {
    $error = 'Geçersiz sefer ID.';
} else {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT t.*, bc.name as company_name, bc.logo_path, bc.contact_email, bc.contact_phone
        FROM Trips t 
        JOIN Bus_Company bc ON t.company_id = bc.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        $error = 'Sefer bulunamadı.';
    }
}

if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
    </div>
<?php elseif ($trip): ?>
    <div class="row">
        <div class="col-12">
            <h6 class="text-primary mb-3">Sefer Bilgileri</h6>
            <table class="table table-borderless">
                <tr>
                    <td><strong>Kalkış Şehri:</strong></td>
                    <td><?php echo escape($trip['departure_city']); ?></td>
                </tr>
                <tr>
                    <td><strong>Varış Şehri:</strong></td>
                    <td><?php echo escape($trip['destination_city']); ?></td>
                </tr>
                <tr>
                    <td><strong>Kalkış Tarihi:</strong></td>
                    <td><?php echo formatDate($trip['departure_time']); ?></td>
                </tr>
                <tr>
                    <td><strong>Kalkış Saati:</strong></td>
                    <td><?php echo formatTime($trip['departure_time']); ?></td>
                </tr>
                <tr>
                    <td><strong>Varış Saati:</strong></td>
                    <td><?php echo formatTime($trip['arrival_time']); ?></td>
                </tr>
                <tr>
                    <td><strong>Süre:</strong></td>
                    <td>
                        <?php
                        $departure = new DateTime($trip['departure_time']);
                        $arrival = new DateTime($trip['arrival_time']);
                        $duration = $departure->diff($arrival);
                        echo $duration->format('%h saat %i dakika');
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <hr>
    
    <div class="row">
        <div class="col-12">
            <h6 class="text-info mb-3">Firma Bilgileri</h6>
            <table class="table table-borderless">
                <tr>
                    <td><strong>Firma:</strong></td>
                    <td><?php echo escape($trip['company_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Koltuk Kapasitesi:</strong></td>
                    <td><?php echo $trip['capacity']; ?> koltuk</td>
                </tr>
                <tr>
                    <td><strong>İletişim:</strong></td>
                    <td>
                        <?php if ($trip['contact_email']): ?>
                            <a href="mailto:<?php echo escape($trip['contact_email']); ?>">
                                <?php echo escape($trip['contact_email']); ?>
                            </a><br>
                        <?php endif; ?>
                        <?php if ($trip['contact_phone']): ?>
                            <a href="tel:<?php echo escape($trip['contact_phone']); ?>">
                                <?php echo escape($trip['contact_phone']); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <hr>
    
    <div class="row">
        <div class="col-12">
            <h6 class="text-success mb-3">Fiyat Bilgisi</h6>
            <div class="text-center">
                <h4 class="text-success mb-2"><?php echo formatPrice($trip['price']); ?></h4>
                <p class="text-muted mb-0">Kişi başı</p>
            </div>
        </div>
    </div>
    
    <hr>
    
    <div class="row">
        <div class="col-12">
            <h6 class="text-warning mb-3">Önemli Bilgiler</h6>
            <ul class="list-unstyled">
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    Biletler sefer saatinden 1 saat öncesine kadar iptal edilebilir.
                </li>
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    İptal edilen biletlerin ücreti hesabınıza iade edilir.
                </li>
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    Bilet satın alma işlemleri sanal kredi sistemi üzerinden yapılmaktadır.
                </li>
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    Sefer saatinden 30 dakika önce terminalde hazır bulunmanız önerilir.
                </li>
                <li class="mb-2">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    Koltuk seçimi bilet satın alma sırasında yapılır.
                </li>
            </ul>
        </div>
    </div>
<?php endif; ?>
