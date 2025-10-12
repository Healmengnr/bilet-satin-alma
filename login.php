<?php
require_once 'config/config.php';

// Eğer zaten giriş yapmışsa ana sayfaya yönlendir
if (isLoggedIn()) {
    redirect('/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'E-posta ve şifre gereklidir.';
    } else {
        if (loginUser($email, $password)) {
            setFlashMessage('success', 'Başarıyla giriş yaptınız!');
            redirect('/');
        } else {
            $error = 'E-posta veya şifre hatalı.';
        }
    }
}

$page_title = 'Giriş Yap';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header text-center">
                <h4 class="mb-0">
                    <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                </h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo escape($_POST['email'] ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            Lütfen e-posta adresinizi girin.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">
                            Lütfen şifrenizi girin.
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Hesabınız yok mu?</p>
                    <a href="/register.php" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="bi bi-person-plus"></i> Kayıt Ol
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Demo Accounts -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Demo Hesaplar</h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <strong>Sistem Admin:</strong> admin@biletotomasyonu.com / admin123<br>
                    <strong>Normal Kullanıcı:</strong> ahmet@example.com / user123<br>
                    <strong>Firma Admin:</strong> admin@metroturizm.com / admin123
                </small>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
