<?php
return [
    'api_key' => env('DEEPSEEK_API_KEY'),
    'api_url' => 'https://api.deepseek.com/v1/chat/completions',
    'model' => 'deepseek-chat',
    'timeout' => 30, // segundos
];