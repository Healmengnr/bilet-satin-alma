<?php
require_once 'config/config.php';

// Sadece Admin ve Company Admin erişebilir
if (!isLoggedIn() || (!hasRole('admin') && !hasRole('company_admin'))) {
    redirect403();
}

$trip_id = $_GET['id'] ?? '';
$trip = null;
$error = '';

if (empty($trip_id)) {
    $error = 'Geçersiz sefer ID.';
} else {
    $pdo = getDBConnection();
    
    // Sefer bilgilerini al
    $stmt = $pdo->prepare("
        SELECT t.*, bc.name as company_name 
        FROM Trips t 
        LEFT JOIN Bus_Company bc ON t.company_id = bc.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        $error = 'Sefer bulunamadı.';
    }
}

$page_title = 'Sefer Detayları';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
            </div>
            <div class="text-center mt-4">
                <a href="/dashboard/" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Dashboard'a Dön
                </a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-bus-front"></i> Sefer Detayları
                    </h5>
                    <a href="/dashboard/" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-left"></i> Dashboard'a Dön
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Sefer Bilgileri</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Sefer ID:</strong></td>
                                    <td><?php echo escape($trip['id']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Firma:</strong></td>
                                    <td><?php echo escape($trip['company_name'] ?? 'Bilinmeyen'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Güzergah:</strong></td>
                                    <td>
                                        <strong><?php echo escape($trip['departure_city']); ?> → <?php echo escape($trip['destination_city']); ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Kalkış:</strong></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Varış:</strong></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($trip['arrival_time'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Fiyat:</strong></td>
                                    <td class="text-success fw-bold"><?php echo formatPrice($trip['price']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Kapasite:</strong></td>
                                    <td><?php echo $trip['capacity']; ?> koltuk</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Otobüs Bilgileri</h6>
                            <div class="text-center">
                                <?php
                                $bus_image = '';
                                switch($trip['capacity']) {
                                    case 25:
                                        $bus_image = '25-kisilik-otobus.png';
                                        break;
                                    case 35:
                                        $bus_image = '35-kisilik-otobus.png';
                                        break;
                                    case 41:
                                        $bus_image = '41-kisilik-otobus.png';
                                        break;
                                }
                                ?>
                                <?php if ($bus_image): ?>
                                    <img src="/assets/images/<?php echo $bus_image; ?>" alt="Otobüs" class="img-fluid mb-3" style="max-height: 200px;">
                                <?php endif; ?>
                                <p class="text-muted"><?php echo $trip['capacity']; ?> kişilik otobüs</p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary">Koltuk Durumu</h6>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                Bu sefer için toplam <?php echo $trip['capacity']; ?> koltuk bulunmaktadır.
                            </div>
                        </div>
                    </div>
                    
                    <?php if (hasRole('company_admin')): ?>
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary">Sefer Biletleri</h6>
                            <div id="ticketsList">
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Biletler yükleniyor...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (hasRole('company_admin')): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadTripTickets();
});

function loadTripTickets() {
    const tripId = '<?php echo $trip_id; ?>';
    
    fetch(`/api/trip-tickets.php?id=${tripId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTickets(data.tickets);
            } else {
                showTicketError(data.message || 'Biletler yüklenirken hata oluştu');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showTicketError('Bir hata oluştu. Lütfen tekrar deneyin.');
        });
}

function displayTickets(tickets) {
    const ticketsList = document.getElementById('ticketsList');
    
    if (tickets.length === 0) {
        ticketsList.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-ticket fs-1"></i>
                <p class="mt-2">Bu sefer için henüz bilet satın alınmamış.</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-striped">';
    html += `
        <thead>
            <tr>
                <th>Yolcu</th>
                <th>E-posta</th>
                <th>Koltuklar</th>
                <th>Fiyat</th>
                <th>Durum</th>
                <th>Satın Alma</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    tickets.forEach(ticket => {
        const statusBadge = ticket.status === 'active' ? 
            '<span class="badge bg-success">Aktif</span>' : 
            '<span class="badge bg-danger">İptal</span>';
        
        const cancelButton = ticket.status === 'active' ? 
            `<button class="btn btn-sm btn-outline-danger" onclick="cancelTicket('${ticket.id}')">
                <i class="bi bi-x-circle"></i> İptal Et
            </button>` : 
            '<span class="text-muted">İptal Edildi</span>';
        
        html += `
            <tr>
                <td><strong>${ticket.full_name}</strong></td>
                <td>${ticket.email}</td>
                <td>${ticket.seats.join(', ')}</td>
                <td class="text-success fw-bold">${ticket.total_price} TL</td>
                <td>${statusBadge}</td>
                <td>${new Date(ticket.created_at).toLocaleDateString('tr-TR')}</td>
                <td>${cancelButton}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    ticketsList.innerHTML = html;
}

function cancelTicket(ticketId) {
    if (!confirm('Bu bilet iptal edilecek. Emin misiniz?')) {
        return;
    }
    
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i> İptal Ediliyor...';
    button.disabled = true;
    
    fetch('/api/cancel-ticket-admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ticket_id=${ticketId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showTicketAlert('success', data.message);
            loadTripTickets(); // Listeyi yenile
        } else {
            showTicketAlert('danger', data.message || 'Bilet iptal edilirken hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showTicketAlert('danger', 'Bir hata oluştu. Lütfen tekrar deneyin.');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function showTicketError(message) {
    const ticketsList = document.getElementById('ticketsList');
    ticketsList.innerHTML = `
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> ${message}
        </div>
    `;
}

function showTicketAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const ticketsList = document.getElementById('ticketsList');
    ticketsList.parentNode.insertBefore(alertDiv, ticketsList);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
