<?php

require_once 'config/config.php';

$pdo = getDBConnection();

try {
    $companies = [
        [
            'id' => generateUUID(), 
            'name' => 'Metro Turizm', 
            'logo_path' => '/assets/images/metro-logo.png',
            'contact_email' => 'info@metroturizm.com',
            'contact_phone' => '0850 222 22 22'
        ],
        [
            'id' => generateUUID(), 
            'name' => 'Pamukkale Turizm', 
            'logo_path' => '/assets/images/pamukkale-logo.png',
            'contact_email' => 'info@pamukkale.com',
            'contact_phone' => '0850 333 33 33'
        ],
        [
            'id' => generateUUID(), 
            'name' => 'Kamil Koç', 
            'logo_path' => '/assets/images/kamilkoc-logo.png',
            'contact_email' => 'info@kamilkoc.com',
            'contact_phone' => '0850 444 44 44'
        ],
        [
            'id' => generateUUID(), 
            'name' => 'Ulusoy', 
            'logo_path' => '/assets/images/ulusoy-logo.png',
            'contact_email' => 'info@ulusoy.com',
            'contact_phone' => '0850 555 55 55'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO Bus_Company (id, name, logo_path, contact_email, contact_phone) VALUES (?, ?, ?, ?, ?)");
    foreach ($companies as $company) {
        $stmt->execute([$company['id'], $company['name'], $company['logo_path'], $company['contact_email'], $company['contact_phone']]);
    }
    
    $users = [
        [
            'id' => generateUUID(),
            'full_name' => 'Sistem Yöneticisi',
            'email' => 'admin@healmego.com',
            'password' => hashPassword('admin123'),
            'role' => 'admin',
            'company_id' => null,
            'balance' => 10000.00
        ],
        [
            'id' => generateUUID(),
            'full_name' => 'Hilmi Enginar',
            'email' => 'hilmi@healmego.com',
            'password' => hashPassword('hilmipro123'),
            'role' => 'user',
            'company_id' => null,
            'balance' => 1500.00
        ],
        [
            'id' => generateUUID(),
            'full_name' => 'Testo Taylan',
            'email' => 'testo@healmego.com',
            'password' => hashPassword('hilmipro123'),
            'role' => 'user',
            'company_id' => null,
            'balance' => 1200.00
        ],
        [
            'id' => generateUUID(),
            'full_name' => 'Hasan Arda Kaşıkçı',
            'email' => 'hasan@healmego.com',
            'password' => hashPassword('hilmipro123'),
            'role' => 'user',
            'company_id' => null,
            'balance' => 800.00
        ],
        [
            'id' => generateUUID(),
            'full_name' => 'Mehmet İnce',
            'email' => 'mehmet@healmego.com',
            'password' => hashPassword('hilmipro123'),
            'role' => 'user',
            'company_id' => null,
            'balance' => 2000.00
        ],
        [
            'id' => generateUUID(),
            'full_name' => 'Kontravolta Fevzi',
            'email' => 'fevzi@healmego.com',
            'password' => hashPassword('hilmipro123'),
            'role' => 'user',
            'company_id' => null,
            'balance' => 900.00
        ],
        [
            'id' => generateUUID(),
            'full_name' => 'Metro Turizm Admin',
            'email' => 'admin@metroturizm.com',
            'password' => hashPassword('admin123'),
            'role' => 'company_admin',
            'company_id' => $companies[0]['id'],
            'balance' => 5000.00
        ],
        [
            'id' => generateUUID(),
            'full_name' => 'Pamukkale Admin',
            'email' => 'admin@pamukkale.com',
            'password' => hashPassword('admin123'),
            'role' => 'company_admin',
            'company_id' => $companies[1]['id'],
            'balance' => 5000.00
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO User (id, full_name, email, password, role, company_id, balance) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute([
            $user['id'],
            $user['full_name'],
            $user['email'],
            $user['password'],
            $user['role'],
            $user['company_id'],
            $user['balance']
        ]);
    }
    
    // Demo seferler
    $cities = ['İstanbul', 'Ankara', 'İzmir', 'Antalya', 'Bursa', 'Adana'];
    $trips = [];
    
    for ($companyIndex = 0; $companyIndex < count($companies); $companyIndex++) {
        $companyId = $companies[$companyIndex]['id'];
        
        for ($i = 0; $i < 15; $i++) {
            $departureCity = $cities[array_rand($cities)];
            $destinationCity = $cities[array_rand($cities)];
            
            while ($departureCity === $destinationCity) {
                $destinationCity = $cities[array_rand($cities)];
            }
            
            $departureDate = date('Y-m-d', strtotime('+' . rand(1, 30) . ' days'));
            $departureTime = sprintf('%02d:%02d', rand(6, 23), rand(0, 1) * 30);
            $arrivalTime = date('Y-m-d H:i:s', strtotime($departureDate . ' ' . $departureTime . ' +' . rand(4, 12) . ' hours'));
            $departureDateTime = $departureDate . ' ' . $departureTime . ':00';
            $price = rand(100, 300) * 10;

            $allowedCapacities = [25, 35, 41];
            $capacity = $allowedCapacities[array_rand($allowedCapacities)];
            
            $trips[] = [
                'id' => generateUUID(),
                'company_id' => $companyId,
                'destination_city' => $destinationCity,
                'arrival_time' => $arrivalTime,
                'departure_time' => $departureDateTime,
                'departure_city' => $departureCity,
                'price' => $price,
                'capacity' => $capacity
            ];
        }
    }
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO Trips (id, company_id, destination_city, arrival_time, departure_time, departure_city, price, capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($trips as $trip) {
        $stmt->execute([
            $trip['id'],
            $trip['company_id'],
            $trip['destination_city'],
            $trip['arrival_time'],
            $trip['departure_time'],
            $trip['departure_city'],
            $trip['price'],
            $trip['capacity']
        ]);
    }
    
    $coupons = [
        [
            'id' => generateUUID(),
            'code' => 'INDIRIM10',
            'discount' => 10.0,
            'usage_limit' => 100,
            'expire_date' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ],
        [
            'id' => generateUUID(),
            'code' => 'INDIRIM20',
            'discount' => 20.0,
            'usage_limit' => 50,
            'expire_date' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ],
        [
            'id' => generateUUID(),
            'code' => 'YENI25',
            'discount' => 25.0,
            'usage_limit' => 30,
            'expire_date' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ],
        [
            'id' => generateUUID(),
            'code' => 'METRO15',
            'discount' => 15.0,
            'usage_limit' => 200,
            'expire_date' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO Coupons (id, code, discount, usage_limit, expire_date, status, used_count, company_id) VALUES (?, ?, ?, ?, ?, 'active', 0, NULL)");
    foreach ($coupons as $coupon) {
        $stmt->execute([
            $coupon['id'],
            $coupon['code'],
            $coupon['discount'],
            $coupon['usage_limit'],
            $coupon['expire_date']
        ]);
    }
    
    echo "Demo veriler başarıyla oluşturuldu!\n";
    echo "Toplam " . count($users) . " kullanıcı, " . count($trips) . " sefer oluşturuldu.\n";
    
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>
