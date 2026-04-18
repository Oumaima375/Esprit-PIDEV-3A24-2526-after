<?php

namespace App\Service;

use App\Entity\Offre;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Intelligent offer recommendation engine.
 *
 * Logic:
 *  - User provides a "mood" keyword (burnout, rupture, aventure, famille…)
 *  - We score each offer by matching service category + title keywords
 *  - We also factor in price range preference and availability
 *
 * This is pure PHP – no external AI API needed.
 * (Can be extended later to call Gemini/Groq for richer matching.)
 */
class OffreRecommandationService
{
    /** Map mood → scored keywords (service category or offer title fragments) */
    private const MOOD_KEYWORDS = [
        'burnout'   => ['spa', 'nature', 'bien-être', 'détente', 'repos', 'yoga', 'relaxation', 'montagne', 'forêt'],
        'rupture'   => ['solo', 'détente', 'voyage', 'mer', 'plage', 'découverte', 'liberté', 'escapade'],
        'aventure'  => ['aventure', 'sport', 'randonnée', 'safari', 'escalade', 'trek', 'jungle'],
        'famille'   => ['famille', 'enfants', 'parc', 'séjour', 'circuit', 'resort', 'club'],
        'romantique'=> ['romantique', 'couple', 'luxe', 'îles', 'croisière', 'villa', 'bali', 'maldives'],
        'culture'   => ['culture', 'histoire', 'musée', 'patrimoine', 'city', 'visite', 'paris', 'rome', 'tokyo'],
        'budget'    => [], // handled by low price scoring below
    ];

    public function __construct(private readonly EntityManagerInterface $em) {}

    /**
     * Returns the top $limit offers sorted by relevance score for the given mood.
     *
     * @param  string  $mood      One of the MOOD_KEYWORDS keys, or any free text
     * @param  float   $maxPrix   Optional price ceiling (0 = no limit)
     * @param  int     $limit
     * @return array<array{offre: Offre, score: int, reasons: string[]}>
     */
    public function recommend(string $mood, float $maxPrix = 0, int $limit = 3): array
    {
        $mood   = mb_strtolower(trim($mood));
        $offres = $this->em->getRepository(Offre::class)->findAll();

        $keywords = self::MOOD_KEYWORDS[$mood] ?? $this->extractKeywords($mood);

        $scored = [];
        foreach ($offres as $offre) {
            // Skip unavailable offers
            if (!$offre->isDisponible()) {
                continue;
            }
            // Price filter
            if ($maxPrix > 0 && $offre->getPrix() > $maxPrix) {
                continue;
            }

            [$score, $reasons] = $this->scoreOffre($offre, $keywords, $mood, $maxPrix);

            if ($score > 0) {
                $scored[] = ['offre' => $offre, 'score' => $score, 'reasons' => $reasons];
            }
        }

        // Sort by score desc
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    // ─────────────────────────────────────────────────────────────────
    private function scoreOffre(Offre $offre, array $keywords, string $mood, float $maxPrix): array
    {
        $score   = 0;
        $reasons = [];

        $haystack = mb_strtolower(
            $offre->getTitre() . ' ' .
            ($offre->getDestination() ?? '') . ' ' .
            $offre->getService()->getNomService() . ' ' .
            $offre->getService()->getDescription()
        );

        foreach ($keywords as $kw) {
            if (str_contains($haystack, $kw)) {
                $score += 10;
                $reasons[] = "Correspond à « $kw »";
            }
        }

        // Popularity bonus (favorites)
        if ($offre->getFavorisCount() > 0) {
            $bonus = min($offre->getFavorisCount(), 5);
            $score += $bonus;
            $reasons[] = "❤️ {$offre->getFavorisCount()} favori(s)";
        }

        // Budget-friendly bonus
        if ($mood === 'budget' || ($maxPrix > 0 && $offre->getPrix() <= $maxPrix * 0.7)) {
            $score += 5;
            $reasons[] = "💰 Bon rapport qualité/prix";
        }

        // Availability bonus (offer expiring soon → boost visibility)
        $joursRestants = $offre->joursRestants();
        if ($joursRestants !== null && $joursRestants <= 7 && $joursRestants > 0) {
            $score += 3;
            $reasons[] = "⏰ Expire dans $joursRestants jour(s)";
        }

        return [$score, $reasons];
    }

    private function extractKeywords(string $mood): array
    {
        // Tokenize free-text mood into 2+ char words
        return array_filter(
            explode(' ', preg_replace('/[^a-z0-9àâçéèêëîïôùûü\s]/u', '', $mood)),
            fn($w) => mb_strlen($w) >= 2
        );
    }
}