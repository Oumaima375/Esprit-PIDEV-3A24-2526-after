<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Voyage;
use Doctrine\Common\Collections\Collection;
use App\Entity\Paiement;

#[ORM\Entity]
class Reservation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_reservation;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut;

    #[ORM\Column(type: "integer")]
    private int $nb_personnes;

        #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $id_user;

        #[ORM\ManyToOne(targetEntity: Voyage::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: 'id_voyage', referencedColumnName: 'id_voyage', onDelete: 'CASCADE')]
    private Voyage $id_voyage;

    #[ORM\Column(type: "integer")]
    private int $id_utilisateur;

    #[ORM\Column(type: "string", length: 50)]
    private string $type;

    #[ORM\Column(type: "string", length: 100)]
    private string $lieu;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "float")]
    private float $prix_total;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getDate_reservation()
    {
        return $this->date_reservation;
    }

    public function setDate_reservation($value)
    {
        $this->date_reservation = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getNb_personnes()
    {
        return $this->nb_personnes;
    }

    public function setNb_personnes($value)
    {
        $this->nb_personnes = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getId_voyage()
    {
        return $this->id_voyage;
    }

    public function setId_voyage($value)
    {
        $this->id_voyage = $value;
    }

    public function getId_utilisateur()
    {
        return $this->id_utilisateur;
    }

    public function setId_utilisateur($value)
    {
        $this->id_utilisateur = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getLieu()
    {
        return $this->lieu;
    }

    public function setLieu($value)
    {
        $this->lieu = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getPrix_total()
    {
        return $this->prix_total;
    }

    public function setPrix_total($value)
    {
        $this->prix_total = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_reservation", targetEntity: Paiement::class)]
    private Collection $paiements;
}
