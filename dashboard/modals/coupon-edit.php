<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    exit('Yetkisiz erişim');
}

$pdo = getDBConnection();
$coupon_id = $_GET['id'] ?? '';
$coupon = null;

if ($coupon_id) {
    $stmt = $pdo->prepare("SELECT * FROM Coupons WHERE id = ? AND company_id IS NULL");
    $stmt->execute([$coupon_id]);
    $coupon = $stmt->fetch();
    
    if (!$coupon) {
        http_response_code(404);
        exit('Kupon bulunamadı');
    }
}
?>

<div class="modal-header">
    <h5 class="modal-title"><?php echo $coupon ? 'Kupon Düzenle' : 'Yeni Sistem Kuponu'; ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="couponEditForm" action="javascript:void(0)">
    <input type="hidden" name="id" value="<?php echo $coupon_id; ?>">
    
    <div class="modal-body">
        <div class="mb-3">
            <label for="code" class="form-label">Kupon Kodu <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="code" name="code" required 
                   value="<?php echo escape($coupon['code'] ?? ''); ?>"
                   placeholder="Örn: YENI10" maxlength="20">
            <div class="form-text">Benzersiz bir kupon kodu girin</div>
        </div>
        
        <div class="mb-3">
            <label for="discount" class="form-label">İndirim Oranı (%) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="discount" name="discount" required 
                   value="<?php echo $coupon['discount'] ?? ''; ?>"
                   min="1" max="100" placeholder="10">
            <div class="form-text">1-100 arası bir değer girin</div>
        </div>
        
        <div class="mb-3">
            <label for="usage_limit" class="form-label">Kullanım Limiti <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="usage_limit" name="usage_limit" required 
                   value="<?php echo $coupon['usage_limit'] ?? ''; ?>"
                   min="1" placeholder="100">
            <div class="form-text">Kaç kez kullanılabileceği</div>
        </div>
        
        <div class="mb-3">
            <label for="expire_date" class="form-label">Son Kullanma Tarihi <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control" id="expire_date" name="expire_date" required
                   value="<?php echo $coupon ? date('Y-m-d\TH:i', strtotime($coupon['expire_date'])) : ''; ?>">
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="active" 
                       <?php echo (!$coupon || $coupon['status'] === 'active') ? 'checked' : ''; ?>>
                <label class="form-check-label" for="status">
                    Aktif
                </label>
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check"></i> <?php echo $coupon ? 'Güncelle' : 'Kupon Oluştur'; ?>
        </button>
    </div>
</form>
