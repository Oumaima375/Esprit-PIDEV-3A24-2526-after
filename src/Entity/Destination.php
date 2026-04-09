<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\DestinationRepository;
use App\Entity\Voyage;

#[ORM\Entity(repositoryClass: DestinationRepository::class)]
class Destination
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_destination = null;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: 'Le pays est obligatoire')]
    private ?string $pays = null;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: 'La ville est obligatoire')]
    private ?string $ville = null;

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\NotBlank(message: 'Le continent est obligatoire')]
    private ?string $continent = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: "id_destination", targetEntity: Voyage::class)]
    private Collection $voyages;

    public function __construct()
    {
        $this->voyages = new ArrayCollection();
    }

    // ✅ ID
    public function getId(): ?int
    {
        return $this->id_destination;
    }

    // ✅ Pays
    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): self
    {
        $this->pays = $pays;
        return $this;
    }

    // ✅ Ville
    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    // ✅ Continent
    public function getContinent(): ?string
    {
        return $this->continent;
    }

    public function setContinent(string $continent): self
    {
        $this->continent = $continent;
        return $this;
    }

    // ✅ Description
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    // ✅ Image
    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    // ✅ Relation Voyages
    public function getVoyages(): Collection
    {
        return $this->voyages;
    }

    public function addVoyage(Voyage $voyage): self
    {
        if (!$this->voyages->contains($voyage)) {
            $this->voyages[] = $voyage;
            $voyage->setIdDestination($this);
        }

        return $this;
    }

    public function removeVoyage(Voyage $voyage): self
    {
        if ($this->voyages->removeElement($voyage)) {
            if ($voyage->getIdDestination() === $this) {
                $voyage->setIdDestination(null);
            }
        }

        return $this;
    }
}