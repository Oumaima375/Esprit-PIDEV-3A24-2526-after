<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Destination;
use Doctrine\Common\Collections\Collection;
use App\Entity\Activite;

#[ORM\Entity]
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
}
