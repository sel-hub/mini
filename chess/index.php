<?php
/**
 * AI Satranç Analizcisi — chess.php
 * Solda tahta + Sağda notasyon girişi + Oynatma + AI Analiz.
 */
require_once __DIR__ . '/../config.php';

$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="AI Satranç Analizcisi — Hamle notasyonu girin, tahtada izleyin, yapay zeka ile analiz edin.">
    <title>AI Satranç Analizcisi — AI Araç Kutusu</title>
    <link rel="canonical" href="<?= BASE_URL ?>/chess.php">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- chessboard.js CSS -->
    <link href="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-primary: #0f0f1a;
            --bg-card: #1a1a2e;
            --bg-card-alt: #16213e;
            --accent: #6c63ff;
            --accent-hover: #5a52e0;
            --accent-glow: rgba(108, 99, 255, 0.25);
            --green: #2ed573;
            --green-glow: rgba(46, 213, 115, 0.2);
            --orange: #ffa502;
            --orange-glow: rgba(255, 165, 2, 0.15);
            --red: #ff4757;
            --text-primary: #e8e8f0;
            --text-secondary: #9d9db5;
            --text-muted: #6b6b85;
            --border-subtle: rgba(255, 255, 255, 0.06);
            --border-glow: rgba(108, 99, 255, 0.3);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .navbar-custom {
            background: rgba(15, 15, 26, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-subtle);
        }

        .navbar-custom .navbar-brand {
            font-weight: 800;
            background: linear-gradient(135deg, #6c63ff, #e040fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar-custom .nav-link {
            color: var(--text-secondary) !important;
            transition: color .2s;
        }

        .navbar-custom .nav-link:hover {
            color: var(--text-primary) !important;
        }

        /* ── Page header ── */
        .page-header {
            text-align: center;
            padding: 2.5rem 1rem 1.2rem;
        }

        .page-header h1 {
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: .4rem;
        }

        .page-header p {
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
            font-size: .95rem;
        }

        /* ── Panel cards ── */
        .panel {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 1.5rem;
            height: 100%;
        }

        /* ── Board ── */
        .board-wrap {
            max-width: 480px;
            margin: 0 auto;
        }

        /* ── Turn indicator ── */
        .turn-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .85rem;
            border-radius: 20px;
            font-size: .82rem;
            font-weight: 600;
        }

        .turn-white {
            background: #fff;
            color: #111;
        }

        .turn-black {
            background: #333;
            color: #fff;
        }

        /* ── Textarea ── */
        .notation-input {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            padding: .8rem 1rem;
            font-family: 'Courier New', monospace;
            font-size: .92rem;
            line-height: 1.6;
            resize: vertical;
            min-height: 140px;
            width: 100%;
            transition: border-color .2s, box-shadow .2s;
        }

        .notation-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
            outline: none;
            background: rgba(255, 255, 255, 0.06);
        }

        .notation-input::placeholder {
            color: var(--text-muted);
        }

        /* ── Buttons ── */
        .btn-action {
            border: none;
            border-radius: 10px;
            padding: .65rem 1.4rem;
            font-weight: 700;
            font-size: .92rem;
            cursor: pointer;
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .btn-action:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .btn-play {
            background: linear-gradient(135deg, var(--green), #1faa59);
            color: #fff;
        }

        .btn-play:hover:not(:disabled) {
            box-shadow: 0 4px 20px var(--green-glow);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-ai {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: #fff;
        }

        .btn-ai:hover:not(:disabled) {
            box-shadow: 0 4px 20px var(--accent-glow);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.06);
            color: var(--text-secondary);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary-custom:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }

        .btn-show {
            background: linear-gradient(135deg, var(--orange), #e69500);
            color: #fff;
            font-size: .78rem;
            padding: .35rem .8rem;
            border-radius: 8px;
        }

        .btn-show:hover:not(:disabled) {
            box-shadow: 0 4px 15px var(--orange-glow);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-back {
            background: rgba(255, 71, 87, 0.12);
            color: var(--red);
            border: 1px solid rgba(255, 71, 87, 0.25);
            font-size: .85rem;
            padding: .5rem 1rem;
        }

        .btn-back:hover {
            background: rgba(255, 71, 87, 0.2);
            color: var(--red);
        }

        /* ── Move log ── */
        .move-log {
            max-height: 110px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: .7rem .9rem;
            font-family: 'Courier New', monospace;
            font-size: .85rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .move-log .move-current {
            color: var(--green);
            font-weight: 700;
        }

        /* ── Analysis result ── */
        .analysis-box {
            background: rgba(108, 99, 255, 0.06);
            border: 1px solid rgba(108, 99, 255, 0.15);
            border-radius: 14px;
            padding: 1.2rem;
            line-height: 1.65;
            font-size: .9rem;
        }

        .analysis-box .overall-comment {
            font-size: .95rem;
            color: var(--text-primary);
            margin-bottom: 1rem;
            padding-bottom: .8rem;
            border-bottom: 1px solid var(--border-subtle);
        }

        .analysis-box .move-analysis-item {
            padding: .5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            display: flex;
            align-items: flex-start;
            gap: .6rem;
        }

        .analysis-box .move-analysis-item:last-child {
            border-bottom: none;
        }

        .move-num-badge {
            background: rgba(108, 99, 255, 0.15);
            color: var(--accent);
            font-weight: 700;
            font-size: .75rem;
            padding: .2rem .5rem;
            border-radius: 6px;
            white-space: nowrap;
            min-width: 50px;
            text-align: center;
            flex-shrink: 0;
        }

        .move-detail {
            flex: 1;
        }

        .move-detail .played-move {
            font-weight: 700;
            color: var(--text-primary);
        }

        .move-detail .move-comment {
            color: var(--text-secondary);
            font-size: .84rem;
        }

        .alt-suggestion {
            margin-top: .3rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .83rem;
            color: var(--orange);
        }

        .alt-suggestion .alt-text {
            flex: 1;
        }

        /* ── Status banner ── */
        .status-banner {
            padding: .45rem .9rem;
            border-radius: 10px;
            font-size: .82rem;
            font-weight: 600;
            text-align: center;
        }

        .status-playing {
            background: rgba(46, 213, 115, 0.1);
            color: var(--green);
            border: 1px solid rgba(46, 213, 115, 0.2);
        }

        .status-done {
            background: rgba(108, 99, 255, 0.1);
            color: var(--accent);
            border: 1px solid rgba(108, 99, 255, 0.2);
        }

        .status-alt {
            background: rgba(255, 165, 2, 0.1);
            color: var(--orange);
            border: 1px solid rgba(255, 165, 2, 0.2);
        }

        .status-error {
            background: rgba(255, 71, 87, 0.08);
            color: var(--red);
            border: 1px solid rgba(255, 71, 87, 0.2);
        }

        /* ── Spinner ── */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* ── Scrollbar ── */
        .move-log::-webkit-scrollbar,
        .analysis-box::-webkit-scrollbar {
            width: 5px;
        }

        .move-log::-webkit-scrollbar-thumb,
        .analysis-box::-webkit-scrollbar-thumb {
            background: rgba(108, 99, 255, 0.3);
            border-radius: 10px;
        }

        /* ── Section label ── */
        .section-label {
            font-weight: 700;
            font-size: .88rem;
            color: var(--text-secondary);
            margin-bottom: .5rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        /* ── Footer ── */
        footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
            font-size: .85rem;
            border-top: 1px solid var(--border-subtle);

            /* ── Responsive ── */
            @media (max-width: 991px) {
                .page-header {
                    padding: 1.5rem 1rem 1rem;
                }

                .page-header h1 {
                    font-size: 1.5rem;
                }
            }

            /* ── Click-and-Point Highlights ── */
            .square-55d63 {
                position: relative;
            }

            .highlight-selected {
                box-shadow: inset 0 0 3px 3px rgba(255, 255, 0, 0.5) !important;
                background: rgba(255, 255, 0, 0.2) !important;
            }

            .highlight-dest {
                /* Sadece class marker olarak kalsın */
            }

            .dest-dot {
                position: absolute;
                width: 40%;
                height: 40%;
                background: rgba(100, 255, 100, 0.8);
                border-radius: 50%;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 10000;
                pointer-events: none;
                box-shadow: 0 0 5px #000;
                border: 2px solid rgba(255, 255, 255, 0.5);
            }
        }

        /* ── Eval Bar ── */
        #evalBarContainer {
            display: block !important;
            min-height: 480px;
            height: 100%;
            flex-shrink: 0;
            width: 30px !important;
            background-color: #222;
            border: 1px solid #444;
            margin-right: 15px;
        }

        /* ── Polishing ── */
        .pulsing {
            animation: pulse-purple 2s infinite;
        }

        @keyframes pulse-purple {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 15px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        #narrationSubtitle {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 15, 26, 0.9);
            color: #fff;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 500;
            pointer-events: none;
            z-index: 10002;
            display: none;
            text-align: center;
            width: auto;
            min-width: 200px;
            max-width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            line-height: 1.4;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-custom navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/">
                AI Araç Kutusu
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Ana Sayfa</a></li>
            </ul>
        </div>
    </nav>

    <!-- Header -->
    <section class="page-header">
        <h1>♟️ AI Satranç Analizcisi</h1>
        <p>Hamle notasyonunu girin, tahtada izleyin ve yapay zeka ile her hamleyi analiz ettirin.</p>
    </section>

    <div class="container pb-5">
        <div class="row g-4">

            <!-- ═══ SOL: Satranç Tahtası + Eval Bar ═══ -->
            <div class="col-lg-6">
                <div class="panel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="turn-badge turn-white" id="turnIndicator">⬜ Beyaz oynar</span>
                        <div class="d-flex gap-2">
                            <span id="openingName" class="badge bg-secondary"
                                style="display:none; font-weight:500;"></span>
                            <button class="btn-action btn-secondary-custom" id="btnReset" type="button"
                                style="padding:.3rem .8rem; font-size:.8rem;">↺ Sıfırla</button>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <!-- Eval Bar -->
                        <div id="evalBarContainer"
                            style="width: 30px; background: #222; border-radius: 4px; position: relative; min-height: 480px;">
                            <div id="evalBarFill"
                                style="width: 100%; background: #fff; position: absolute; bottom: 0; height: 50%; transition: height 0.5s;">
                            </div>
                            <div id="evalScore"
                                style="position: absolute; width: 100%; text-align: center; font-size: 10px; font-weight: bold; color: #555; top: 50%; transform: translateY(-50%); mix-blend-mode: difference; color: #fff;">
                                0.0</div>
                        </div>

                        <!-- Tahta -->
                        <div class="board-wrap flex-grow-1" style="position: relative;">
                            <div id="chessBoard"></div>
                            <div id="boardOverlay"
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;">
                            </div>
                            <div id="narrationSubtitle"></div>
                        </div>
                    </div>

                    <!-- Durum Banner -->
                    <div id="statusBanner" class="mt-3" style="display:none;"></div>

                    <!-- Hamle Log -->
                    <div id="moveLog" class="move-log mt-3" style="display:none;"></div>
                </div>
            </div>

            <!-- ═══ SAĞ: Notasyon + Kontroller + AI Analiz ═══ -->
            <div class="col-lg-6">
                <div class="panel d-flex flex-column gap-3">

                    <!-- Notasyon Girişi -->
                    <div>
                        <div class="section-label">📝 Hamle Notasyonu</div>
                        <textarea class="notation-input" id="notationInput" rows="6"
                            placeholder="Her satıra bir hamle çifti yazın:&#10;e4 c5&#10;Vh5 g5&#10;Fc4 g4&#10;Vxf7#"></textarea>
                        <div style="font-size:.75rem; color:var(--text-muted); margin-top:.3rem;">
                            Türkçe: V=Vezir, F=Fil, A=At, K=Kale, Ş=Şah &nbsp;|&nbsp; x=alış, +=şah, #=mat
                        </div>
                    </div>

                    <!-- CSRF token -->
                    <input type="hidden" id="csrfToken"
                        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Butonlar -->
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn-action btn-play" id="btnPlay" type="button">▶ Başlat</button>
                        <button class="btn-action btn-ai" id="btnAnalyze" type="button" disabled>🤖 AI Analiz</button>
                        <button class="btn-action btn-secondary-custom" id="btnShare" type="button"
                            style="display:none;">🔗 Paylaş</button>
                        <button class="btn-action btn-primary-custom" id="btnNarrate" type="button"
                            style="display:none; background: #6f42c1; color: white;">🎙️ Anlat</button>
                        <button class="btn-action btn-back" id="btnBackToReal" type="button" style="display:none;">↩
                            Gerçek Pozisyona Dön</button>
                    </div>

                    <!-- Ses Seçimi (Gizli) -->
                    <div id="voiceSettingsContainer" style="display:none;">
                        <select id="voiceSelect" style="display:none;"></select>
                    </div>

                    <!-- AI Analiz Sonuçları -->
                    <div id="analysisContainer" style="display:none;">
                        <div class="section-label">📊 AI Analizi</div>
                        <div class="analysis-box" id="analysisContent"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> AI Araç Kutusu — Tüm hakları saklıdır.
    </footer>

    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
    <script src="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.js"></script>

    <script>
        (function () {
            'use strict';

            // ── Chess engine ──
            const game = new Chess();
            let playbackInterval = null;
            let isPlaying = false;
            let parsedMoves = [];
            let currentMoveIndex = 0;
            let realFen = null;        // Gerçek pozisyonu sakla (alternatif gösterimden dönmek için)
            let realGamePgn = null;
            let showingAlternative = false;

            // ── UI elements ──
            const notationInput = document.getElementById('notationInput');
            const btnPlay = document.getElementById('btnPlay');
            const btnAnalyze = document.getElementById('btnAnalyze');
            const btnReset = document.getElementById('btnReset');
            const btnShare = document.getElementById('btnShare');
            const btnBackToReal = document.getElementById('btnBackToReal');
            const turnIndicator = document.getElementById('turnIndicator');
            const openingLabel = document.getElementById('openingName');
            const csrfToken = document.getElementById('csrfToken').value;
            const moveLogDiv = document.getElementById('moveLog');
            const statusBanner = document.getElementById('statusBanner');
            const analysisContainer = document.getElementById('analysisContainer');
            const analysisContent = document.getElementById('analysisContent');

            // Eval Bar elements
            const evalBarContainer = document.getElementById('evalBarContainer');
            const evalBarFill = document.getElementById('evalBarFill');
            const evalScore = document.getElementById('evalScore');

            let stockfish = null;

            // ── Stockfish Init ──
            let engineReady = false;

            function initStockfish() {
                // UI Debug Helper
                const statusBanner = document.getElementById('statusBanner');
                if (statusBanner) statusBanner.style.display = 'block';

                const logUI = (msg, color = 'white') => {
                    if (statusBanner) statusBanner.innerHTML = `<span style="color:${color}">${msg}</span>`;
                    console.log('SF UI:', msg);
                };

                logUI('Stockfish v10 Başlatılıyor...', 'orange');

                const sfUrl = 'https://cdnjs.cloudflare.com/ajax/libs/stockfish.js/10.0.0/stockfish.js';

                fetch(sfUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.text();
                    })
                    .then(scriptContent => {
                        logUI('Worker hazırlanıyor...', 'cyan');
                        const blob = new Blob([scriptContent], { type: 'application/javascript' });
                        const workerUrl = URL.createObjectURL(blob);
                        stockfish = new Worker(workerUrl);

                        stockfish.onerror = function (e) {
                            logUI('Worker Hatası: ' + e.message, 'red');
                        };

                        stockfish.onmessage = function (event) {
                            const line = event.data;

                            if (line === 'uciok') {
                                stockfish.postMessage('isready');
                                logUI('Motor hazır (uciok)...', 'cyan');
                            }
                            if (line === 'readyok') {
                                engineReady = true;
                                logUI('Analiz Başladı!', '#0f0');
                                startEval();
                            }

                            if (line.startsWith('info') && line.includes('score')) {
                                const matchCp = line.match(/score cp (-?\d+)/);
                                const matchMate = line.match(/score mate (-?\d+)/);

                                if (matchMate) {
                                    const mate = parseInt(matchMate[1]);
                                    updateEvalBar(mate > 0 ? 2000 : -2000, true);
                                } else if (matchCp) {
                                    let cp = parseInt(matchCp[1]);
                                    if (game.turn() === 'b') cp = -cp;
                                    updateEvalBar(cp);
                                }
                            }
                        };

                        stockfish.postMessage('uci');

                        // Fallback: 3 saniye içinde ready gelmezse zorla başlat
                        setTimeout(() => {
                            if (!engineReady) {
                                logUI('Zorla Başlatılıyor (Timeout)...', 'orange');
                                engineReady = true;
                                startEval();
                            }
                        }, 3000);
                    })
                    .catch(err => {
                        logUI('Yükleme Hatası: ' + err.message, 'red');
                    });
            }

            function startEval() {
                if (!stockfish) return;
                stockfish.postMessage('stop');
                stockfish.postMessage('position fen ' + game.fen());
                stockfish.postMessage('go depth 15'); // Hızlı analiz
            }
            // ── Board config (Click-and-Point) ──
            let selectedSquare = null;

            function removeHighlights() {
                $('#chessBoard .square-55d63').removeClass('highlight-selected highlight-dest');
                $('#boardOverlay').empty(); // Overlay temizle
            }

            function highlightSquare(square, type) {
                const $square = $('#chessBoard .square-' + square);

                if (type === 'selected') {
                    $square.addClass('highlight-selected');
                } else {
                    // Overlay Dot Injection (Kesin Çözüm)
                    if ($square.length) {
                        const pos = $square.position();
                        const dotSize = $square.width() * 0.4;

                        const dot = $('<div class="dest-dot"></div>');
                        dot.css({
                            width: dotSize,
                            height: dotSize,
                            position: 'absolute',
                            top: pos.top + ($square.height() / 2),
                            left: pos.left + ($square.width() / 2),
                            transform: 'translate(-50%, -50%)',
                            background: 'rgba(100, 255, 100, 0.9)',
                            borderRadius: '50%',
                            zIndex: 10000,
                            boxShadow: '0 0 5px black',
                            border: '2px solid rgba(255,255,255,0.8)',
                            pointerEvents: 'none'
                        });

                        $('#boardOverlay').append(dot);
                    }
                }
            }

            // Click handler (Delegation)
            $('#chessBoard').on('click', '.square-55d63', function () {
                if (isPlaying || showingAlternative) return;
                if (game.game_over()) return;

                const square = $(this).data('square');
                const piece = game.get(square);
                const turn = game.turn(); // 'w' or 'b'

                try {
                    // 1. Eğer bir kare seçiliyse ve hedef kareye tıklandıysa (Hamle veya Yeni Seçim)
                    if (selectedSquare) {
                        // Aynı kareye tıklandıysa -> Seçimi kaldır
                        if (selectedSquare === square) {
                            selectedSquare = null;
                            removeHighlights();
                            return;
                        }

                        // Hamle mi?
                        const move = game.move({
                            from: selectedSquare,
                            to: square,
                            promotion: 'q' // Basitlik için vezir
                        });

                        if (move) {
                            // Geçerli hamle!
                            board.position(game.fen());
                            updateStatus();
                            appendMoveToInput(move);
                            startEval();
                            btnAnalyze.disabled = false;

                            selectedSquare = null;
                            removeHighlights();
                            return;
                        }

                        // Geçersiz hamle ama belki yeni bir taş seçmek istiyor?
                        // Eğer tıklanan karede kendi taşı varsa, seçimi değiştir
                        if (piece && piece.color === turn) {
                            selectedSquare = square;
                            removeHighlights();
                            highlightSquare(square, 'selected');

                            // Olası hamleleri göster
                            const moves = game.moves({
                                square: square,
                                verbose: true
                            });
                            for (const m of moves) {
                                highlightSquare(m.to, 'dest');
                            }
                        } else {
                            // Boş veya rakip taş (ama hamle geçersizdi) -> Seçimi kaldır
                            selectedSquare = null;
                            removeHighlights();
                        }

                    } else {
                        // 2. Hiçbir kare seçili değilse -> Taş Seçimi
                        if (piece && piece.color === turn) {
                            selectedSquare = square;
                            removeHighlights();
                            highlightSquare(square, 'selected');

                            // Olası hamleleri göster
                            const moves = game.moves({
                                square: square,
                                verbose: true
                            });
                            for (const m of moves) {
                                highlightSquare(m.to, 'dest');
                            }
                        }
                    }
                } catch (e) {
                    console.error('Move handling error:', e);
                    alert('Hamle hatası: ' + e.message);
                }
            });

            function updateStatus() {
                updateTurnIndicator();
                checkGameEnd();
            }

            function onSnapEnd() {
                // board.position(game.fen()); // Artık gerek yok, manuel yapıyoruz
            }

            const boardConfig = {
                draggable: false, // Click-and-Point sadece
                position: 'start',
                pieceTheme: 'https://lichess1.org/assets/piece/cburnett/{piece}.svg',
                animationDuration: 200
            };
            const board = Chessboard('chessBoard', boardConfig);


            // ══════════════════════════════════════════════
            // Türkçe → SAN dönüşümü
            // ══════════════════════════════════════════════
            function appendMoveToInput(move) {
                // Mevcut metni al
                let text = notationInput.value.trim();
                const moveSan = move.san;

                // Türkçe'ye çevir (örn Nf3 -> Af3)
                const turkSan = sanToTurkish(moveSan);

                // Hamle numarası?
                // game.history().length şu anki toplam yarı-hamle sayısı
                const halfMoves = game.history().length;
                const moveNum = Math.ceil(halfMoves / 2);

                if (game.turn() === 'b') {
                    // Beyaz oynadı (sıra siyaha geçti)
                    // Yeni satır veya devam?
                    if (text === '') {
                        text += moveNum + ". " + turkSan;
                    } else {
                        text += "\n" + moveNum + ". " + turkSan;
                    }
                } else {
                    // Siyah oynadı
                    text += " " + turkSan;
                }

                notationInput.value = text;
                notationInput.scrollTop = notationInput.scrollHeight;
            }

            function sanToTurkish(san) {
                if (!san) return '';
                // K (King) -> Ş, Q -> V, R -> K, B -> F, N -> A
                let s = san;
                s = s.replace('N', 'A');
                s = s.replace('B', 'F');
                s = s.replace('R', 'K');
                s = s.replace('Q', 'V');
                s = s.replace('K', 'Ş');
                return s;
            }

            function turkishToSAN(move) {
                if (!move) return move;
                // V→Q, F→B, A→N, K→R, Ş→K
                let san = move;
                // Büyük harfle başlayan taş sembolleri
                san = san.replace(/^Ş/, 'K');   // Şah
                san = san.replace(/^V/, 'Q');   // Vezir
                san = san.replace(/^F/, 'B');   // Fil
                san = san.replace(/^A/, 'N');   // At
                san = san.replace(/^K/, 'R');   // Kale
                // Rok notasyonu koruma
                // 0-0-0 veya 0-0 veya O-O-O / O-O zaten SAN formatında
                return san;
            }

            // ══════════════════════════════════════════════
            // Notasyonu ayrıştır
            // ══════════════════════════════════════════════
            function parseNotation(text) {
                const moves = [];
                const lines = text.trim().split('\n');

                for (const line of lines) {
                    let cleaned = line.trim();
                    if (!cleaned) continue;

                    // Hamle numarasını temizle: "1. e4 c5" → "e4 c5"
                    cleaned = cleaned.replace(/^\d+\.\s*/, '');

                    // Boşlukla ayır
                    const parts = cleaned.split(/\s+/);

                    for (const part of parts) {
                        if (!part) continue;
                        const san = turkishToSAN(part);
                        moves.push({ original: part, san: san });
                    }
                }

                return moves;
            }

            // ══════════════════════════════════════════════
            // Durumu güncelle
            // ══════════════════════════════════════════════
            function updateTurnIndicator() {
                if (game.turn() === 'w') {
                    turnIndicator.className = 'turn-badge turn-white';
                    turnIndicator.textContent = '⬜ Beyaz oynar';
                } else {
                    turnIndicator.className = 'turn-badge turn-black';
                    turnIndicator.textContent = '⬛ Siyah oynar';
                }
            }

            function showStatus(text, type) {
                statusBanner.style.display = 'block';
                statusBanner.className = 'mt-3 status-banner status-' + type;
                statusBanner.textContent = text;
            }

            function hideStatus() {
                statusBanner.style.display = 'none';
            }

            function updateMoveLog() {
                if (parsedMoves.length === 0) {
                    moveLogDiv.style.display = 'none';
                    return;
                }
                moveLogDiv.style.display = 'block';
                let html = '';
                for (let i = 0; i < parsedMoves.length; i++) {
                    const m = parsedMoves[i];
                    const isCurrent = (i === currentMoveIndex - 1);
                    const isPlayed = (i < currentMoveIndex);

                    if (i % 2 === 0) {
                        html += '<span style="color:var(--text-muted);">' + (Math.floor(i / 2) + 1) + '.</span> ';
                    }

                    if (isCurrent) {
                        html += '<span class="move-current">' + m.original + '</span>';
                    } else if (isPlayed) {
                        html += '<span style="color:var(--text-primary);">' + m.original + '</span>';
                    } else {
                        html += '<span>' + m.original + '</span>';
                    }

                    html += ' ';
                    if (i % 2 === 1) html += '&nbsp;&nbsp;';
                }
                moveLogDiv.innerHTML = html;
                moveLogDiv.scrollTop = moveLogDiv.scrollHeight;
            }

            // ══════════════════════════════════════════════
            // ▶ BAŞLAT / ⏸ DURDUR
            // ══════════════════════════════════════════════
            btnPlay.addEventListener('click', function () {
                if (isPlaying) {
                    stopPlayback();
                    return;
                }

                const text = notationInput.value.trim();
                if (!text) {
                    showStatus('⚠️ Lütfen hamle notasyonu girin.', 'error');
                    return;
                }

                // Yeni oynatma
                game.reset();
                board.start(false);
                parsedMoves = parseNotation(text);
                currentMoveIndex = 0;
                showingAlternative = false;
                btnBackToReal.style.display = 'none';
                analysisContainer.style.display = 'none';

                if (parsedMoves.length === 0) {
                    showStatus('⚠️ Geçerli hamle bulunamadı.', 'error');
                    return;
                }

                startPlayback();
            });

            // ══════════════════════════════════════════════
            // Eval Bar Güncelle
            // ══════════════════════════════════════════════
            function updateEvalBar(score, isMate = false) {
                const barFill = document.getElementById('evalBarFill');
                const barScore = document.getElementById('evalScore');
                if (!barFill || !barScore) return;

                let percent = 50;
                let text = '0.0';

                if (isMate) {
                    percent = (score > 0) ? 100 : 0;
                    text = (score > 0 ? '+M' : '-M');
                } else {
                    // Lineer: -2000 ile +2000 arası %0-%100
                    const clamped = Math.max(-2000, Math.min(2000, score));
                    percent = ((clamped + 2000) / 4000) * 100;

                    text = (score / 100).toFixed(1);
                    if (score > 0) text = '+' + text;
                }

                barFill.style.height = percent + '%';
                barScore.innerText = text;
            }

            function startPlayback() {
                isPlaying = true;
                btnPlay.innerHTML = '⏸ Durdur';
                btnAnalyze.disabled = true;
                showStatus('▶ Oynatılıyor... (' + currentMoveIndex + '/' + parsedMoves.length + ')', 'playing');
                updateMoveLog();

                playbackInterval = setInterval(function () {
                    if (currentMoveIndex >= parsedMoves.length) {
                        stopPlayback();
                        checkGameEnd();
                        return;
                    }

                    const move = parsedMoves[currentMoveIndex];
                    const result = game.move(move.san);

                    if (result === null) {
                        stopPlayback();
                        showStatus('❌ Geçersiz hamle: ' + move.original + ' (hamle #' + (currentMoveIndex + 1) + ')', 'error');
                        return;
                    }

                    currentMoveIndex++;
                    board.position(game.fen(), true); // animate=true
                    updateTurnIndicator();
                    updateMoveLog();
                    startEval(); // Stockfish her hamlede çalışsın
                    showStatus('▶ Oynatılıyor... (' + currentMoveIndex + '/' + parsedMoves.length + ')', 'playing');

                }, 1200);
            }

            function stopPlayback() {
                isPlaying = false;
                clearInterval(playbackInterval);
                playbackInterval = null;
                btnPlay.innerHTML = '▶ Başlat';

                if (currentMoveIndex > 0) {
                    btnAnalyze.disabled = false;
                    showStatus('✅ Oynatma tamamlandı — ' + currentMoveIndex + ' hamle oynandı.', 'done');
                }
            }

            function checkGameEnd() {
                if (game.in_checkmate()) {
                    const winner = game.turn() === 'w' ? 'Siyah' : 'Beyaz';
                    showStatus('♚ Şah Mat! ' + winner + ' kazandı.', 'done');
                } else if (game.in_stalemate()) {
                    showStatus('½ Pat — Berabere!', 'done');
                } else if (game.in_draw()) {
                    showStatus('½ Berabere!', 'done');
                }
            }

            // ══════════════════════════════════════════════
            // ↺ SIFIRLA
            // ══════════════════════════════════════════════
            btnReset.addEventListener('click', function () {
                stopPlayback();
                game.reset();
                board.start(false);
                parsedMoves = [];
                currentMoveIndex = 0;
                notationInput.value = '';
                openingLabel.style.display = 'none';
                updateTurnIndicator();
                statusBanner.style.display = 'none';
                analysisContainer.style.display = 'none';
                btnAnalyze.disabled = true;
                btnShare.style.display = 'none';
                if (btnNarrate) btnNarrate.style.display = 'none';
                updateEvalBar(0); // Reset bar

                // Seçimi temizle
                selectedSquare = null;
                removeHighlights();
            });

            // ══════════════════════════════════════════════
            // 🤖 AI ANALİZ
            // ══════════════════════════════════════════════
            btnAnalyze.addEventListener('click', function () {
                const fen = game.fen();
                // Hamle geçmişini SAN listesi olarak hazırla
                const history = game.history();
                const movesStr = history.join(' ');
                const pgn = game.pgn();

                // Kaydet (alternatif gösterimden dönmek için)
                realFen = fen;
                realGamePgn = pgn;

                btnAnalyze.disabled = true;
                btnAnalyze.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Analiz...';
                analysisContainer.style.display = 'none';

                const formData = new FormData();
                formData.append('fen', fen);
                formData.append('moves', movesStr);
                formData.append('csrf_token', csrfToken);

                fetch('api/analyze.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if (data.analysis) {
                                renderStructuredAnalysis(data.analysis);
                                btnShare.style.display = 'inline-flex'; // Paylaş butonu aç
                                if (btnNarrate) btnNarrate.style.display = 'inline-flex';
                                // PGN ve Analizi butona kaydet
                                btnShare.dataset.pgn = pgn;
                                btnShare.dataset.fen = fen;
                                btnShare.dataset.json = JSON.stringify(data.analysis);
                            }
                        } else {
                            renderPlainAnalysis('❌ ' + (data.error || 'Hata oluştu.'));
                        }
                    })
                    .catch(() => {
                        renderPlainAnalysis('❌ Sunucuya bağlanılamadı.');
                    })
                    .finally(() => {
                        btnAnalyze.disabled = false;
                        btnAnalyze.innerHTML = '🤖 AI Analiz';
                    });
            });

            // ── Yapılandırılmış Analiz Render ──
            function renderStructuredAnalysis(analysis) {
                window.currentAnalysis = analysis; // Global erişim için sakla
                analysisContainer.style.display = 'block';

                // Açılış İsmi
                if (analysis.opening) {
                    openingLabel.textContent = analysis.opening;
                    openingLabel.style.display = 'inline-block';
                }

                let html = '';

                // Genel yorum
                if (analysis.overall) {
                    html += '<div class="overall-comment">💡 ' + escapeHtml(analysis.overall) + '</div>';
                }

                // Hamle analizleri
                if (analysis.moves && analysis.moves.length > 0) {
                    for (const m of analysis.moves) {
                        // Terminoloji Düzeltmesi (Q->V, N->A etc.)
                        const playedTR = sanToTurkish(m.played);

                        html += '<div class="move-analysis-item">';
                        html += '<div class="move-num-badge">' + m.moveNum + '. ' + (m.side === 'white' ? '⬜' : '⬛') + '</div>';
                        html += '<div class="move-detail">';
                        html += '<span class="played-move">' + escapeHtml(playedTR) + '</span> ';
                        html += '<span class="move-comment">' + escapeHtml(m.comment) + '</span>';

                        if (m.alternative) {
                            const altTR = sanToTurkish(m.alternative);
                            html += '<div class="alt-suggestion">';
                            html += '<span class="alt-text">💡 Daha iyi: <strong>' + escapeHtml(altTR) + '</strong> — ' + escapeHtml(m.altReason || '') + '</span>';
                            // onclick içinde orijinal SAN kullanılır (move fonksiyonu için better) - ama TR gösterilir
                            // Aslında move fonksiyonu da TR kabul etmez, ingilizce SAN ister. O yüzden m.alternative kalmalı.
                            // showAlternative fonksiyonu game.move(altMove) yaptığı için İNGİLİZCE SAN lazım.
                            html += '<button class="btn-action btn-show" onclick="showAlternative(' + m.moveNum + ',\'' + escapeAttr(m.side) + '\',\'' + escapeAttr(m.alternative) + '\')">👁 Göster</button>';
                            html += '</div>';
                        }

                        html += '</div>';
                        html += '</div>';
                    }
                }

                analysisContent.innerHTML = html;
            }

            // ── Düz metin Analiz Render ──
            function renderPlainAnalysis(text) {
                analysisContainer.style.display = 'block';
                analysisContent.innerHTML = '<div style="white-space:pre-wrap;line-height:1.7;">' + escapeHtml(text) + '</div>';
            }

            // ══════════════════════════════════════════════
            // 🔗 PAYLAŞ
            // ══════════════════════════════════════════════
            btnShare.addEventListener('click', function () {
                const btn = btnShare;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                const formData = new FormData();
                formData.append('fen', btn.dataset.fen);
                formData.append('pgn', btn.dataset.pgn);
                formData.append('analysis_json', btn.dataset.json);
                formData.append('csrf_token', csrfToken);

                fetch('api/save_analysis.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.share_id) {
                            const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
                            const shareUrl = window.location.origin + basePath + 'share.php?id=' + data.share_id;
                            navigator.clipboard.writeText(shareUrl).then(() => {
                                alert('Link kopyalandı!\n' + shareUrl);
                            });
                        } else {
                            // Hata detayını göster
                            console.error('Save error:', data);
                            alert('Hata: ' + (data.error || 'Kaydedilemedi'));
                        }
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        alert('Sunucu hatası: ' + err.message);
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
            });

            // ══════════════════════════════════════════════
            // 👁 ALTERNATİF GÖSTER
            // ══════════════════════════════════════════════
            window.showAlternative = function (moveNum, side, altMove) {
                // Parti başından itibaren hamleleri tekrar oynat, ilgili hamlede alternatifi oyna
                const tempGame = new Chess();
                const history = game.history ? game.history() : [];

                // İlgili hamlenin indeksini bul
                // moveNum=1, side="white" → index 0
                // moveNum=1, side="black" → index 1
                // moveNum=2, side="white" → index 2
                const targetIndex = (moveNum - 1) * 2 + (side === 'black' ? 1 : 0);

                // Hamleleri tekrar oynat, hedef indekste alternatifi kullan
                for (let i = 0; i < Math.min(targetIndex + 1, history.length); i++) {
                    if (i === targetIndex) {
                        const result = tempGame.move(altMove);
                        if (result === null) {
                            showStatus('⚠️ Alternatif hamle uygulanamadı: ' + altMove, 'error');
                            return;
                        }
                    } else {
                        tempGame.move(history[i]);
                    }
                }

                // Tahtayı güncelle
                board.position(tempGame.fen(), true);
                showingAlternative = true;
                btnBackToReal.style.display = 'inline-flex';
                showStatus('🔄 Alternatif gösteriliyor: ' + moveNum + '. hamle → ' + altMove, 'alt');
                updateTurnIndicator.call(null); // turn indicator won't match but that's fine
            };

            // ══════════════════════════════════════════════
            // ↩ GERİ DÖN
            // ══════════════════════════════════════════════
            btnBackToReal.addEventListener('click', function () {
                if (realFen) {
                    board.position(realFen, true);
                    showingAlternative = false;
                    btnBackToReal.style.display = 'none';
                    showStatus('✅ Gerçek pozisyona geri dönüldü.', 'done');
                }
            });

            // ══════════════════════════════════════════════
            // Yardımcılar
            // ══════════════════════════════════════════════
            function escapeHtml(str) {
                if (!str) return '';
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function escapeAttr(str) {
                if (!str) return '';
                return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            }

            // ══════════════════════════════════════════════
            // 🎙️ SESLİ ANLATIM
            // ══════════════════════════════════════════════
            const btnNarrate = document.getElementById('btnNarrate');
            let isNarrating = false;

            // ── Ses Ayarları ──
            let selectedVoice = null;
            const voiceSelect = document.getElementById('voiceSelect');

            const voiceContainer = document.getElementById('voiceSettingsContainer');

            // ElevenLabs Key


            function loadVoices() {
                const voices = window.speechSynthesis.getVoices();
                if (voices.length === 0) return;

                const trVoices = voices.filter(v => v.lang.toLowerCase().includes('tr'));
                if (trVoices.length === 0) return;

                // Sıralama: Google > Microsoft
                trVoices.sort((a, b) => {
                    const an = a.name.toLowerCase();
                    const bn = b.name.toLowerCase();
                    if (an.includes('google') && !bn.includes('google')) return -1;
                    if (!an.includes('google') && bn.includes('google')) return 1;
                    if (an.includes('microsoft') && !bn.includes('microsoft')) return -1;
                    if (!an.includes('microsoft') && bn.includes('microsoft')) return 1;
                    return 0;
                });

                if (voiceSelect) {
                    voiceSelect.innerHTML = '';
                    trVoices.forEach((v, i) => {
                        const opt = document.createElement('option');
                        opt.value = i;
                        let label = v.name.replace(/(Microsoft|Google|Turkish)/g, '').replace(/[()]/g, '').trim();
                        if (v.name.includes('Google')) label = 'Google ' + label;
                        else if (v.name.includes('Microsoft')) label = 'Microsoft ' + label;
                        opt.textContent = label || v.name;
                        voiceSelect.appendChild(opt);
                    });

                    voiceSelect.selectedIndex = 0;
                    selectedVoice = trVoices[0];
                    if (voiceContainer) voiceContainer.style.display = 'block';

                    voiceSelect.onchange = function () {
                        selectedVoice = trVoices[this.value];
                    };
                }
            }

            if (window.speechSynthesis) {
                setTimeout(loadVoices, 100);
                window.speechSynthesis.onvoiceschanged = loadVoices;
            }

            if (btnNarrate) {
                btnNarrate.addEventListener('click', function () {
                    if (isNarrating) stopNarration();
                    else startNarration();
                });
            }

            function startNarration() {
                if (!window.currentAnalysis || !window.currentAnalysis.moves) {
                    showStatus('⚠️ Önce analiz yapılmalı!', 'error');
                    return;
                }

                isNarrating = true;
                btnNarrate.innerHTML = '⏹ Durdur';
                btnNarrate.style.background = '#dc3545'; // Kırmızı
                btnNarrate.classList.add('pulsing'); // Animasyon

                // Başa sar
                stopPlayback(); // Eğer manuel oynatma varsa durdur
                game.reset();
                board.start(false);

                let moveIndex = 0;
                const moves = window.currentAnalysis.moves;

                showStatus('🎙️ Sesli Anlatım Başladı...', 'playing');
                speak("Analiz anlatımı başlıyor.", () => {
                    playNextStep();
                });

                function playNextStep() {
                    if (!isNarrating) return;

                    if (moveIndex >= moves.length) {
                        stopNarration();
                        showStatus('✅ Anlatım tamamlandı.', 'done');
                        speak("Maç analizi sona erdi.");
                        return;
                    }

                    const m = moves[moveIndex];
                    const side = m.side === 'white' ? 'Beyaz' : 'Siyah';
                    const san = m.played;
                    const trSan = sanToTurkish(san);
                    const moveText = detailedMoveText(trSan);

                    // Tahtada oynat
                    game.move(san);
                    board.position(game.fen(), true);
                    updateTurnIndicator();

                    // Doğal Dil Varyasyonları
                    const templates = [
                        `${side}, ${moveText} oynadı.`,
                        `${side} tarafından ${moveText} hamlesi.`,
                        `Şimdi ${side}, ${moveText} yapıyor.`,
                        `${side}ın tercihi: ${moveText}.`
                    ];
                    let text = templates[moveIndex % templates.length];

                    // Yorum
                    if (m.comment) {
                        text += " " + m.comment;
                    }

                    // Okut
                    speak(text, function () {
                        // Alternatif var mı?
                        // Alternatif var mı?
                        if (m.alternative) {
                            const altSan = m.alternative;
                            const altTr = sanToTurkish(altSan);
                            const altReason = m.altReason || 'Daha iyi bir seçenek.';
                            const altMoveText = detailedMoveText(altTr);

                            const altText = `Ancak, daha iyisi ${altMoveText} olabilirdi. ${altReason}`;

                            // ÖNCE GÖSTER (Senkronize)
                            game.undo(); // Gerçek hamleyi geri al
                            game.move(altSan); // Alternatifi yap
                            board.position(game.fen(), true);
                            showStatus('💡 Alternatif: ' + altTr, 'alt');

                            // SONRA ANLAT
                            speak(altText, function () {
                                // Anlatım bitince biraz bekle, sonra geri al
                                setTimeout(() => {
                                    game.undo(); // Alternatifi geri al
                                    game.move(san); // Gerçeği tekrar yap
                                    board.position(game.fen(), true);

                                    moveIndex++;
                                    setTimeout(playNextStep, 500);
                                }, 1500); // 1.5 saniye bekle
                            });
                        } else {
                            // Alternatif yok
                            moveIndex++;
                            setTimeout(playNextStep, 500);
                        }
                    });
                }
            }

            function stopNarration() {
                isNarrating = false;
                window.speechSynthesis.cancel();
                if (btnNarrate) {
                    btnNarrate.innerHTML = '🎙️ Anlat';
                    btnNarrate.style.background = '#6f42c1'; // Mor
                    btnNarrate.classList.remove('pulsing');
                }
                const sub = document.getElementById('narrationSubtitle');
                if (sub) {
                    sub.style.display = 'none';
                    sub.style.opacity = '0';
                }
            }

            function detailedMoveText(trSan) {
                // Af3 -> At F 3
                let s = trSan;
                s = s.replace('0-0-0', 'Uzun rok');
                s = s.replace('0-0', 'Kısa rok');
                s = s.replace('A', 'At ');
                s = s.replace('F', 'Fil ');
                s = s.replace('K', 'Kale ');
                s = s.replace('V', 'Vezir ');
                s = s.replace('Ş', 'Şah ');
                s = s.replace(/x/g, ' alır ');
                s = s.replace(/\+/g, ' şah');
                s = s.replace(/#/g, ' mat');
                return s;
            }

            function speak(text, onEnd) {
                if (!text) { if (onEnd) onEnd(); return; }

                // Altyazı
                if (!text) { if (onEnd) onEnd(); return; }

                // Altyazı (İptal edildi - Kullanıcı isteği)
                const sub = document.getElementById('narrationSubtitle');
                if (sub) {
                    sub.style.display = 'none'; // Görünmesin
                }



                // ElevenLabs Kontrolü (HARDCODED)
                const elKey = 'sk_a1899f18fb322f927756cf4c4df379e297fcfe616720fd5f';

                if (elKey && elKey.length > 20) {

                    window.speechSynthesis.cancel();

                    fetch('https://api.elevenlabs.io/v1/text-to-speech/21m00Tcm4TlvDq8ikWAM', { // Rachel
                        method: 'POST',
                        headers: {
                            'xi-api-key': elKey,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            text: text,
                            model_id: "eleven_multilingual_v2",
                            voice_settings: { stability: 0.5, similarity_boost: 0.75 }
                        })
                    })
                        .then(response => {
                            if (!response.ok) {
                                response.text().then(text => alert("ElevenLabs API Hatası: " + response.status + " - " + text));
                                throw new Error('ElevenLabs Error: ' + response.statusText);
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            const url = URL.createObjectURL(blob);
                            const audio = new Audio(url);
                            audio.playbackRate = 1.0;
                            audio.onended = function () {
                                if (sub) {
                                    sub.style.opacity = '0';
                                    setTimeout(() => { if (sub) sub.style.display = 'none'; }, 300);
                                }
                                if (onEnd) onEnd();
                            };
                            audio.onerror = function () {
                                alert("Audio Playback Hatası (Format desteklenmiyor olabilir)");
                                doSystemSpeak(text, onEnd, sub);
                            };
                            audio.play();
                        })
                        .catch(err => {
                            console.error('ElevenLabs Failed:', err);
                            // Fallback öncesi bilgi verelim
                            // alert("Bağlantı Hatası: " + err.message); 
                            doSystemSpeak(text, onEnd, sub);
                        });
                    return;
                }

                // Fallback (Sistem Sesi)
                doSystemSpeak(text, onEnd, sub);
            }

            function doSystemSpeak(text, onEnd, sub) {
                window.speechSynthesis.cancel();
                const utter = new SpeechSynthesisUtterance(text);
                if (selectedVoice) {
                    utter.voice = selectedVoice;
                }
                utter.lang = 'tr-TR';
                utter.rate = 0.9; // Biraz daha doğal hız
                utter.onend = function () {
                    if (sub) {
                        sub.style.opacity = '0';
                        setTimeout(() => { if (sub) sub.style.display = 'none'; }, 300);
                    }
                    if (onEnd) onEnd();
                };
                utter.onerror = function (e) {
                    console.error('Speech error:', e);
                    if (sub) sub.style.display = 'none';
                    if (onEnd) onEnd();
                };
                window.speechSynthesis.speak(utter);
            }

            // Motoru başlat
            initStockfish();

        })();
    </script>
</body>

</html>