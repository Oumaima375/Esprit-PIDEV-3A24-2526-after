<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Destination;
use Doctrine\Common\Collections\Collection;
use App\Entity\Activite;


#[ORM\Entity(repositoryClass: App\Repository\VoyageRepository::class)]
class Voyage
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_voyage;

    #[ORM\Column(type: "string", length: 150)]
    private string $titre;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_debut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_fin;

    #[ORM\Column(type: "float")]
    private float $prix;

    #[ORM\Column(type: "integer")]
    private int $nb_places;

    #[ORM\Column(type: "string", length: 255)]
    private string $image;

        #[ORM\ManyToOne(targetEntity: Destination::class, inversedBy: "voyages")]
    #[ORM\JoinColumn(name: 'id_destination', referencedColumnName: 'id_destination', onDelete: 'CASCADE')]
    private Destination $id_destination;

    public function getId_voyage()
    {
        return $this->id_voyage;
    }

    public function setId_voyage($value)
    {
        $this->id_voyage = $value;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($value)
    {
        $this->titre = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getDate_debut()
    {
        return $this->date_debut;
    }

    public function setDate_debut($value)
    {
        $this->date_debut = $value;
    }

    public function getDate_fin()
    {
        return $this->date_fin;
    }

    public function setDate_fin($value)
    {
        $this->date_fin = $value;
    }

    public function getPrix()
    {
        return $this->prix;
    }

    public function setPrix($value)
    {
        $this->prix = $value;
    }

    public function getNb_places()
    {
        return $this->nb_places;
    }

    public function setNb_places($value)
    {
        $this->nb_places = $value;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($value)
    {
        $this->image = $value;
    }

    public function getId_destination()
    {
        return $this->id_destination;
    }

    public function setId_destination($value)
    {
        $this->id_destination = $value;
    }

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

    public function getIdVoyage(): ?int
    {
        return $this->id_voyage;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTime $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nb_places;
    }

    public function setNbPlaces(int $nb_places): static
    {
        $this->nb_places = $nb_places;

        return $this;
    }

    public function getIdDestination(): ?Destination
    {
        return $this->id_destination;
    }

    public function setIdDestination(?Destination $id_destination): static
    {
        $this->id_destination = $id_destination;

        return $this;
    }

    /**
     * @return Collection<int, Activite>
     */
    public function getActivites(): Collection
    {
        return $this->activites;
    }

    public function addActivite(Activite $activite): static
    {
        if (!$this->activites->contains($activite)) {
            $this->activites->add($activite);
            $activite->setIdVoyage($this);
        }

        return $this;
    }

    public function removeActivite(Activite $activite): static
    {
        if ($this->activites->removeElement($activite)) {
            // set the owning side to null (unless already changed)
            if ($activite->getIdVoyage() === $this) {
                $activite->setIdVoyage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Depense>
     */
    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depense $depense): static
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses->add($depense);
            $depense->setIdVoyage($this);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): static
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getIdVoyage() === $this) {
                $depense->setIdVoyage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setIdVoyage($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getIdVoyage() === $this) {
                $reservation->setIdVoyage(null);
            }
        }

        return $this;
    }
}
