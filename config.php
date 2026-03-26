<?php
/**
 * AI Araç Kutusu — Yapılandırma Dosyası
 * ----------------------------------------
 * PDO ile MySQL bağlantısı ve Gemini API anahtarı.
 */

// ── Oturum başlat (CSRF için gerekli) ──
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── API Anahtarları ──
define('GEMINI_API_KEY', 'YOUR_API_KEY_HERE');
define('GROQ_API_KEY', 'YOUR_GROQ_API_KEY');
define('SPOTIFY_CLIENT_ID', 'YOUR_SPOTIFY_CLIENT_ID');
define('SPOTIFY_CLIENT_SECRET', 'YOUR_SPOTIFY_CLIENT_SECRET');
define('SPOTIFY_REDIRECT_URI', 'https://tercihlist.com/mini/spotify/callback.php');

// ── Site Ayarları ──
define('BASE_URL', 'https://tercihlist.com/mini');
define('SITE_NAME', 'AI Araç Kutusu');

// ── MySQL Bağlantı Bilgileri ──
$db_host = 'localhost';
$db_name = 'tercihli_minichess';
$db_user = 'tercihli_minichess';       // Sunucunuzdaki kullanıcı adını girin
$db_pass = 'Elseli0.';            // Sunucunuzdaki şifreyi girin

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Veritabanı bağlantı hatası: ' . $e->getMessage());
}

// ── CSRF Token Yardımcı Fonksiyonları ──

/**
 * Oturumda yoksa yeni CSRF token üretir, varsa mevcut olanı döner.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Gelen token'ı oturumdaki ile karşılaştırır.
 */
function csrf_validate(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
