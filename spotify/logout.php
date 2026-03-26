<?php
require_once __DIR__ . '/../config.php';

// Session'daki Spotify bilgilerini temizle
unset($_SESSION['spotify_access_token']);
unset($_SESSION['spotify_refresh_token']);
unset($_SESSION['spotify_expires_at']);

header('Location: index.php');
exit;
?>