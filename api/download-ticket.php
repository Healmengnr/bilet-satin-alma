<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect403();
}

$ticketId = $_GET['id'] ?? '';

if (empty($ticketId)) {
    http_response_code(400);
    die('Geçersiz bilet ID');
}

try {
    $pdo = getDBConnection();
    $user = getCurrentUser();
    
    if (hasRole('company_admin')) {
        $stmt = $pdo->prepare("
            SELECT t.*, tr.departure_city, tr.destination_city, tr.departure_time, tr.arrival_time,
                   bc.name as company_name, u.full_name, u.email
            FROM Tickets t
            JOIN Trips tr ON t.trip_id = tr.id
            JOIN Bus_Company bc ON tr.company_id = bc.id
            JOIN User u ON t.user_id = u.id
            WHERE t.id = ? AND tr.company_id = ? AND t.status IN ('active', 'cancelled')
        ");
        $stmt->execute([$ticketId, $user['company_id']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT t.*, tr.departure_city, tr.destination_city, tr.departure_time, tr.arrival_time,
                   bc.name as company_name, u.full_name, u.email
            FROM Tickets t
            JOIN Trips tr ON t.trip_id = tr.id
            JOIN Bus_Company bc ON tr.company_id = bc.id
            JOIN User u ON t.user_id = u.id
            WHERE t.id = ? AND t.user_id = ? AND t.status IN ('active', 'cancelled')
        ");
        $stmt->execute([$ticketId, $_SESSION['user_id']]);
    }
    
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        redirect404();
    }
    
    // Get booked seats
    $stmt = $pdo->prepare("
        SELECT seat_number FROM Booked_Seats 
        WHERE ticket_id = ? 
        ORDER BY seat_number
    ");
    $stmt->execute([$ticketId]);
    $seats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (isset($_GET['format']) && $_GET['format'] === 'pdf') {
        require_once '../vendor/autoload.php';
        
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml(generateTicketHTML($ticket, $seats));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = 'bilet_' . substr($ticket['id'], 0, 8) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $dompdf->output();
        exit;
    }
    
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $html = generateTicketHTML($ticket, $seats);
    echo $html;
    
} catch (Exception $e) {
    http_response_code(500);
    die('Bilet oluşturulurken hata oluştu: ' . $e->getMessage());
}

function generateTicketHTML($ticket, $seats) {
    return '
     <!DOCTYPE html>
     <html lang="tr">
     <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <title>Bilet - ' . escape($ticket['departure_city']) . ' → ' . escape($ticket['destination_city']) . '</title>
         <style>
             body { 
                 font-family: Arial, sans-serif; 
                 margin: 0;
                 padding: 20px;
                 background: #f8f9fa;
             }
             .ticket-container {
                 max-width: 800px;
                 margin: 0 auto;
                 background: white;
                 border-radius: 15px;
                 box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                 overflow: hidden;
             }
             .bilet-no {
                 background: linear-gradient(135deg, #007bff, #0056b3);
                 color: white;
                 padding: 15px;
                 text-align: center;
                 font-size: 20px;
                 font-weight: bold;
             }
             .status-badge {
                 display: inline-block;
                 padding: 5px 15px;
                 border-radius: 20px;
                 font-size: 12px;
                 font-weight: bold;
                 margin-left: 15px;
             }
             .status-active {
                 background: #28a745;
                 color: white;
             }
             .status-cancelled {
                 background: #dc3545;
                 color: white;
             }
             .header { 
                 text-align: center; 
                 border-bottom: 3px solid #007bff; 
                 padding: 30px 20px; 
                 background: linear-gradient(135deg, #f8f9fa, #e9ecef);
             }
             .logo { 
                 font-size: 32px; 
                 font-weight: bold; 
                 color: #007bff; 
                 margin-bottom: 10px;
             }
             .ticket-title {
                 font-size: 28px;
                 font-weight: bold;
                 color: #333;
             }
             .ticket-info { 
                 display: flex; 
                 justify-content: space-between; 
                 margin: 30px 20px; 
                 flex-wrap: wrap;
                 gap: 20px;
             }
             .info-section { 
                 flex: 1; 
                 min-width: 250px;
                 background: #f8f9fa;
                 padding: 20px;
                 border-radius: 10px;
                 border-left: 4px solid #007bff;
             }
             .info-title { 
                 font-weight: bold; 
                 color: #007bff; 
                 margin-bottom: 15px; 
                 font-size: 18px;
                 border-bottom: 2px solid #007bff;
                 padding-bottom: 8px;
             }
             .info-item { 
                 margin-bottom: 10px; 
                 font-size: 15px;
                 line-height: 1.4;
             }
             .seats { 
                 margin: 30px 20px;
                 text-align: center;
                 background: #f8f9fa;
                 padding: 20px;
                 border-radius: 10px;
             }
             .seat-number { 
                 display: inline-block; 
                 background: #007bff; 
                 color: white; 
                 padding: 10px 15px; 
                 margin: 5px; 
                 border-radius: 8px; 
                 font-weight: bold;
                 font-size: 16px;
                 box-shadow: 0 2px 5px rgba(0,123,255,0.3);
             }
             .qr-section {
                 text-align: center;
                 margin: 30px 20px;
                 padding: 20px;
                 background: #f8f9fa;
                 border-radius: 10px;
             }
             .qr-placeholder { 
                 width: 150px; 
                 height: 150px; 
                 border: 3px solid #007bff; 
                 margin: 20px auto; 
                 display: flex; 
                 align-items: center; 
                 justify-content: center; 
                 background: white;
                 border-radius: 15px;
                 font-weight: bold;
                 color: #007bff;
                 box-shadow: 0 4px 10px rgba(0,0,0,0.1);
             }
             .footer { 
                 text-align: center; 
                 margin: 30px 20px; 
                 padding: 20px; 
                 border-top: 2px solid #007bff; 
                 color: #666; 
                 font-size: 13px;
                 background: #f8f9fa;
                 border-radius: 10px;
             }
             .download-section {
                 text-align: center;
                 margin: 30px 20px;
                 padding: 20px;
                 background: linear-gradient(135deg, #28a745, #20c997);
                 border-radius: 10px;
             }
             .download-btn {
                 background: white;
                 color: #28a745;
                 padding: 15px 30px;
                 border: none;
                 border-radius: 25px;
                 font-size: 18px;
                 font-weight: bold;
                 cursor: pointer;
                 text-decoration: none;
                 display: inline-block;
                 box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                 transition: all 0.3s ease;
             }
             .download-btn:hover {
                 transform: translateY(-2px);
                 box-shadow: 0 6px 20px rgba(0,0,0,0.3);
                 color: #28a745;
                 text-decoration: none;
             }
             @media (max-width: 768px) {
                 .ticket-info {
                     flex-direction: column;
                 }
                 .info-section {
                     min-width: 100%;
                 }
             }
         </style>
     </head>
     <body>
         <div class="ticket-container">
             <div class="bilet-no">
                 BİLET NO: ' . substr($ticket['id'], 0, 8) . '
                 <span class="status-badge status-' . $ticket['status'] . '">
                     ' . ($ticket['status'] === 'active' ? 'AKTİF' : 'İPTAL EDİLDİ') . '
                 </span>
             </div>
             
             <div class="header">
                 <div class="logo">HEALME </div>
                 <div class="ticket-title">OTOBÜS BİLETİ</div>
             </div>
             
             <div class="ticket-info">
                 <div class="info-section">
                     <div class="info-title">YOLCU BİLGİLERİ</div>
                     <div class="info-item"><strong>Ad Soyad:</strong> ' . escape($ticket['full_name']) . '</div>
                     <div class="info-item"><strong>E-posta:</strong> ' . escape($ticket['email']) . '</div>
                     <div class="info-item"><strong>Bilet ID:</strong> ' . $ticket['id'] . '</div>
                 </div>
                 
                 <div class="info-section">
                     <div class="info-title">SEFER BİLGİLERİ</div>
                     <div class="info-item"><strong>Güzergah:</strong> ' . escape($ticket['departure_city']) . ' → ' . escape($ticket['destination_city']) . '</div>
                     <div class="info-item"><strong>Tarih:</strong> ' . formatDate($ticket['departure_time']) . '</div>
                     <div class="info-item"><strong>Kalkış:</strong> ' . formatTime($ticket['departure_time']) . '</div>
                     <div class="info-item"><strong>Varış:</strong> ' . formatTime($ticket['arrival_time']) . '</div>
                 </div>
                 
                 <div class="info-section">
                     <div class="info-title">FİRMA BİLGİLERİ</div>
                     <div class="info-item"><strong>Firma:</strong> ' . escape($ticket['company_name']) . '</div>
                     <div class="info-item"><strong>Fiyat:</strong> ' . formatPrice($ticket['total_price']) . '</div>
                     <div class="info-item"><strong>Satın Alma:</strong> ' . formatDate($ticket['created_at']) . '</div>
                 </div>
             </div>
             
             <div class="seats">
                 <div class="info-title">KOLTUK NUMARALARI</div>
                 <div>';
    
    foreach ($seats as $seat) {
        $html .= '<span class="seat-number">' . $seat . '</span>';
    }
    
    $html .= '
                 </div>
             </div>
             
             <div class="qr-section">
                 <div class="info-title">QR CODE</div>
                 <div class="qr-placeholder">
                     <img src="../../assets/images/hilmi-insta-qr.png" alt="QR Code" style="max-width: 100%; max-height: 100%;">
                 </div>
             </div>
             
             <div class="download-section">
                 <h3 style="color: white; margin-bottom: 20px;">📄 PDF İndir</h3>
                 <a href="?id=' . $ticketId . '&format=pdf" class="download-btn">
                     💾 PDF Olarak İndir
                 </a>
             </div>
             
             <div class="footer">
                 <p><strong>⚠️ Önemli:</strong> Bu bilet sefer saatinden 1 saat öncesine kadar iptal edilebilir.</p>
                 <p><strong>🔒 Güvenlik:</strong> Bu bilet sadece ' . escape($ticket['full_name']) . ' tarafından kullanılabilir.</p>
                 <p>HealmeGO - Güvenli Seyahat | ' . date('d.m.Y H:i') . '</p>
             </div>
         </div>
     </body>
     </html>';
    
    return $html;
}

function createTicketPDF($ticket, $seats) {
    $pdf = "%PDF-1.4\n";
    
    // PDF objeleri
    $objects = [];
    $objectCount = 0;
    
    // Font objesi
    $objectCount++;
    $fontObj = $objectCount;
    $objects[$fontObj] = "<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n>>\n";
    
    // Sayfa içeriği
    $objectCount++;
    $contentObj = $objectCount;
    
    $content = "BT\n";
    $content .= "/F1 20 Tf\n";
    $content .= "50 750 Td\n";
    $content .= "(HEALMEGO) Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 16 Tf\n";
    $content .= "50 720 Td\n";
    $content .= "(OTOBUS BILETI) Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 690 Td\n";
    $content .= "(Bilet No: " . substr($ticket['id'], 0, 8) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 10 Tf\n";
    $content .= "50 670 Td\n";
    $content .= "(Tam Bilet ID: " . $ticket['id'] . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 660 Td\n";
    $content .= "(Yolcu: " . escape($ticket['full_name']) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 630 Td\n";
    $content .= "(E-posta: " . escape($ticket['email']) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 600 Td\n";
    $content .= "(Guzergah: " . escape($ticket['departure_city']) . " -> " . escape($ticket['destination_city']) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 570 Td\n";
    $content .= "(Tarih: " . formatDate($ticket['departure_time']) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 540 Td\n";
    $content .= "(Kalkis: " . formatTime($ticket['departure_time']) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 510 Td\n";
    $content .= "(Varis: " . formatTime($ticket['arrival_time']) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 480 Td\n";
    $content .= "(Firma: " . escape($ticket['company_name']) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 450 Td\n";
    $content .= "(Fiyat: " . formatPrice($ticket['total_price']) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 12 Tf\n";
    $content .= "50 420 Td\n";
    $content .= "(Koltuklar: " . implode(', ', $seats) . ") Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 10 Tf\n";
    $content .= "50 380 Td\n";
    $content .= "(Bu bilet sadece " . escape($ticket['full_name']) . " tarafindan kullanilabilir.) Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 10 Tf\n";
    $content .= "50 360 Td\n";
    $content .= "(Sefer saatinden 1 saat oncesine kadar iptal edilebilir.) Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 10 Tf\n";
    $content .= "50 340 Td\n";
    $content .= "(QR Code: hilmi-insta-qr.png) Tj\n";
    $content .= "ET\n";
    
    $content .= "BT\n";
    $content .= "/F1 10 Tf\n";
    $content .= "50 320 Td\n";
    $content .= "(HealmeGO - Guvenli Seyahat | " . date('d.m.Y H:i') . ") Tj\n";
    $content .= "ET\n";
    
    $objects[$contentObj] = "<<\n/Length " . strlen($content) . "\n>>\nstream\n" . $content . "\nendstream\n";
    
    // Sayfa objesi
    $objectCount++;
    $pageObj = $objectCount;
    $objects[$pageObj] = "<<\n/Type /Page\n/Parent " . ($objectCount + 1) . " 0 R\n/MediaBox [0 0 612 792]\n/Resources <<\n/Font <<\n/F1 " . $fontObj . " 0 R\n>>\n>>\n/Contents " . $contentObj . " 0 R\n>>\n";
    
    // Sayfalar objesi
    $objectCount++;
    $pagesObj = $objectCount;
    $objects[$pagesObj] = "<<\n/Type /Pages\n/Kids [" . $pageObj . " 0 R]\n/Count 1\n>>\n";
    
    // Katalog objesi
    $objectCount++;
    $catalogObj = $objectCount;
    $objects[$catalogObj] = "<<\n/Type /Catalog\n/Pages " . $pagesObj . " 0 R\n>>\n";
    
    // PDF içeriğini oluştur
    $pdfContent = "";
    $xref = [];
    $offset = strlen($pdf);
    
    foreach ($objects as $objNum => $objContent) {
        $xref[$objNum] = $offset;
        $pdfContent .= $objNum . " 0 obj\n" . $objContent . "endobj\n";
        $offset += strlen($objNum . " 0 obj\n" . $objContent . "endobj\n");
    }
    
    $pdf .= $pdfContent;
    
    // XRef tablosu
    $xrefStart = strlen($pdf);
    $pdf .= "xref\n";
    $pdf .= "0 " . ($objectCount + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    
    for ($i = 1; $i <= $objectCount; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $xref[$i]);
    }
    
    // Trailer
    $pdf .= "trailer\n";
    $pdf .= "<<\n";
    $pdf .= "/Size " . ($objectCount + 1) . "\n";
    $pdf .= "/Root " . $catalogObj . " 0 R\n";
    $pdf .= ">>\n";
    $pdf .= "startxref\n";
    $pdf .= $xrefStart . "\n";
    $pdf .= "%%EOF\n";
    
    return $pdf;
}
?>