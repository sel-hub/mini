<?php
require_once __DIR__ . '/../config.php';

// Spotify'dan dönen authorization code
if (!isset($_GET['code'])) {
    header('Location: index.php');
    exit;
}

$code = $_GET['code'];

// Code'u access token'a çevir
$ch = curl_init('https://accounts.spotify.com/api/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => SPOTIFY_REDIRECT_URI
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET),
    'Content-Type: application/x-www-form-urlencoded'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['access_token'])) {
    $_SESSION['spotify_access_token'] = $data['access_token'];
    $_SESSION['spotify_refresh_token'] = $data['refresh_token'] ?? '';
    $_SESSION['spotify_expires_at'] = time() + ($data['expires_in'] ?? 3600);
    // Kullanım sayacını artır
    try {
        $pdo->query("UPDATE tools SET usage_count = usage_count + 1 WHERE slug = 'spotify'");
    } catch (PDOException $e) {
    }

    header('Location: stats.php');
} else {
    echo '<p style="color:red; padding:2rem;">Spotify giriş hatası: ' . ($data['error_description'] ?? 'Bilinmeyen hata') . '</p>';
    echo '<a href="index.php">Tekrar dene</a>';
}
?>