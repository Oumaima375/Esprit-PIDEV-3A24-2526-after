<?php

namespace App\Repository;

use App\Entity\Paiement;
use App\Service\DatabaseConnection;

final class PaiementRepository
{
    public function __construct(private DatabaseConnection $databaseConnection)
    {
    }

    /**
     * @return Paiement[]
     */
    public function findAll(): array
    {
        $sql = 'SELECT p.id, p.reference, p.montant, p.devise, p.methode, p.statut, p.date_paiement, p.id_reservation, r.lieu
                FROM paiement p
                LEFT JOIN reservation r ON r.id = p.id_reservation
                ORDER BY p.date_paiement DESC, p.id DESC';

        $statement = $this->databaseConnection->getConnection()->query($sql);
        $rows = $statement->fetchAll();

        return array_map(fn (array $row): Paiement => $this->hydrate($row), $rows);
    }

    public function findById(int $id): ?Paiement
    {
        $statement = $this->databaseConnection->getConnection()->prepare(
            'SELECT p.id, p.reference, p.montant, p.devise, p.methode, p.statut, p.date_paiement, p.id_reservation, r.lieu
             FROM paiement p
             LEFT JOIN reservation r ON r.id = p.id_reservation
             WHERE p.id = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function countAll(): int
    {
        return (int) $this->databaseConnection->getConnection()->query('SELECT COUNT(*) FROM paiement')->fetchColumn();
    }

    public function sumAllAmounts(): float
    {
        $value = $this->databaseConnection->getConnection()->query('SELECT COALESCE(SUM(montant), 0) FROM paiement')->fetchColumn();

        return (float) $value;
    }

    public function create(string $reference, float $montant, string $devise, string $methode, string $statut, string $datePaiement, int $reservationId): void
    {
        $statement = $this->databaseConnection->getConnection()->prepare(
            'INSERT INTO paiement (reference, montant, devise, methode, statut, date_paiement, id_reservation)
             VALUES (:reference, :montant, :devise, :methode, :statut, :date_paiement, :id_reservation)'
        );

        $statement->execute([
            'reference' => $reference,
            'montant' => $montant,
            'devise' => $devise,
            'methode' => $methode,
            'statut' => $statut,
            'date_paiement' => $datePaiement,
            'id_reservation' => $reservationId,
        ]);
    }

    public function update(int $id, string $reference, float $montant, string $devise, string $methode, string $statut, string $datePaiement, int $reservationId): void
    {
        $statement = $this->databaseConnection->getConnection()->prepare(
            'UPDATE paiement
             SET reference = :reference, montant = :montant, devise = :devise, methode = :methode, statut = :statut,
                 date_paiement = :date_paiement, id_reservation = :id_reservation
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'reference' => $reference,
            'montant' => $montant,
            'devise' => $devise,
            'methode' => $methode,
            'statut' => $statut,
            'date_paiement' => $datePaiement,
            'id_reservation' => $reservationId,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->databaseConnection->getConnection()->prepare('DELETE FROM paiement WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function hydrate(array $row): Paiement
    {
        return new Paiement(
            (int) $row['id'],
            $row['reference'],
            (float) $row['montant'],
            $row['devise'] ?? 'TND',
            $row['methode'],
            $row['statut'],
            $row['date_paiement'],
            (int) $row['id_reservation'],
            $row['lieu'] ?? null,
        );
    }
}