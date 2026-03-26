<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Kod Oluşturucu — AI Araç Kutusu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f0f1a;
            color: #e8e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .main-container {
            flex: 1;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .input-panel {
            background: #1a1a2e;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            height: 100%;
        }

        .form-control,
        .form-select {
            background: #0f0f1a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e8e8f0;
            border-radius: 10px;
            padding: 0.8rem 1rem;
        }

        .form-control:focus,
        .form-select:focus {
            background: #0f0f1a;
            color: #e8e8f0;
            border-color: #6c63ff;
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
        }

        .form-select option {
            background: #1a1a2e;
            color: #e8e8f0;
        }

        .form-label {
            color: #9d9db5;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .btn-generate {
            background: linear-gradient(135deg, #6c63ff, #e040fb);
            border: none;
            color: white;
            padding: 0.8rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(108, 99, 255, 0.4);
            color: white;
        }

        .qr-output {
            background: #1a1a2e;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 400px;
        }

        #qrCanvas {
            max-width: 100%;
            overflow: hidden;
        }

        #qrCanvas img,
        #qrCanvas canvas {
            max-width: 100% !important;
            height: auto !important;
            border-radius: 12px;
        }

        .btn-download {
            display: inline-block;
            background: rgba(46, 213, 115, 0.15);
            border: 1px solid rgba(46, 213, 115, 0.3);
            color: #2ed573;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-download:hover {
            background: rgba(46, 213, 115, 0.25);
            color: #2ed573;
        }

        .tab-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #9d9db5;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab-btn.active {
            background: rgba(108, 99, 255, 0.15);
            border-color: #6c63ff;
            color: #6c63ff;
        }

        .tab-btn:hover {
            border-color: #6c63ff;
            color: #e8e8f0;
        }

        .color-row {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .color-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
        }

        .color-item small {
            color: #9d9db5;
            font-size: 0.75rem;
        }

        input[type="color"] {
            width: 50px;
            height: 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background: transparent;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/">
                <span style="color:#6c63ff">Yaratıcı</span>Stüdyo
            </a>
            <a href="<?= BASE_URL ?>/" class="btn btn-outline-light btn-sm rounded-pill">← Ana Menü</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="text-center mb-4">
            <h2>📐 QR Kod Oluşturucu</h2>
            <p class="text-muted">Link, metin veya WiFi bilgisi gir — anında QR kod oluştur ve indir.</p>
        </div>

        <div class="row g-4">
            <!-- Sol: Giriş -->
            <div class="col-lg-5">
                <div class="input-panel">
                    <!-- Tip Seçimi -->
                    <div class="mb-3">
                        <label class="form-label">Tür</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="tab-btn active" data-type="text" onclick="switchType('text')">📝 Metin /
                                Link</button>
                            <button class="tab-btn" data-type="wifi" onclick="switchType('wifi')">📶 WiFi</button>
                            <button class="tab-btn" data-type="email" onclick="switchType('email')">📧 E-Posta</button>
                        </div>
                    </div>

                    <!-- Metin / Link -->
                    <div id="input-text">
                        <div class="mb-3">
                            <label class="form-label">İçerik</label>
                            <textarea class="form-control" id="qrText" rows="4"
                                placeholder="https://ornek.com veya herhangi bir metin..."></textarea>
                        </div>
                    </div>

                    <!-- WiFi -->
                    <div id="input-wifi" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Ağ Adı (SSID)</label>
                            <input class="form-control" id="wifiSSID" placeholder="WiFi adı">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Şifre</label>
                            <input class="form-control" id="wifiPass" type="password" placeholder="WiFi şifresi">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Şifreleme</label>
                            <select class="form-select" id="wifiEnc">
                                <option value="WPA">WPA/WPA2</option>
                                <option value="WEP">WEP</option>
                                <option value="nopass">Şifresiz</option>
                            </select>
                        </div>
                    </div>

                    <!-- Email -->
                    <div id="input-email" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">E-Posta Adresi</label>
                            <input class="form-control" id="emailAddr" placeholder="ornek@mail.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konu</label>
                            <input class="form-control" id="emailSubject" placeholder="Merhaba!">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mesaj</label>
                            <textarea class="form-control" id="emailBody" rows="2"
                                placeholder="Mesaj içeriği..."></textarea>
                        </div>
                    </div>

                    <!-- Renk Ayarları -->
                    <div class="mb-3">
                        <label class="form-label">Renkler</label>
                        <div class="color-row">
                            <div class="color-item">
                                <input type="color" id="colorDark" value="#000000">
                                <small>QR Rengi</small>
                            </div>
                            <div class="color-item">
                                <input type="color" id="colorLight" value="#ffffff">
                                <small>Arka Plan</small>
                            </div>
                        </div>
                    </div>

                    <!-- Boyut -->
                    <div class="mb-4">
                        <label class="form-label">Boyut: <span id="sizeLabel">300</span>px</label>
                        <input type="range" class="form-range" id="qrSize" min="150" max="500" step="50" value="300">
                    </div>

                    <button class="btn btn-generate" onclick="generateQR()">
                        ✨ QR Kod Oluştur
                    </button>
                </div>
            </div>

            <!-- Sağ: Çıktı -->
            <div class="col-lg-7">
                <div class="qr-output" id="qrOutput">
                    <div class="text-muted">
                        <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">📐</div>
                        <p>Henüz QR kod oluşturulmadı.<br>Sol tarafı doldurup butona tıkla.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentType = 'text';

        function switchType(type) {
            currentType = type;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelector(`[data-type="${type}"]`).classList.add('active');
            document.getElementById('input-text').style.display = type === 'text' ? '' : 'none';
            document.getElementById('input-wifi').style.display = type === 'wifi' ? '' : 'none';
            document.getElementById('input-email').style.display = type === 'email' ? '' : 'none';
        }

        document.getElementById('qrSize').addEventListener('input', function () {
            document.getElementById('sizeLabel').textContent = this.value;
        });

        function getContent() {
            if (currentType === 'text') {
                return document.getElementById('qrText').value.trim();
            } else if (currentType === 'wifi') {
                const ssid = document.getElementById('wifiSSID').value.trim();
                const pass = document.getElementById('wifiPass').value;
                const enc = document.getElementById('wifiEnc').value;
                if (!ssid) return '';
                return `WIFI:T:${enc};S:${ssid};P:${pass};;`;
            } else if (currentType === 'email') {
                const addr = document.getElementById('emailAddr').value.trim();
                const subj = document.getElementById('emailSubject').value.trim();
                const body = document.getElementById('emailBody').value.trim();
                if (!addr) return '';
                return `mailto:${addr}?subject=${encodeURIComponent(subj)}&body=${encodeURIComponent(body)}`;
            }
            return '';
        }

        function generateQR() {
            const content = getContent();
            if (!content) {
                alert('Lütfen içerik girin.');
                return;
            }

            const size = parseInt(document.getElementById('qrSize').value);
            const colorDark = document.getElementById('colorDark').value.replace('#', '');
            const colorLight = document.getElementById('colorLight').value.replace('#', '');
            const output = document.getElementById('qrOutput');

            // QR Server API ile oluştur
            const apiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(content)}&color=${colorDark}&bgcolor=${colorLight}&format=png&margin=10`;

            // Kullanım sayacını artır
            fetch('api/track.php').catch(() => { });

            output.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-light mb-3" role="status"></div>
                    <p class="text-muted">QR Kod oluşturuluyor...</p>
                </div>
            `;

            // Resmi yükle
            const img = new Image();
            img.onload = function () {
                output.innerHTML = '';
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.style.borderRadius = '12px';
                img.style.background = '#' + colorLight;
                img.style.padding = '12px';
                output.appendChild(img);

                // İndir butonu
                const btn = document.createElement('a');
                btn.className = 'btn-download mt-3';
                btn.textContent = '⬇️ PNG olarak İndir';
                btn.href = apiUrl;
                btn.download = 'qr-kod.png';
                btn.target = '_blank';
                output.appendChild(btn);
            };
            img.onerror = function () {
                output.innerHTML = '<p class="text-danger">QR oluşturulamadı. Lütfen tekrar deneyin.</p>';
            };
            img.src = apiUrl;
        }
    </script>
</body>

</html>