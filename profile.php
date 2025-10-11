<?php
require_once 'config/config.php';
requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $pdo = getDBConnection();
    
    // Profil bilgilerini güncelle
    if (!empty($full_name)) {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$full_name, $phone, $user['id']])) {
            $_SESSION['full_name'] = $full_name;
            $success = 'Profil bilgileri güncellendi.';
        } else {
            $error = 'Profil bilgileri güncellenirken hata oluştu.';
        }
    }
    
    // Şifre değiştirme
    if (!empty($current_password) && !empty($new_password)) {
        if (!verifyPassword($current_password, $user['password'])) {
            $error = 'Mevcut şifre hatalı.';
        } elseif (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            $error = 'Yeni şifre en az ' . PASSWORD_MIN_LENGTH . ' karakter olmalıdır.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Yeni şifreler eşleşmiyor.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([hashPassword($new_password), $user['id']])) {
                $success = 'Şifre başarıyla değiştirildi.';
            } else {
                $error = 'Şifre değiştirilirken hata oluştu.';
            }
        }
    }
    
    // Kullanıcı bilgilerini yeniden al
    $user = getCurrentUser();
}

$page_title = 'Hesabım';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <!-- User Info Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Hesap Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="bi bi-person-circle" style="font-size: 4rem; color: #6c757d;"></i>
                </div>
                
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Kullanıcı Adı:</strong></td>
                        <td><?php echo escape($user['username']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>E-posta:</strong></td>
                        <td><?php echo escape($user['email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Ad Soyad:</strong></td>
                        <td><?php echo escape($user['full_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Telefon:</strong></td>
                        <td><?php echo escape($user['phone'] ?: 'Belirtilmemiş'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Rol:</strong></td>
                        <td>
                            <?php
                            $roleNames = [
                                'admin' => 'Admin',
                                'firma_admin' => 'Firma Admin',
                                'user' => 'Kullanıcı'
                            ];
                            echo $roleNames[$user['role']] ?? $user['role'];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Kayıt Tarihi:</strong></td>
                        <td><?php echo formatDate($user['created_at']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Credit Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-wallet2"></i> Hesap Kredisi</h5>
            </div>
            <div class="card-body text-center">
                <h3 class="text-success"><?php echo formatPrice($user['credit']); ?></h3>
                <p class="text-muted mb-0">Kullanılabilir kredi</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Profile Update Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Profil Güncelle</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo escape($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo escape($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Telefon</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo escape($user['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Profil Bilgilerini Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Password Change Form -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key"></i> Şifre Değiştir</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mevcut Şifre</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_password" class="form-label">Yeni Şifre</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Yeni Şifre Tekrar</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> Şifre Değiştir
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Hızlı İşlemler</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <a href="/tickets.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-ticket"></i> Biletlerim
                        </a>
                    </div>
                    <div class="col-md-6 mb-2">
                        <a href="/search.php" class="btn btn-outline-success w-100">
                            <i class="bi bi-search"></i> Sefer Ara
                        </a>
                    </div>
                </div>
                
                <?php if (hasRole('firma_admin') || hasRole('admin')): ?>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="/firma-admin/" class="btn btn-outline-info w-100">
                                <i class="bi bi-building"></i> Firma Paneli
                            </a>
                        </div>
                        <?php if (hasRole('admin')): ?>
                            <div class="col-md-6 mb-2">
                                <a href="/admin/" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-shield-check"></i> Admin Paneli
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Şifre eşleşme kontrolü
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity('Şifreler eşleşmiyor');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
