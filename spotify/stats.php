<?php
require_once __DIR__ . '/../config.php';

// Token kontrolü
if (!isset($_SESSION['spotify_access_token'])) {
    header('Location: index.php');
    exit;
}

$token = $_SESSION['spotify_access_token'];

// Spotify API helper
function spotifyGet($endpoint, $token)
{
    $ch = curl_init('https://api.spotify.com/v1/' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 401) {
        // Token expired
        session_destroy();
        header('Location: index.php');
        exit;
    }
    return json_decode($res, true);
}

// Kullanıcı bilgisi
$me = spotifyGet('me', $token);

// Şu an çalan
$currentlyPlaying = spotifyGet('me/player/currently-playing', $token);

// Top şarkılar (Limit 50 yapıldı)
$topTracks = spotifyGet('me/top/tracks?limit=50&time_range=short_term', $token);

// Top sanatçılar (Limit 50 yapıldı)
$topArtists = spotifyGet('me/top/artists?limit=50&time_range=short_term', $token);

// Son dinlenenler
$recent = spotifyGet('me/player/recently-played?limit=10', $token);

$userName = $me['display_name'] ?? 'Kullanıcı';
$userImage = $me['images'][0]['url'] ?? '';
$userFollowers = $me['followers']['total'] ?? 0;
$userUrl = $me['external_urls']['spotify'] ?? '#';

