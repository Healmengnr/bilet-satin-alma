<?php
require_once 'config/config.php';

$page_title = 'Sefer Ara';
include 'includes/header.php';

$departure_city = $_GET['departure_city'] ?? '';
$arrival_city = $_GET['arrival_city'] ?? '';
$departure_date = $_GET['departure_date'] ?? '';

$trips = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($departure_city) && !empty($arrival_city)) {
    $pdo = getDBConnection();
    
    // Seferleri ara
    $sql = "
        SELECT t.*, bc.name as company_name, bc.logo_path 
        FROM Trips t 
        JOIN Bus_Company bc ON t.company_id = bc.id 
        WHERE t.departure_city = ? AND t.destination_city = ?
    ";
    $params = [$departure_city, $arrival_city];
    
    // Tarih filtresi (opsiyonel)
    if (!empty($departure_date)) {
        $sql .= " AND DATE(t.departure_time) = ?";
        $params[] = $departure_date;
    } else {
        // Tarih seçilmediyse gelecekteki seferleri göster
        $sql .= " AND DATE(t.departure_time) >= DATE('now')";
    }
    
    $sql .= " ORDER BY t.departure_time ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $trips = $stmt->fetchAll();
    
    if (empty($trips)) {
        $error = 'Belirtilen kriterlere uygun sefer bulunamadı.';
    }
}

// Şehir listesi
$cities = [
    'İstanbul', 'Ankara', 'İzmir', 'Antalya', 'Bursa', 'Adana', 
    'Gaziantep', 'Konya', 'Kayseri', 'Eskişehir', 'Trabzon', 'Samsun'
];
?>

<div class="row">
    <div class="col-12">
        <!-- Arama Formu -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-search"></i> Sefer Ara</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="departure_city" class="form-label">Kalkış Şehri <span class="text-danger">*</span></label>
                            <select class="form-select" id="departure_city" name="departure_city" required>
                                <option value="">Kalkış şehrini seçin</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?php echo $city; ?>" <?php echo $departure_city === $city ? 'selected' : ''; ?>>
                                        <?php echo $city; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Lütfen kalkış şehrini seçin.
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="arrival_city" class="form-label">Varış Şehri <span class="text-danger">*</span></label>
                            <select class="form-select" id="arrival_city" name="arrival_city" required>
                                <option value="">Varış şehrini seçin</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?php echo $city; ?>" <?php echo $arrival_city === $city ? 'selected' : ''; ?>>
                                        <?php echo $city; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Lütfen varış şehrini seçin.
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="departure_date" class="form-label">Tarih <small class="text-muted">(Opsiyonel)</small></label>
                            <input type="date" class="form-control" id="departure_date" name="departure_date" 
                                   value="<?php echo escape($departure_date); ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                            <div class="form-text">
                                Tarih seçmezseniz gelecekteki tüm seferler gösterilir.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-search"></i> Seferleri Ara
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($trips)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-bus-front"></i> 
                        <?php echo escape($departure_city); ?> → <?php echo escape($arrival_city); ?>
                        <?php if (!empty($departure_date)): ?>
                            - <?php echo formatDate($departure_date); ?>
                        <?php endif; ?>
                    </h5>
                    <span class="badge bg-primary"><?php echo count($trips); ?> sefer bulundu</span>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($trips as $trip): ?>
                        <div class="trip-item border-bottom p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="trip-info">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="trip-route me-4">
                                                <h6 class="mb-1">
                                                    <?php echo escape($trip['departure_city']); ?> → 
                                                    <?php echo escape($trip['destination_city']); ?>
                                                </h6>
                                                <small class="text-muted"><?php echo escape($trip['company_name']); ?></small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-calendar-event text-primary me-2"></i>
                                                    <span><?php echo formatDate($trip['departure_time']); ?></span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-clock text-primary me-2"></i>
                                                    <span class="trip-time">
                                                        <?php echo formatTime($trip['departure_time']); ?> - 
                                                        <?php echo formatTime($trip['arrival_time']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-people text-info me-2"></i>
                                                    <span><?php echo $trip['capacity']; ?> koltuk</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-bus-front text-success me-2"></i>
                                                    <span><?php echo escape($trip['company_name']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 text-md-end">
                                    <div class="trip-price mb-3">
                                        <h4 class="text-success mb-0"><?php echo formatPrice($trip['price']); ?></h4>
                                        <small class="text-muted">Kişi başı</small>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="showTripDetails('<?php echo $trip['id']; ?>')">
                                            <i class="bi bi-eye"></i> Detayları Gör
                                        </button>
                                        
                                        <?php if (isLoggedIn()): ?>
                                            <a href="/book-ticket.php?id=<?php echo $trip['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-ticket"></i> Bilet Al
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-primary btn-sm" 
                                                    onclick="alert('Bilet satın almak için giriş yapmalısınız!'); window.location.href='/login.php';">
                                                <i class="bi bi-ticket"></i> Bilet Al
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($departure_city) && empty($arrival_city)): ?>
    <!-- Popüler Rotalar -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-star"></i> Popüler Rotalar</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <i class="bi bi-arrow-right text-primary me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">İstanbul → Ankara</h6>
                                    <small class="text-muted">Günlük 15+ sefer</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <i class="bi bi-arrow-right text-primary me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">İstanbul → İzmir</h6>
                                    <small class="text-muted">Günlük 12+ sefer</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <i class="bi bi-arrow-right text-primary me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">Ankara → Antalya</h6>
                                    <small class="text-muted">Günlük 8+ sefer</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <i class="bi bi-arrow-right text-primary me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">İzmir → Bursa</h6>
                                    <small class="text-muted">Günlük 6+ sefer</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Trip Details Modal -->
<div class="modal fade" id="tripDetailsModal" tabindex="-1" aria-labelledby="tripDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tripDetailsModalLabel">
                    <i class="bi bi-bus-front"></i> Sefer Detayları
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="tripDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                    <p class="mt-2">Sefer detayları yükleniyor...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary" id="bookTicketBtn" style="display: none;">
                    <i class="bi bi-ticket"></i> Bilet Satın Al
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showTripDetails(tripId) {
    const modal = new bootstrap.Modal(document.getElementById('tripDetailsModal'));
    const content = document.getElementById('tripDetailsContent');
    const bookBtn = document.getElementById('bookTicketBtn');
    
    // Modal'ı aç
    modal.show();
    
    // İçeriği temizle ve loading göster
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <p class="mt-2">Sefer detayları yükleniyor...</p>
        </div>
    `;
    bookBtn.style.display = 'none';
    
    // AJAX ile sefer detaylarını yükle
    fetch(`/api/trip-details.php?id=${tripId}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
            bookBtn.style.display = 'block';
            bookBtn.onclick = function() {
                modal.hide();
                window.location.href = `/book-ticket.php?id=${tripId}`;
            };
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Sefer detayları yüklenirken hata oluştu.
                </div>
            `;
        });
}
</script>

<?php include 'includes/footer.php'; ?>
