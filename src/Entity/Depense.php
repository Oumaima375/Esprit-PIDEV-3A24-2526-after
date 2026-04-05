<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Voyage;
use App\Repository\DepenseRepository;

#[ORM\Entity(repositoryClass: App\Repository\DepenseRepository::class)]
class Depense
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_dep;

    #[ORM\Column(type: "string", length: 150)]
    private string $titre;

    #[ORM\Column(type: "float")]
    private float $montant;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_depense;

        #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: "depenses")]
    #[ORM\JoinColumn(name: 'id_categorie', referencedColumnName: 'id_cat', onDelete: 'CASCADE')]
    private Categorie $id_categorie;

        #[ORM\ManyToOne(targetEntity: Voyage::class, inversedBy: "depenses")]
    #[ORM\JoinColumn(name: 'id_voyage', referencedColumnName: 'id_voyage', onDelete: 'CASCADE')]
    private Voyage $id_voyage;

    public function getId_dep()
    {
        return $this->id_dep;
    }

    public function setId_dep($value)
    {
        $this->id_dep = $value;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($value)
    {
        $this->titre = $value;
    }

    public function getMontant()
    {
        return $this->montant;
    }

    public function setMontant($value)
    {
        $this->montant = $value;
    }

    public function getDate_depense()
    {
        return $this->date_depense;
    }

    public function setDate_depense($value)
    {
        $this->date_depense = $value;
    }

    public function getId_categorie()
    {
        return $this->id_categorie;
    }

    public function setId_categorie($value)
    {
        $this->id_categorie = $value;
    }

    public function getId_voyage()
    {
        return $this->id_voyage;
    }

    public function setId_voyage($value)
    {
        $this->id_voyage = $value;
    }

    public function getIdDep(): ?int
    {
        return $this->id_dep;
    }

    public function getDateDepense(): ?\DateTime
    {
        return $this->date_depense;
    }

    public function setDateDepense(\DateTime $date_depense): static
    {
        $this->date_depense = $date_depense;

        return $this;
    }

    public function getIdCategorie(): ?Categorie
    {
        return $this->id_categorie;
    }

    public function setIdCategorie(?Categorie $id_categorie): static
    {
        $this->id_categorie = $id_categorie;

        return $this;
    }

    public function getIdVoyage(): ?Voyage
    {
        return $this->id_voyage;
    }

    public function setIdVoyage(?Voyage $id_voyage): static
    {
        $this->id_voyage = $id_voyage;

        return $this;
    }
}
