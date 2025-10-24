<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || !hasRole('company_admin')) {
    http_response_code(403);
    exit('Yetkisiz erişim');
}

$pdo = getDBConnection();
$user = getCurrentUser();
?>

<form id="companyCouponNewForm" action="javascript:void(0)">
    <div class="modal-body">
        <div class="mb-3">
            <label for="code" class="form-label">Kupon Kodu <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="code" name="code" required 
                   placeholder="Örn: FIRMA10" maxlength="20">
            <div class="form-text">Benzersiz bir kupon kodu girin</div>
        </div>
        
        <div class="mb-3">
            <label for="discount" class="form-label">İndirim Oranı (%) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="discount" name="discount" required 
                   min="1" max="100" placeholder="10">
            <div class="form-text">1-100 arası bir değer girin</div>
        </div>
        
        <div class="mb-3">
            <label for="usage_limit" class="form-label">Kullanım Limiti <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="usage_limit" name="usage_limit" required 
                   min="1" placeholder="100">
            <div class="form-text">Kaç kez kullanılabileceği</div>
        </div>
        
        <div class="mb-3">
            <label for="expire_date" class="form-label">Son Kullanma Tarihi <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control" id="expire_date" name="expire_date" required>
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="active" checked>
                <label class="form-check-label" for="status">
                    Aktif
                </label>
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check"></i> Kupon Oluştur
        </button>
    </div>
</form>
