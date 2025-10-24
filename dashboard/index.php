<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect403();
}

$user = getCurrentUser();
$page_title = 'Dashboard';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
            <div class="text-muted">
                Hoş geldin, <strong><?php echo escape($user['full_name']); ?></strong>
                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'company_admin' ? 'warning' : 'primary'); ?> ms-2">
                    <?php 
                    echo $user['role'] === 'admin' ? 'Sistem Admin' : 
                         ($user['role'] === 'company_admin' ? 'Firma Admin' : 'Kullanıcı'); 
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Admin Paneli -->
<?php if (hasRole('admin')): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Sistem Admin Paneli</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-building text-primary fs-1"></i>
                                <h6 class="mt-2">Otobüs Firmaları</h6>
                                <button class="btn btn-primary btn-sm" onclick="loadSection('companies')">
                                    <i class="bi bi-list"></i> Listele
                                </button>
                                <button class="btn btn-success btn-sm ms-1" onclick="showModal('company-new')">
                                    <i class="bi bi-plus"></i> Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-people text-warning fs-1"></i>
                                <h6 class="mt-2">Firma Adminleri</h6>
                                <button class="btn btn-primary btn-sm" onclick="loadSection('company-admins')">
                                    <i class="bi bi-list"></i> Listele
                                </button>
                                <button class="btn btn-success btn-sm ms-1" onclick="showModal('company-admin-new')">
                                    <i class="bi bi-plus"></i> Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-ticket-perforated text-info fs-1"></i>
                                <h6 class="mt-2">Sistem Kuponları</h6>
                                <button class="btn btn-primary btn-sm" onclick="loadSection('coupons')">
                                    <i class="bi bi-list"></i> Listele
                                </button>
                                <button class="btn btn-success btn-sm ms-1" onclick="showModal('coupon-new')">
                                    <i class="bi bi-plus"></i> Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-bus-front text-success fs-1"></i>
                                <h6 class="mt-2">Tüm Seferler</h6>
                                <button class="btn btn-primary btn-sm" onclick="loadSection('all-trips')">
                                    <i class="bi bi-list"></i> Listele
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Company Admin Paneli -->
<?php if (hasRole('company_admin')): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-building"></i> Firma Admin Paneli</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-bus-front text-primary fs-1"></i>
                                <h6 class="mt-2">Firma Seferleri</h6>
                                <button class="btn btn-primary btn-sm" onclick="loadSection('company-trips')">
                                    <i class="bi bi-list"></i> Listele
                                </button>
                                <button class="btn btn-success btn-sm ms-1" onclick="showModal('trip-new')">
                                    <i class="bi bi-plus"></i> Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-ticket-perforated text-info fs-1"></i>
                                <h6 class="mt-2">Firma Kuponları</h6>
                                <button class="btn btn-primary btn-sm" onclick="loadSection('company-coupons')">
                                    <i class="bi bi-list"></i> Listele
                                </button>
                                <button class="btn btn-success btn-sm ms-1" onclick="showModal('company-coupon-new')">
                                    <i class="bi bi-plus"></i> Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-people text-success fs-1"></i>
                                <h6 class="mt-2">Müşteriler</h6>
                                <button class="btn btn-primary btn-sm" onclick="loadSection('customers')">
                                    <i class="bi bi-list"></i> Listele
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- User Paneli -->
<?php if (hasRole('user')): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person"></i> Kullanıcı Paneli</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-ticket text-success fs-1"></i>
                                <h6 class="mt-2">Biletlerim</h6>
                                <a href="/tickets.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-list"></i> Görüntüle
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-search text-info fs-1"></i>
                                <h6 class="mt-2">Sefer Ara</h6>
                                <a href="/search.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i> Ara
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-person-circle text-warning fs-1"></i>
                                <h6 class="mt-2">Profil</h6>
                                <a href="/profile.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-gear"></i> Düzenle
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- İçerik Alanı -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0" id="section-title">Hoş Geldiniz</h5>
            </div>
            <div class="card-body" id="section-content">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-house-door fs-1"></i>
                    <p class="mt-3">Yukarıdaki butonlardan birini seçerek işlemlerinizi gerçekleştirebilirsiniz.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Container -->
<div class="modal fade" id="mainModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Modal içeriği buraya yüklenecek -->
            </div>
        </div>
    </div>
</div>

<script>
// AJAX ile bölüm yükleme
function loadSection(section) {
    const titleMap = {
        'companies': 'Otobüs Firmaları',
        'company-admins': 'Firma Adminleri',
        'coupons': 'Sistem Kuponları',
        'all-trips': 'Tüm Seferler',
        'company-trips': 'Firma Seferleri',
        'company-coupons': 'Firma Kuponları',
        'customers': 'Müşteriler'
    };
    
    document.getElementById('section-title').textContent = titleMap[section] || 'İçerik';
    document.getElementById('section-content').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-2">Yükleniyor...</p></div>';
    
    fetch(`/dashboard/sections/${section}.php`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('section-content').innerHTML = data;
            
            if (section === 'customers') {
                loadCustomers();
            }
        })
        .catch(error => {
            document.getElementById('section-content').innerHTML = '<div class="alert alert-danger">Hata: ' + error.message + '</div>';
        });
}

