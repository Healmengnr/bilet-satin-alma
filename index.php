<?php
require_once 'config/config.php';

// Admin ve Company Admin'i direkt dashboard'a yönlendir
if (isLoggedIn()) {
    if (hasRole('admin') || hasRole('company_admin')) {
        header('Location: /dashboard/');
        exit;
    }
}

$page_title = 'Ana Sayfa';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <!-- Hero Section -->
        <div class="hero-section bg-primary text-white rounded p-5 mb-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="bi bi-bus-front"></i> Otobüs Bileti Satın Al
                    </h1>
                    <p class="lead mb-4">
                        Türkiye'nin en güvenilir otobüs bileti satış platformu. 
                        En uygun fiyatlarla biletinizi hemen satın alın!
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="bi bi-bus-front" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <?php if (!isLoggedIn() || hasRole('user')): ?>
        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-search"></i> Sefer Ara</h5>
            </div>
            <div class="card-body">
                <form action="/search.php" method="GET" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="departure_city" class="form-label">Kalkış Şehri</label>
                            <select class="form-select" id="departure_city" name="departure_city" required>
                                <option value="">Kalkış şehrini seçin</option>
                                <option value="İstanbul">İstanbul</option>
                                <option value="Ankara">Ankara</option>
                                <option value="İzmir">İzmir</option>
                                <option value="Antalya">Antalya</option>
                                <option value="Bursa">Bursa</option>
                                <option value="Adana">Adana</option>
                                <option value="Gaziantep">Gaziantep</option>
                                <option value="Konya">Konya</option>
                                <option value="Kayseri">Kayseri</option>
                                <option value="Eskişehir">Eskişehir</option>
                                <option value="Trabzon">Trabzon</option>
                                <option value="Samsun">Samsun</option>
                            </select>
                            <div class="invalid-feedback">
                                Lütfen kalkış şehrini seçin.
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="arrival_city" class="form-label">Varış Şehri</label>
                            <select class="form-select" id="arrival_city" name="arrival_city" required>
                                <option value="">Varış şehrini seçin</option>
                                <option value="İstanbul">İstanbul</option>
                                <option value="Ankara">Ankara</option>
                                <option value="İzmir">İzmir</option>
                                <option value="Antalya">Antalya</option>
                                <option value="Bursa">Bursa</option>
                                <option value="Adana">Adana</option>
                                <option value="Gaziantep">Gaziantep</option>
                                <option value="Konya">Konya</option>
                                <option value="Kayseri">Kayseri</option>
                                <option value="Eskişehir">Eskişehir</option>
                                <option value="Trabzon">Trabzon</option>
                                <option value="Samsun">Samsun</option>
                            </select>
                            <div class="invalid-feedback">
                                Lütfen varış şehrini seçin.
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="departure_date" class="form-label">Tarih <small class="text-muted">(Opsiyonel)</small></label>
                            <input type="date" class="form-control" id="departure_date" name="departure_date" 
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
        <?php endif; ?>

        <!-- Popular Routes -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-star"></i> Popüler Rotalar</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <i class="bi bi-arrow-right text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-1">İstanbul → Ankara</h6>
                                <small class="text-muted">Günlük 15+ sefer</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <i class="bi bi-arrow-right text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-1">İstanbul → İzmir</h6>
                                <small class="text-muted">Günlük 12+ sefer</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <i class="bi bi-arrow-right text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-1">Ankara → Antalya</h6>
                                <small class="text-muted">Günlük 8+ sefer</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
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
    
    <div class="col-lg-4">
        <!-- User Info Card -->
        <?php if (isLoggedIn()): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Hoş Geldiniz</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo escape($_SESSION['full_name']); ?></h6>
                    <p class="text-muted mb-2"><?php echo escape($_SESSION['email']); ?></p>
                    
                    <?php
                    $user = getCurrentUser();
                    if ($user):
                    ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Hesap Bakiyesi:</span>
                            <strong class="text-success"><?php echo formatPrice($user['balance']); ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="/tickets.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-ticket"></i> Biletlerim
                        </a>
                        <a href="/profile.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-gear"></i> Hesap Ayarları
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
