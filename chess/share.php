<?php
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? '';
$data = null;
$error = null;

if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM analysis_saves WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            $error = "Analiz bulunamadı.";
        }
    } catch (PDOException $e) {
        $error = "Veritabanı hatası.";
    }
} else {
    $error = "Geçersiz ID.";
}

if ($error) {
    die("<h1>Hata: " . htmlspecialchars($error) . "</h1><a href='chess.php'>Ana Sayfaya Dön</a>");
}

$analysis = json_decode($data['analysis_json'], true);
$pgn = $data['pgn'];
$opening = $analysis['opening'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paylaşılan Analiz — AI Satranç</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f0f1a;
            color: #e8e8f0;
        }

        .panel {
            background: #1a1a2e;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 1.5rem;
            height: 100%;
        }

        .turn-badge {
            padding: .3rem .8rem;
            border-radius: 20px;
            font-size: .85rem;
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

        .analysis-box {
            background: rgba(108, 99, 255, 0.06);
            border: 1px solid rgba(108, 99, 255, 0.15);
            border-radius: 14px;
            padding: 1.2rem;
            margin-top: 1rem;
        }

        .overall-comment {
            margin-bottom: 1rem;
            padding-bottom: .8rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .move-analysis-item {
            padding: .5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            display: flex;
            gap: .6rem;
        }

        .move-num-badge {
            background: rgba(108, 99, 255, 0.15);
            color: #6c63ff;
            font-weight: 700;
            padding: .2rem .5rem;
            border-radius: 6px;
            min-width: 50px;
            text-align: center;
        }

        .alt-suggestion {
            color: #ffa502;
            font-size: .85rem;
            margin-top: .3rem;
        }

        .btn-action {
            border: none;
            border-radius: 8px;
            padding: .4rem .8rem;
            font-size: .85rem;
            cursor: pointer;
        }

        .btn-play {
            background: #2ed573;
            color: #fff;
        }

        .btn-home {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .btn-show {
            background: #ffa502;
            color: #fff;
            font-size: .75rem;
            padding: .2rem .5rem;
        }

        .pulsing {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% {
                box-shadow: 0 0 0 0 rgba(111, 66, 193, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(111, 66, 193, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(111, 66, 193, 0);
            }
        }

        .highlight1-32417,
        .highlight2-9c5d2 {
            box-shadow: inset 0 0 3px 3px rgba(255, 255, 0, 0.5) !important;
            background: rgba(255, 255, 0, 0.2) !important;
        }
    </style>
</head>

<body>

    <div class="container py-4">
        <a href="./" class="btn-home">← Yeni Analiz Yap</a>

        <div class="row g-4">
            <!-- Board -->
            <div class="col-lg-6">
                <div class="panel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="turn-badge turn-white" id="turnIndicator">⬜ Beyaz Başlar</span>
                        <?php if ($opening): ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($opening) ?></span>
                        <?php endif; ?>
                    </div>
                    <div id="chessBoard" style="width: 100%;"></div>
                    <div class="mt-3 text-center">
                        <button class="btn-action btn-play" id="btnReplay">▶ Tekrar Oynat</button>
                        <button class="btn-action" id="btnNarrate" style="background:#6f42c1; color:white;">🎙️
                            Anlat</button>
                        <button class="btn-action bg-secondary text-white" id="btnBackToReal" style="display:none;">↩
                            Asıl Pozisyon</button>
                    </div>
                </div>
            </div>

            <!-- Analysis -->
            <div class="col-lg-6">
                <div class="panel">
                    <h4>📊 Analiz Raporu</h4>
                    <div class="analysis-box">
                        <?php if (!empty($analysis['overall'])): ?>
                            <div class="overall-comment">💡 <?= htmlspecialchars($analysis['overall']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($analysis['moves'])): ?>
                            <?php foreach ($analysis['moves'] as $key => $m): ?>
                                <div class="move-analysis-item" id="move-item-<?= $key ?>">
                                    <div class="move-num-badge">
                                        <?= $m['moveNum'] ?>. <?= $m['side'] === 'white' ? '⬜' : '⬛' ?>
                                    </div>
                                    <div style="flex:1;">
                                        <b><?= htmlspecialchars($m['played']) ?></b>
                                        <span style="color:#9d9db5;"><?= htmlspecialchars($m['comment']) ?></span>
                                        <?php if (!empty($m['alternative'])): ?>
                                            <div class="alt-suggestion">
                                                💡 Alternatif: <strong><?= htmlspecialchars($m['alternative']) ?></strong>
                                                — <?= htmlspecialchars($m['altReason']) ?>
                                                <button class="btn-action btn-show ms-2"
                                                    onclick="showAlternative(<?= $m['moveNum'] ?>, '<?= $m['side'] ?>', '<?= addslashes($m['alternative']) ?>')">
                                                    👁 Göster
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
    <script src="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.js"></script>
    <script>
        const game = new Chess();
        const pgn = <?= json_encode($pgn) ?>;
        const analysisData = <?= json_encode($analysis) ?>;
        const board = Chessboard('chessBoard', {
            position: 'start',
            pieceTheme: 'https://lichess1.org/assets/piece/cburnett/{piece}.svg'
        });

        // Load game logic
        game.load_pgn(pgn);
        // Show final position initially? Or start?
        // Let's show final position
        board.position(game.fen());

        const history = game.history();
        let isPlaying = false;
        let playInterval;
        let realFen = game.fen();

        document.getElementById('btnReplay').addEventListener('click', () => {
            if (isPlaying) return;
            isPlaying = true;
            game.reset();
            board.start();

            let i = 0;
            playInterval = setInterval(() => {
                if (i >= history.length) {
                    clearInterval(playInterval);
                    isPlaying = false;
                    return;
                }
                game.move(history[i]);
                board.position(game.fen(), true);
                i++;
            }, 1200);
        });

        window.showAlternative = function (moveNum, side, altMove) {
            // Reconstruct game until that point
            const temp = new Chess();
            const targetIndex = (moveNum - 1) * 2 + (side === 'black' ? 1 : 0);

            for (let i = 0; i <= targetIndex; i++) {
                if (i === targetIndex) {
                    temp.move(altMove);
                } else if (i < history.length) {
                    temp.move(history[i]);
                }
            }

            board.position(temp.fen(), true);
            document.getElementById('btnBackToReal').style.display = 'inline-block';
        };

        document.getElementById('btnBackToReal').addEventListener('click', () => {
            board.position(realFen, true); // Go back to final position
            document.getElementById('btnBackToReal').style.display = 'none';
            stopNarration();
        });

        // ─── SESLİ ANLATIM ───
        let isNarrating = false;
        let narrateIndex = 0;
        const btnNarrate = document.getElementById('btnNarrate');

        btnNarrate.addEventListener('click', () => {
            if (isNarrating) stopNarration();
            else startNarration();
        });

        function startNarration() {
            if (!analysisData || !analysisData.moves) {
                alert("Analiz verisi yok.");
                return;
            }

            // Stop any other playback
            if (playInterval) { clearInterval(playInterval); isPlaying = false; }

            isNarrating = true;
            btnNarrate.innerHTML = "⏹ Durdur";
            btnNarrate.style.background = "#dc3545";
            btnNarrate.classList.add("pulsing");

            game.reset();
            board.position(game.fen());
            narrateIndex = 0;

            speak("Analiz anlatımı başlıyor.", () => {
                playNarratedStep();
            });
        }

        function stopNarration() {
            isNarrating = false;
            window.speechSynthesis.cancel();
            btnNarrate.innerHTML = "🎙️ Anlat";
            btnNarrate.style.background = "#6f42c1";
            btnNarrate.classList.remove("pulsing");
        }

        function playNarratedStep() {
            if (!isNarrating) return;
            const moves = analysisData.moves;

            if (narrateIndex >= moves.length) {
                stopNarration();
                speak("Analiz tamamlandı.");
                return;
            }

            const m = moves[narrateIndex];
            const san = m.played;

            // Move on board
            game.move(san);
            board.position(game.fen(), true);

            // Highlight logic (optional, skip for simplicity)

            // Prepare text
            const side = m.side === 'white' ? 'Beyaz' : 'Siyah';
            const trSan = sanToTurkish(san);
            const moveText = detailedMoveText(trSan);

            let text = `${side}, ${moveText} oynadı.`;
            if (m.comment) text += " " + m.comment;

            speak(text, () => {
                // Alternatif?
                if (m.alternative) {
                    const altSan = m.alternative;
                    const altTr = sanToTurkish(altSan);
                    const altReason = m.altReason || '';
                    const altMoveText = detailedMoveText(altTr);
                    const altText = `Ancak, daha iyisi ${altMoveText} olabilirdi. ${altReason}`;

                    // ÖNCE GÖSTER
                    game.undo();
                    game.move(altSan);
                    board.position(game.fen(), true);

                    speak(altText, () => {
                        setTimeout(() => {
                            game.undo();
                            game.move(san);
                            board.position(game.fen(), true);

                            narrateIndex++;
                            setTimeout(playNarratedStep, 500);
                        }, 1500);
                    });
                } else {
                    narrateIndex++;
                    setTimeout(playNarratedStep, 500);
                }
            });
        }

        function sanToTurkish(san) {
            let s = san;
            s = s.replace('N', 'A').replace('B', 'F').replace('R', 'K').replace('Q', 'V').replace('K', 'Ş');
            return s;
        }

        function detailedMoveText(trSan) {
            let s = trSan;
            s = s.replace('O-O-O', 'Uzun rok').replace('0-0-0', 'Uzun rok');
            s = s.replace('O-O', 'Kısa rok').replace('0-0', 'Kısa rok');
            s = s.replace('A', 'At ').replace('F', 'Fil ').replace('K', 'Kale ').replace('V', 'Vezir ').replace('Ş', 'Şah ');
            s = s.replace(/x/g, ' alır ').replace(/\+/g, ' şah').replace(/#/g, ' mat');
            return s;
        }

        function speak(text, onEnd) {
            if (!text) { if (onEnd) onEnd(); return; }

            // Hardcoded Key from chess.php
            const elKey = 'sk_a1899f18fb322f927756cf4c4df379e297fcfe616720fd5f';

            if (elKey && elKey.length > 20) {
                window.speechSynthesis.cancel();
                fetch('https://api.elevenlabs.io/v1/text-to-speech/21m00Tcm4TlvDq8ikWAM', {
                    method: 'POST',
                    headers: { 'xi-api-key': elKey, 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        text: text,
                        model_id: "eleven_multilingual_v2",
                        voice_settings: { stability: 0.5, similarity_boost: 0.75 }
                    })
                })
                    .then(res => {
                        if (!res.ok) throw new Error(res.statusText);
                        return res.blob();
                    })
                    .then(blob => {
                        const url = URL.createObjectURL(blob);
                        const audio = new Audio(url);
                        audio.playbackRate = 1.0;
                        audio.onended = onEnd;
                        audio.onerror = () => doSystemSpeak(text, onEnd);
                        audio.play();
                    })
                    .catch(e => {
                        console.error(e);
                        doSystemSpeak(text, onEnd);
                    });
                return;
            }
            doSystemSpeak(text, onEnd);
        }

        function doSystemSpeak(text, onEnd) {
            window.speechSynthesis.cancel();
            const utter = new SpeechSynthesisUtterance(text);
            utter.lang = 'tr-TR';
            utter.rate = 0.9;
            utter.onend = onEnd;
            utter.onerror = onEnd; // Hata olsa da devam et
            window.speechSynthesis.speak(utter);
        }
    </script>
</body>

</html>