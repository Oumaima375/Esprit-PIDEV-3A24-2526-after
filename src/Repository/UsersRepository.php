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

    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.type_utilisateur) = :role')
            ->setParameter('role', strtolower($role))
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

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

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByRole(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('LOWER(u.type_utilisateur) = :role')
            ->setParameter('role', strtolower($role))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countGroupedByRole(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.type_utilisateur AS role, COUNT(u.id) AS total')
            ->groupBy('u.type_utilisateur')
            ->getQuery()
            ->getResult();
    }

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

    public function findByVerificationToken(string $token): ?Users
    {
        return $this->createQueryBuilder('u')
            ->where('u.verification_token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByPasswordResetToken(string $token): ?Users
    {
        return $this->createQueryBuilder('u')
            ->where('u.password_reset_token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

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

    public function findAllForExport(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.type_utilisateur', 'ASC')
            ->addOrderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

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

    public function findPaginated(int $page = 1, int $perPage = 12): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countPages(int $perPage = 12): int
    {
        $total = $this->countAll();
        return (int) ceil($total / $perPage);
    }
}