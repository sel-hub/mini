<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Only POST allowed']);
    exit;
}

$code = $_POST['code'] ?? '';

if (empty($code)) {
    echo json_encode(['success' => false, 'error' => 'Kod boş olamaz.']);
    exit;
}

// Groq API Call (Llama 3)
$apiKey = GROQ_API_KEY;
$apiUrl = "https://api.groq.com/openai/v1/chat/completions";

$prompt = "Aşağıdaki kodu Türkçe olarak analiz et.\n\n" .
    "Formatın şu olsun:\n" .
    "## 📌 Özet\nBu kod ne yapıyor? (3-4 cümle)\n\n" .
    "## 🔍 Detaylı Açıklama\nKodun her önemli bölümünü sırayla açıkla. Her bölüm için:\n" .
    "- Bölümün başlığını yaz\n" .
    "- İlgili kod parçasını kod bloğu olarak göster\n" .
    "- Altında 4-6 cümlelik detaylı bir açıklama yaz. Her satırın ne yaptığını, hangi değişkenlerin ne işe yaradığını, neden bu şekilde yazıldığını anlat. Açıklamayı yeni başlayan biri anlayacak şekilde yaz.\n\n" .
    "## ⚠️ Hatalar & Öneriler\nKodda hata, eksik veya iyileştirilebilecek yer varsa detaylıca belirt. Yoksa 'Sorun görünmüyor.' yaz.\n\n" .
    "Kod:\n```\n$code\n```";

$payload = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => [
        ["role" => "system", "content" => "Sen deneyimli bir yazılım eğitmenisin. Her kod parçasını gösterdikten sonra detaylı ve anlaşılır bir açıklama yazarsın. Kısa kesmezsin, her satırın ne yaptığını açıklarsın. Markdown formatı kullan."],
        ["role" => "user", "content" => $prompt]
    ],
    "temperature" => 0.5,
    "max_tokens" => 4000
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['success' => false, 'error' => 'Bağlantı hatası: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);

if ($httpCode !== 200) {
    $errorMsg = $responseData['error']['message'] ?? "HTTP $httpCode hatası";
    echo json_encode(['success' => false, 'error' => "API Hatası: $errorMsg"]);
    exit;
}

if (isset($responseData['choices'][0]['message']['content'])) {
    $explanation = $responseData['choices'][0]['message']['content'];

    // Save to History
    try {
        $stmt = $pdo->prepare("INSERT INTO code_history (code_snippet, explanation) VALUES (?, ?)");
        $stmt->execute([$code, $explanation]);
        $pdo->query("UPDATE tools SET usage_count = usage_count + 1 WHERE slug = 'code'");
    } catch (PDOException $e) {
        // Ignore DB errors
    }

    echo json_encode(['success' => true, 'explanation' => $explanation]);
} else {
    echo json_encode(['success' => false, 'error' => 'AI yanıt veremedi.']);
}
?>