<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Voyage;

#[ORM\Entity]
class Destination
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_destination;

    #[ORM\Column(type: "string", length: 100)]
    private string $pays;

    #[ORM\Column(type: "string", length: 100)]
    private string $ville;

    #[ORM\Column(type: "string", length: 50)]
    private string $continent;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string", length: 255)]
    private string $image;

    public function getId_destination()
    {
        return $this->id_destination;
    }

    public function setId_destination($value)
    {
        $this->id_destination = $value;
    }

    public function getPays()
    {
        return $this->pays;
    }

    public function setPays($value)
    {
        $this->pays = $value;
    }

    public function getVille()
    {
        return $this->ville;
    }

    public function setVille($value)
    {
        $this->ville = $value;
    }

    public function getContinent()
    {
        return $this->continent;
    }

    public function setContinent($value)
    {
        $this->continent = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($value)
    {
        $this->image = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_destination", targetEntity: Voyage::class)]
    private Collection $voyages;

        public function getVoyages(): Collection
        {
            return $this->voyages;
        }
    
        public function addVoyage(Voyage $voyage): self
        {
            if (!$this->voyages->contains($voyage)) {
                $this->voyages[] = $voyage;
                $voyage->setId_destination($this);
            }
    
            return $this;
        }
    
        public function removeVoyage(Voyage $voyage): self
        {
            if ($this->voyages->removeElement($voyage)) {
                // set the owning side to null (unless already changed)
                if ($voyage->getId_destination() === $this) {
                    $voyage->setId_destination(null);
                }
            }
    
            return $this;
        }
}
