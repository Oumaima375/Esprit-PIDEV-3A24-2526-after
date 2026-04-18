<?php

namespace App\Repository;

use App\Entity\Depense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depense::class);
    }

    public function getTotalByCategorie(int $categorieId): float
    {
        return $this->createQueryBuilder('d')
            ->select('SUM(d.montant)')
            ->where('d.id_categorie = :catId')
            ->setParameter('catId', $categorieId)
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0;
    }

    public function getTotalGlobal(): float
    {
        return $this->createQueryBuilder('d')
            ->select('SUM(d.montant)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0;
    }

    public function getDepensesByDateRange(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.date_depense BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('d.date_depense', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getDepensesWithLocation(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.latitude IS NOT NULL')
            ->andWhere('d.longitude IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}