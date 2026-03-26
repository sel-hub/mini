<?php
require_once __DIR__ . '/../config.php';

// Kullanıcı zaten giriş yapmışsa stats'a yönlendir
if (isset($_SESSION['spotify_access_token'])) {
    header('Location: stats.php');
    exit;
}

// Spotify login URL oluştur
$scopes = 'user-top-read user-read-recently-played user-read-private user-read-currently-playing';
$authUrl = 'https://accounts.spotify.com/authorize?' . http_build_query([
    'client_id' => SPOTIFY_CLIENT_ID,
    'response_type' => 'code',
    'redirect_uri' => SPOTIFY_REDIRECT_URI,
    'scope' => $scopes,
    'show_dialog' => 'true'
]);
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify İstatistik — AI Araç Kutusu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }

        .hero-content {
            max-width: 600px;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .hero h1 span {
            color: #1DB954;
        }

        .hero p {
            color: #9d9db5;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .btn-spotify {
            background: #1DB954;
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            transition: all 0.2s;
        }

        .btn-spotify:hover {
            background: #1ed760;
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(29, 185, 84, 0.3);
            color: white;
        }

        .btn-spotify svg {
            width: 24px;
            height: 24px;
        }

        .features {
            display: flex;
            gap: 2rem;
            justify-content: center;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .feature-item {
            text-align: center;
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .feature-label {
            color: #9d9db5;
            font-size: 0.85rem;
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

    <div class="hero">
        <div class="hero-content">
            <h1>🎵 <span>Spotify</span> İstatistiklerin</h1>
            <p>En çok dinlediğin şarkıları, sanatçıları ve türleri keşfet. Spotify hesabınla giriş yap, istatistiklerini
                gör!</p>

            <a href="<?= $authUrl ?>" class="btn-spotify">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z" />
                </svg>
                Spotify ile Giriş Yap
            </a>

            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">🎤</div>
                    <div class="feature-label">Top Sanatçılar</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🎵</div>
                    <div class="feature-label">Top Şarkılar</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📊</div>
                    <div class="feature-label">Dinleme Alışkanlıkları</div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>