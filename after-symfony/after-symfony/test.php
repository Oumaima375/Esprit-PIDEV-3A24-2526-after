<?php

$apiKey = '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';

$data = json_encode([
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'user', 'content' => 'Bonjour']
    ],
    'max_tokens' => 100,
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data),
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "CURL Error: " . $error . "\n";
} else {
    echo $response . "\n";
}