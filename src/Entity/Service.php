<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Offre;
use App\Repository\ServiceRepository;

#[ORM\Entity(repositoryClass: App\Repository\ServiceRepository::class)]
class Service
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_service;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom_service;

    #[ORM\Column(type: "string", length: 255)]
    private string $description;

    #[ORM\Column(type: "string", length: 100)]
    private string $categorie;

    public function getId_service()
    {
        return $this->id_service;
    }

    public function setId_service($value)
    {
        $this->id_service = $value;
    }

    public function getNom_service()
    {
        return $this->nom_service;
    }

    public function setNom_service($value)
    {
        $this->nom_service = $value;
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

    #[ORM\OneToMany(mappedBy: "id_service", targetEntity: Offre::class)]
    private Collection $offres;

    public function __construct()
    {
        $this->offres = new ArrayCollection();
    }

        public function getOffres(): Collection
        {
            return $this->offres;
        }
    
        public function addOffre(Offre $offre): self
        {
            if (!$this->offres->contains($offre)) {
                $this->offres[] = $offre;
                $offre->setId_service($this);
            }
    
            return $this;
        }
    
        public function removeOffre(Offre $offre): self
        {
            if ($this->offres->removeElement($offre)) {
                // set the owning side to null (unless already changed)
                if ($offre->getId_service() === $this) {
                    $offre->setId_service(null);
                }
            }
    
            return $this;
        }

        public function getIdService(): ?int
        {
            return $this->id_service;
        }

        public function getNomService(): ?string
        {
            return $this->nom_service;
        }

        public function setNomService(string $nom_service): static
        {
            $this->nom_service = $nom_service;

            return $this;
        }
}
