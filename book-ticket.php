<?php
require_once 'config/config.php';

if (!isLoggedIn() || !hasRole('user')) {
    redirect403();
}

$trip_id = $_GET['id'] ?? '';
$trip = null;
$error = '';
$success = '';

if (empty($trip_id)) {
    $error = 'Geçersiz sefer ID.';
} else {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT t.*, bc.name as company_name, bc.logo_path
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

$page_title = $trip ? 'Bilet Satın Al - ' . $trip['departure_city'] . ' → ' . $trip['destination_city'] : 'Bilet Satın Al';
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
            <!-- Sefer Bilgileri -->
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
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Kalkış:</strong></td>
                                    <td><?php echo escape($trip['departure_city']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Varış:</strong></td>
                                    <td><?php echo escape($trip['destination_city']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tarih:</strong></td>
                                    <td><?php echo formatDate($trip['departure_time']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Saat:</strong></td>
                                    <td><?php echo formatTime($trip['departure_time']); ?> - <?php echo formatTime($trip['arrival_time']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Firma:</strong></td>
                                    <td><?php echo escape($trip['company_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Koltuk Sayısı:</strong></td>
                                    <td><?php echo $trip['capacity']; ?> koltuk</td>
                                </tr>
                                <tr>
                                    <td><strong>Fiyat:</strong></td>
                                    <td><?php echo formatPrice($trip['price']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Koltuk Seçimi -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-grid-3x3-gap"></i> Koltuk Seçimi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Bus Image by Capacity -->
                        <div class="col-12">
                            <div class="bus-sketch">
                                <div class="sketch-header">
                                    <h6><i class="bi bi-bus-front"></i> Otobüs Taslağı</h6>
                                </div>
                                <div class="sketch-body text-center">
                                    <?php
                                    $capacity = (int)$trip['capacity'];
                                    $imageMap = [
                                        25 => '/assets/images/25-kisilik-otobus.png',
                                        35 => '/assets/images/35-kisilik-otobus.png',
                                        41 => '/assets/images/41-kisilik-otobus.png',
                                    ];
                                    $imgSrc = isset($imageMap[$capacity]) ? $imageMap[$capacity] : '/assets/images/35-kisilik-otobus.png';
                                    ?>
                                    <img src="<?php echo $imgSrc; ?>" alt="<?php echo $capacity; ?> kişilik otobüs taslağı" class="img-fluid" style="max-height:300px; object-fit:contain;">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Seat Selection -->
                        <div class="col-12 mt-4">
                            <div class="seat-selection">
                                <div class="selection-header">
                                    <h6><i class="bi bi-check-square"></i> Koltuk Seçin</h6>
                                </div>
                                <div class="selection-body">
                                    <div class="seat-status-legend mb-3">
                                        <div class="legend-item">
                                            <div class="seat-indicator available"></div>
                                            <span>Müsait</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="seat-indicator selected"></div>
                                            <span>Seçili</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="seat-indicator occupied"></div>
                                            <span>Dolu</span>
                                        </div>
                                    </div>
                                    
                                    <div class="seat-grid" id="seatGrid">
                                        <!-- Seats will be generated by JavaScript -->
                                    </div>
                                    
                                    <div class="selected-seats-info mt-3">
                                        <h6>Seçilen Koltuklar:</h6>
                                        <div id="selectedSeatsList" class="selected-seats-list">
                                            <p class="text-muted">Henüz koltuk seçilmedi</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Ödeme Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-credit-card"></i> Ödeme Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Kupon Kodu (Opsiyonel)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="couponCode" placeholder="Kupon kodunu girin">
                            <button class="btn btn-outline-secondary" type="button" onclick="applyCoupon()">
                                Uygula
                            </button>
                        </div>
                        <div id="couponResult" class="mt-2" style="display: none;"></div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Bilet Fiyatı:</span>
                        <span id="originalPrice"><?php echo formatPrice($trip['price']); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2" id="discountRow" style="display: none;">
                        <span>İndirim:</span>
                        <span class="text-success" id="discountAmount">-0,00 ₺</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2" id="seatCountRow" style="display: none;">
                        <span>Koltuk Sayısı:</span>
                        <span id="seatCount">1</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <strong>Toplam:</strong>
                        <strong class="text-success" id="totalPrice"><?php echo formatPrice($trip['price']); ?></strong>
                    </div>
                </div>
            </div>
            
            <!-- Hesap Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-wallet2"></i> Hesap Bilgileri</h5>
                </div>
                <div class="card-body">
                    <?php
                    $user = getCurrentUser();
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Mevcut Bakiye:</span>
                        <span class="text-primary"><?php echo formatPrice($user['balance']); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <span>Kalan Bakiye:</span>
                        <span class="text-success" id="remainingBalance">
                            <?php echo formatPrice($user['balance'] - $trip['price']); ?>
                        </span>
                    </div>
                    
                    <?php if ($user['balance'] < $trip['price']): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            Yetersiz bakiye! Hesabınıza kredi eklemeniz gerekiyor.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Satın Alma Butonu -->
            <div class="card">
                <div class="card-body">
                    <button class="btn btn-success btn-lg w-100" id="purchaseBtn" onclick="purchaseTicket()" disabled>
                        <i class="bi bi-ticket"></i> Bilet Satın Al
                    </button>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i>
                            Güvenli ödeme sistemi
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<script>
let selectedSeats = [];
let tripPrice = <?php echo $trip ? $trip['price'] : 0; ?>;
let discountPercentage = 0;
let occupiedSeats = []; // This will be populated from the database

// Initialize seat layout
function initializeSeatLayout() {
    const seatGrid = document.getElementById('seatGrid');
    const totalSeats = <?php echo $trip ? $trip['capacity'] : 45; ?>;
    
    // Clear existing seats
    seatGrid.innerHTML = '';
    
    // Generate seats in a simple grid
    for (let i = 1; i <= totalSeats; i++) {
        const seat = document.createElement('div');
        seat.className = 'seat-item available';
        seat.textContent = i;
        seat.dataset.seatNumber = i;
        seat.onclick = () => toggleSeat(i);
        
        // Check if seat is occupied
        if (occupiedSeats.includes(i)) {
            seat.className = 'seat-item occupied';
            seat.onclick = null;
        }
        
        seatGrid.appendChild(seat);
    }
}

// Toggle seat selection
function toggleSeat(seatNumber) {
    const seatElement = document.querySelector(`[data-seat-number="${seatNumber}"]`);
    
    if (selectedSeats.includes(seatNumber)) {
        // Deselect seat
        selectedSeats = selectedSeats.filter(seat => seat !== seatNumber);
        seatElement.className = 'seat-item available';
    } else {
        // Select seat
        selectedSeats.push(seatNumber);
        seatElement.className = 'seat-item selected';
    }
    
    updateSelectedSeatsInfo();
}

// Update selected seats info
function updateSelectedSeatsInfo() {
    const listDiv = document.getElementById('selectedSeatsList');
    
    if (selectedSeats.length === 0) {
        listDiv.innerHTML = '<p class="text-muted">Henüz koltuk seçilmedi</p>';
    } else {
        listDiv.innerHTML = `
            <div class="selected-seats-list">
                ${selectedSeats.map(seat => `<span class="badge bg-primary me-1">${seat}</span>`).join('')}
            </div>
            <p class="mt-2"><strong>Toplam:</strong> ${selectedSeats.length} koltuk</p>
        `;
    }
    
    // Update main page seat info
    updateMainPageSeatInfo();
}

// Load occupied seats from server
function loadOccupiedSeats() {
    fetch(`/api/occupied-seats.php?trip_id=<?php echo $trip_id; ?>`)
        .then(response => response.json())
        .then(data => {
            occupiedSeats = data.occupied_seats || [];
            initializeSeatLayout();
        })
        .catch(error => {
            console.error('Error loading occupied seats:', error);
            initializeSeatLayout();
        });
}


// Update main page seat info
function updateMainPageSeatInfo() {
    const seatCountRow = document.getElementById('seatCountRow');
    const seatCount = document.getElementById('seatCount');
    const totalPrice = document.getElementById('totalPrice');
    const remainingBalance = document.getElementById('remainingBalance');
    const purchaseBtn = document.getElementById('purchaseBtn');
    
    if (selectedSeats.length > 0) {
        seatCountRow.style.display = 'flex';
        seatCount.textContent = selectedSeats.length;
        
        const totalAmount = tripPrice * selectedSeats.length * (1 - discountPercentage / 100);
        totalPrice.textContent = formatPrice(totalAmount);
        
        // Update remaining balance
        const userBalance = <?php echo $user ? $user['balance'] : 0; ?>;
        const newBalance = userBalance - totalAmount;
        remainingBalance.textContent = formatPrice(newBalance);
        
        // Enable purchase button if balance is sufficient
        if (newBalance >= 0) {
            purchaseBtn.disabled = false;
        } else {
            purchaseBtn.disabled = true;
        }
    } else {
        seatCountRow.style.display = 'none';
        purchaseBtn.disabled = true;
    }
}

// Apply coupon
function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value.trim();
    const resultDiv = document.getElementById('couponResult');
    
    if (!couponCode) {
        resultDiv.innerHTML = '<div class="alert alert-warning">Lütfen kupon kodunu girin.</div>';
        resultDiv.style.display = 'block';
        return;
    }
    
    fetch('/api/apply-coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            coupon_code: couponCode,
            trip_id: '<?php echo $trip_id; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            discountPercentage = data.discount_percentage;
            resultDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            
            // Show discount row
            document.getElementById('discountRow').style.display = 'flex';
            document.getElementById('discountAmount').textContent = `-${formatPrice(tripPrice * discountPercentage / 100)}`;
            
            updateMainPageSeatInfo();
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
        resultDiv.style.display = 'block';
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger">Kupon uygulanırken hata oluştu.</div>';
        resultDiv.style.display = 'block';
    });
}

// Purchase ticket
function purchaseTicket() {
    if (selectedSeats.length === 0) {
        alert('Lütfen koltuk seçin.');
        return;
    }
    
    const totalAmount = tripPrice * selectedSeats.length * (1 - discountPercentage / 100);
    const userBalance = <?php echo $user ? $user['balance'] : 0; ?>;
    
    if (userBalance < totalAmount) {
        alert('Yetersiz bakiye!');
        return;
    }
    
    if (confirm(`Bilet satın almak istediğinizden emin misiniz?\n\nSeçilen Koltuklar: ${selectedSeats.join(', ')}\nToplam Tutar: ${formatPrice(totalAmount)}`)) {
        // Submit purchase
        fetch('/api/purchase-ticket.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                trip_id: '<?php echo $trip_id; ?>',
                selected_seats: selectedSeats,
                coupon_code: document.getElementById('couponCode').value.trim() || null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Bilet başarıyla satın alındı!');
                window.location.href = '/tickets.php';
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Bilet satın alma işleminde hata oluştu.');
        });
    }
}

// Format price helper
function formatPrice(amount) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(amount);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadOccupiedSeats();
    updateMainPageSeatInfo();
});
</script>

<style>
/* Bus Sketch Styles */
.bus-sketch {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    overflow: hidden;
}

.sketch-header {
    background: #007bff;
    color: white;
    padding: 10px 15px;
    margin: 0;
}

.sketch-header h6 {
    margin: 0;
    font-size: 14px;
}

.sketch-body {
    padding: 15px;
}

.bus-outline-sketch {
    position: relative;
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border: 2px solid #6c757d;
    border-radius: 15px;
    padding: 20px;
    height: 300px;
}

.driver-sketch {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 30px;
    height: 30px;
    background: #6c757d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.seat-layout-sketch {
    display: flex;
    gap: 20px;
    height: 100%;
    padding-left: 50px;
    align-items: flex-start;
}

.left-column-sketch {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 30px;
}

.right-column-sketch {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 80px;
}

.seat-row-sketch {
    display: flex;
    gap: 8px;
}

.seat-sketch {
    width: 25px;
    height: 25px;
    border: 1px solid #6c757d;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    background: #fff;
    color: #495057;
}

.aisle-sketch {
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 15px;
    background: linear-gradient(90deg, transparent, #6c757d, transparent);
    transform: translateX(-50%);
    opacity: 0.3;
}

/* Seat Selection Styles */
.seat-selection {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    overflow: hidden;
}

.selection-header {
    background: #28a745;
    color: white;
    padding: 10px 15px;
    margin: 0;
}

.selection-header h6 {
    margin: 0;
    font-size: 14px;
}

.selection-body {
    padding: 15px;
}

.seat-status-legend {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.seat-indicator {
    width: 20px;
    height: 20px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.seat-indicator.available {
    background-color: #fff;
    border-color: #28a745;
}

.seat-indicator.selected {
    background-color: #007bff;
    border-color: #007bff;
}

.seat-indicator.occupied {
    background-color: #dc3545;
    border-color: #dc3545;
}

.seat-grid {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 8px;
    margin-bottom: 20px;
    max-height: 200px;
    overflow-y: auto;
    padding: 10px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.seat-item {
    width: 35px;
    height: 35px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 12px;
    background: #fff;
    color: #495057;
}

.seat-item.available:hover {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
    transform: scale(1.05);
}

.seat-item.selected {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
    transform: scale(1.1);
}

.seat-item.occupied {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
    cursor: not-allowed;
    opacity: 0.7;
}

.selected-seats-info h6 {
    font-size: 14px;
    margin-bottom: 10px;
    color: #495057;
}

.selected-seats-list {
    margin-bottom: 10px;
}

.selected-seats-list .badge {
    font-size: 12px;
    padding: 6px 10px;
}
</style>

<?php include 'includes/footer.php'; ?>
