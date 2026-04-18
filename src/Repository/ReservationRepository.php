<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Voyage;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Called by DashboardController::createReservation()
     * Fixed: use correct entity setter names matching Reservation.php
     */
    public function create(
        int     $voyageId,
        int     $utilisateurId,
        ?string $type,
        ?string $lieu,
        ?string $description,
        string  $dateReservation,
        string  $statut,
        int     $nbPersonnes,
        float   $prixTotal,
    ): Reservation {
        $em = $this->getEntityManager();

        // Resolve the Voyage entity
        $voyage = $em->getReference(Voyage::class, $voyageId);

        // Resolve the User entity (null-safe if no user logged in)
        $user = $utilisateurId > 0
            ? $em->getReference(Users::class, $utilisateurId)
            : null;

        $reservation = new Reservation();
        $reservation->setVoyage($voyage);
        $reservation->setUser($user);                                      // ✅ fixed: was setId_utilisateur()
        $reservation->setType($type);
        $reservation->setLieu($lieu);
        $reservation->setDescription($description);
        $reservation->setDateReservation(new \DateTime($dateReservation)); // ✅ fixed: was setDate_reservation()
        $reservation->setStatut($statut);
        $reservation->setNbPersonnes($nbPersonnes);                        // ✅ fixed: was setNb_personnes()
        $reservation->setPrixTotal($prixTotal);                            // ✅ fixed: was setPrix_total()

        $em->persist($reservation);
        $em->flush();

        return $reservation;
    }

    public function findById(int $id): ?Reservation
    {
        return $this->find($id);
    }

    public function countAll(): int
    {
        return $this->count([]);
    }

    public function countByStatus(string $statut): int
    {
        return $this->count(['statut' => $statut]);
    }

    public function delete(int $id): void
    {
        $reservation = $this->find($id);
        if ($reservation) {
            $this->getEntityManager()->remove($reservation);
            $this->getEntityManager()->flush();
        }
    }
}