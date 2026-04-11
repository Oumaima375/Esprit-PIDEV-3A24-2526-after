<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

<<<<<<< HEAD
    // Add custom methods as needed
=======
    private function buildQuery(string $search, string $filtre, string $tri, string $ordre)
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.categorie', 'c');

        if (!empty($search)) {
            $qb->andWhere('d.nomDocument LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $today = new \DateTime();
        if ($filtre === 'expires') {
            $qb->andWhere('d.dateExpiration < :today')
               ->setParameter('today', $today);
        } elseif ($filtre === 'valides') {
            $qb->andWhere('d.dateExpiration >= :today OR d.dateExpiration IS NULL')
               ->setParameter('today', $today);
        } elseif ($filtre === 'bientot') {
            $soon = new \DateTime('+30 days');
            $qb->andWhere('d.dateExpiration BETWEEN :today AND :soon')
               ->setParameter('today', $today)
               ->setParameter('soon', $soon);
        }

        $allowedTri = ['nomDocument', 'dateAjout', 'dateExpiration'];
        if (in_array($tri, $allowedTri)) {
            $qb->orderBy('d.' . $tri, $ordre === 'DESC' ? 'DESC' : 'ASC');
        }

        return $qb;
    }

    // TODO after merge — remplacez buildQuery() par cette version avec filtre user
    // private function buildQuery(string $search, string $filtre, string $tri, string $ordre, ?object $user = null)
    // {
    //     $qb = $this->createQueryBuilder('d')
    //         ->leftJoin('d.categorie', 'c');
    //
    //     // Filtre par utilisateur connecté (sauf admin)
    //     if ($user !== null) {
    //         $qb->andWhere('d.user = :user')
    //            ->setParameter('user', $user);
    //     }
    //
    //     // ... reste du code identique
    // }

    public function findByFiltersQuery(string $search, string $filtre, string $tri, string $ordre)
    {
        return $this->buildQuery($search, $filtre, $tri, $ordre)->getQuery();
    }

    public function findByFilters(string $search, string $filtre, string $tri, string $ordre): array
    {
        return $this->buildQuery($search, $filtre, $tri, $ordre)->getQuery()->getResult();
    }

    // TODO after merge — ajoutez ces méthodes après intégration User
    // public function findByFiltersQueryForUser(string $search, string $filtre, string $tri, string $ordre, object $user)
    // {
    //     return $this->buildQuery($search, $filtre, $tri, $ordre, $user)->getQuery();
    // }
    //
    // public function findByUser(object $user): array
    // {
    //     return $this->createQueryBuilder('d')
    //         ->andWhere('d.user = :user')
    //         ->setParameter('user', $user)
    //         ->getQuery()
    //         ->getResult();
    // }
    //
    // public function findAllWithUser(): array
    // {
    //     return $this->createQueryBuilder('d')
    //         ->leftJoin('d.categorie', 'c')
    //         ->leftJoin('d.user', 'u')  // ← join user pour admin
    //         ->addSelect('u')
    //         ->getQuery()
    //         ->getResult();
    // }

    public function findExpired(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.dateExpiration < :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function findExpiringSoon(): array
    {
        $today = new \DateTime();
        $soon  = new \DateTime('+30 days');
        return $this->createQueryBuilder('d')
            ->andWhere('d.dateExpiration BETWEEN :today AND :soon')
            ->setParameter('today', $today)
            ->setParameter('soon', $soon)
            ->getQuery()
            ->getResult();
    }

    public function countByCategorie(): array
    {
        return $this->createQueryBuilder('d')
            ->select('c.libelle, COUNT(d.idDocument) as total')
            ->leftJoin('d.categorie', 'c')
            ->groupBy('c.libelle')
            ->getQuery()
            ->getResult();
    }

    public function getMonthlyUploads(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql  = "SELECT DATE_FORMAT(date_ajout, '%Y-%m') AS month, COUNT(id_document) as total 
                 FROM document GROUP BY month ORDER BY month ASC";
        return $conn->executeQuery($sql)->fetchAllAssociative();
    }
>>>>>>> gestionDocument-symfony
}