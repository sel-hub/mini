<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kod Analizcisi — AI Araç Kutusu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500&display=swap"
        rel="stylesheet">
    <!-- Highlight.js Theme -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">

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
            padding: 2rem 0;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .code-input {
            font-family: 'Fira Code', monospace;
            background: #1a1a2e;
            color: #dcdccc;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            width: 100%;
            height: 400px;
            padding: 1rem;
            resize: vertical;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .code-input:focus {
            outline: none;
            border-color: #6c63ff;
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
        }

        .analysis-output {
            background: #1a1a2e;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 2rem;
            min-height: 200px;
            max-height: 500px;
            overflow-y: auto;
            line-height: 1.7;
        }

        .analysis-output h1,
        .analysis-output h2,
        .analysis-output h3 {
            color: #fff;
            margin-top: 1.5rem;
        }

        .analysis-output p {
            color: #cbd5e1;
        }

        .analysis-output code {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: 'Fira Code', monospace;
            font-size: 0.9em;
            color: #6c63ff;
        }

        .analysis-output pre code {
            background: transparent;
            padding: 0;
            color: inherit;
        }

        .analysis-output pre {
            background: #0d0d15;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
        }

        .btn-analyze {
            background: linear-gradient(135deg, #6c63ff, #e040fb);
            border: none;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-analyze:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(108, 99, 255, 0.4);
            color: white;
        }

        .loading-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 15, 26, 0.8);
            justify-content: center;
            align-items: center;
            border-radius: 12px;
            z-index: 10;
        }

        .card-wrapper {
            position: relative;
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
        <!-- Kod Girişi -->
        <div class="mb-4">
            <h4 class="mb-3">💻 Kodunu Yapıştır</h4>
            <div class="card-wrapper">
                <textarea class="code-input" id="codeInput"
                    placeholder="// Buraya herhangi bir dilde kod yapıştır...&#10;function example() {&#10;  console.log('Hello World');&#10;}"></textarea>
            </div>
            <div class="mt-3 text-center">
                <button class="btn btn-analyze" id="btnAnalyze">
                    ✨ Analiz Et & Açıkla
                </button>

            </div>
        </div>

        <!-- Analiz Sonucu -->
        <div>
            <h4 class="mb-3">🔍 Yapay Zeka Yorumu</h4>
            <div class="card-wrapper">
                <div class="analysis-output" id="outputArea">
                    <div class="text-center text-muted py-5">
                        <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">🤖</div>
                        <p>Henüz bir kod analiz edilmedi.<br>Yukarıya kodunu yapıştır ve butona tıkla.</p>
                    </div>
                </div>
                <!-- Loading -->
                <div class="loading-overlay" id="loadingOverlay">
                    <div class="text-center">
                        <div class="spinner-border text-light mb-3" role="status"></div>
                        <h5 class="text-white">Kod İnceleniyor...</h5>
                        <p class="text-white-50 small">Bu işlem karmaşıklığa göre 5-10 saniye sürebilir.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

    <script>
        const btnAnalyze = document.getElementById('btnAnalyze');
        const codeInput = document.getElementById('codeInput');
        const outputArea = document.getElementById('outputArea');
        const loadingOverlay = document.getElementById('loadingOverlay');

        btnAnalyze.addEventListener('click', async () => {
            const code = codeInput.value.trim();
            if (!code) {
                alert('Lütfen analiz edilecek bir kod parçası girin.');
                return;
            }

            // UI Loading
            loadingOverlay.style.display = 'flex';
            btnAnalyze.disabled = true;

            try {
                const formData = new FormData();
                formData.append('code', code);

                // Fetch API
                const response = await fetch('api/explain.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Render Markdown
                    outputArea.innerHTML = marked.parse(data.explanation);
                    // Highlight blocks
                    outputArea.querySelectorAll('pre code').forEach((block) => {
                        hljs.highlightElement(block);
                    });
                } else {
                    outputArea.innerHTML = `<div class="alert alert-danger">❌ Hata: ${data.error || 'Bilinmeyen bir hata oluştu.'}</div>`;
                }

            } catch (err) {
                console.error(err);
                outputArea.innerHTML = `<div class="alert alert-danger">❌ Sunucu hatası: ${err.message}</div>`;
            } finally {
                loadingOverlay.style.display = 'none';
                btnAnalyze.disabled = false;
            }
        });
    </script>
</body>

</html>