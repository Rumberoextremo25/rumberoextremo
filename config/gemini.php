<?php
// config/gemini.php

return [
    'api_key' => env('GEMINI_API_KEY'),
    'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent',
];
