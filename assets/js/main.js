// Main JavaScript for Bilet Otomasyonu

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Seat selection functionality
    initializeSeatSelection();
    
    // Date picker initialization
    initializeDatePickers();
    
    // Coupon code validation
    initializeCouponValidation();
});

// Seat Selection Functions
function initializeSeatSelection() {
    const seats = document.querySelectorAll('.seat.available');
    const selectedSeatInput = document.getElementById('selected_seat');
    
    seats.forEach(seat => {
        seat.addEventListener('click', function() {
            // Remove previous selection
            seats.forEach(s => s.classList.remove('selected'));
            
            // Add selection to clicked seat
            this.classList.add('selected');
            
            // Update hidden input
            if (selectedSeatInput) {
                selectedSeatInput.value = this.dataset.seat;
            }
            
            // Update seat info
            updateSeatInfo(this.dataset.seat);
        });
    });
}

function updateSeatInfo(seatNumber) {
    const seatInfo = document.getElementById('seat-info');
    if (seatInfo) {
        seatInfo.textContent = `Seçilen Koltuk: ${seatNumber}`;
        seatInfo.style.display = 'block';
    }
}

// Date Picker Functions
function initializeDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(input => {
        // Set minimum date to today
        input.min = today;
        
        // For departure date, set maximum to 1 year from now
        if (input.id === 'departure_date') {
            const maxDate = new Date();
            maxDate.setFullYear(maxDate.getFullYear() + 1);
            input.max = maxDate.toISOString().split('T')[0];
        }
    });
}

// Coupon Code Functions
function initializeCouponValidation() {
    const couponInput = document.getElementById('coupon_code');
    const applyCouponBtn = document.getElementById('apply_coupon');
    const couponResult = document.getElementById('coupon_result');
    
    if (couponInput && applyCouponBtn) {
        applyCouponBtn.addEventListener('click', function() {
            const couponCode = couponInput.value.trim();
            
            if (!couponCode) {
                showCouponResult('Lütfen kupon kodunu girin.', 'warning');
                return;
            }
            
            // Show loading
            applyCouponBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Kontrol ediliyor...';
            applyCouponBtn.disabled = true;
            
            // Simulate API call (replace with actual AJAX call)
            setTimeout(() => {
                validateCouponCode(couponCode);
                applyCouponBtn.innerHTML = 'Kupon Uygula';
                applyCouponBtn.disabled = false;
            }, 1000);
        });
    }
}

function validateCouponCode(couponCode) {
    // This would be replaced with actual AJAX call to server
    const couponResult = document.getElementById('coupon_result');
    
    // Simulate validation
    if (couponCode === 'INDIRIM10') {
        showCouponResult('Kupon kodu başarıyla uygulandı! %10 indirim kazandınız.', 'success');
        updatePrice(0.9); // 10% discount
    } else if (couponCode === 'INDIRIM20') {
        showCouponResult('Kupon kodu başarıyla uygulandı! %20 indirim kazandınız.', 'success');
        updatePrice(0.8); // 20% discount
    } else {
        showCouponResult('Geçersiz kupon kodu. Lütfen tekrar deneyin.', 'danger');
    }
}

function showCouponResult(message, type) {
    const couponResult = document.getElementById('coupon_result');
    if (couponResult) {
        couponResult.className = `alert alert-${type} mt-2`;
        couponResult.textContent = message;
        couponResult.style.display = 'block';
    }
}

function updatePrice(discountMultiplier) {
    const originalPriceElement = document.getElementById('original_price');
    const finalPriceElement = document.getElementById('final_price');
    
    if (originalPriceElement && finalPriceElement) {
        const originalPrice = parseFloat(originalPriceElement.textContent.replace(/[^\d.,]/g, '').replace(',', '.'));
        const finalPrice = originalPrice * discountMultiplier;
        
        finalPriceElement.textContent = finalPrice.toFixed(2).replace('.', ',') + ' ₺';
        finalPriceElement.style.color = '#198754';
        finalPriceElement.style.fontWeight = 'bold';
    }
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    return timeString.substring(0, 5); // HH:MM format
}

// AJAX Helper Functions
function makeRequest(url, method = 'GET', data = null) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data ? JSON.stringify(data) : null
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Request failed:', error);
        throw error;
    });
}

// Loading States
function showLoading(element) {
    if (element) {
        element.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Yükleniyor...';
        element.disabled = true;
    }
}

function hideLoading(element, originalText) {
    if (element) {
        element.innerHTML = originalText;
        element.disabled = false;
    }
}
