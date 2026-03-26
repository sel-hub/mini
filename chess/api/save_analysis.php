<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('CSRF token mismatch');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $fen = $_POST['fen'] ?? '';
    $pgn = $_POST['pgn'] ?? '';
    $json = $_POST['analysis_json'] ?? '';

    if (!$fen || !$json) {
        throw new Exception('Eksik veri (FEN veya JSON girilmedi)');
    }

    // Tabloyu oluştur (Lazy init)
    $sql = "CREATE TABLE IF NOT EXISTS analysis_saves (
        id VARCHAR(32) PRIMARY KEY,
        fen VARCHAR(255) NOT NULL,
        pgn TEXT,
        analysis_json LONGTEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);

    // Benzersiz ID oluştur (8 karakter)
    $shareId = bin2hex(random_bytes(4));

    // Veriyi kaydet
    $stmt = $pdo->prepare("INSERT INTO analysis_saves (id, fen, pgn, analysis_json) VALUES (?, ?, ?, ?)");
    if (!$stmt->execute([$shareId, $fen, $pgn, $json])) {
        throw new Exception('Veritabanına yazılamadı.');
    }

    echo json_encode(['success' => true, 'share_id' => $shareId]);

} catch (PDOException $e) {
    error_log("Database Error in save_analysis.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Veritabanı hatası: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error in save_analysis.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
