<?php
require_once 'config/config.php';

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
    <div class="col-lg-8">
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
                                <option value="istanbul">İstanbul</option>
                                <option value="ankara">Ankara</option>
                                <option value="izmir">İzmir</option>
                                <option value="antalya">Antalya</option>
                                <option value="bursa">Bursa</option>
                                <option value="adana">Adana</option>
                                <option value="gaziantep">Gaziantep</option>
                                <option value="konya">Konya</option>
                                <option value="kayseri">Kayseri</option>
                                <option value="eskisehir">Eskişehir</option>
                            </select>
                            <div class="invalid-feedback">
                                Lütfen kalkış şehrini seçin.
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="arrival_city" class="form-label">Varış Şehri</label>
                            <select class="form-select" id="arrival_city" name="arrival_city" required>
                                <option value="">Varış şehrini seçin</option>
                                <option value="istanbul">İstanbul</option>
                                <option value="ankara">Ankara</option>
                                <option value="izmir">İzmir</option>
                                <option value="antalya">Antalya</option>
                                <option value="bursa">Bursa</option>
                                <option value="adana">Adana</option>
                                <option value="gaziantep">Gaziantep</option>
                                <option value="konya">Konya</option>
                                <option value="kayseri">Kayseri</option>
                                <option value="eskisehir">Eskişehir</option>
                            </select>
                            <div class="invalid-feedback">
                                Lütfen varış şehrini seçin.
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="departure_date" class="form-label">Tarih</label>
                            <input type="date" class="form-control" id="departure_date" name="departure_date" required>
                            <div class="invalid-feedback">
                                Lütfen seyahat tarihini seçin.
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

        <!-- Popular Routes -->
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
    
    <div class="col-lg-4">
        <!-- User Info Card -->
        <?php if (isLoggedIn()): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Hoş Geldiniz</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo escape($_SESSION['full_name']); ?></h6>
                    <p class="text-muted mb-2"><?php echo escape($_SESSION['username']); ?></p>
                    
                    <?php
                    $user = getCurrentUser();
                    if ($user):
                    ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Hesap Kredisi:</span>
                            <strong class="text-success"><?php echo formatPrice($user['credit']); ?></strong>
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Hesap Oluşturun</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Bilet satın almak için hesap oluşturun ve hemen <?php echo formatPrice(DEFAULT_CREDIT); ?> kredi kazanın!</p>
                    <div class="d-grid gap-2">
                        <a href="/register.php" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Kayıt Ol
                        </a>
                        <a href="/login.php" class="btn btn-outline-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Features -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-check-circle"></i> Neden Bizi Seçmelisiniz?</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-shield-check text-success me-2"></i>
                        Güvenli ödeme sistemi
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-clock text-primary me-2"></i>
                        7/24 müşteri desteği
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-phone text-info me-2"></i>
                        Mobil uyumlu platform
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-ticket text-warning me-2"></i>
                        Anında bilet iptali
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-percent text-danger me-2"></i>
                        Özel indirim kuponları
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
