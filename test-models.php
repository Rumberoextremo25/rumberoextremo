<?php
// test-models.php
require __DIR__ . '/vendor/autoload.php';

$apiKey = 'AIzaSyCUqPmoU4OS9J9hcE49uRkY9CogPXnX8iE';
$modelos = [
    'gemini-1.5-pro',
    'gemini-1.5-flash',
    'gemini-pro',
];

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Hola, responde "OK" si funciono']
            ]
        ]
    ]
];

foreach ($modelos as $modelo) {
    echo "Probando modelo: $modelo\n";
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$modelo:generateContent?key=$apiKey";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    if ($httpCode == 200) {
        echo "✅ Modelo $modelo funciona!\n";
        $resp = json_decode($response, true);
        echo "Respuesta: " . ($resp['candidates'][0]['content']['parts'][0]['text'] ?? 'sin texto') . "\n";
    } else {
        echo "❌ Modelo $modelo falló\n";
    }
    echo str_repeat('-', 50) . "\n";
}