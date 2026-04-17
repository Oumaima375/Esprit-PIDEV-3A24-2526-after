<?php

namespace App\Repository;

use App\Entity\Depense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Depense>
 */
class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depense::class);
    }

    /**
     * Retourne le total des dépenses pour une catégorie donnée.
     */
    public function getTotalByCategorie(int $categorieId): float
    {
        return $this->createQueryBuilder('d')
            ->select('SUM(d.montant)')
            ->where('d.categorie = :catId')
            ->setParameter('catId', $categorieId)
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0;
    }

    /**
     * Récupère les dépenses entre deux dates (pour le calendrier).
     */
    public function getDepensesByDateRange(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.dateDepense BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('d.dateDepense', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les dépenses ayant des coordonnées GPS (géolocalisation).
     */
    public function getDepensesWithLocation(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.latitude IS NOT NULL')
            ->andWhere('d.longitude IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les catégories dont le total des dépenses dépasse un pourcentage donné du budget.
     * Utile pour les notifications de budget.
     */
    public function getCategoriesDepassementBudget(float $seuilPourcentage = 80): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT c.id_cat, c.nom_categorie, c.budget_max, COALESCE(SUM(d.montant), 0) as total_depense
            FROM categorie c
            LEFT JOIN depense d ON d.id_categorie = c.id_cat
            WHERE c.budget_max IS NOT NULL
            GROUP BY c.id_cat
            HAVING total_depense > (c.budget_max * :seuil / 100)
        ";
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['seuil' => $seuilPourcentage]);
        return $result->fetchAllAssociative();
    }
}