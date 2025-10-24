<?php
require_once 'config/config.php';

// Sadece User rolündeki kullanıcılar biletlerini görebilir
if (!isLoggedIn() || !hasRole('user')) {
    redirect403();
}

$pdo = getDBConnection();
$user = getCurrentUser();

// Get user's tickets
$stmt = $pdo->prepare("
    SELECT t.*, tr.departure_city, tr.destination_city, tr.departure_time, tr.arrival_time, 
           bc.name as company_name
    FROM Tickets t
    JOIN Trips tr ON t.trip_id = tr.id
    JOIN Bus_Company bc ON tr.company_id = bc.id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$user['id']]);
$tickets = $stmt->fetchAll();

$page_title = 'Biletlerim';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-ticket"></i> Biletlerim</h2>
            <a href="/search.php" class="btn btn-primary">
                <i class="bi bi-plus"></i> Yeni Bilet Al
            </a>
        </div>
        
        <?php if (empty($tickets)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-ticket" style="font-size: 4rem; color: #6c757d;"></i>
                    <h4 class="mt-3">Henüz biletiniz yok</h4>
                    <p class="text-muted">İlk biletinizi satın almak için sefer arama sayfasını ziyaret edin.</p>
                    <a href="/search.php" class="btn btn-primary">
                        <i class="bi bi-search"></i> Sefer Ara
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Bilet #<?php echo substr($ticket['id'], 0, 8); ?></h6>
                                <span class="badge bg-<?php echo $ticket['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $ticket['status'] === 'active' ? 'Aktif' : 'İptal'; ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="trip-info mb-3">
                                    <h6 class="text-primary">
                                        <?php echo escape($ticket['departure_city']); ?> → 
                                        <?php echo escape($ticket['destination_city']); ?>
                                    </h6>
                                    <p class="text-muted mb-1"><?php echo escape($ticket['company_name']); ?></p>
                                </div>
                                
                                <div class="trip-details">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Tarih</small>
                                            <p class="mb-1"><?php echo formatDate($ticket['departure_time']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Saat</small>
                                            <p class="mb-1"><?php echo formatTime($ticket['departure_time']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Varış</small>
                                            <p class="mb-1"><?php echo formatTime($ticket['arrival_time']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Fiyat</small>
                                            <p class="mb-1"><?php echo formatPrice($ticket['total_price']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Booked Seats -->
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT seat_number FROM Booked_Seats 
                                    WHERE ticket_id = ? 
                                    ORDER BY seat_number
                                ");
                                $stmt->execute([$ticket['id']]);
                                $seats = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                ?>
                                
                                <div class="mt-3">
                                    <small class="text-muted">Koltuklar</small>
                                    <div class="seat-numbers">
                                        <?php foreach ($seats as $seat): ?>
                                            <span class="badge bg-primary me-1"><?php echo $seat; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-grid gap-2">
                                    <?php if ($ticket['status'] === 'active'): ?>
                                        <!-- Check if ticket can be cancelled -->
                                        <?php
                                        $departureDateTime = $ticket['departure_time'];
                                        $departureTimestamp = strtotime($departureDateTime);
                                        $currentTimestamp = time();
                                        $timeDifference = $departureTimestamp - $currentTimestamp;
                                        $canCancel = $timeDifference > (CANCELLATION_HOURS * 3600);
                                        ?>
                                        
                                        <?php if ($canCancel): ?>
                                            <button class="btn btn-outline-danger btn-sm" 
                                                    onclick="cancelTicket('<?php echo $ticket['id']; ?>')">
                                                <i class="bi bi-x-circle"></i> İptal Et
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                                <i class="bi bi-clock"></i> İptal Süresi Doldu
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="downloadTicket('<?php echo $ticket['id']; ?>')">
                                            <i class="bi bi-download"></i> PDF İndir
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="bi bi-check-circle"></i> İptal Edildi
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cancel Ticket Modal -->
<div class="modal fade" id="cancelTicketModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bilet İptali</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bu bileti iptal etmek istediğinizden emin misiniz?</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    İptal edilen biletin ücreti hesabınıza iade edilecektir.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hayır</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">Evet, İptal Et</button>
            </div>
        </div>
    </div>
</div>

<script>
function cancelTicket(ticketId) {
    const modal = new bootstrap.Modal(document.getElementById('cancelTicketModal'));
    modal.show();
    
    document.getElementById('confirmCancelBtn').onclick = function() {
        fetch('/api/cancel-ticket.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                ticket_id: ticketId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Bilet başarıyla iptal edildi!');
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Bilet iptal işleminde hata oluştu.');
        });
        
        modal.hide();
    };
}

function downloadTicket(ticketId) {
    window.open('/api/download-ticket.php?id=' + ticketId, '_blank');
}
</script>

<?php include 'includes/footer.php'; ?>
