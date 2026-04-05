<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Reservation;

#[ORM\Entity]
class Paiement
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 50)]
    private string $reference;

    #[ORM\Column(type: "float")]
    private float $montant;

    #[ORM\Column(type: "string", length: 50)]
    private string $methode;

        #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: "paiements")]
    #[ORM\JoinColumn(name: 'id_reservation', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Reservation $id_reservation;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut;

    #[ORM\Column(type: "string", length: 10)]
    private string $devise;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_paiement;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($value)
    {
        $this->reference = $value;
    }

    public function getMontant()
    {
        return $this->montant;
    }

    public function setMontant($value)
    {
        $this->montant = $value;
    }

    public function getMethode()
    {
        return $this->methode;
    }

    public function setMethode($value)
    {
        $this->methode = $value;
    }

    public function getId_reservation()
    {
        return $this->id_reservation;
    }

    public function setId_reservation($value)
    {
        $this->id_reservation = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getDevise()
    {
        return $this->devise;
    }

    public function setDevise($value)
    {
        $this->devise = $value;
    }

    public function getDate_paiement()
    {
        return $this->date_paiement;
    }

    public function setDate_paiement($value)
    {
        $this->date_paiement = $value;
    }
}
