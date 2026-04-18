<?php

namespace App\Controller;

use App\Repository\ActiviteRepository;
use App\Repository\DestinationRepository;
use App\Repository\DocumentRepository;
use App\Repository\OffreRepository;
use App\Repository\PaiementRepository;
use App\Repository\ReservationRepository;
use App\Repository\ServiceRepository;
use App\Repository\UsersRepository;
use App\Repository\VoyageRepository;
use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'app_chatbot')]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }

    #[Route('/chatbot/send', name: 'app_chatbot_send', methods: ['POST'])]
    public function send(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $data     = json_decode($request->getContent(), true);
        $messages = $data['messages'] ?? [];

        try {
            $response = $chatbot->chat($messages);
            return $this->json(['response' => $response]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin-specific chatbot endpoint — injects live DB context into the system prompt.
     * Called from admin/dashboard.html.twig via fetch('/admin/chatbot/send', …)
     */
    #[Route('/admin/chatbot/send', name: 'app_admin_chatbot_send', methods: ['POST'])]
    public function adminSend(
        Request               $request,
        ChatbotService        $chatbot,
        VoyageRepository      $voyageRepo,
        DestinationRepository $destinationRepo,
        UsersRepository       $usersRepo,
        ActiviteRepository    $activiteRepo,
        ReservationRepository $reservationRepo,
        PaiementRepository    $paiementRepo,
        ServiceRepository     $serviceRepo,
        OffreRepository       $offreRepo,
        DocumentRepository    $documentRepo
    ): JsonResponse {

        $data     = json_decode($request->getContent(), true);
        $messages = $data['messages'] ?? [];

        // ── Build context strings ──────────────────────────────────────────────

        $voyages = $voyageRepo->findAll();
        $voyageLines = array_map(static fn($v) =>
            sprintf('  - [ID:%d] %s | Destination: %s | Prix: %.0f TND | Places: %d | %s → %s',
                $v->getId(),
                $v->getTitre(),
                $v->getIdDestination()?->getPays() ?? 'N/A',
                $v->getPrix(),
                $v->getNbPlaces(),
                $v->getDateDebut()?->format('d/m/Y') ?? '?',
                $v->getDateFin()?->format('d/m/Y') ?? '?'
            ), $voyages);

        $destinations = $destinationRepo->findAll();
        $destLines = array_map(static fn($d) =>
            sprintf('  - [ID:%d] %s — %s (%s)',
                $d->getId(), $d->getPays(), $d->getVille(), $d->getContinent()
            ), $destinations);

        $users = $usersRepo->findAll();
        $userLines = array_map(static fn($u) =>
            sprintf('  - [ID:%d] %s %s <%s> rôle: %s',
                $u->getId(),
                $u->getNom() ?? '',
                $u->getPrenom() ?? '',
                $u->getEmail(),
                in_array('ROLE_ADMIN', $u->getRoles()) ? 'Admin' : 'Client'
            ), $users);

        $activites = $activiteRepo->findAll();
        $activiteLines = array_map(static fn($a) =>
            sprintf('  - [ID:%d] %s | Catégorie: %s | Lieu: %s | Prix: %.0f TND',
                $a->getIdActivite(),
                $a->getNom() ?? 'N/A',
                $a->getCategorie() ?? 'N/A',
                $a->getLieu() ?? 'N/A',
                $a->getPrix() ?? 0
            ), $activites);

        $reservations = $reservationRepo->findAll();
        $resLines = array_map(static fn($r) =>
            sprintf('  - [ID:%d] Voyage#%s | User#%s | %s pers. | %.0f TND | Statut: %s',
                $r->getId(),
                $r->getVoyageId(),
                $r->getUtilisateurId(),
                $r->getNbPersonnes(),
                $r->getPrixTotal() ?? 0,
                $r->getStatut()
            ), $reservations);

        $payments = $paiementRepo->findAll();
        $totalRevenu = array_sum(array_map(static fn($p) => $p->getMontant(), $payments));
        $payLines = array_map(static fn($p) =>
            sprintf('  - [ID:%d] Réf:%s | %.0f %s | %s | %s',
                $p->getId(),
                $p->getReference(),
                $p->getMontant(),
                $p->getDevise(),
                $p->getMethode(),
                $p->getStatut()
            ), $payments);

        $services = $serviceRepo->findAll();
        $serviceLines = array_map(static fn($s) =>
            sprintf('  - [ID:%d] %s', $s->getId(), $s->getTitre() ?? $s->getNomService() ?? 'N/A'),
            $services);

        $offres = $offreRepo->findAll();
        $offreLines = array_map(static fn($o) =>
            sprintf('  - [ID:%d] %s | %.0f TND | %d jours',
                $o->getId(), $o->getTitre() ?? 'N/A', $o->getPrix() ?? 0, $o->getDuree() ?? 0),
            $offres);

        $docs = $documentRepo->findAll();
        $expired = $documentRepo->findExpired();

        // ── System prompt with live data ───────────────────────────────────────
        $systemContent = <<<EOT
Tu es AfterTravelBot, l'assistant IA intégré au tableau de bord administrateur de la plateforme "After Travel".
Tu as accès en temps réel aux données de la base de données.
Réponds TOUJOURS en français, de façon concise, précise et professionnelle.
Utilise des emojis pour rendre les réponses plus lisibles.
Si l'utilisateur demande une liste, formate-la proprement.
Si on te demande des stats, calcule et réponds directement.

═══════════════════════════════════════════════
📊 DONNÉES EN TEMPS RÉEL — After Travel
═══════════════════════════════════════════════

🗺️ VOYAGES (Total: {$this->count($voyages)}) :
{$this->joinLines($voyageLines)}

📍 DESTINATIONS (Total: {$this->count($destinations)}) :
{$this->joinLines($destLines)}

👥 UTILISATEURS (Total: {$this->count($users)}) :
{$this->joinLines($userLines)}

🎯 ACTIVITÉS (Total: {$this->count($activites)}) :
{$this->joinLines($activiteLines)}

📅 RÉSERVATIONS (Total: {$this->count($reservations)}) :
{$this->joinLines($resLines)}

💳 PAIEMENTS (Total: {$this->count($payments)} | Revenu: {$totalRevenu} TND) :
{$this->joinLines($payLines)}

💼 SERVICES (Total: {$this->count($services)}) :
{$this->joinLines($serviceLines)}

🎁 OFFRES (Total: {$this->count($offres)}) :
{$this->joinLines($offreLines)}

📄 DOCUMENTS : {$this->count($docs)} total, {$this->count($expired)} expirés
═══════════════════════════════════════════════

Exemples de questions que tu peux répondre :
- "Montre-moi les voyages en Espagne"
- "Combien de réservations en attente ?"
- "Quel est le revenu total ?"
- "Quels utilisateurs sont admins ?"
- "Liste les activités sportives"
EOT;

        try {
            $response = $chatbot->chatWithSystem($messages, $systemContent);
            return $this->json(['response' => $response]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function count(array $arr): int
    {
        return count($arr);
    }

    private function joinLines(array $lines): string
    {
        return empty($lines) ? '  (aucun)' : implode("\n", $lines);
    }
}