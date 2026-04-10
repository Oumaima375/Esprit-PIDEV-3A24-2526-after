<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ConseilsService
{
    private HttpClientInterface $client;
    private string $apiKey = 'AIzaSyAK-TbfIO1XovUD6ha9WCDBOWUKgSfDL6g';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function genererConseils(
        string $nomDocument,
        string $categorie,
        ?string $dateExpiration
    ): array {
        try {
            $expireInfo = $dateExpiration
                ? "expire le $dateExpiration"
                : "sans date d'expiration";

            $prompt =
                "Tu es un assistant expert en voyages internationaux. " .
                "Un voyageur possède ce document : '$nomDocument' " .
                "de catégorie '$categorie' qui $expireInfo. " .
                "Donne exactement 3 conseils pratiques et personnalisés pour ce document. " .
                "Réponds UNIQUEMENT en JSON valide avec ce format exact, sans texte avant ou après : " .
                '[{"titre":"conseil court","description":"explication détaillée","urgence":"haute|moyenne|basse"},' .
                '{"titre":"...","description":"...","urgence":"..."},' .
                '{"titre":"...","description":"...","urgence":"..."}]';

            $response = $this->client->request(
                'POST',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->apiKey,
                [
                    'timeout' => 15,
                    'json'    => [
                        'contents' => [[
                            'parts' => [['text' => $prompt]]
                        ]],
                        'generationConfig' => [
                            'temperature'     => 0.7,
                            'maxOutputTokens' => 500,
                        ]
                    ]
                ]
            );

            $data   = $response->toArray();
            $texte  = trim($data['candidates'][0]['content']['parts'][0]['text']);

            // Nettoyer le JSON
            $texte  = preg_replace('/```json|```/', '', $texte);
            $texte  = trim($texte);

            $conseils = json_decode($texte, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($conseils)) {
                return $conseils;
            }

            return $this->conseilsDefaut($categorie, $dateExpiration);

        } catch (\Exception $e) {
            error_log('ConseilsService erreur: ' . $e->getMessage());
            return $this->conseilsDefaut($categorie, $dateExpiration);
        }
    }

    // Conseils par défaut si Gemini échoue
    private function conseilsDefaut(string $categorie, ?string $dateExpiration): array
    {
        $conseils = [
            [
                'titre'       => '📋 Gardez une copie',
                'description' => 'Faites toujours une photocopie de vos documents importants et stockez-les séparément de vos originaux.',
                'urgence'     => 'moyenne'
            ],
            [
                'titre'       => '📱 Version numérique',
                'description' => 'Scannez et sauvegardez vos documents dans le cloud (Google Drive, iCloud) pour y accéder partout.',
                'urgence'     => 'basse'
            ],
        ];

        if ($dateExpiration) {
            $conseils[] = [
                'titre'       => '⏰ Vérifiez la validité',
                'description' => "Ce document expire le $dateExpiration. Pensez à le renouveler au moins 6 mois avant l'expiration.",
                'urgence'     => 'haute'
            ];
        }

        return $conseils;
    }
}