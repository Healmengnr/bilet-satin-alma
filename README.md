# HealmeGO - Otobüs Bileti Platformu

PHP ile yazılmış modern otobüs bileti satış sistemi. Docker ile paketlenmiş, rol tabanlı yetkilendirme sistemi içerir.

## 🚀 Hızlı Başlangıç

### Gereksinimler
- Docker
- Docker Compose

### Kurulum
```bash
# Repository'yi klonlayın
git clone https://github.com/KULLANICI_ADI/bilet-satin-alma.git
cd bilet-satin-alma

# Docker image'ı build edin
docker-compose build

# Docker container'ları başlatın
docker-compose up -d

# Demo verilerini yükleyin
docker exec biletotomasyonu-web-1 php init_demo_data.php
```

### Erişim
- **Web Sitesi:** http://localhost:8080
- **Dashboard:** http://localhost:8080/dashboard

## 🏗️ Teknik Özellikler

- **Backend:** PHP 8.1
- **Veritabanı:** SQLite
- **Frontend:** Bootstrap 5, JavaScript
- **PDF:** dompdf kütüphanesi
- **Container:** Docker & Docker Compose

## 🔐 Güvenlik Önlemleri

- SQL injection koruması
- XSS koruması  
- Path traversal koruması
- CSRF token sistemi
- Şifre hash'leme
- Session güvenliği
- Rol tabanlı erişim kontrolü

## 👥 Demo Hesaplar

- **Sistem Admin:** admin@healmego.com / admin123
- **Firma Admin:** admin@metroturizm.com / admin123
- **Demo Kullanıcılar:** 
  - hilmi@healmego.com / hilmipro123
  - testo@healmego.com / hilmipro123
  - hasan@healmego.com / hilmipro123
  - mehmet@healmego.com / hilmipro123
  - fevzi@healmego.com / hilmipro123

## 🛠️ Geliştirme

### Docker Komutları
```bash
# Container'ları durdur
docker-compose down

# Logları görüntüle
docker-compose logs -f

# Container'a bağlan
docker exec -it biletotomasyonu-web-1 bash

# Image'ı yeniden build et
docker-compose build --no-cache
```

### Veritabanı Sıfırlama
```bash
# Demo verilerini yeniden yükle
docker exec biletotomasyonu-web-1 php init_demo_data.php
```
