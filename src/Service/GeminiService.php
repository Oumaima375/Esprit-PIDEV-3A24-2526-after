<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private string $apiKey;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        // ← Clé hardcodée pour éviter les problèmes .env
        $this->apiKey = 'AIzaSyD52wwRa_tVbNf70w8FPrySriLQVqYnmgg';
    }
        public function detecterCategorie(string $nomDocument, array $categories): ?string
        {
            if (empty($categories)) return null;

            $listeCategories = implode(', ', $categories);

            try {
                $response = $this->client->request(
                    'POST',
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->apiKey,
                    [
                        'timeout' => 15,
                        'json'    => [
                            'contents' => [[
                                'parts' => [[
                                    'text' =>
                                        "Tu es un assistant de classification. " .
                                        "Document de voyage : '$nomDocument'. " .
                                        "Catégories disponibles : $listeCategories. " .
                                        "Réponds avec UNIQUEMENT le nom exact d'une catégorie de la liste. " .
                                        "Pas d'explication, pas de ponctuation, juste le nom."
                                ]]
                            ]],
                            'generationConfig' => [
                                'temperature'     => 0.0,
                                'maxOutputTokens' => 30,
                            ]
                        ]
                    ]
                );

                $data     = $response->toArray();
                $resultat = trim($data['candidates'][0]['content']['parts'][0]['text']);
                $resultat = preg_replace('/[^a-zA-ZÀ-ÿ0-9\s\'\-]/u', '', $resultat);
                $resultat = trim($resultat);

                error_log('🤖 Gemini input: ' . $nomDocument);
                error_log('🤖 Gemini output brut: ' . $resultat);

                // ← Match exact insensible casse + espaces
                foreach ($categories as $cat) {
                    if (strtolower(trim($cat)) === strtolower(trim($resultat))) {
                        error_log('✅ Match exact: ' . $cat);
                        return $cat;
                    }
                }

                // ← Match partiel insensible casse
                foreach ($categories as $cat) {
                    if (stripos($resultat, trim($cat)) !== false) {
                        error_log('✅ Match partiel A: ' . $cat);
                        return $cat;
                    }
                    if (stripos(trim($cat), $resultat) !== false) {
                        error_log('✅ Match partiel B: ' . $cat);
                        return $cat;
                    }
                }

                // ← Match par mots clés si Gemini répond autrement
                $motsDocument = explode(' ', strtolower($nomDocument));
                foreach ($categories as $cat) {
                    foreach ($motsDocument as $mot) {
                        if (strlen($mot) > 3 && stripos($cat, $mot) !== false) {
                            error_log('✅ Match mot-clé: ' . $cat . ' via ' . $mot);
                            return $cat;
                        }
                    }
                }

                error_log('❌ Aucun match pour: ' . $resultat);
                return null;

            } catch (\Exception $e) {
                error_log('❌ Gemini erreur: ' . $e->getMessage());
                return null;
            }
        }
        }