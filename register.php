<?php
require_once 'config/config.php';

// Eğer zaten giriş yapmışsa ana sayfaya yönlendir
if (isLoggedIn()) {
    redirect('/');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    
    // Validasyon
    if (empty($email) || empty($password) || empty($full_name)) {
        $error = 'Tüm zorunlu alanları doldurun.';
    } elseif (!validateEmail($email)) {
        $error = 'Geçerli bir e-posta adresi girin.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Şifre en az ' . PASSWORD_MIN_LENGTH . ' karakter olmalıdır.';
    } elseif ($password !== $password_confirm) {
        $error = 'Şifreler eşleşmiyor.';
    } else {
        // Kullanıcı kaydı
        $userData = [
            'email' => $email,
            'password' => $password,
            'full_name' => $full_name,
            'role' => 'user'
        ];
        
        if (registerUser($userData)) {
            setFlashMessage('success', 'Kayıt başarılı! Giriş yapabilirsiniz.');
            redirect('/login.php');
        } else {
            $error = 'Bu e-posta zaten kullanılıyor.';
        }
    }
}

$page_title = 'Kayıt Ol';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-header text-center">
                <h4 class="mb-0">
                    <i class="bi bi-person-plus"></i> Kayıt Ol
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
                        <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo escape($_POST['email'] ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            Lütfen geçerli bir e-posta adresi girin.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo escape($_POST['full_name'] ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            Lütfen adınızı ve soyadınızı girin.
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Şifre <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                            <div class="invalid-feedback">
                                Şifre en az <?php echo PASSWORD_MIN_LENGTH; ?> karakter olmalıdır.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirm" class="form-label">Şifre Tekrar <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            <div class="invalid-feedback">
                                Şifreler eşleşmelidir.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Kullanım şartlarını</a> kabul ediyorum.
                            </label>
                            <div class="invalid-feedback">
                                Kullanım şartlarını kabul etmelisiniz.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Kayıt Ol
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Zaten hesabınız var mı?</p>
                    <a href="/login.php" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kullanım Şartları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Genel Hükümler</h6>
                <p>Bu platform otobüs bileti satışı için kullanılmaktadır. Platformu kullanarak bu şartları kabul etmiş sayılırsınız.</p>
                
                <h6>2. Hesap Güvenliği</h6>
                <p>Hesap bilgilerinizi güvenli tutmak sizin sorumluluğunuzdadır. Şifrenizi kimseyle paylaşmayın.</p>
                
                <h6>3. Bilet İptali</h6>
                <p>Biletler sefer saatinden 1 saat öncesine kadar iptal edilebilir. İptal edilen biletlerin ücreti hesabınıza iade edilir.</p>
                
                <h6>4. Ödeme</h6>
                <p>Bilet satın alma işlemleri sanal kredi sistemi üzerinden yapılmaktadır.</p>
                
                <h6>5. Değişiklik Hakkı</h6>
                <p>Bu şartlar önceden haber verilmeksizin değiştirilebilir.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
// Şifre eşleşme kontrolü
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Şifreler eşleşmiyor');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
