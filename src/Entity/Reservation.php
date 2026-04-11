<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationRepository;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateReservation = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $nbPersonnes = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: Voyage::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: 'id_voyage', referencedColumnName: 'id_voyage', onDelete: 'CASCADE')]
    private ?Voyage $voyage = null;

    // nullable so existing DB rows with NULL don't crash on hydration
    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $prixTotal = null;

    #[ORM\OneToMany(mappedBy: "reservation", targetEntity: Paiement::class)]
    private Collection $paiements;

    public function __construct()
    {
        $this->paiements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateReservationObject(): ?\DateTimeInterface
    {
        return $this->dateReservation;
    }

    public function getDateReservation(): string
    {
        return $this->dateReservation ? $this->dateReservation->format('Y-m-d') : '';
    }

    public function setDateReservation(?\DateTimeInterface $dateReservation): self
    {
        $this->dateReservation = $dateReservation;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut ?? '';
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNbPersonnes(): int
    {
        return $this->nbPersonnes ?? 0;
    }

    public function setNbPersonnes(?int $nbPersonnes): self
    {
        $this->nbPersonnes = $nbPersonnes;
        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getVoyage(): ?Voyage
    {
        return $this->voyage;
    }

    public function setVoyage(?Voyage $voyage): self
    {
        $this->voyage = $voyage;
        return $this;
    }

    public function getType(): string
    {
        return $this->type ?? '';
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getLieu(): string
    {
        return $this->lieu ?? '';
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrixTotal(): float
    {
        return $this->prixTotal ?? 0.0;
    }

    public function setPrixTotal(?float $prixTotal): self
    {
        $this->prixTotal = $prixTotal;
        return $this;
    }

    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function getVoyageId(): int
    {
        return $this->voyage?->getId() ?? 0;
    }

    public function getUtilisateurId(): int
    {
        return $this->user?->getId() ?? 0;
    }

    public function getDate(): string
    {
        return $this->getDateReservation();
    }

    public function getStatus(): string
    {
        return $this->statut ?? '';
    }

    public function getLabelDate(): string
    {
        return $this->dateReservation ? $this->dateReservation->format('d M Y') : 'N/A';
    }

    public function getDestination(): string
    {
        return $this->voyage?->getTitre() ?? $this->lieu ?? 'N/A';
    }

    public function getPeople(): int
    {
        return $this->nbPersonnes ?? 0;
    }

    public function getPrice(): string
    {
        return number_format($this->prixTotal ?? 0.0, 2, ',', ' ') . ' TND';
    }
}