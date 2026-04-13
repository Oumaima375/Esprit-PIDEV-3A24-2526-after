<?php

namespace App\Controller;

use App\Entity\ReservationActivite;
use App\Repository\PlanningRepository;
use App\Repository\ActiviteRepository;
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
        $data       = json_decode($request->getContent(), true);
        $planningId = $data['planningId'] ?? null;
        $nom        = $data['nom'] ?? null;
        $prenom     = $data['prenom'] ?? null;
        $email      = $data['email'] ?? null;

        if (!$planningId || !$nom || !$prenom || !$email) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        $planning = $planningRepository->find($planningId);
        if (!$planning) {
            return $this->json(['error' => 'Planning introuvable'], 404);
        }

        if ($planning->getReservations()->count() > 0) {
            return $this->json(['error' => 'Déjà réservé'], 409);
        }

        $reservation = new ReservationActivite();
        $reservation->setNom($nom);
        $reservation->setPrenom($prenom);
        $reservation->setEmail($email);
        $reservation->setPlanning($planning);
        $reservation->setStatut('confirmée');
        $reservation->setCreatedAt(new \DateTime());

        $em->persist($reservation);
        $em->flush();

        return $this->json([
            'success' => true,
            'reservation' => [
                'nom'    => $nom,
                'prenom' => $prenom,
                'email'  => $email,
                'date'   => $planning->getDateActivite()->format('d/m/Y'),
                'heure'  => $planning->getHeureDebut(),
            ]
        ]);
    }

    #[Route('/activite/{id}/reservations', name: 'app_activite_reservations')]
    public function listReservations(
        int $id,
        ActiviteRepository $activiteRepository
    ): JsonResponse {
        $activite = $activiteRepository->find($id);
        if (!$activite) {
            return $this->json(['error' => 'Activité introuvable'], 404);
        }

        $data = [];
        foreach ($activite->getPlannings() as $planning) {
            foreach ($planning->getReservations() as $res) {
                $data[] = [
                    'nom'    => $res->getNom(),
                    'prenom' => $res->getPrenom(),
                    'email'  => $res->getEmail(),
                    'date'   => $planning->getDateActivite()->format('d/m/Y'),
                    'heure'  => $planning->getHeureDebut(),
                ];
            }
        }

        return $this->json($data);
    }
}