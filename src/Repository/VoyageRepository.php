<?php

namespace App\Repository;

use App\Entity\Voyage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VoyageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voyage::class);
    }

    public function findWithFilters($search = null, $prixMin = null, $prixMax = null, $dateDebut = null, $placesMin = null): array
{
    $qb = $this->createQueryBuilder('v');

    if ($search) {
        $qb->andWhere('v.titre LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }
    if ($prixMin) {
        $qb->andWhere('v.prix >= :prixMin')
           ->setParameter('prixMin', $prixMin);
    }
    if ($prixMax) {
        $qb->andWhere('v.prix <= :prixMax')
           ->setParameter('prixMax', $prixMax);
    }
    if ($dateDebut) {
        $qb->andWhere('v.date_debut >= :dateDebut')
           ->setParameter('dateDebut', new \DateTime($dateDebut));
    }
    if ($placesMin) {
        $qb->andWhere('v.nb_places >= :placesMin')
           ->setParameter('placesMin', $placesMin);
    }

    return $qb->orderBy('v.id_voyage', 'DESC')->getQuery()->getResult();
}
}