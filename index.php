<?php
/**
 * AI Araç Kutusu — Ana Sayfa
 * Araçları usage_count'a göre azalan sırayla listeler.
 */
require_once __DIR__ . '/config.php';

// Araçları çek
$stmt = $pdo->query('SELECT id, name, description, slug, usage_count FROM tools ORDER BY usage_count DESC');
$tools = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="AI Araç Kutusu — Hayal gücünü ve üretkenliğini artıracak dijital araçlar. Satranç, kod analizi ve daha fazlası.">
    <title>AI Araç Kutusu</title>
    <link rel="canonical" href="<?= BASE_URL ?>/index.php">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-primary: #0f0f1a;
            --bg-card: #1a1a2e;
            --accent: #6c63ff;
            --accent-hover: #5a52e0;
            --text-primary: #e8e8f0;
            --text-secondary: #9d9db5;
            --border-glow: rgba(108, 99, 255, 0.3);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ── Hero ── */
        .hero {
            text-align: center;
            padding: 6rem 1rem 4rem;
            background: radial-gradient(circle at center, #1a1a2e 0%, #0f0f1a 100%);
        }

        .hero h1 {
            font-weight: 800;
            font-size: 3.2rem;
            background: linear-gradient(135deg, #fff 0%, #a5aab0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            letter-spacing: -1px;
        }

        .hero p {
            color: var(--text-secondary);
            font-size: 1.25rem;
            max-width: 640px;
            margin: 0 auto;
            line-height: 1.6;
            opacity: 0.9;
        }

        /* ── Araç Kartları ── */
        .tool-card {
            background: var(--bg-card);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 2rem;
            transition: transform .25s ease, box-shadow .25s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .tool-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px var(--border-glow);
        }

        .tool-card .icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--accent), #e040fb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.2rem;
        }

        .tool-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: .6rem;
        }

        .tool-card p {
            color: var(--text-secondary);
            font-size: .92rem;
            flex-grow: 1;
        }

        .tool-card .badge-usage {
            font-size: .78rem;
            background: rgba(108, 99, 255, 0.15);
            color: var(--accent);
            border-radius: 20px;
            padding: .35rem .75rem;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .tool-card .btn-tool {
            background: linear-gradient(135deg, var(--accent), #5a52e0);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .65rem 1.5rem;
            font-weight: 600;
            transition: opacity .2s;
            text-decoration: none;
            text-align: center;
        }

        .tool-card .btn-tool:hover {
            opacity: .85;
            color: #fff;
        }

        /* ── Footer ── */
        footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
            font-size: .85rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: 3rem;
        }

        /* ── Boş durum ── */
        .empty-state {
            text-align: center;
            color: var(--text-secondary);
            padding: 4rem 1rem;
        }
    </style>
</head>

<body>

    <!-- Hero -->
    <section class="hero">
        <h1>AI Araç Kutusu</h1>
        <p>Hayal gücünü ve üretkenliğini artıracak dijital araçlar. Yazılım, oyun ve analiz — tek bir çatı altında.</p>
    </section>

    <!-- Araç Listesi -->
    <div class="container py-5">
        <div class="row g-4">
            <?php if (empty($tools)): ?>
                <div class="col-12 empty-state">
                    <h4>Henüz araç eklenmemiş.</h4>
                    <p>Lütfen setup/ klasörüne giderek kurulumu tamamlayın.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tools as $tool): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="tool-card">
                            <div class="icon-wrapper">
                                <?php
                                $icons = ['chess' => '♟️', 'code' => '💻', 'prompt' => '✨', 'spotify' => '🎵', 'ui' => '🎨'];
                                if ($tool['slug'] === 'qr') {
                                    echo '<svg width="28" height="28" viewBox="0 0 24 24" fill="white"><rect x="2" y="2" width="8" height="8" rx="1"/><rect x="14" y="2" width="8" height="8" rx="1"/><rect x="2" y="14" width="8" height="8" rx="1"/><rect x="4" y="4" width="4" height="4" fill="#1a1a2e"/><rect x="16" y="4" width="4" height="4" fill="#1a1a2e"/><rect x="4" y="16" width="4" height="4" fill="#1a1a2e"/><rect x="14" y="14" width="3" height="3"/><rect x="19" y="14" width="3" height="3"/><rect x="14" y="19" width="3" height="3"/><rect x="19" y="19" width="3" height="3"/></svg>';
                                } else {
                                    echo $icons[$tool['slug']] ?? '🔧';
                                }
                                ?>
                            </div>
                            <span class="badge-usage">
                                <?= number_format($tool['usage_count']) ?> kullanım
                            </span>
                            <h3>
                                <?= htmlspecialchars($tool['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <p>
                                <?= htmlspecialchars($tool['description'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <a href="<?= htmlspecialchars($tool['slug'], ENT_QUOTES, 'UTF-8') ?>/" class="btn-tool">
                                Aracı Kullan →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        &copy;
        <?= date('Y') ?> AI Araç Kutusu — Tüm hakları saklıdır.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>