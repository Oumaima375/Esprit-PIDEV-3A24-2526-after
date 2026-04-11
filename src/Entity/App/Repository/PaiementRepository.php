<?php

namespace App\Repository;

use App\Entity\Paiement;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paiement>
 */
class PaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiement::class);
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function sumAllAmounts(): float
    {
        return (float) $this->createQueryBuilder('p')
            ->select('COALESCE(SUM(p.montant), 0)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findById(int $id): ?Paiement
    {
        return $this->find($id);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.datePaiement', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function create(
        string $reference,
        float $montant,
        string $devise,
        string $methode,
        string $statut,
        string $datePaiement,
        int $idReservation,
    ): Paiement {
        $em = $this->getEntityManager();

        $paiement = new Paiement();
        $paiement->setReference($reference);
        $paiement->setMontant($montant);
        $paiement->setDevise($devise);
        $paiement->setMethode($methode);
        $paiement->setStatut($statut);

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $datePaiement)
            ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $datePaiement)
            ?: new \DateTimeImmutable();
        $paiement->setDatePaiement($date);

        /** @var \App\Entity\Reservation|null $reservation */
        $reservation = $idReservation > 0 ? $em->getRepository(Reservation::class)->find($idReservation) : null;
        $paiement->setReservation($reservation);

        $em->persist($paiement);
        $em->flush();

        return $paiement;
    }

    public function update(
        int $id,
        string $reference,
        float $montant,
        string $devise,
        string $methode,
        string $statut,
        string $datePaiement,
        int $idReservation,
    ): void {
        $em = $this->getEntityManager();

        $paiement = $this->find($id);
        if (!$paiement) {
            return;
        }

        $paiement->setReference($reference);
        $paiement->setMontant($montant);
        $paiement->setDevise($devise);
        $paiement->setMethode($methode);
        $paiement->setStatut($statut);

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $datePaiement)
            ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $datePaiement);
        if ($date !== false) {
            $paiement->setDatePaiement($date);
        }

        /** @var \App\Entity\Reservation|null $reservation */
        $reservation = $idReservation > 0 ? $em->getRepository(Reservation::class)->find($idReservation) : null;
        $paiement->setReservation($reservation);

        $em->flush();
    }

    public function delete(int $id): void
    {
        $em = $this->getEntityManager();
        $paiement = $this->find($id);
        if ($paiement) {
            $em->remove($paiement);
            $em->flush();
        }
    }
}