<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Destination;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Voyage
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id_voyage = null;

    #[ORM\Column(type: "string", length: 150)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(min: 3, max: 150, minMessage: 'Minimum 3 caractères')]
    private ?string $titre = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: 'La description est obligatoire')]
    private ?string $description = null;

    #[ORM\Column(type: "date", nullable: true)]
    #[Assert\NotBlank(message: 'La date de début est obligatoire')]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: "date", nullable: true)]
    #[Assert\NotBlank(message: 'La date de fin est obligatoire')]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: 'Le prix est obligatoire')]
    #[Assert\Positive(message: 'Le prix doit être positif')]
    private ?float $prix = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: 'Le nombre de places est obligatoire')]
    #[Assert\Positive(message: 'Doit être un nombre positif')]
    private ?int $nb_places = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: Destination::class, inversedBy: "voyages")]
    #[ORM\JoinColumn(name: 'id_destination', referencedColumnName: 'id_destination', onDelete: 'CASCADE')]
    private ?Destination $id_destination = null;

    #[ORM\OneToMany(mappedBy: "id_voyage", targetEntity: Activite::class)]
    private Collection $activites;

    #[ORM\OneToMany(mappedBy: "id_voyage", targetEntity: Depense::class)]
    private Collection $depenses;

    #[ORM\OneToMany(mappedBy: "id_voyage", targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->activites = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    // --- ID ---
    public function getId(): ?int
    {
        return $this->id_voyage;
    }

    public function getId_voyage(): ?int
    {
        return $this->id_voyage;
    }

    // --- Titre ---
    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    // --- Description ---
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    // --- Date début (camelCase — used by Symfony Form) ---
    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    // --- Date fin (camelCase — used by Symfony Form) ---
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    // --- Prix ---
    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    // --- Nb places (camelCase — used by Symfony Form) ---
    public function getNbPlaces(): ?int
    {
        return $this->nb_places;
    }

    public function setNbPlaces(?int $nb_places): self
    {
        $this->nb_places = $nb_places;
        return $this;
    }

    // --- Image ---
    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    // --- Destination (camelCase — used by Symfony Form) ---
    public function getIdDestination(): ?Destination
    {
        return $this->id_destination;
    }

    public function setIdDestination(?Destination $id_destination): self
    {
        $this->id_destination = $id_destination;
        return $this;
    }

    // --- Twig templates still use snake_case accessors ---
    public function getDate_debut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function getDate_fin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function getNb_places(): ?int
    {
        return $this->nb_places;
    }

    public function getId_destination(): ?Destination
    {
        return $this->id_destination;
    }
}