<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ORM\Table(name: 'categorie')]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_cat')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_categorie', length: 100)]
    private ?string $nomCategorie = null;

    #[ORM\Column(name: 'description', nullable: true, length: 255)]
    private ?string $description = null;

    #[ORM\Column(name: 'icone_url', nullable: true, length: 255)]
    private ?string $iconeUrl = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: Depense::class)]
    private Collection $depenses;

    public function __construct()
    {
        $this->depenses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCategorie(): ?string
    {
        return $this->nomCategorie;
    }

    public function setNomCategorie(string $nomCategorie): static
    {
        $this->nomCategorie = $nomCategorie;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getIconeUrl(): ?string
    {
        return $this->iconeUrl;
    }

    public function setIconeUrl(?string $iconeUrl): static
    {
        $this->iconeUrl = $iconeUrl;
        return $this;
    }

    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depense $depense): static
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses->add($depense);
            $depense->setCategorie($this);
        }
        return $this;
    }

    public function removeDepense(Depense $depense): static
    {
        if ($this->depenses->removeElement($depense)) {
            if ($depense->getCategorie() === $this) {
                $depense->setCategorie(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nomCategorie ?? '';
    }
}