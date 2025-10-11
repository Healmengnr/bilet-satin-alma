# Otobüs Bileti Satış Platformu

Modern web teknolojileri kullanılarak geliştirilmiş dinamik, veritabanı destekli ve çok kullanıcılı otobüs bileti satış platformu.

## Teknolojiler
- **Backend**: PHP 8.3
- **Veritabanı**: SQLite
- **Frontend**: HTML, CSS, Bootstrap
- **Containerization**: Docker

## Kullanıcı Rolleri
- **Ziyaretçi**: Sefer arama ve görüntüleme
- **User (Yolcu)**: Bilet satın alma, iptal etme, PDF indirme
- **Firma Admin**: Kendi firmasına ait sefer yönetimi
- **Admin**: Sistem geneli yönetim

## Özellikler
- Sefer arama ve listeleme
- Koltuk seçimi ile bilet satın alma
- Kupon kodu sistemi
- Sanal kredi sistemi
- Bilet iptal etme (1 saat kuralı)
- PDF bilet üretimi
- Rol tabanlı yetkilendirme

## Kurulum
```bash
docker-compose up -d
```

## Erişim
- Web: http://localhost:8080
- Admin Panel: http://localhost:8080/admin
