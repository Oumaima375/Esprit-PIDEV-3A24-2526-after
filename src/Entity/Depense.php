<?php

namespace App\Entity;

use App\Repository\DepenseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepenseRepository::class)]
#[ORM\Table(name: 'depense')]
class Depense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_dep')]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(min: 3, minMessage: "Le titre doit contenir au moins 3 caractères")]
    #[ORM\Column(length: 150)]
    private ?string $titre = null;

    #[Assert\NotBlank(message: "Le montant est obligatoire")]
    #[Assert\Positive(message: "Le montant doit être positif")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montant = null;

    #[Assert\NotBlank(message: "La date est obligatoire")]
    #[ORM\Column(name: 'date_depense', type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDepense = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: 'id_categorie', referencedColumnName: 'id_cat', nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\Column(name: 'id_voyage', nullable: true)]
    private ?int $idVoyage = null;

    // ========== GÉOLOCALISATION ==========
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;

    // ========== GETTERS / SETTERS ==========
    public function getId(): ?int { return $this->id; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }
    public function getMontant(): ?float { return $this->montant ? (float) $this->montant : null; }
    public function setMontant(float $montant): static { $this->montant = (string) $montant; return $this; }
    public function getDateDepense(): ?\DateTimeInterface { return $this->dateDepense; }
    public function setDateDepense(\DateTimeInterface $dateDepense): static { $this->dateDepense = $dateDepense; return $this; }
    public function getCategorie(): ?Categorie { return $this->categorie; }
    public function setCategorie(?Categorie $categorie): static { $this->categorie = $categorie; return $this; }
    public function getIdVoyage(): ?int { return $this->idVoyage; }
    public function setIdVoyage(?int $idVoyage): static { $this->idVoyage = $idVoyage; return $this; }
    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): static { $this->lieu = $lieu; return $this; }
    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): static { $this->latitude = $latitude; return $this; }
    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): static { $this->longitude = $longitude; return $this; }
}