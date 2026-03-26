<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Geçersiz metot.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userPrompt = $input['prompt'] ?? '';

if (empty($userPrompt)) {
    echo json_encode(['success' => false, 'error' => 'Lütfen bir tanım girin.']);
    exit;
}

if (!defined('GROQ_API_KEY') || empty(GROQ_API_KEY)) {
    echo json_encode(['success' => false, 'error' => 'API anahtarı ayarlanmamış.']);
    exit;
}

$outputType = $input['type'] ?? 'both';
$designLevel = $input['designLevel'] ?? 'professional';

// Prepare dynamic instructions based on output type
if ($outputType === 'html') {
    $typeInstructions = "SADECE HTML kodu üret. Stil işlemleri için Tailwind, Bootstrap veya Vanilla CSS utility class'ları kullanabilirsin. AYRI BİR CSS bloğu YAZMA. Sadece ```html ... ``` bloğu üret.";
} elseif ($outputType === 'css') {
    $typeInstructions = "SADECE CSS kodu üret. HTML bloğu YAZMA. Saf CSS kodunu ```css ... ``` bloğu içinde üret.";
} else {
    $typeInstructions = "Yanıtını İKİ AYRI kod bloğu olarak ver: Önce HTML kodunu ```html ... ``` bloğu içine, ardından CSS kodunu ```css ... ``` bloğu içine yaz.";
}

// Prepare dynamic instructions based on design level
if ($designLevel === 'amateur') {
    $qualityInstructions = "Temel ve sade (basic) bir tasarım yap. Gelişmiş CSS özellikleri (animasyon, gölge, gradient) KULLANMA. Düz renkler ve standart HTML bileşenleri yeterlidir. Hızlı üretilen bir prototip gibi olsun.";
} elseif ($designLevel === 'intermediate') {
    $qualityInstructions = "Modern, temiz ve şık bir kurumsal tasarım yap. Göze hoş gelen ama abartılı olmayan standart CSS özelliklerini (hafif gölgeler, yumuşak kenarlar, temel hover efektleri) kullan. Profesyonel ama standart bir görünüm olsun.";
} else {
    $qualityInstructions = "BİR ŞAHESER YARAT! MUHTEŞEM, GÖZ ALICI, ULTRA-MODERN ve çok İLERİ SEVİYE bir tasarım yap. Dünyanın en elit UI/UX tasarımcısı sensin. Apple veya Vercel kalitesinde kod yaz. 
- Sıradan renkler yerine harika gradientler, neon ışımalar veya dark-mode asaletini kullan.
- Glassmorphism, neumorphism, cyberpunk veya modern minimalist dokunuşlar ekle.
- Büyüleyici CSS animasyonları, pürüzsüz transition'lar, karmaşık hover efektleri ve derinlik katan box-shadow kombinasyonlarını sınırlarına kadar kullan.
- Modern typography ve harika grid/flex yerleşimleri kullan. Tasarım tek kelimeyle 'vay canına' dedirtmeli.";
}

// Prepare the Prompt for Groq Llama 3.3
$systemContext = "Sen uzman ve yaratıcı bir Frontend & UI Geliştiricisisin. Görevin, kullanıcının istediği spesifikasyonu HTML/CSS bileşeni olarak üretmek.
LÜTFEN ŞU KURALLARA KESİNLİKLE UY:
1. " . $typeInstructions . "
2. TASARIM KALİTESİ HEDEFİ: " . $qualityInstructions . "
3. ASLA ekstra bir açıklama, yorum, selamlaşma yazma. Sadece doğrudan istenilen kod bloklarını ver.
4. HTML yazarken <body>, <html>, <head> etiketlerini KESİNLİKLE kullanma, sadece asıl bileşenin içeriğini yaz.
5. `body` veya `*` etiketlerine stil verme, sadece bileşene özel benzersiz class'lar (örneğin `.cyber-btn-v2`) kullan.";

$messages = [
    [
        "role" => "system",
        "content" => $systemContext
    ],
    [
        "role" => "user",
        "content" => "Lütfen şu bileşeni harika bir şekilde tasarla: " . $userPrompt
    ]
];

$data = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => $messages,
    "temperature" => 0.6,
    "max_tokens" => 3000
];

// Call Groq API
$url = "https://api.groq.com/openai/v1/chat/completions";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . GROQ_API_KEY
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    $errorData = json_decode($response, true);
    $apiError = $errorData['error']['message'] ?? 'Bilinmeyen API hatası';
    echo json_encode(['success' => false, 'error' => 'API hatası (' . $httpCode . '): ' . $apiError]);
    exit;
}

$decoded = json_decode($response, true);
$responseText = $decoded['choices'][0]['message']['content'] ?? '';

if (empty($responseText)) {
    echo json_encode(['success' => false, 'error' => 'Boş yanıt alındı.']);
    exit;
}

// Extract HTML and CSS using Regex
$html = '';
$css = '';

// Helper to extract block by language
function extractMarkdownBlock($text, $lang)
{
    if (preg_match('/```' . $lang . '\s+([\s\S]*?)```/i', $text, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

$html = extractMarkdownBlock($responseText, 'html');
$css = extractMarkdownBlock($responseText, 'css');

// Fallback logic
if (empty($html) && empty($css)) {
    // Check if wrapping tags were missed entirely
    $cleanText = trim(str_replace('```', '', $responseText));

    if ($outputType === 'css' || stripos($cleanText, '{') !== false) {
        $css = $cleanText; // Assume it's CSS
    } elseif ($outputType === 'html' || strpos($cleanText, '<') !== false) {
        $html = $cleanText; // Assume it's HTML
    } else {
        $html = $cleanText; // Total fallback
    }

    // Remove language name at the very top if it leaked out of the removed tags
    $html = trim(preg_replace('/^html\s+/i', '', $html));
    $css = trim(preg_replace('/^css\s+/i', '', $css));
}

// Log Tool Usage (Fire and forget)
try {
    if (isset($pdo)) {
        $pdo->query("UPDATE tools SET usage_count = usage_count + 1 WHERE slug = 'ui'");
    }
} catch (Exception $e) {
}


echo json_encode([
    'success' => true,
    'html' => $html,
    'css' => $css
]);
