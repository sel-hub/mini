<?php
/**
 * AI Satranç Analizcisi — API Endpoint
 * POST ile FEN + hamle geçmişi alır, Gemini API'ye gönderir, yapılandırılmış JSON analiz döner.
 */
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../config.php';

// ── Sadece POST kabul et ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Sadece POST istekleri kabul edilir.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── CSRF Doğrulama ──
$csrfToken = $_POST['csrf_token'] ?? '';
if (!csrf_validate($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Geçersiz güvenlik token\'ı. Lütfen sayfayı yenileyip tekrar deneyin.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── FEN Validasyonu ──
$fen = trim($_POST['fen'] ?? '');
$moves = trim($_POST['moves'] ?? '');

if (empty($fen)) {
    echo json_encode(['success' => false, 'error' => 'FEN pozisyonu boş bırakılamaz.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Standart FEN regex kontrolü
$fenRegex = '/^([rnbqkpRNBQKP1-8\/]+)\s+([wb])\s+([KQkq-]+)\s+([-a-h1-8]+)\s+(\d+)\s+(\d+)$/';
if (!preg_match($fenRegex, $fen)) {
    echo json_encode(['success' => false, 'error' => 'Geçersiz FEN formatı. Lütfen doğru bir FEN pozisyonu girin.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Gemini API İsteği ──
$prompt = <<<PROMPT
Sen usta bir Türk satranç koçusun. Aşağıdaki satranç partisini analiz et.

Hamle geçmişi (SAN formatında): {$moves}
Son pozisyon (FEN): {$fen}

ÖNEMLİ — TÜRKÇE SATRANÇ TERİMLERİ:
Taş isimlerini mutlaka doğru Türkçe satranç terminolojisiyle yaz:
- Queen = VEZİR (Kraliçe YAZMA, kesinlikle "vezir" de)
- Bishop = FİL (Piskopos YAZMA, kesinlikle "fil" de)
- Knight = AT (Şövalye YAZMA, kesinlikle "at" de)
- Rook = KALE (Hisar YAZMA, kesinlikle "kale" de)
- King = ŞAH (Kral YAZMA, kesinlikle "şah" de)
- Pawn = PİYON
Bu kurala kesinlikle uy. "Kraliçe", "piskopos", "şövalye", "kral" gibi çeviriler YANLIŞTIR.

Görevlerin:
1. Partiyi genel olarak yorumla (kimin avantajlı olduğu, kritik anlar, stratejik temalar).
2. Partinin açılış ismini tespit et (Örn: "Sicilya Savunması: Najdorf Varyantı").
3. Her hamle için kısa bir yorum yap. Bazı hamlelerde daha iyi bir alternatif varsa, alternatif hamleyi SAN formatında öner ve nedenini açıkla.

Yanıtını kesinlikle aşağıdaki JSON formatında ver, başka hiçbir şey ekleme (yani ```json falan yazma, direkt JSON döndür):
{
  "opening": "Tespit edilen açılış ismi",
  "overall": "Partinin genel değerlendirmesi burada (2-3 cümle, Türkçe, samimi ve eğitici)",
  "moves": [
    {
      "moveNum": 1,
      "side": "white",
      "played": "e4",
      "comment": "Klasik ve güçlü bir açılış hamlesi.",
      "alternative": null,
      "altReason": null
    },
    {
      "moveNum": 1,
      "side": "black",
      "played": "c5",
      "comment": "Sicilya savunması, agresif bir tercih. Fil ve vezir için merkez kontrolü sağlıyor.",
      "alternative": "e5",
      "altReason": "e5 ile daha simetrik ve sağlam bir piyon yapısı kurulabilirdi."
    }
  ]
}

Kurallar:
- "alternative" ve "altReason" alanları, sadece daha iyi bir hamle varsa doldurulmalı, yoksa null olmalı.
- Alternatif hamleler mutlaka standart SAN formatında olmalı (örn: Nf3, Bb5, O-O, Qxd4).
- Hamle numaraları ve taraflar doğru eşleşmeli.
- Türkçe, samimi ve öğretici bir dil kullan.
- Taş isimlerinde MUTLAKA Türkçe satranç terimlerini kullan: vezir, fil, at, kale, şah, piyon.
- "Kraliçe", "piskopos", "şövalye" veya "kral" kelimeleri kesinlikle KULLANMA.
- Sadece JSON döndür, başka metin ekleme.
PROMPT;

$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY;

$requestBody = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ]
], JSON_UNESCAPED_UNICODE);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $requestBody,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json; charset=UTF-8',
    ],
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// ── cURL Hata Kontrolü ──
if ($response === false || !empty($curlError)) {
    echo json_encode(['success' => false, 'error' => 'Şu an analiz yapılamıyor. (Bağlantı hatası)'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── HTTP Durum Kontrolü ──
if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'error' => 'Şu an analiz yapılamıyor. (API hatası: ' . $httpCode . ')'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Yanıtı parse et ──
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'Şu an analiz yapılamıyor. (Yanıt ayrıştırma hatası)'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Gemini API yanıt yapısından metin çıkar
$analysisText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (empty($analysisText)) {
    echo json_encode(['success' => false, 'error' => 'Şu an analiz yapılamıyor. (AI yanıt vermedi)'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── JSON parse dene ──
// Gemini bazen ```json ... ``` ile sarmalayabiliyor, temizle
$cleanedText = trim($analysisText);
$cleanedText = preg_replace('/^```json\s*/i', '', $cleanedText);
$cleanedText = preg_replace('/\s*```$/i', '', $cleanedText);
$cleanedText = trim($cleanedText);

$analysisJson = json_decode($cleanedText, true);

// ── Kullanım sayacını artır ──
try {
    $stmt = $pdo->prepare('UPDATE tools SET usage_count = usage_count + 1 WHERE slug = :slug');
    $stmt->execute(['slug' => 'chess']);
} catch (PDOException $e) {
    // Sayaç hatası kritik değil, devam et
}

// ── Başarılı yanıt ──
if ($analysisJson && isset($analysisJson['overall'])) {
    // Yapılandırılmış yanıt döndür
    echo json_encode([
        'success' => true,
        'structured' => true,
        'analysis' => $analysisJson
    ], JSON_UNESCAPED_UNICODE);
} else {
    // Yapılandırılamadıysa düz metin olarak döndür
    echo json_encode([
        'success' => true,
        'structured' => false,
        'analysis' => $analysisText
    ], JSON_UNESCAPED_UNICODE);
}
