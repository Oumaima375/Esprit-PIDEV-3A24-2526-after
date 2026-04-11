<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PaiementRepository;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $reference;

    #[ORM\Column(type: "float")]
    private float $montant;

    #[ORM\Column(type: "string", length: 50)]
    private string $methode;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: "paiements")]
    #[ORM\JoinColumn(name: 'id_reservation', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Reservation $reservation = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut;

    #[ORM\Column(type: "string", length: 10)]
    private string $devise;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $datePaiement;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getMontant(): float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getMethode(): string
    {
        return $this->methode;
    }

    public function setMethode(string $methode): self
    {
        $this->methode = $methode;
        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDevise(): string
    {
        return $this->devise;
    }

    public function setDevise(string $devise): self
    {
        $this->devise = $devise;
        return $this;
    }

    /**
     * Returns the raw DateTimeInterface (for Doctrine / Twig date filter).
     */
    public function getDatePaiementObject(): \DateTimeInterface
    {
        return $this->datePaiement;
    }

    /**
     * Returns a Y-m-d string — used by AdminController implode() comparisons.
     */
    public function getDatePaiement(): string
    {
        return isset($this->datePaiement) ? $this->datePaiement->format('Y-m-d') : '';
    }

    public function setDatePaiement(\DateTimeInterface $datePaiement): self
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    public function getReservationId(): int
    {
        return $this->reservation?->getId() ?? 0;
    }

    public function getReservationLieu(): ?string
    {
        return $this->reservation?->getLieu();
    }

    public function getDatePaiementFormatted(): string
    {
        return isset($this->datePaiement) ? $this->datePaiement->format('Y-m-d') : '';
    }

    public function getAmount(): string
    {
        return number_format($this->montant, 2, ',', ' ') . ' ' . $this->devise;
    }

    public function getStatus(): string
    {
        return $this->statut;
    }

    public function getRef(): string
    {
        return $this->reference;
    }

    public function getTrip(): string
    {
        return $this->reservation?->getDestination() ?? 'Reservation #' . $this->getReservationId();
    }
}