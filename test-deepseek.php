<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? 'no encontrada';

echo "=== DIAGNÓSTICO DEEPSEEK ===\n";
echo "API Key existe: " . (!empty($apiKey) ? 'SI' : 'NO') . "\n";
echo "API Key: " . substr($apiKey, 0, 10) . "...\n\n";

if (empty($apiKey)) {
    echo "❌ ERROR: No hay API Key en el .env\n";
    exit(1);
}

echo "Probando conexión con DeepSeek API...\n";

$ch = curl_init('https://api.deepseek.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model' => 'deepseek-chat',
    'messages' => [
        ['role' => 'user', 'content' => 'Hola, prueba']
    ]
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "Código HTTP: " . $httpCode . "\n";

if ($httpCode == 200) {
    echo "✅ CONEXIÓN EXITOSA!\n";
    $data = json_decode($response, true);
    echo "Respuesta: " . ($data['choices'][0]['message']['content'] ?? 'No hay respuesta') . "\n";
} else {
    echo "❌ ERROR:\n";
    echo "Código: $httpCode\n";
    echo "Respuesta: $response\n";
    echo "Error CURL: $error\n";
}