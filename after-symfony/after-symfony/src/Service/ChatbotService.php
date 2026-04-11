<?php

namespace App\Service;

class ChatbotService
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function chat(array $messages): string
    {
        $systemMessage = [
            'role' => 'system',
            'content' => 'Tu es un assistant de voyage expert. Tu aides les utilisateurs à planifier leurs voyages, choisir des destinations, des activités, gérer leur budget. Réponds toujours en français de manière amicale et professionnelle.'
        ];

        $allMessages = array_merge([$systemMessage], $messages);

        $data = json_encode([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => $allMessages,
            'max_tokens' => 1024,
            'temperature' => 0.7,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if (!isset($result['choices'])) {
            throw new \Exception('HTTP ' . $httpCode . ' | ' . json_encode($result));
        }

        return $result['choices'][0]['message']['content'];
    }
}