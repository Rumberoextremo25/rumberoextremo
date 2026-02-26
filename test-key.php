<?php
// test-key.php
$apiKey = 'AIzaSyCUqPmoU4OS9J9hcE49uRkY9CogPXnX8iE';

// Endpoint para listar modelos (esto siempre debería funcionar si la key es válida)
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response:\n" . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n";

if ($httpCode == 200) {
    echo "✅ API Key VÁLIDA\n";
    $data = json_decode($response, true);
    echo "Modelos disponibles:\n";
    foreach ($data['models'] as $model) {
        echo "- " . $model['name'] . "\n";
    }
} else {
    echo "❌ API Key INVÁLIDA o problema con la cuenta\n";
}