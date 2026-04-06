<?php

namespace App\Entity;

final class Reservation
{
    public function __construct(
        private int $id,
        private int $voyageId,
        private int $utilisateurId,
        private ?string $type,
        private ?string $lieu,
        private ?string $description,
        private string $dateReservation,
        private string $statut,
        private int $nbPersonnes,
        private float $prixTotal,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVoyageId(): int
    {
        return $this->voyageId;
    }

    public function getUtilisateurId(): int
    {
        return $this->utilisateurId;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function getDestination(): ?string
    {
        return $this->lieu;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDateReservation(): string
    {
        return $this->dateReservation;
    }

    public function getDate(): string
    {
        return $this->dateReservation;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getStatus(): string
    {
        return $this->statut;
    }

    public function getNbPersonnes(): int
    {
        return $this->nbPersonnes;
    }

    public function getPeople(): int
    {
        return $this->nbPersonnes;
    }

    public function getPrixTotal(): float
    {
        return $this->prixTotal;
    }

    public function getFormattedDate(): string
    {
        $timestamp = strtotime($this->dateReservation);

        if ($timestamp === false) {
            return $this->dateReservation;
        }

        $months = ['janv.', 'fevr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'aout', 'sept.', 'oct.', 'nov.', 'dec.'];

        return date('d', $timestamp) . ' ' . $months[(int) date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
    }

    public function getLabelDate(): string
    {
        return $this->getFormattedDate();
    }

    public function getFormattedPrice(): string
    {
        return number_format($this->prixTotal, 2, ',', ' ') . ' TND';
    }

    public function getPrice(): string
    {
        return $this->getFormattedPrice();
    }
}
