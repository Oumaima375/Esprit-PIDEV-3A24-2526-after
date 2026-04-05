<?php

namespace App\Repository;

use App\Entity\Activite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activite>
 */
class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activite::class);
    }

    public function findActiviteByNom(string $nom): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.nom LIKE :nom')
            ->setParameter('nom', '%'.$nom.'%')
            ->getQuery()
            ->getResult();
    }
    public function findActivitesSorted(string $sortBy): array
{
    $validFields = ['nom', 'prix', 'categorie', 'lieu'];
    
    if (!in_array($sortBy, $validFields)) {
        $sortBy = 'nom';
    }

    return $this->createQueryBuilder('a')
        ->orderBy('a.' . $sortBy, 'ASC')
        ->getQuery()
        ->getResult();
}
}