// Format süre
function formatDuration($ms)
{
    if (!$ms)
        return "0:00";
    $secs = floor($ms / 1000);
    $mins = floor($secs / 60);
    $remSecs = $secs % 60;
    return $mins . ':' . str_pad($remSecs, 2, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($userName) ?> — Spotify İstatistik
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f0f1a;
            color: #e8e8f0;
            min-height: 100vh;
        }

        .navbar {
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .user-header {
            text-align: center;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid #1DB954;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .user-name {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .time-range {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .time-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #9d9db5;
            padding: 0.4rem 1.2rem;
            border-radius: 50px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .time-btn.active {
            background: rgba(29, 185, 84, 0.15);
            border-color: #1DB954;
            color: #1DB954;
        }

        .time-btn:hover {
            border-color: #1DB954;
            color: #e8e8f0;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .scroll-container {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .scroll-container::-webkit-scrollbar {
            width: 6px;
        }

        .scroll-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }

        .scroll-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .scroll-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .now-playing {
            background: linear-gradient(135deg, rgba(29, 185, 84, 0.15) 0%, rgba(29, 185, 84, 0.05) 100%);
            border: 1px solid rgba(29, 185, 84, 0.3);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            display: none;
            /* JS will show this if playing */
        }

        .now-playing-img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            flex-shrink: 0;
        }

        .now-playing-info {
            flex: 1;
            min-width: 0;
        }

        .now-playing-info h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .now-playing-info p {
            margin: 0;
            color: #1DB954;
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .now-playing-badge {
            display: inline-block;
            background: #1DB954;
            color: #000;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            animation: pulse 2s infinite;
        }

        .live-progress {
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.8rem;
            color: #9d9db5;
        }

        .live-bar-bg {
            flex: 1;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
            position: relative;
        }

        .live-bar-fill {
            height: 100%;
            background: #1DB954;
            width: 0%;
            transition: width 1s linear;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
        }

        .now-playing-info h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
        }

        .now-playing-info p {
            margin: 0;
            color: #1DB954;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .now-playing-badge {
            display: inline-block;
            background: #1DB954;
            color: #000;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .track-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #1a1a2e;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 0.8rem 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }

        .track-card:hover {
            background: #1e1e35;
            transform: translateX(4px);
        }

        .track-rank {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(29, 185, 84, 0.15);
            color: #1DB954;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .track-rank.gold {
            background: rgba(255, 215, 0, 0.15);
            color: #FFD700;
        }

        .track-rank.silver {
            background: rgba(192, 192, 192, 0.15);
            color: #C0C0C0;
        }

        .track-rank.bronze {
            background: rgba(205, 127, 50, 0.15);
            color: #CD7F32;
        }

        .track-img {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .track-info {
            flex: 1;
            min-width: 0;
        }

        .track-name {
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .track-artist {
            color: #9d9db5;
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .track-meta {
            text-align: right;
            font-size: 0.8rem;
            color: #9d9db5;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .pop-bar {
            width: 40px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 4px;
            overflow: hidden;
        }

        .pop-fill {
            height: 100%;
            background: #1DB954;
        }

        .artist-card {
            background: #1a1a2e;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s;
            height: 100%;
        }

        .artist-card:hover {
            background: #1e1e35;
            transform: translateY(-3px);
            border-color: rgba(29, 185, 84, 0.4);
        }

        .artist-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 0.5rem;
            border: 2px solid rgba(29, 185, 84, 0.3);
        }

        .artist-name {
            font-weight: 600;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist-genre {
            color: #9d9db5;
            font-size: 0.75rem;
            margin-top: 0.2rem;
        }

        .genre-tag {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            margin: 0.2rem;
            border-radius: 20px;
            font-size: 0.8rem;
            background: rgba(29, 185, 84, 0.12);
            color: #1DB954;
            border: 1px solid rgba(29, 185, 84, 0.2);
        }

        .genre-tag.top {
            background: rgba(29, 185, 84, 0.2);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .btn-logout {
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid rgba(255, 71, 87, 0.2);
            color: #ff4757;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: rgba(255, 71, 87, 0.2);
            color: #ff4757;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/">
                <span style="color:#6c63ff">Yaratıcı</span>Stüdyo
            </a>
            <div class="d-flex gap-2">
                <a href="logout.php" class="btn-logout">Çıkış</a>
                <a href="<?= BASE_URL ?>/" class="btn btn-outline-light btn-sm rounded-pill">← Ana Menü</a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Kullanıcı -->
        <div class="user-header">
            <?php if ($userImage): ?>
                <img src="<?= htmlspecialchars($userImage) ?>" class="user-avatar" alt="avatar">
            <?php endif; ?>
            <div class="user-name">Merhaba,
                <a href="<?= $userUrl ?>" target="_blank"
                    style="color:inherit;text-decoration:none;"><?= htmlspecialchars($userName) ?></a>! 🎵
            </div>
            <p class="text-muted mt-1">İşte Spotify istatistiklerin • <?= number_format($userFollowers) ?> Takipçi</p>
        </div>

        <!-- Şu An Çalan (Canlı JS) -->
        <div class="now-playing" id="nowPlayingWrapper">
            <img src="" class="now-playing-img" id="npImg" alt="">
            <div class="now-playing-info">
                <div class="now-playing-badge">🔴 ŞU AN DİNLİYORSUN</div>
                <h4 id="npTitle">Şarkı</h4>
                <p id="npArtist">Sanatçı</p>
                <div class="live-progress">
                    <span id="npCurrent">0:00</span>
                    <div class="live-bar-bg">
                        <div class="live-bar-fill" id="npBar"></div>
                    </div>
                    <span id="npTotal">0:00</span>
                </div>
            </div>
            <div class="ms-auto pe-3 text-success d-none d-md-block">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z" />
                </svg>
            </div>
        </div>

        <!-- Zaman Aralığı -->
        <div class="time-range">
            <a href="?range=short_term"
                class="time-btn <?= ($_GET['range'] ?? 'short_term') === 'short_term' ? 'active' : '' ?>">Son 4
                Hafta</a>
            <a href="?range=medium_term"
                class="time-btn <?= ($_GET['range'] ?? '') === 'medium_term' ? 'active' : '' ?>">Son 6 Ay</a>
            <a href="?range=long_term"
                class="time-btn <?= ($_GET['range'] ?? '') === 'long_term' ? 'active' : '' ?>">Tüm Zamanlar</a>
        </div>

        <?php
        // Zaman aralığına göre yeniden çek
        $range = $_GET['range'] ?? 'short_term';
        if (in_array($range, ['short_term', 'medium_term', 'long_term']) && isset($_GET['range'])) {
            $topTracks = spotifyGet("me/top/tracks?limit=50&time_range={$range}", $token);
            $topArtists = spotifyGet("me/top/artists?limit=50&time_range={$range}", $token);
        }
        ?>

        <div class="row g-4">
            <!-- Top Şarkılar -->
            <div class="col-lg-6">
                <div class="section-title mb-3">🎵 En Çok Dinlediğin Şarkılar (Top 50)</div>
                <div class="scroll-container">
                    <?php if (!empty($topTracks['items'])): ?>
                        <?php foreach ($topTracks['items'] as $i => $track): ?>
                            <div class="track-card">
                                <div
                                    class="track-rank <?= $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) ?>">
                                    <?= $i + 1 ?>
                                </div>
                                <?php if (!empty($track['album']['images'])): ?>
                                    <img src="<?= $track['album']['images'][2]['url'] ?? $track['album']['images'][0]['url'] ?>"
                                        class="track-img" alt="">
                                <?php endif; ?>
                                <div class="track-info">
                                    <div class="track-name">
                                        <a href="<?= $track['external_urls']['spotify'] ?? '#' ?>" target="_blank"
                                            style="color:inherit;text-decoration:none;"><?= htmlspecialchars($track['name']) ?></a>
                                    </div>
                                    <div class="track-artist">
                                        <?= htmlspecialchars($track['artists'][0]['name'] ?? '') ?>
                                    </div>
                                </div>
                                <div class="track-meta">
                                    <div><?= formatDuration($track['duration_ms'] ?? 0) ?></div>
                                    <?php $pop = $track['popularity'] ?? 0; ?>
                                    <div class="pop-bar" title="Popülerlik: <?= $pop ?>">
                                        <div class="pop-fill" style="width: <?= $pop ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Bu dönem için yeterli veri yok.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Sanatçılar -->
            <div class="col-lg-6">
                <div class="section-title">🎤 Top Sanatçılar (Top 50)</div>
                <div class="scroll-container">
                    <div class="row g-2">
                        <?php if (!empty($topArtists['items'])): ?>
                            <?php foreach ($topArtists['items'] as $i => $artist): ?>
                                <div class="col-6 col-md-4">
                                    <div class="artist-card m-0">
                                        <div
                                            style="position:absolute; top:10px; left:10px; background:rgba(0,0,0,0.5); border-radius:10px; padding:2px 8px; font-size:12px; font-weight:bold;">
                                            #<?= $i + 1 ?></div>
                                        <?php if (!empty($artist['images'])): ?>
                                            <img src="<?= end($artist['images'])['url'] ?? $artist['images'][0]['url'] ?>"
                                                class="artist-img" alt="">
                                        <?php endif; ?>
                                        <div class="artist-name">
                                            <a href="<?= $artist['external_urls']['spotify'] ?? '#' ?>" target="_blank"
                                                style="color:inherit;text-decoration:none;"><?= htmlspecialchars($artist['name']) ?></a>
                                        </div>
                                        <div class="artist-genre">
                                            <?= number_format($artist['followers']['total'] ?? 0) ?> takipçi
                                            <div style="font-size:0.7rem; color:#6c63ff; margin-top:2px;">
                                                <?= htmlspecialchars($artist['genres'][0] ?? 'Bilinmiyor') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Bu dönem için yeterli veri yok.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Türler -->
                <?php
                $allGenres = [];
                if (!empty($topArtists['items'])) {
                    foreach ($topArtists['items'] as $artist) {
                        foreach ($artist['genres'] ?? [] as $genre) {
                            $allGenres[$genre] = ($allGenres[$genre] ?? 0) + 1;
                        }
                    }
                    arsort($allGenres);
                }
                ?>
                <?php if (!empty($allGenres)): ?>
                    <div class="section-title mt-4">🎸 En Çok Dinlediğin Türler</div>
                    <div>
                        <?php $gi = 0;
                        foreach (array_slice($allGenres, 0, 12) as $genre => $count): ?>
                            <span class="genre-tag <?= $gi < 3 ? 'top' : '' ?>">
                                <?= htmlspecialchars($genre) ?>
                            </span>
                            <?php $gi++; endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Son Dinlenenler -->
        <?php if (!empty($recent['items'])): ?>
            <div class="section-title mt-4">🕐 Son Dinlediklerin</div>
            <div class="row g-2">
                <?php foreach (array_slice($recent['items'], 0, 5) as $item): ?>
                    <div class="col-12">
                        <div class="track-card">
                            <?php if (!empty($item['track']['album']['images'])): ?>
                                <img src="<?= $item['track']['album']['images'][2]['url'] ?? $item['track']['album']['images'][0]['url'] ?>"
                                    class="track-img" alt="">
                            <?php endif; ?>
                            <div class="track-info">
                                <div class="track-name">
                                    <?= htmlspecialchars($item['track']['name']) ?>
                                </div>
                                <div class="track-artist">
                                    <?= htmlspecialchars($item['track']['artists'][0]['name'] ?? '') ?>
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= date('H:i', strtotime($item['played_at'])) ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>


        // Canlı Şu An Çalan Takibi
        let liveInterval = null;
        let localProgressMs = 0;
        let localDurationMs = 0;
        let isPlaying = false;

        function formatMs(ms) {
            const totalSec = Math.floor(ms / 1000);
            const m = Math.floor(totalSec / 60);
            const s = totalSec % 60;
            return m + ':' + (s < 10 ? '0' : '') + s;
        }

        async function fetchNowPlaying() {
            try {
                const res = await fetch('api/now_playing.php');
                const data = await res.json();

                const wrapper = document.getElementById('nowPlayingWrapper');

                if (data.success && data.item) {
                    wrapper.style.display = 'flex';
                    isPlaying = data.is_playing;

                    document.getElementById('npImg').src = data.item.image;
                    document.getElementById('npTitle').textContent = data.item.name;
                    document.getElementById('npArtist').textContent = data.item.artist;

                    localProgressMs = data.progress_ms;
                    localDurationMs = data.item.duration_ms;

                    document.getElementById('npTotal').textContent = formatMs(localDurationMs);
                    updateBarUI();
                } else {
                    wrapper.style.display = 'none';
                    isPlaying = false;
                }
            } catch (e) {
                console.error('NP error', e);
            }
        }

        function updateBarUI() {
            if (localDurationMs > 0) {
                document.getElementById('npCurrent').textContent = formatMs(localProgressMs);
                const pct = (localProgressMs / localDurationMs) * 100;
                document.getElementById('npBar').style.width = Math.min(pct, 100) + '%';
            }
        }

        // API'ye 5 saniyede bir sor
        fetchNowPlaying();
        setInterval(fetchNowPlaying, 5000);

        // Arayüzü (bar ve süreyi) lokal olarak saniyede bir güncelle
        setInterval(() => {
            if (isPlaying && localProgressMs < localDurationMs) {
                localProgressMs += 1000;
                updateBarUI();
            }
        }, 1000);
    </script>
</body>

</html>