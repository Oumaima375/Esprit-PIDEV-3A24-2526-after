<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ServiceRepository;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ORM\Table(name: 'service')]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_service = null;

    public function getId_service(): ?int
    {
        return $this->id_service;
    }

    public function setId_service(int $id_service): self
    {
        $this->id_service = $id_service;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nom_service = null;

    public function getNom_service(): ?string
    {
        return $this->nom_service;
    }

    public function setNom_service(?string $nom_service): self
    {
        $this->nom_service = $nom_service;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $categorie = null;

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Offre::class, mappedBy: 'service')]
    private Collection $offres;

    public function __construct()
    {
        $this->offres = new ArrayCollection();
    }

    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        if (!$this->offres instanceof Collection) {
            $this->offres = new ArrayCollection();
        }
        return $this->offres;
    }

    public function addOffre(Offre $offre): self
    {
        if (!$this->getOffres()->contains($offre)) {
            $this->getOffres()->add($offre);
        }
        return $this;
    }

    public function removeOffre(Offre $offre): self
    {
        $this->getOffres()->removeElement($offre);
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

    public function setNomService(?string $nom_service): static
    {
        $this->nom_service = $nom_service;

        return $this;
    }

}