// Modal gösterme
function showModal(modalType, id = '') {
    
    const titleMap = {
        'company-new': 'Yeni Firma',
        'company-edit': 'Firma Düzenle',
        'company-admin-new': 'Yeni Firma Admini',
        'company-admin-edit': 'Firma Admini Düzenle',
        'coupon-new': 'Yeni Sistem Kuponu',
        'coupon-edit': 'Sistem Kuponu Düzenle',
        'company-coupon-new': 'Yeni Firma Kuponu',
        'company-coupon-edit': 'Firma Kuponu Düzenle',
        'trip-new': 'Yeni Sefer',
        'trip-edit': 'Sefer Düzenle'
    };
    
    document.getElementById('modalTitle').textContent = titleMap[modalType] || 'Modal';
    document.getElementById('modalBody').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-2">Yükleniyor...</p></div>';
    
    const modal = new bootstrap.Modal(document.getElementById('mainModal'));
    modal.show();
    
    const url = id ? `/dashboard/modals/${modalType}.php?id=${id}` : `/dashboard/modals/${modalType}.php`;
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(data => {
            document.getElementById('modalBody').innerHTML = data;
            
            // Form submit event listener'ları ekle
            const forms = document.querySelectorAll('#modalBody form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitForm(this.id);
                });
                
                // Button click event listener'ı da ekle
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const formId = form.getAttribute('id');
                        submitForm(formId);
                    });
                }
            });
        })
        .catch(error => {
            document.getElementById('modalBody').innerHTML = '<div class="alert alert-danger">Hata: ' + error.message + '</div>';
        });
}

