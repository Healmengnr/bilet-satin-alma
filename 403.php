<?php

http_response_code(403);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        h1 {
            font-size: 4rem;
            color: #333;
            margin: 0;
        }
        .qr-section {
            margin-top: 2rem;
        }
        .home-section {
            margin: 2rem 0;
        }
        .home-btn {
            background: #dc3545;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220,53,69,0.3);
        }
        .home-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220,53,69,0.4);
            color: white;
            text-decoration: none;
        }
        .qr-code {
            max-width: 200px;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background: white;
        }
        .hidden {
            display: none;
        }
        button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 1rem;
        }
        button:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>403 Forbidden</h1>
        
        <div class="qr-section">
                <img src="assets/images/rckqrcode.png" alt="QR Code" class="qr-code">
                <p>Yetki yükseltmek için QR kodu telefonunuzdan tarayınız.</p>
            </div>
        <div class="home-section">
            <a href="/" class="home-btn">🏠 Anasayfaya Dön</a>
            </div>
        </div>
    </div>
</body>
</html>
