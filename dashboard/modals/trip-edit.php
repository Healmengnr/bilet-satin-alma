<?php
require_once '../../config/config.php';

if (!isLoggedIn() || !hasRole('company_admin')) {
    http_response_code(403);
    exit('Yetkisiz erişim');
}

$pdo = getDBConnection();
$user = getCurrentUser();
$trip_id = $_GET['id'] ?? '';
$trip = null;

if ($trip_id) {
    $stmt = $pdo->prepare("SELECT * FROM Trips WHERE id = ? AND company_id = ?");
    $stmt->execute([$trip_id, $user['company_id']]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        http_response_code(404);
        exit('Sefer bulunamadı');
    }
}

// Şehir listesi
$cities = ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya', 'Adana', 'Konya', 'Gaziantep', 'Mersin', 'Diyarbakır', 'Kayseri', 'Eskişehir', 'Urfa', 'Malatya', 'Erzurum', 'Van', 'Batman', 'Elazığ', 'Isparta', 'Trabzon'];
?>

<form id="tripEditForm" action="javascript:void(0)">
    <input type="hidden" name="id" value="<?php echo $trip_id; ?>">
    
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="departure_city" class="form-label">Kalkış Şehri <span class="text-danger">*</span></label>
                <select class="form-select" id="departure_city" name="departure_city" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo $city; ?>" <?php echo ($trip && $trip['departure_city'] === $city) ? 'selected' : ''; ?>>
                            <?php echo $city; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="destination_city" class="form-label">Varış Şehri <span class="text-danger">*</span></label>
                <select class="form-select" id="destination_city" name="destination_city" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo $city; ?>" <?php echo ($trip && $trip['destination_city'] === $city) ? 'selected' : ''; ?>>
                            <?php echo $city; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="departure_time" class="form-label">Kalkış Saati <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" required
                       value="<?php echo $trip ? date('Y-m-d\TH:i', strtotime($trip['departure_time'])) : ''; ?>">
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="arrival_time" class="form-label">Varış Saati <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="arrival_time" name="arrival_time" required
                       value="<?php echo $trip ? date('Y-m-d\TH:i', strtotime($trip['arrival_time'])) : ''; ?>">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="price" class="form-label">Fiyat (TL) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="price" name="price" required 
                       value="<?php echo $trip['price'] ?? ''; ?>"
                       min="1" placeholder="100">
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="capacity" class="form-label">Kapasite <span class="text-danger">*</span></label>
                <select class="form-select" id="capacity" name="capacity" required>
                    <option value="">Seçiniz</option>
                    <option value="25" <?php echo ($trip && $trip['capacity'] == 25) ? 'selected' : ''; ?>>25 Koltuk</option>
                    <option value="35" <?php echo ($trip && $trip['capacity'] == 35) ? 'selected' : ''; ?>>35 Koltuk</option>
                    <option value="41" <?php echo ($trip && $trip['capacity'] == 41) ? 'selected' : ''; ?>>41 Koltuk</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check"></i> <?php echo $trip ? 'Güncelle' : 'Sefer Oluştur'; ?>
        </button>
    </div>
</form>

<script>
// Kalkış ve varış saatlerini kontrol et
document.getElementById('departure_time').addEventListener('change', function() {
    const departureTime = new Date(this.value);
    const arrivalInput = document.getElementById('arrival_time');
    
    // Geçmiş zaman kontrolü
    const now = new Date();
    if (departureTime <= now) {
        alert('Kalkış saati geçmiş bir tarih olamaz!');
        this.value = '';
        return;
    }
    
    if (departureTime && arrivalInput.value) {
        const arrivalTime = new Date(arrivalInput.value);
        if (arrivalTime <= departureTime) {
            alert('Varış saati kalkış saatinden sonra olmalıdır!');
            arrivalInput.value = '';
        }
    }
});

document.getElementById('arrival_time').addEventListener('change', function() {
    const arrivalTime = new Date(this.value);
    const departureInput = document.getElementById('departure_time');
    
    // Geçmiş zaman kontrolü
    const now = new Date();
    if (arrivalTime <= now) {
        alert('Varış saati geçmiş bir tarih olamaz!');
        this.value = '';
        return;
    }
    
    if (arrivalTime && departureInput.value) {
        const departureTime = new Date(departureInput.value);
        if (arrivalTime <= departureTime) {
            alert('Varış saati kalkış saatinden sonra olmalıdır!');
            this.value = '';
        }
    }
});

// Sayfa yüklendiğinde minimum tarihi bugün olarak ayarla
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const minDateTime = now.toISOString().slice(0, 16); // YYYY-MM-DDTHH:MM formatı
    
    document.getElementById('departure_time').min = minDateTime;
    document.getElementById('arrival_time').min = minDateTime;
});
</script>
