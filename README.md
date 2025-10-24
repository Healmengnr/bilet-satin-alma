# HealmeGO - Otobüs Bileti Platformu

PHP ile yazılmış modern otobüs bileti satış sistemi. Docker ile paketlenmiş, rol tabanlı yetkilendirme sistemi içerir.

##  Hızlı Başlangıç

### Gereksinimler
- Docker
- Docker Compose

### Kurulum
```bash

git clone https://github.com/Healmengnr/bilet-satin-alma.git
cd bilet-satin-alma

docker-compose build

docker-compose up -d

docker exec biletotomasyonu-web-1 php init_demo_data.php
```

### Erişim
- **Web Sitesi:** http://localhost:8080
- **Dashboard:** http://localhost:8080/dashboard

##  Teknik Özellikler

- **Backend:** PHP 8.1
- **Veritabanı:** SQLite
- **Frontend:** Bootstrap 5, JavaScript

##  Demo Hesaplar

- **Sistem Admin:** admin@healmego.com / admin123
- **Firma Admin:** admin@metroturizm.com / admin123
- **Demo Kullanıcılar:** 
  - hilmi@healmego.com / hilmipro123
  - testo@healmego.com / hilmipro123
  - hasan@healmego.com / hilmipro123
  - mehmet@healmego.com / hilmipro123
  - fevzi@healmego.com / hilmipro123


## Veritabanı Sıfırlama
```bash
rm -rf database/

docker exec biletotomasyonu-web-1 php init_demo_data.php
```
