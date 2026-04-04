<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Depense;

#[ORM\Entity]
class Categorie
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_cat;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom_categorie;

    #[ORM\Column(type: "string", length: 255)]
    private string $description;

    #[ORM\Column(type: "string", length: 255)]
    private string $icone_url;

    public function getId_cat()
    {
        return $this->id_cat;
    }

    public function setId_cat($value)
    {
        $this->id_cat = $value;
    }

    public function getNom_categorie()
    {
        return $this->nom_categorie;
    }

    public function setNom_categorie($value)
    {
        $this->nom_categorie = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getIcone_url()
    {
        return $this->icone_url;
    }

    public function setIcone_url($value)
    {
        $this->icone_url = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_categorie", targetEntity: Depense::class)]
    private Collection $depenses;

        public function getDepenses(): Collection
        {
            return $this->depenses;
        }
    
        public function addDepense(Depense $depense): self
        {
            if (!$this->depenses->contains($depense)) {
                $this->depenses[] = $depense;
                $depense->setId_categorie($this);
            }
    
            return $this;
        }
    
        public function removeDepense(Depense $depense): self
        {
            if ($this->depenses->removeElement($depense)) {
                // set the owning side to null (unless already changed)
                if ($depense->getId_categorie() === $this) {
                    $depense->setId_categorie(null);
                }
            }
    
            return $this;
        }
}
