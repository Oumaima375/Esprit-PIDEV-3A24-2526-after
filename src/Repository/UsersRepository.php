<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    // ─── RECHERCHE & FILTRAGE ────────────────────────────────────────────────

    /**
     * Recherche par nom, prénom ou email (utilisé par la toolbar).
     */
    public function searchByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.nom) LIKE :kw')
            ->orWhere('LOWER(u.prenom) LIKE :kw')
            ->orWhere('LOWER(u.email) LIKE :kw')
            ->setParameter('kw', '%' . strtolower($keyword) . '%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtre par type d'utilisateur (admin, voyageur, employe…).
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.type_utilisateur) = :role')
            ->setParameter('role', strtolower($role))
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche combinée : mot-clé + rôle (utilisé par le filtre de la page index).
     */
    public function findByFilters(?string $keyword, ?string $role): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($keyword) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(u.nom) LIKE :kw',
                    'LOWER(u.prenom) LIKE :kw',
                    'LOWER(u.email) LIKE :kw',
                    'u.telephone LIKE :kw'
                )
            )->setParameter('kw', '%' . strtolower($keyword) . '%');
        }

        if ($role) {
            $qb->andWhere('LOWER(u.type_utilisateur) = :role')
               ->setParameter('role', strtolower($role));
        }

        return $qb->orderBy('u.nom', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    // ─── STATISTIQUES ────────────────────────────────────────────────────────

    /**
     * Nombre total d'utilisateurs.
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre d'utilisateurs par rôle.
     */
    public function countByRole(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('LOWER(u.type_utilisateur) = :role')
            ->setParameter('role', strtolower($role))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne le nombre d'utilisateurs groupés par rôle.
     * Exemple de retour : [['role' => 'admin', 'total' => 5], ...]
     */
    public function countGroupedByRole(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.type_utilisateur AS role, COUNT(u.id) AS total')
            ->groupBy('u.type_utilisateur')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre d'utilisateurs vérifiés / non vérifiés.
     */
    public function countVerified(bool $verified = true): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.is_verified = :v')
            ->setParameter('v', $verified)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ─── VÉRIFICATION ────────────────────────────────────────────────────────

    /**
     * Trouve un utilisateur par son token de vérification.
     */
    public function findByVerificationToken(string $token): ?Users
    {
        return $this->createQueryBuilder('u')
            ->where('u.verification_token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les utilisateurs dont le token de vérification est expiré
     * et qui ne sont pas encore vérifiés.
     */
    public function findExpiredUnverified(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.is_verified = false')
            ->andWhere('u.verification_expiry < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    // ─── UNICITÉ ─────────────────────────────────────────────────────────────

    /**
     * Vérifie si un email est déjà utilisé (utile lors de la création / modification).
     */
    public function isEmailTaken(string $email, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('LOWER(u.email) = :email')
            ->setParameter('email', strtolower($email));

        if ($excludeId !== null) {
            $qb->andWhere('u.id != :id')
               ->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    // ─── EXPORT ──────────────────────────────────────────────────────────────

    /**
     * Retourne tous les utilisateurs triés par nom pour les exports CSV/PDF.
     */
    public function findAllForExport(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.type_utilisateur', 'ASC')
            ->addOrderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Export filtré par rôle (ex: exporter seulement les admins).
     */
    public function findForExportByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.type_utilisateur) = :role')
            ->setParameter('role', strtolower($role))
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ─── PAGINATION ──────────────────────────────────────────────────────────

    /**
     * Retourne une page d'utilisateurs (pour une pagination manuelle).
     *
     * @param int $page    Numéro de page (commence à 1)
     * @param int $perPage Nombre d'éléments par page
     */
    public function findPaginated(int $page = 1, int $perPage = 12): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre total de pages.
     */
    public function countPages(int $perPage = 12): int
    {
        $total = $this->countAll();
        return (int) ceil($total / $perPage);
    }
}
