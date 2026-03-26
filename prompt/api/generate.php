<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Only POST allowed']);
    exit;
}

$idea = $_POST['idea'] ?? '';
$targetAI = $_POST['target_ai'] ?? 'genel';
$detail = $_POST['detail'] ?? 'detayli';
$lang = $_POST['lang'] ?? 'tr';

if (empty($idea)) {
    echo json_encode(['success' => false, 'error' => 'Fikir boş olamaz.']);
    exit;
}

// Hedef AI'a özel kurallar
$aiRules = [
    'genel' => 'Prompt herhangi bir AI asistanda çalışacak şekilde evrensel olmalı. Standart prompt mühendisliği teknikleri kullan.',
    'chatgpt' => 'ChatGPT için optimize et. System prompt formatında yaz. Markdown formatı kullan. "Adım adım düşün" gibi chain-of-thought teknikleri ekle. Rol tanımıyla başla (örn: "Sen deneyimli bir...").',
    'gemini' => 'Google Gemini için optimize et. Gemini çok modaliteli çalışır, bunu göz önünde bulundur. Net ve yapısal talimatlar ver. Örneklerle destekle.',
    'claude' => 'Anthropic Claude için optimize et. Claude uzun ve detaylı talimatlarda çok iyidir. XML tag\'leri kullanarak yapıyı belirle (örn: <role>, <task>, <constraints>). Düşünme sürecini açıkça tanımla.',
    'midjourney' => 'Midjourney görsel oluşturucu için optimize et. Prompt formatı: "/imagine prompt: [açıklama]" şeklinde olsun. Stil, ışık, açı, renk paleti, kalite parametreleri (--ar, --v, --style) ekle. Sanatsal terimler kullan.',
    'cursor' => 'Cursor/Copilot kod asistanı için optimize et. Teknik spesifikasyonları net belirt. Kullanılacak dil, framework, mimari pattern, dosya yapısı gibi detayları dahil et. Kod stili ve best practice kurallarını açıkça belirt.'
];

// Detay seviyesine göre yapı kuralları
$detailRules = [
    'kisa' => 'ÇOK KISA yaz. Maksimum 3-5 cümle. Sadece temel talimatı ver, gereksiz detay ekleme. Direkt ve etkili ol.',
    'orta' => 'ORTA detayda yaz. 5-10 cümle. Rol tanımı, ana görev ve temel kısıtlamaları belirt. Fazla detaya girme.',
    'detayli' => 'DETAYLI ve PROFESYONEL yaz. Maddeler halinde düzenle. Şunları dahil et: 1) Rol tanımı 2) Bağlam 3) Ana görev 4) Adım adım talimatlar 5) Kısıtlamalar 6) Beklenen çıktı formatı. 15-25 cümle.',
    'uzman' => 'UZMAN SEVİYE yaz. Çok kapsamlı ol. Şunları dahil et: 1) Detaylı rol tanımı ve uzmanlık alanları 2) Kapsamlı bağlam 3) Ana ve alt görevler 4) Adım adım metodoloji 5) Edge case\'ler ve dikkat edilmesi gerekenler 6) Kalite kriterleri 7) Beklenen çıktı formatı ve örnekler 8) Yasaklar ve kaçınılması gerekenler. 25+ cümle.'
];

$langStr = $lang === 'en' ? 'İngilizce' : 'Türkçe';
$aiRule = $aiRules[$targetAI] ?? $aiRules['genel'];
$detailRule = $detailRules[$detail] ?? $detailRules['detayli'];

$systemPrompt = "Sen dünyanın en iyi prompt mühendisisin. Kullanıcının basit fikrini alıp mükemmel bir prompt'a dönüştürürsün.

HEDEF AI KURALLARI:
{$aiRule}

DETAY SEVİYESİ KURALLARI:
{$detailRule}

GENEL KURALLAR:
- Prompt'u {$langStr} olarak yaz.
- SADECE prompt'un kendisini yaz. Başına 'İşte prompt' gibi açıklama ekleme.
- Prompt doğrudan kopyala-yapıştır kullanılabilir olmalı.
- Kullanıcının fikrindeki belirsizlikleri profesyonelce doldur.";

$userMsg = "Fikrim: {$idea}";

// Groq API Call
$apiKey = GROQ_API_KEY;
$apiUrl = "https://api.groq.com/openai/v1/chat/completions";

$payload = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => [
        ["role" => "system", "content" => $systemPrompt],
        ["role" => "user", "content" => $userMsg]
    ],
    "temperature" => 0.7,
    "max_tokens" => 2000
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
    $prompt = $responseData['choices'][0]['message']['content'];

    // Update usage count
    try {
        $pdo->query("UPDATE tools SET usage_count = usage_count + 1 WHERE slug = 'prompt'");
    } catch (PDOException $e) {
    }

    echo json_encode(['success' => true, 'prompt' => $prompt]);
} else {
    echo json_encode(['success' => false, 'error' => 'AI yanıt veremedi.']);
}
?>