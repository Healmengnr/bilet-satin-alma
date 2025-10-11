<?php
/**
 * Demo verileri oluştur
 */

require_once 'config/config.php';

$pdo = getDBConnection();

try {
    // Demo firmalar
    $companies = [
        ['name' => 'Metro Turizm', 'description' => 'Türkiye\'nin en büyük otobüs firması', 'contact_email' => 'info@metroturizm.com', 'contact_phone' => '0850 222 22 22'],
        ['name' => 'Pamukkale Turizm', 'description' => 'Güvenilir seyahat deneyimi', 'contact_email' => 'info@pamukkale.com', 'contact_phone' => '0850 333 33 33'],
        ['name' => 'Kamil Koç', 'description' => 'Kaliteli hizmet anlayışı', 'contact_email' => 'info@kamilkoc.com', 'contact_phone' => '0850 444 44 44'],
        ['name' => 'Ulusoy', 'description' => 'Konforlu yolculuk', 'contact_email' => 'info@ulusoy.com', 'contact_phone' => '0850 555 55 55']
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO companies (name, description, contact_email, contact_phone) VALUES (?, ?, ?, ?)");
    foreach ($companies as $company) {
        $stmt->execute([$company[0], $company[1], $company[2], $company[3]]);
    }
    
    // Demo kullanıcılar
    $users = [
        // Admin
        [
            'username' => 'admin',
            'email' => 'admin@biletotomasyonu.com',
            'password' => hashPassword('admin123'),
            'full_name' => 'Sistem Yöneticisi',
            'phone' => '0555 111 11 11',
            'role' => 'admin',
            'credit' => 1000.00,
            'company_id' => null
        ],
        // Firma Admin'ler
        [
            'username' => 'firma1',
            'email' => 'firma1@metroturizm.com',
            'password' => hashPassword('firma123'),
            'full_name' => 'Metro Turizm Admin',
            'phone' => '0555 222 22 22',
            'role' => 'firma_admin',
            'credit' => 500.00,
            'company_id' => 1
        ],
        [
            'username' => 'firma2',
            'email' => 'firma2@pamukkale.com',
            'password' => hashPassword('firma123'),
            'full_name' => 'Pamukkale Admin',
            'phone' => '0555 333 33 33',
            'role' => 'firma_admin',
            'credit' => 500.00,
            'company_id' => 2
        ],
        // Normal kullanıcılar
        [
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password' => hashPassword('user123'),
            'full_name' => 'Ahmet Yılmaz',
            'phone' => '0555 444 44 44',
            'role' => 'user',
            'credit' => 200.00,
            'company_id' => null
        ],
        [
            'username' => 'user2',
            'email' => 'user2@example.com',
            'password' => hashPassword('user123'),
            'full_name' => 'Ayşe Demir',
            'phone' => '0555 555 55 55',
            'role' => 'user',
            'credit' => 150.00,
            'company_id' => null
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (username, email, password, full_name, phone, role, credit, company_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute([
            $user['username'],
            $user['email'], 
            $user['password'],
            $user['full_name'],
            $user['phone'],
            $user['role'],
            $user['credit'],
            $user['company_id']
        ]);
    }
    
    // Demo seferler
    $cities = ['istanbul', 'ankara', 'izmir', 'antalya', 'bursa', 'adana'];
    $cityNames = [
        'istanbul' => 'İstanbul',
        'ankara' => 'Ankara', 
        'izmir' => 'İzmir',
        'antalya' => 'Antalya',
        'bursa' => 'Bursa',
        'adana' => 'Adana'
    ];
    
    $trips = [];
    $prices = [120, 150, 180, 200, 250, 300];
    
    // Her firma için seferler oluştur
    for ($companyId = 1; $companyId <= 4; $companyId++) {
        for ($i = 0; $i < 20; $i++) {
            $departureCity = $cities[array_rand($cities)];
            $arrivalCity = $cities[array_rand($cities)];
            
            // Aynı şehir olmasın
            while ($departureCity === $arrivalCity) {
                $arrivalCity = $cities[array_rand($cities)];
            }
            
            $departureDate = date('Y-m-d', strtotime('+' . rand(1, 30) . ' days'));
            $departureTime = sprintf('%02d:%02d', rand(6, 23), rand(0, 1) * 30);
            $arrivalTime = date('H:i', strtotime($departureTime . ' +' . rand(4, 12) . ' hours'));
            $price = $prices[array_rand($prices)];
            $availableSeats = rand(5, 45);
            
            $trips[] = [
                $companyId,
                $cityNames[$departureCity],
                $cityNames[$arrivalCity],
                $departureDate,
                $departureTime,
                $arrivalTime,
                $price,
                45,
                $availableSeats,
                'active'
            ];
        }
    }
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($trips as $trip) {
        $stmt->execute([
            $trip[0], $trip[1], $trip[2], $trip[3], $trip[4], 
            $trip[5], $trip[6], $trip[7], $trip[8], $trip[9]
        ]);
    }
    
    // Demo kuponlar
    $coupons = [
        ['INDIRIM10', 10, 100, 1, date('Y-m-d'), date('Y-m-d', strtotime('+30 days')), 'active'],
        ['INDIRIM20', 20, 50, 1, date('Y-m-d'), date('Y-m-d', strtotime('+30 days')), 'active'],
        ['YENI25', 25, 30, null, date('Y-m-d'), date('Y-m-d', strtotime('+30 days')), 'active'],
        ['METRO15', 15, 200, 1, date('Y-m-d'), date('Y-m-d', strtotime('+30 days')), 'active'],
        ['PAMUKKALE20', 20, 150, 2, date('Y-m-d'), date('Y-m-d', strtotime('+30 days')), 'active']
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO coupons (code, discount_percentage, max_usage, company_id, valid_from, valid_until, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($coupons as $coupon) {
        $stmt->execute([
            $coupon[0], $coupon[1], $coupon[2], $coupon[3], 
            $coupon[4], $coupon[5], $coupon[6]
        ]);
    }
    
    echo "Demo veriler başarıyla oluşturuldu!\n";
    echo "Admin: admin / admin123\n";
    echo "Firma Admin: firma1 / firma123\n";
    echo "User: user1 / user123\n";
    
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>
