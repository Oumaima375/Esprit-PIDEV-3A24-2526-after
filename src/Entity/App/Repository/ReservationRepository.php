<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Users;
use App\Entity\Voyage;
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

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.statut = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findById(int $id): ?Reservation
    {
        return $this->find($id);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function create(
        int $voyageId,
        int $utilisateurId,
        ?string $type,
        ?string $lieu,
        ?string $description,
        string $dateReservation,
        string $statut,
        int $nbPersonnes,
        float $prixTotal,
    ): Reservation {
        $em = $this->getEntityManager();

        $reservation = new Reservation();

        /** @var \App\Entity\Voyage|null $voyage */
        $voyage = $voyageId > 0 ? $em->getRepository(Voyage::class)->find($voyageId) : null;
        $reservation->setVoyage($voyage);

        /** @var \App\Entity\Users|null $user */
        $user = $utilisateurId > 0 ? $em->getRepository(Users::class)->find($utilisateurId) : null;
        $reservation->setUser($user);

        $reservation->setType($type ?? '');
        $reservation->setLieu($lieu ?? '');
        $reservation->setDescription($description ?? '');
        $reservation->setStatut($statut);
        $reservation->setNbPersonnes($nbPersonnes);
        $reservation->setPrixTotal($prixTotal);

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateReservation);
        if ($date === false) {
            $date = new \DateTimeImmutable();
        }
        $reservation->setDateReservation($date);

        $em->persist($reservation);
        $em->flush();

        return $reservation;
    }

    public function update(
        int $id,
        int $voyageId,
        int $utilisateurId,
        ?string $type,
        ?string $lieu,
        ?string $description,
        string $dateReservation,
        string $statut,
        int $nbPersonnes,
        float $prixTotal,
    ): void {
        $em = $this->getEntityManager();

        $reservation = $this->find($id);
        if (!$reservation) {
            return;
        }

        /** @var \App\Entity\Voyage|null $voyage */
        $voyage = $voyageId > 0 ? $em->getRepository(Voyage::class)->find($voyageId) : null;
        $reservation->setVoyage($voyage);

        /** @var \App\Entity\Users|null $user */
        $user = $utilisateurId > 0 ? $em->getRepository(Users::class)->find($utilisateurId) : null;
        $reservation->setUser($user);

        $reservation->setType($type ?? '');
        $reservation->setLieu($lieu ?? '');
        $reservation->setDescription($description ?? '');
        $reservation->setStatut($statut);
        $reservation->setNbPersonnes($nbPersonnes);
        $reservation->setPrixTotal($prixTotal);

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateReservation);
        if ($date !== false) {
            $reservation->setDateReservation($date);
        }

        $em->flush();
    }

    public function delete(int $id): void
    {
        $em = $this->getEntityManager();
        $reservation = $this->find($id);
        if ($reservation) {
            $em->remove($reservation);
            $em->flush();
        }
    }
}