// Form gönderme
function submitForm(formId) {
    const form = document.getElementById(formId);
    
    if (!form) {
        return;
    }
    
    const formData = new FormData(form);
    
    // Form tipine göre endpoint belirle
    let endpoint = '';
    if (formId === 'companyAdminNewForm' || formId === 'companyAdminEditForm') {
        endpoint = '/dashboard/actions/company-admin.php';
    } else if (formId === 'couponNewForm' || formId === 'couponEditForm') {
        endpoint = '/dashboard/actions/coupon.php';
    } else if (formId === 'companyCouponNewForm' || formId === 'companyCouponEditForm') {
        endpoint = '/dashboard/actions/company-coupon.php';
    } else if (formId === 'tripNewForm' || formId === 'tripEditForm') {
        endpoint = '/dashboard/actions/trip.php';
    } else if (formId.includes('company')) {
        endpoint = '/dashboard/actions/company.php';
    } else if (formId.includes('coupon')) {
        endpoint = '/dashboard/actions/coupon.php';
    } else if (formId.includes('trip')) {
        endpoint = '/dashboard/actions/trip.php';
    }
    
    // Loading göster
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> İşleniyor...';
    submitBtn.disabled = true;
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('mainModal')).hide();
            showAlert('success', data.message);
            // İlgili bölümü yeniden yükle
            if (data.reload) {
                setTimeout(() => {
                    loadSection(data.reload);
                }, 1000);
            }
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Hata: ' + error.message);
    })
    .finally(() => {
        // Loading'i kaldır
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Silme işlemi
function deleteItem(type, id) {
    if (confirm('Bu öğeyi silmek istediğinizden emin misiniz?')) {
        // Loading göster
        const deleteBtn = event.target;
        const originalHTML = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        deleteBtn.disabled = true;
        
        fetch(`/dashboard/actions/${type}-delete.php?id=${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => {
                    // Silme tipine göre ilgili bölümü yeniden yükle
                    if (type === 'company') {
                        loadSection('companies');
                    } else if (type === 'company-admin') {
                        loadSection('company-admins');
                    } else if (type === 'coupon') {
                        loadSection('coupons');
                    } else if (type === 'company-coupon') {
                        loadSection('company-coupons');
                    } else if (type === 'trip') {
                        loadSection('company-trips');
                    }
                }, 1000);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Hata: ' + error.message);
        })
        .finally(() => {
            // Loading'i kaldır
            deleteBtn.innerHTML = originalHTML;
            deleteBtn.disabled = false;
        });
    }
}

// Alert gösterme
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
    
    const icon = type === 'success' ? 'check-circle-fill' : 
                 type === 'danger' ? 'exclamation-triangle-fill' : 
                 type === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill';
    
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${icon} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // 4 saniye sonra otomatik kapat
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 4000);
}

// Müşteriler fonksiyonları
function loadCustomers() {
    const content = document.getElementById('customers-content');
    if (!content) return;
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <p class="mt-2">Müşteriler yükleniyor...</p>
        </div>
    `;
    
    fetch('/api/customers.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCustomers(data.data);
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Müşteriler yüklenirken hata oluştu.
                </div>
            `;
        });
}

function displayCustomers(customers) {
    const content = document.getElementById('customers-content');
    if (!content) return;
    
    if (customers.length === 0) {
        content.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Henüz müşteri bulunmuyor.
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Müşteri Adı</th>
                        <th>E-posta</th>
                        <th>Toplam Bilet</th>
                        <th>Toplam Harcama</th>
                        <th>Son Satın Alma</th>
                        <th>Rotalar</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    customers.forEach(customer => {
        const lastPurchase = new Date(customer.last_purchase).toLocaleDateString('tr-TR');
        const totalSpent = parseFloat(customer.total_spent).toFixed(2);
        const routes = customer.routes ? customer.routes.split(',').join('<br>') : '-';
        
        html += `
            <tr>
                <td>
                    <strong>${escapeHtml(customer.full_name)}</strong>
                </td>
                <td>${escapeHtml(customer.email)}</td>
                <td>
                    <span class="badge bg-primary">${customer.total_tickets}</span>
                </td>
                <td>
                    <strong class="text-success">₺${totalSpent}</strong>
                </td>
                <td>${lastPurchase}</td>
                <td><small>${routes}</small></td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewCustomerTickets('${customer.id}', '${escapeHtml(customer.full_name)}')">
                        <i class="bi bi-ticket-perforated"></i> Biletleri Gör
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    content.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Müşteri biletlerini görüntüleme
function viewCustomerTickets(customerId, customerName) {
    // Modal başlığını güncelle
    document.getElementById('modalTitle').innerHTML = `<i class="bi bi-ticket-perforated"></i> ${customerName} - Biletleri`;
    
    // Modal içeriğini yükleme durumuna getir
    document.getElementById('modalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <p class="mt-2">Biletler yükleniyor...</p>
        </div>
    `;
    
    // Modal'ı göster
    const modal = new bootstrap.Modal(document.getElementById('mainModal'));
    modal.show();
    
    // API'den biletleri çek
    fetch(`/api/customer-tickets.php?customer_id=${customerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCustomerTickets(data.data, customerName);
            } else {
                document.getElementById('modalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Biletler yüklenirken hata oluştu.
                </div>
            `;
        });
}

function displayCustomerTickets(tickets, customerName) {
    const modalBody = document.getElementById('modalBody');
    
    if (tickets.length === 0) {
        modalBody.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Bu müşterinin henüz bilet alımı bulunmuyor.
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Bilet ID</th>
                        <th>Rota</th>
                        <th>Kalkış Tarihi</th>
                        <th>Varış Tarihi</th>
                        <th>Fiyat</th>
                        <th>Durum</th>
                        <th>Koltuklar</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    tickets.forEach(ticket => {
        const departureDate = new Date(ticket.departure_time).toLocaleDateString('tr-TR');
        const arrivalDate = new Date(ticket.arrival_time).toLocaleDateString('tr-TR');
        const statusBadge = ticket.status === 'active' ? 'bg-success' : 
                           ticket.status === 'cancelled' ? 'bg-danger' : 'bg-warning';
        const statusText = ticket.status === 'active' ? 'Aktif' : 
                          ticket.status === 'cancelled' ? 'İptal' : 'Süresi Dolmuş';
        const seats = ticket.seats ? ticket.seats.join(', ') : '-';
        
        html += `
            <tr>
                <td><code>${ticket.id.substring(0, 8)}...</code></td>
                <td>${escapeHtml(ticket.departure_city)} → ${escapeHtml(ticket.destination_city)}</td>
                <td>${departureDate}</td>
                <td>${arrivalDate}</td>
                <td><strong class="text-success">₺${ticket.total_price}</strong></td>
                <td><span class="badge ${statusBadge}">${statusText}</span></td>
                <td><span class="badge bg-info">${seats}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="viewTicketDetails('${ticket.id}')">
                        <i class="bi bi-eye"></i> Detay
                    </button>
                    ${ticket.status === 'active' ? `
                        <button class="btn btn-sm btn-danger ms-1" onclick="cancelCustomerTicket('${ticket.id}')">
                            <i class="bi bi-x-circle"></i> İptal
                        </button>
                    ` : ''}
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    modalBody.innerHTML = html;
}

// Bilet detaylarını görüntüleme (download-ticket.php gibi)
function viewTicketDetails(ticketId) {
    // Yeni sekmede bilet detaylarını aç
    window.open(`/api/download-ticket.php?id=${ticketId}`, '_blank');
}

// Müşteri biletini iptal etme
function cancelCustomerTicket(ticketId) {
    if (confirm('Bu bilet iptal edilecek ve ücret müşteriye iade edilecek. Devam etmek istediğinizden emin misiniz?')) {
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
                showAlert('success', data.message);
                // Modal'ı kapat ve müşterileri yenile
                bootstrap.Modal.getInstance(document.getElementById('mainModal')).hide();
                loadCustomers();
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Hata: ' + error.message);
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
