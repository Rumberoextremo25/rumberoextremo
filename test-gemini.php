<?php
// test-gemini.php
require __DIR__ . '/vendor/autoload.php';

$apiKey = 'AIzaSyCUqPmoU4OS9J9hcE49uRkY9CogPXnX8iE';

// Modelo correcto para Gemini 1.5
$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Hola, responde con un saludo amigable']
            ]
        ]
    ]
];

// Usar el modelo correcto: gemini-1.5-pro
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=' . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "URL: $url\n";
echo "HTTP Code: $httpCode\n";
echo "Response: " . $response . "\n";

if ($httpCode == 200) {
    echo "✅ Gemini funciona correctamente\n";
} else {
    echo "❌ Error en Gemini\n";
}