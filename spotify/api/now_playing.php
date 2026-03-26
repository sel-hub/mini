<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['spotify_access_token'])) {
    echo json_encode(['success' => false, 'error' => 'No token']);
    exit;
}

$token = $_SESSION['spotify_access_token'];

$ch = curl_init('https://api.spotify.com/v1/me/player/currently-playing');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code === 204 || empty($res)) {
    echo json_encode(['success' => true, 'is_playing' => false]);
    exit;
}

if ($code === 200) {
    $data = json_decode($res, true);
    if (isset($data['item'])) {
        echo json_encode([
            'success' => true,
            'is_playing' => $data['is_playing'],
            'progress_ms' => $data['progress_ms'],
            'item' => [
                'name' => $data['item']['name'],
                'duration_ms' => $data['item']['duration_ms'],
                'artist' => $data['item']['artists'][0]['name'] ?? 'Bilinmiyor',
                'image' => $data['item']['album']['images'][0]['url'] ?? ''
            ]
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'API error', 'code' => $code]);
?>