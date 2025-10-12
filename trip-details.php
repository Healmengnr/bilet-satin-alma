<?php
require_once 'config/config.php';

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

$page_title = $trip ? 'Sefer Detayları - ' . $trip['departure_city'] . ' → ' . $trip['destination_city'] : 'Sefer Detayları';
include 'includes/header.php';
?>

<?php if ($error): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
            </div>
            <div class="text-center mt-4">
                <a href="/search.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Sefer Ara Sayfasına Dön
                </a>
            </div>
        </div>
    </div>
<?php elseif ($trip): ?>
    <div class="row">
        <div class="col-lg-8">
            <!-- Sefer Detayları -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-bus-front"></i> 
                        <?php echo escape($trip['departure_city']); ?> → <?php echo escape($trip['destination_city']); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
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
                        
                        <div class="col-md-6">
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
                </div>
            </div>
            
            <!-- Önemli Bilgiler -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Önemli Bilgiler</h5>
                </div>
                <div class="card-body">
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
        </div>
        
        <div class="col-lg-4">
            <!-- Fiyat ve Satın Alma -->
            <div class="card mb-4">
                <div class="card-header text-center">
                    <h5 class="mb-0">Bilet Fiyatı</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success mb-3"><?php echo formatPrice($trip['price']); ?></h2>
                    <p class="text-muted mb-4">Kişi başı</p>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="d-grid gap-2">
                            <a href="/book-ticket.php?id=<?php echo $trip['id']; ?>" 
                               class="btn btn-primary btn-lg">
                                <i class="bi bi-ticket"></i> Bilet Satın Al
                            </a>
                        </div>
                        
                        <?php
                        $user = getCurrentUser();
                        if ($user && $user['balance'] < $trip['price']):
                        ?>
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i>
                                Yetersiz bakiye! Hesabınızda <?php echo formatPrice($user['balance']); ?> var.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-lg" 
                                    onclick="alert('Bilet satın almak için giriş yapmalısınız!'); window.location.href='/login.php';">
                                <i class="bi bi-ticket"></i> Bilet Satın Al
                            </button>
                        </div>
                        <p class="text-muted mt-3">
                            <i class="bi bi-info-circle"></i>
                            Bilet satın almak için giriş yapmalısınız.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Hızlı İşlemler -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Hızlı İşlemler</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/search.php" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> Başka Sefer Ara
                        </a>
                        <a href="/" class="btn btn-outline-secondary">
                            <i class="bi bi-house"></i> Ana Sayfa
                        </a>
                        <?php if (isLoggedIn()): ?>
                            <a href="/tickets.php" class="btn btn-outline-info">
                                <i class="bi bi-ticket"></i> Biletlerim
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
