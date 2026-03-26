<?php
require_once __DIR__ . '/../config.php';

// Araç kullanım sayacını isteğe bağlı olarak artırabiliriz
// $pdo->query("UPDATE tools SET usage_count = usage_count + 1 WHERE slug = 'ui'");
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metinden UI Üretici — AI Araç Kutusu</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500&display=swap"
        rel="stylesheet">
    <!-- Highlight.js for code syntax -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">

    <style>
        :root {
            --bg-primary: #0f0f1a;
            --bg-card: #1a1a2e;
            --bg-input: #23233b;
            --accent: #6c63ff;
            --accent-hover: #5a52e0;
            --text-primary: #e8e8f0;
            --text-secondary: #9d9db5;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            overflow: hidden;
            /* Avoid main body scroll, handle internally */
        }

        /* Navbar */
        .navbar {
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .navbar-brand span {
            color: var(--accent);
        }

        /* Split Layout Main Container */
        .app-container {
            display: flex;
            flex: 1;
            height: calc(100vh - 60px);
            /* Subtract Navbar height */
        }

        /* Left Panel - Control/Input */
        .panel-left {
            width: 35%;
            min-width: 300px;
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .panel-left h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .prompt-textarea {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 12px;
            padding: 1rem;
            resize: none;
            height: 150px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            margin-bottom: 1rem;
        }

        .prompt-textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.15);
        }

        .btn-generate {
            background: linear-gradient(135deg, var(--accent), #5a52e0);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-generate:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        .btn-generate:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .idea-chips {
            margin-top: 1.5rem;
        }

        .idea-chips p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .chip {
            display: inline-block;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--text-primary);
            cursor: pointer;
            margin: 0.25rem;
            transition: all 0.2s;
        }

        .chip:hover {
            background: rgba(108, 99, 255, 0.15);
            border-color: var(--accent);
            color: #fff;
        }

        /* Right Panel - Output */
        .panel-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--bg-primary);
            position: relative;
        }

        /* Custom Tabs */
        .output-tabs {
            display: flex;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            padding: 0 1rem;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            position: relative;
            transition: color 0.2s;
        }

        .tab-btn:hover {
            color: var(--text-primary);
        }

        .tab-btn.active {
            color: var(--accent);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent);
            border-radius: 3px 3px 0 0;
        }

        .copy-btn {
            margin-left: auto;
            /* Push to right */
            align-self: center;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.4rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            display: none;
            /* Only show in Code tab */
        }

        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .tab-content {
            flex: 1;
            display: none;
            position: relative;
            overflow: hidden;
        }

        .tab-content.active {
            display: flex;
            flex-direction: column;
        }

        /* Live Preview Container */
        .preview-container {
            flex: 1;
            background: #ffffff;
            /* Typically white to see true colors */
            background-image: radial-gradient(#d5d5ea 1px, transparent 1px);
            background-size: 20px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto;
            position: relative;
        }

        /* Code Editor Container */
        .code-container {
            flex: 1;
            overflow: auto;
            background: #282c34;
            /* Atom One Dark bg */
            margin: 0;
        }

        pre {
            margin: 0;
            padding: 1.5rem;
        }

        code {
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
        }

        /* Loading Overlay inside Right Panel */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 15, 26, 0.8);
            backdrop-filter: blur(4px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
            display: none;
        }

        .loading-text {
            margin-top: 1rem;
            font-weight: 500;
            color: var(--accent);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Initial State Empty Placeholder */
        .empty-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            text-align: center;
            padding: 2rem;
            z-index: 5;
            background: var(--bg-primary);
        }

        .empty-placeholder svg {
            width: 64px;
            height: 64px;
            opacity: 0.2;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="<?= BASE_URL ?>/">
                <span>Yaratıcı</span>Stüdyo
            </a>
            <a href="<?= BASE_URL ?>/" class="btn btn-sm btn-outline-light rounded-pill">← Ana Menü</a>
        </div>
    </nav>

    <!-- App Container -->
    <div class="app-container">

        <!-- Left Panel: Input -->
        <div class="panel-left">
            <h2>🎨 AI UI Üretici</h2>
            <p style="color:var(--text-secondary); font-size:0.9rem;">
                İstediğiniz UI bileşenini (buton, kart, form) tarif edin. Gelişmiş yapay zeka hemen HTML ve CSS
                kodlarını üretsin.
            </p>

            <textarea id="promptInput" class="prompt-textarea"
                placeholder="Örn: Pembeden mora açılan neon parlaklıklı, köşeleri yuvarlak, hover olunca hafif büyüyen bir İndir butonu tasarla..."></textarea>

            <div class="mb-3">
                <label for="outputType"
                    style="color:var(--text-secondary); font-size:0.85rem; margin-bottom:0.5rem; display:block;">Çıktı
                    Türü</label>
                <select id="outputType" class="form-select"
                    style="background:var(--bg-input); color:var(--text-primary); border-color:var(--border-color);">
                    <option value="both" selected>Her İkisi (HTML + CSS)</option>
                    <option value="html">Sadece HTML (Tailwind/Bootstrap vb.)</option>
                    <option value="css">Sadece CSS (Mevcut HTML'im var)</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="designLevel"
                    style="color:var(--text-secondary); font-size:0.85rem; margin-bottom:0.5rem; display:block;">Tasarım
                    Seviyesi (Kalite)</label>
                <select id="designLevel" class="form-select"
                    style="background:var(--bg-input); color:var(--text-primary); border-color:var(--border-color);">
                    <option value="professional" selected>Profesyonel (Modern, Şık, İleri Seviye)</option>
                    <option value="intermediate">Orta Seviye (Standart Kurumsal)</option>
                    <option value="amateur">Temel / Sade (Hızlı ve Basit)</option>
                </select>
            </div>

            <button id="btnGenerate" class="btn-generate">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
                Oluştur
            </button>

            <div class="idea-chips">
                <p>💡 İlham Alın</p>
                <?php
                $ideas = [
                    // Kartlar & Sunum
                    "Glassmorphism tarzı şeffaf fiyatlandırma kartı",
                    "Karanlık mod (dark theme) destekli, neon detaylı profil kartı",
                    "Hover olunca 3D dönen (flip) e-ticaret ürün kartı",
                    "Havadar (airy) ve geniş boşluklu, minimalist blog yazısı kartı",
                    "Neumorphism tarzı soft görünümlü müzik çalar arayüzü",
                    // Formlar & Girdi
                    "Cyberpunk esintili, glitch efektli giriş formu",
                    "Apple tarzı şık, pürüzsüz geçişli ayarlar menüsü",
                    "Minimalist, yüzen etiketlere (floating labels) sahip iletişim formu",
                    "Modern, çok adımlı (stepper) ve animasyonlu kayıt formu",
                    "Retro 8-bit piksel art stili şifre giriş paneli",
                    // Butonlar & Kontroller
                    "Dalga (ripple) efektli modern e-ticaret 'Sepete Ekle' butonu",
                    "Parıldayan gradient neon efektli, nabız atan 'Hemen İndir' butonu",
                    "Skeuomorfik (gerçekçi), basılma hissi veren kalın buton",
                    "Gündüz/Gece modu geçişi için animasyonlu toggle switch",
                    "Swipe (kaydırarak) onaylama butonu tasarımı",
                    // Navigasyon & Menü
                    "Kaydırıldığında üstte sabitlenen (sticky), blur efektli navbar",
                    "Yarım daire şeklinde açılan dairesel hareketli menü (radial menu)",
                    "Yan panelli (sidebar), ikonları parlayan admin paneli navigasyonu",
                    "Alt sekme (bottom tab) tarzı mobil uygulama navigasyon çubuğu",
                    // Modallar & Uyarilar
                    "Havadar ve geniş boşluklu, başarılı işlem bildirim popup'ı",
                    "Ekranı kaplayan (fullscreen), blur arka planlı arama penceresi",
                    "Ekranın sağ altından kayarak giren modern bildirim (toast) kartı",
                    "Kırmızı alarm ışığı efektiyle yanıp sönen uyarı modalı",
                    // Görsel & Animasyon
                    "Minimalist, sonsuz dönen modern spinner (yükleniyor) animasyonu",
                    "Hareketli gradient arka plana sahip, büyük başlıklı kahraman (hero) alanı",
                    "İskelet (skeleton) yüklenme ekranı animasyonu",
                    "Kayan yazı (marquee) efektiyle kripto para fiyat bandı",
                    // Veri & İstatistik
                    "Dairesel ilerleme çubuğu (circular progress bar) ile stat gösterimi",
                    "Sıralama panosu (leaderboard) listesi, altın kupa detaylı",
                    "Seviye atlama (level up) progress bar tasarımı",
                    // Orijinal Ekler
                    "Holografik efektli fütüristik kullanıcı rozeti",
                    "Retro Windows 95 klasörü tarzında dosya yükleme alanı"
                ];
                shuffle($ideas);
                $selected_ideas = array_slice($ideas, 0, 4);
                foreach ($selected_ideas as $idea) {
                    echo '<span class="chip" onclick="setPrompt(this.innerText)">' . htmlspecialchars($idea) . '</span>';
                }
                ?>
            </div>
        </div>

        <!-- Right Panel: Output -->
        <div class="panel-right">

            <!-- Tabs -->
            <div class="output-tabs">
                <button class="tab-btn active" data-target="previewTab" onclick="switchTab('previewTab')">👁️ Canlı
                    Önizleme</button>
                <button class="tab-btn" data-target="codeTab" onclick="switchTab('codeTab')">💻 Kaynak Kodu</button>
                <button class="copy-btn" id="btnCopy" onclick="copyCode()">Kodu Kopyala</button>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <polyline points="16 18 22 12 16 6"></polyline>
                    <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
                <h4>Bileşen Bekleniyor</h4>
                <p>Sol taraftaki kutuya bir komut girerek tasarıma başlayın.</p>
            </div>

            <!-- Loading State -->
            <div id="loadingOverlay" class="loading-overlay">
                <div class="spinner-border text-primary"
                    style="width: 3rem; height: 3rem; color: var(--accent) !important;" role="status">
                    <span class="visually-hidden">Yükleniyor...</span>
                </div>
                <div class="loading-text" id="loadingText">Yapay Zeka kodları yazıyor...</div>
            </div>

            <!-- Preview Content -->
            <div id="previewTab" class="tab-content active">
                <div class="preview-container" id="previewContainer">
                    <!-- The generated HTML/CSS goes here inside a Shadow DOM or Iframe -->
                    <iframe id="previewFrame"
                        style="width:100%; height:100%; border:none; background:transparent;"></iframe>
                </div>
            </div>

            <!-- Code Content -->
            <div id="codeTab" class="tab-content">
                <div class="code-container" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">

                    <div>
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.5rem;">
                            <span
                                style="color:var(--text-secondary); font-size: 0.85rem; font-weight:600; text-transform:uppercase;">HTML
                                Kodu</span>
                            <button class="copy-btn" onclick="copySpecificCode('codeBlockHtml', this)"
                                style="display:block; margin:0; padding: 0.2rem 0.6rem; font-size: 0.75rem;">Kopyala</button>
                        </div>
                        <pre
                            style="padding: 1rem; border-radius: 8px; background: #1e1e2e; margin: 0;"><code class="language-html" id="codeBlockHtml">/* HTML code */</code></pre>
                    </div>

                    <div>
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.5rem;">
                            <span
                                style="color:var(--text-secondary); font-size: 0.85rem; font-weight:600; text-transform:uppercase;">CSS
                                Kodu</span>
                            <button class="copy-btn" onclick="copySpecificCode('codeBlockCss', this)"
                                style="display:block; margin:0; padding: 0.2rem 0.6rem; font-size: 0.75rem;">Kopyala</button>
                        </div>
                        <pre
                            style="padding: 1rem; border-radius: 8px; background: #1e1e2e; margin: 0;"><code class="language-css" id="codeBlockCss">/* CSS code */</code></pre>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>
        hljs.highlightAll();

        let currentHtml = '';
        let currentCss = '';

        // UI Interactions
        function setPrompt(text) {
            document.getElementById('promptInput').value = text;
        }

        function switchTab(tabId) {
            // Update buttons
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-target="${tabId}"]`).classList.add('active');

            // Update content
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');

            // Copy button visibility
            const copyBtn = document.getElementById('btnCopy');
            if (tabId === 'codeTab') {
                copyBtn.style.display = 'block';
                // The copy button is now handled by individual code blocks, so we hide the main one
                copyBtn.style.display = 'none';
            } else {
                copyBtn.style.display = 'none';
            }
        }

        // Individual Copy Function for the new blocks
        async function copySpecificCode(elementId, btn) {
            const code = document.getElementById(elementId).innerText;
            if (!code) return;

            try {
                await navigator.clipboard.writeText(code);
                const origText = btn.innerText;
                btn.innerText = 'Kopyalandı! ✔️';
                btn.style.color = 'var(--accent)';
                setTimeout(() => {
                    btn.innerText = origText;
                    btn.style.color = '';
                }, 2000);
            } catch (err) {
                console.error('Failed to copy', err);
            }
        }

        function renderPreview(html, css) {
            const iframe = document.getElementById('previewFrame');
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

            const fullHtml = `
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body {
                            margin: 0;
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            background: transparent;
                            font-family: 'Inter', system-ui, sans-serif;
                        }
                        ${css}
                    </style>
                </head>
                <body>
                    ${html}
                </body>
                </html>
            `;

            iframeDoc.open();
            iframeDoc.write(fullHtml);
            iframeDoc.close();
        }

        // Logic
        document.getElementById('btnGenerate').addEventListener('click', async () => {
            const prompt = document.getElementById('promptInput').value.trim();
            const outputType = document.getElementById('outputType').value;
            const designLevel = document.getElementById('designLevel').value;

            if (!prompt) return;

            const btn = document.getElementById('btnGenerate');
            const overlay = document.getElementById('loadingOverlay');
            const emptyState = document.getElementById('emptyState');

            const htmlBlock = document.getElementById('codeBlockHtml');
            const cssBlock = document.getElementById('codeBlockCss');

            btn.disabled = true;
            btn.innerHTML = 'Üretiliyor...';
            overlay.style.display = 'flex';
            emptyState.style.display = 'none';

            try {
                const response = await fetch('api/generate.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: prompt, type: outputType, designLevel: designLevel })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Server error occurred');
                }

                currentHtml = data.html || '';
                currentCss = data.css || '';

                // Render Preview
                renderPreview(currentHtml, currentCss);

                // Render separated codes
                htmlBlock.textContent = currentHtml;
                cssBlock.textContent = currentCss;

                // Re-highlight both
                delete htmlBlock.dataset.highlighted;
                delete cssBlock.dataset.highlighted;
                hljs.highlightElement(htmlBlock);
                hljs.highlightElement(cssBlock);

            } catch (err) {
                alert('Hata: ' + err.message);
                emptyState.style.display = 'flex'; // Reset on error
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Oluştur`;
                overlay.style.display = 'none';
            }
        });
    </script>
</body>

</html>