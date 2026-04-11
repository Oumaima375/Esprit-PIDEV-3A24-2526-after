<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CategorieRepository;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ORM\Table(name: 'categorie')]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_cat = null;

    public function getId_cat(): ?int
    {
        return $this->id_cat;
    }

    public function setId_cat(int $id_cat): self
    {
        $this->id_cat = $id_cat;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom_categorie = null;

    public function getNom_categorie(): ?string
    {
        return $this->nom_categorie;
    }

    public function setNom_categorie(string $nom_categorie): self
    {
        $this->nom_categorie = $nom_categorie;
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $icone_url = null;

    public function getIcone_url(): ?string
    {
        return $this->icone_url;
    }

    public function setIcone_url(string $icone_url): self
    {
        $this->icone_url = $icone_url;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Depense::class, mappedBy: 'categorie')]
    private Collection $depenses;

    public function __construct()
    {
        $this->depenses = new ArrayCollection();
    }

    /**
     * @return Collection<int, Depense>
     */
    public function getDepenses(): Collection
    {
        if (!$this->depenses instanceof Collection) {
            $this->depenses = new ArrayCollection();
        }
        return $this->depenses;
    }

    public function addDepense(Depense $depense): self
    {
        if (!$this->getDepenses()->contains($depense)) {
            $this->getDepenses()->add($depense);
        }
        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        $this->getDepenses()->removeElement($depense);
        return $this;
    }

    public function getIdCat(): ?int
    {
        return $this->id_cat;
    }

    public function getNomCategorie(): ?string
    {
        return $this->nom_categorie;
    }

    public function setNomCategorie(string $nom_categorie): static
    {
        $this->nom_categorie = $nom_categorie;

        return $this;
    }

    public function getIconeUrl(): ?string
    {
        return $this->icone_url;
    }

    public function setIconeUrl(string $icone_url): static
    {
        $this->icone_url = $icone_url;

        return $this;
    }

}
