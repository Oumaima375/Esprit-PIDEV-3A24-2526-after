<?php

namespace App\Service;

class CategorieDetectorService
{
    // Dictionnaire mots-clés → catégorie
    private array $regles = [
        'BILLET' => [
            'billet', 'avion', 'vol', 'flight', 'ticket', 'embarquement',
            'boarding', 'air', 'airline', 'airways', 'train', 'sncf',
            'tgv', 'ferry', 'bateau', 'bus', 'transport'
        ],
        'ASSURANCE' => [
            'assurance', 'insurance', 'couverture', 'garantie',
            'protection', 'médical', 'medical', 'santé', 'sante',
            'accident', 'rapatriement', 'mutuelle'
        ],
        'HÉBERGEMENT' => [
            'hotel', 'hôtel', 'hébergement', 'hebergement', 'airbnb',
            'booking', 'reservation', 'réservation', 'chambre', 'logement',
            'appartement', 'hostel', 'auberge', 'villa', 'resort'
        ],
        'Documents d\'identité' => [
            'passeport', 'passport', 'carte identite', 'carte d identite',
            'cni', 'identite', 'identité', 'identity', 'cin', 'permis'
        ],
        'Autorisation d\'entrée' => [
            'visa', 'autorisation', 'entree', 'entrée', 'frontiere',
            'frontière', 'transit', 'sejour', 'séjour', 'residence',
            'résidence', 'immigration', 'esta', 'eta', 'permis entree'
        ],
    ];

    public function detecterCategorie(string $nomDocument, array $categoriesDisponibles): ?string
    {
        $nomLower = strtolower(trim($nomDocument));

        // Score par catégorie
        $scores = [];

        foreach ($this->regles as $categorie => $motsCles) {
            // Vérifier que la catégorie existe en BDD
            if (!in_array($categorie, $categoriesDisponibles)) {
                continue;
            }

            $score = 0;
            foreach ($motsCles as $mot) {
                if (str_contains($nomLower, strtolower($mot))) {
                    $score++;
                }
            }

            if ($score > 0) {
                $scores[$categorie] = $score;
            }
        }

        if (empty($scores)) {
            return null;
        }

        // Retourner la catégorie avec le score le plus élevé
        arsort($scores);
        return array_key_first($scores);
    }
}