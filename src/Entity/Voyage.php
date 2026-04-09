<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Destination;
use App\Entity\Activite;
use App\Entity\Depense;
use App\Entity\Reservation;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: App\Repository\VoyageRepository::class)]
class Voyage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_voyage = null;

    #[ORM\Column(type: "string", length: 150)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    private ?string $titre = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: 'La description est obligatoire')]
    private ?string $description = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message: 'La date de début est obligatoire')]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message: 'La date de fin est obligatoire')]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?float $prix = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank]
    #[Assert\Positive]
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

    public function getId(): ?int
    {
        return $this->id_voyage;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date): self
    {
        $this->date_debut = $date;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date): self
    {
        $this->date_fin = $date;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nb_places;
    }

    public function setNbPlaces(int $nb): self
    {
        $this->nb_places = $nb;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $img): self
    {
        $this->image = $img;
        return $this;
    }

    public function getIdDestination(): ?Destination
    {
        return $this->id_destination;
    }

    public function setIdDestination(?Destination $dest): self
    {
        $this->id_destination = $dest;
        return $this;
    }

    // Relations
    public function getActivites(): Collection
    {
        return $this->activites;
    }

    public function addActivite(Activite $activite): self
    {
        if (!$this->activites->contains($activite)) {
            $this->activites->add($activite);
            $activite->setIdVoyage($this);
        }
        return $this;
    }

    public function removeActivite(Activite $activite): self
    {
        if ($this->activites->removeElement($activite)) {
            if ($activite->getIdVoyage() === $this) {
                $activite->setIdVoyage(null);
            }
        }
        return $this;
    }

    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }
}