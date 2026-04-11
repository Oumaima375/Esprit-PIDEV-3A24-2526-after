<?php

namespace App\Repository;

use App\Entity\Planning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planning::class);
    }

    // ===================== RECHERCHE =====================
    public function findPlanningByNom(string $nom): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.activite', 'a')
            ->where('a.nom LIKE :nom')
            ->setParameter('nom', '%'.$nom.'%')
            ->getQuery()
            ->getResult();
    }

    // ===================== TRI =====================
    public function findPlanningsSorted(string $sortBy): array
    {
        $validFields = [
            'nom'       => 'a.nom',
            'lieu'      => 'a.lieu',
            'categorie' => 'a.categorie',
            'prix'      => 'a.prix',
            'date'      => 'p.dateActivite',
            'duree'     => 'p.duree',
        ];

        $orderField = $validFields[$sortBy] ?? 'a.nom';

        return $this->createQueryBuilder('p')
            ->join('p.activite', 'a')
            ->orderBy($orderField, 'ASC')
            ->getQuery()
            ->getResult();
    }
}