<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Service\DatabaseConnection;

final class ReservationRepository
{
    public function __construct(private DatabaseConnection $databaseConnection)
    {
    }

    /**
     * @return Reservation[]
     */
    public function findAll(): array
    {
        $sql = 'SELECT id, id_voyage, id_utilisateur, type, lieu, description, date_reservation, statut, nb_personnes, prix_total
                FROM reservation
                ORDER BY date_reservation ASC, id DESC';

        $statement = $this->databaseConnection->getConnection()->query($sql);
        $rows = $statement->fetchAll();

        return array_map(fn (array $row): Reservation => $this->hydrate($row), $rows);
    }

    public function findById(int $id): ?Reservation
    {
        $statement = $this->databaseConnection->getConnection()->prepare(
            'SELECT id, id_voyage, id_utilisateur, type, lieu, description, date_reservation, statut, nb_personnes, prix_total
             FROM reservation WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function countAll(): int
    {
        return (int) $this->databaseConnection->getConnection()->query('SELECT COUNT(*) FROM reservation')->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $statement = $this->databaseConnection->getConnection()->prepare('SELECT COUNT(*) FROM reservation WHERE statut = :status');
        $statement->execute(['status' => $status]);

        return (int) $statement->fetchColumn();
    }

    public function create(int $voyageId, int $utilisateurId, ?string $type, ?string $lieu, ?string $description, string $dateReservation, string $statut, int $nbPersonnes, float $prixTotal): void
    {
        $statement = $this->databaseConnection->getConnection()->prepare(
            'INSERT INTO reservation (id_voyage, id_utilisateur, type, lieu, description, date_reservation, statut, nb_personnes, prix_total)
             VALUES (:id_voyage, :id_utilisateur, :type, :lieu, :description, :date_reservation, :statut, :nb_personnes, :prix_total)'
        );

        $statement->execute([
            'id_voyage' => $voyageId,
            'id_utilisateur' => $utilisateurId,
            'type' => $type,
            'lieu' => $lieu,
            'description' => $description,
            'date_reservation' => $dateReservation,
            'statut' => $statut,
            'nb_personnes' => $nbPersonnes,
            'prix_total' => $prixTotal,
        ]);
    }

    public function update(int $id, int $voyageId, int $utilisateurId, ?string $type, ?string $lieu, ?string $description, string $dateReservation, string $statut, int $nbPersonnes, float $prixTotal): void
    {
        $statement = $this->databaseConnection->getConnection()->prepare(
            'UPDATE reservation
             SET id_voyage = :id_voyage, id_utilisateur = :id_utilisateur, type = :type, lieu = :lieu, description = :description,
                 date_reservation = :date_reservation, statut = :statut, nb_personnes = :nb_personnes, prix_total = :prix_total
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'id_voyage' => $voyageId,
            'id_utilisateur' => $utilisateurId,
            'type' => $type,
            'lieu' => $lieu,
            'description' => $description,
            'date_reservation' => $dateReservation,
            'statut' => $statut,
            'nb_personnes' => $nbPersonnes,
            'prix_total' => $prixTotal,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->databaseConnection->getConnection()->prepare('DELETE FROM reservation WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function hydrate(array $row): Reservation
    {
        return new Reservation(
            (int) $row['id'],
            (int) $row['id_voyage'],
            (int) $row['id_utilisateur'],
            $row['type'],
            $row['lieu'],
            $row['description'],
            $row['date_reservation'],
            $row['statut'],
            (int) $row['nb_personnes'],
            (float) $row['prix_total'],
        );
    }
}