<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Voyage;
use Doctrine\Common\Collections\Collection;
use App\Entity\Planning;

#[ORM\Entity]
class Activite
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_activite;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string", length: 50)]
    private string $categorie;

    #[ORM\Column(type: "string", length: 100)]
    private string $lieu;

    #[ORM\Column(type: "float")]
    private float $prix;

        #[ORM\ManyToOne(targetEntity: Voyage::class, inversedBy: "activites")]
    #[ORM\JoinColumn(name: 'id_voyage', referencedColumnName: 'id_voyage', onDelete: 'CASCADE')]
    private Voyage $id_voyage;

    public function getId_activite()
    {
        return $this->id_activite;
    }

    public function setId_activite($value)
    {
        $this->id_activite = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getCategorie()
    {
        return $this->categorie;
    }

    public function setCategorie($value)
    {
        $this->categorie = $value;
    }

    public function getLieu()
    {
        return $this->lieu;
    }

    public function setLieu($value)
    {
        $this->lieu = $value;
    }

    public function getPrix()
    {
        return $this->prix;
    }

    public function setPrix($value)
    {
        $this->prix = $value;
    }

    public function getId_voyage()
    {
        return $this->id_voyage;
    }

    public function setId_voyage($value)
    {
        $this->id_voyage = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_activite", targetEntity: Planning::class)]
    private Collection $plannings;

        public function getPlannings(): Collection
        {
            return $this->plannings;
        }
    
        public function addPlanning(Planning $planning): self
        {
            if (!$this->plannings->contains($planning)) {
                $this->plannings[] = $planning;
                $planning->setId_activite($this);
            }
    
            return $this;
        }
    
        public function removePlanning(Planning $planning): self
        {
            if ($this->plannings->removeElement($planning)) {
                // set the owning side to null (unless already changed)
                if ($planning->getId_activite() === $this) {
                    $planning->setId_activite(null);
                }
            }
    
            return $this;
        }
}
