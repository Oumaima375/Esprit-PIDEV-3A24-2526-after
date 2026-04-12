<?php

namespace App\Controller;

use App\Entity\ReservationActivite;
use App\Repository\PlanningRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ReservationActiviteController extends AbstractController
{
    #[Route('/reservation/new', name: 'app_reservation_new', methods: ['POST'])]
    public function new(
        Request $request,
        PlanningRepository $planningRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data      = json_decode($request->getContent(), true);
        $planningId = $data['planningId'] ?? null;
        $nom        = $data['nom'] ?? null;
        $prenom     = $data['prenom'] ?? null;

        if (!$planningId || !$nom || !$prenom) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        $planning = $planningRepository->find($planningId);
        if (!$planning) {
            return $this->json(['error' => 'Planning introuvable'], 404);
        }

        // Vérifier si déjà réservé
        if ($planning->getReservations()->count() > 0) {
            return $this->json(['error' => 'Déjà réservé'], 409);
        }

        $reservation = new ReservationActivite();
        $reservation->setNom($nom);
        $reservation->setPrenom($prenom);
        $reservation->setPlanning($planning);
        $reservation->setStatut('confirmée');
        $reservation->setCreatedAt(new \DateTime());

        $em->persist($reservation);
        $em->flush();

        return $this->json(['success' => true]);
    }
}