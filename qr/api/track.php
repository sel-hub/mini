<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

try {
    $pdo->query("UPDATE tools SET usage_count = usage_count + 1 WHERE slug = 'qr'");
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>