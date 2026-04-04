<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private string $apiKey = 'AIzaSyAK-TbfIO1XovUD6ha9WCDBOWUKgSfDL6g';
    
    public function __construct(private HttpClientInterface $httpClient) {}

    public function detectCategorie(string $nomDocument): string
    {
        try {
            $response = $this->httpClient->request('POST',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->apiKey,
                [
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => "Tu es un classificateur de documents de voyage. 
                                        Classifie ce document : '$nomDocument' 
                                        dans UNE SEULE catégorie parmi : 
                                        PASSEPORT, VISA, BILLET, ASSURANCE, HÉBERGEMENT, DOCUMENT IDENTITÉ, Autorisation d'entrée, Autre.
                                        Réponds avec SEULEMENT le nom de la catégorie, rien d'autre."
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );

            $data = $response->toArray();
            $categorie = trim($data['candidates'][0]['content']['parts'][0]['text']);
            return $categorie;

        } catch (\Exception $e) {
            return 'Autre';
        }
    }
}