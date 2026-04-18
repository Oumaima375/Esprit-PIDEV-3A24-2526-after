<?php

namespace App\Service;

class ChatbotService
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Standard chat — uses the default travel assistant system prompt.
     */
    public function chat(array $messages): string
    {
        $systemMessage = [
            'role'    => 'system',
            'content' => 'Tu es un assistant de voyage expert. Tu aides les utilisateurs à planifier leurs voyages, choisir des destinations, des activités, gérer leur budget. Réponds toujours en français de manière amicale et professionnelle.',
        ];

        return $this->callGroq(array_merge([$systemMessage], $messages));
    }

    /**
     * Admin chat — caller passes a fully-built system prompt with live DB context.
     */
    public function chatWithSystem(array $messages, string $systemContent): string
    {
        $systemMessage = [
            'role'    => 'system',
            'content' => $systemContent,
        ];

        return $this->callGroq(array_merge([$systemMessage], $messages));
    }

    private function callGroq(array $allMessages): string
    {
        // Guard: if no API key configured, return helpful message instead of crashing
        if (empty(trim($this->apiKey))) {
            throw new \Exception(
                'Clé API GROQ non configurée. Ajoutez GROQ_API_KEY dans votre fichier .env'
            );
        }

        $data = json_encode([
            'model'       => 'llama-3.3-70b-versatile',
            'messages'    => $allMessages,
            'max_tokens'  => 1024,
            'temperature' => 0.7,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            'https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT,        30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        // Network-level error (DNS failure, timeout, etc.)
        if ($response === false) {
            throw new \Exception('Erreur réseau lors de la connexion à GROQ : ' . $curlErr);
        }

        $result = json_decode($response, true);

        // HTTP 401 → invalid / expired API key
        if ($httpCode === 401) {
            throw new \Exception(
                'Clé API GROQ invalide ou expirée (HTTP 401). ' .
                'Générez une nouvelle clé sur console.groq.com et mettez à jour GROQ_API_KEY dans .env, ' .
                'puis exécutez : php bin/console cache:clear'
            );
        }

        // HTTP 429 → rate-limited
        if ($httpCode === 429) {
            throw new \Exception(
                'Limite de requêtes GROQ atteinte (HTTP 429). Réessayez dans quelques secondes.'
            );
        }

        // Any other non-200 or missing choices
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new \Exception(
                'Réponse inattendue de GROQ (HTTP ' . $httpCode . ') : ' . json_encode($result)
            );
        }

        return $result['choices'][0]['message']['content'];
    }
}