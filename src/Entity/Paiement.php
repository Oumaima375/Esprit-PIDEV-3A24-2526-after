<?php

namespace App\Entity;

final class Paiement
{
    public function __construct(
        private int $id,
        private string $reference,
        private float $montant,
        private string $devise,
        private string $methode,
        private ?string $statut,
        private ?string $datePaiement,
        private int $reservationId,
        private ?string $reservationLieu = null,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getRef(): string
    {
        return $this->reference;
    }

    public function getMontant(): float
    {
        return $this->montant;
    }

    public function getDevise(): string
    {
        return $this->devise;
    }

    public function getMethode(): string
    {
        return $this->methode;
    }

    public function getStatut(): string
    {
        return $this->statut ?: 'En attente';
    }

    public function getStatus(): string
    {
        return $this->getStatut();
    }

    public function getDatePaiement(): ?string
    {
        return $this->datePaiement;
    }

    public function getReservationId(): int
    {
        return $this->reservationId;
    }

    public function getReservationLieu(): ?string
    {
        return $this->reservationLieu;
    }

    public function getTripLabel(): string
    {
        $label = 'Ref #' . $this->reservationId;

        if ($this->reservationLieu) {
            $label .= ' - ' . $this->reservationLieu;
        }

        return $label;
    }

    public function getTrip(): string
    {
        return $this->getTripLabel();
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->montant, 2, ',', ' ') . ' ' . strtoupper($this->devise);
    }

    public function getAmount(): string
    {
        return $this->getFormattedAmount();
    }
}
