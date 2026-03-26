<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prompt Oluşturucu — AI Araç Kutusu</title>
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
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        .input-area {
            background: #1a1a2e;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
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

        .form-control::placeholder {
            color: #555;
        }

        .form-select option {
            background: #1a1a2e;
        }

        .form-label {
            color: #9d9db5;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .btn-generate {
            background: linear-gradient(135deg, #6c63ff, #e040fb);
            border: none;
            color: white;
            padding: 0.8rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: all 0.2s;
            font-size: 1rem;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(108, 99, 255, 0.4);
            color: white;
        }

        .btn-generate:disabled {
            opacity: 0.6;
            transform: none;
        }

        .output-area {
            background: #1a1a2e;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            position: relative;
        }

        .prompt-result {
            background: #0d0d15;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.8;
            white-space: pre-wrap;
            min-height: 200px;
            max-height: 500px;
            overflow-y: auto;
            color: #dcdccc;
        }

        .btn-copy {
            background: rgba(108, 99, 255, 0.15);
            border: 1px solid rgba(108, 99, 255, 0.3);
            color: #6c63ff;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-copy:hover {
            background: rgba(108, 99, 255, 0.25);
            color: #6c63ff;
        }

        .tag-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #9d9db5;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tag-btn.active {
            background: rgba(108, 99, 255, 0.15);
            border-color: #6c63ff;
            color: #6c63ff;
        }

        .tag-btn:hover {
            border-color: #6c63ff;
            color: #e8e8f0;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 3rem 0;
        }

        .loading.show {
            display: block;
        }

        .example-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .example-chip {
            background: rgba(224, 64, 251, 0.1);
            border: 1px solid rgba(224, 64, 251, 0.2);
            color: #e040fb;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.78rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .example-chip:hover {
            background: rgba(224, 64, 251, 0.2);
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
            <h2>✨ AI Prompt Oluşturucu</h2>
            <p class="text-muted">Basit bir fikir yaz — profesyonel ve detaylı bir AI prompt'una dönüştürsün.</p>
        </div>

        <!-- Giriş -->
        <div class="input-area mb-4">
            <div class="mb-3">
                <label class="form-label">Ne yapmak istiyorsun?</label>
                <textarea class="form-control" id="userIdea" rows="3"
                    placeholder="Fikrini birkaç kelimeyle anlat..."></textarea>
                <div class="example-chips" id="exampleChips"></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Hedef AI</label>
                    <select class="form-select" id="targetAI">
                        <option value="genel">Genel (Tüm AI'lar)</option>
                        <option value="chatgpt">ChatGPT</option>
                        <option value="gemini">Gemini</option>
                        <option value="claude">Claude</option>
                        <option value="midjourney">Midjourney (Görsel)</option>
                        <option value="cursor">Cursor / Copilot (Kod)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Detay Seviyesi</label>
                    <select class="form-select" id="detailLevel">
                        <option value="orta">Orta</option>
                        <option value="kisa">Kısa & Öz</option>
                        <option value="detayli" selected>Detaylı & Profesyonel</option>
                        <option value="uzman">Uzman Seviye</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Dil</label>
                <div class="d-flex gap-2">
                    <button class="tag-btn active" data-lang="tr" onclick="setLang(this)">🇹🇷 Türkçe</button>
                    <button class="tag-btn" data-lang="en" onclick="setLang(this)">🇬🇧 İngilizce</button>
                </div>
            </div>

            <button class="btn btn-generate" id="btnGenerate" onclick="generate()">
                ✨ Prompt Oluştur
            </button>
        </div>

        <!-- Çıktı -->
        <div class="output-area" id="outputSection" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">📋 Oluşturulan Prompt</h5>
                <button class="btn-copy" onclick="copyPrompt()">📋 Kopyala</button>
            </div>
            <div class="prompt-result" id="promptResult"></div>
        </div>

        <!-- Loading -->
        <div class="loading" id="loadingSection">
            <div class="spinner-border text-light mb-3" role="status"></div>
            <h5>Prompt hazırlanıyor...</h5>
            <p class="text-muted small">Yapay zeka fikrinizi profesyonel bir prompt'a dönüştürüyor.</p>
        </div>
    </div>

    <script>
        let selectedLang = 'tr';

        const allExamples = [
            'E-ticaret sitesi tasarla',
            'Python ile veri analizi yap',
            'Instagram için içerik planı oluştur',
            'Yapay zeka destekli chatbot geliştir',
            'Portföy web sitesi yap',
            'Unity ile 2D oyun geliştir',
            'React Native mobil uygulama yaz',
            'SEO uyumlu blog yazısı yaz',
            'YouTube video senaryosu hazırla',
            'Logo tasarımı için brief oluştur',
            'Restoran menü uygulaması geliştir',
            'Discord botu programla',
            'Machine learning modeli eğit',
            'Startup iş planı oluştur',
            'Landing page tasarla',
            'API dokümantasyonu yaz',
            'Sosyal medya stratejisi belirle',
            'Pixel art oyun tasarla'
        ];

        function loadRandomExamples() {
            const shuffled = allExamples.sort(() => 0.5 - Math.random());
            const picked = shuffled.slice(0, 3);
            const container = document.getElementById('exampleChips');
            container.innerHTML = picked.map(ex =>
                `<span class="example-chip" onclick="setExample(this)">${ex}</span>`
            ).join('');
        }

        function setExample(el) {
            document.getElementById('userIdea').value = el.textContent;
        }

        document.addEventListener('DOMContentLoaded', loadRandomExamples);

        function setLang(el) {
            document.querySelectorAll('.tag-btn').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
            selectedLang = el.dataset.lang;
        }

        async function generate() {
            const idea = document.getElementById('userIdea').value.trim();
            if (!idea) { alert('Lütfen bir fikir yazın.'); return; }

            const targetAI = document.getElementById('targetAI').value;
            const detail = document.getElementById('detailLevel').value;
            const btn = document.getElementById('btnGenerate');

            btn.disabled = true;
            btn.textContent = '⏳ Oluşturuluyor...';
            document.getElementById('outputSection').style.display = 'none';
            document.getElementById('loadingSection').classList.add('show');

            try {
                const formData = new FormData();
                formData.append('idea', idea);
                formData.append('target_ai', targetAI);
                formData.append('detail', detail);
                formData.append('lang', selectedLang);

                const res = await fetch('api/generate.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    document.getElementById('promptResult').textContent = data.prompt;
                    document.getElementById('outputSection').style.display = '';
                } else {
                    alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
                }
            } catch (e) {
                alert('Bağlantı hatası: ' + e.message);
            }

            btn.disabled = false;
            btn.textContent = '✨ Prompt Oluştur';
            document.getElementById('loadingSection').classList.remove('show');
        }

        function copyPrompt() {
            const text = document.getElementById('promptResult').textContent;
            navigator.clipboard.writeText(text);
            const btn = document.querySelector('.btn-copy');
            btn.textContent = '✅ Kopyalandı!';
            setTimeout(() => btn.textContent = '📋 Kopyala', 2000);
        }
    </script>
</body>

</html>