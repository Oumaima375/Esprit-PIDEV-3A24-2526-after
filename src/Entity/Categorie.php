<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Entity\Depense;
use App\Repository\CategorieRepository;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_cat;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom_categorie;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $icone_url = null;

    #[ORM\OneToMany(mappedBy: "id_categorie", targetEntity: Depense::class)]
    private Collection $depenses;

    public function __construct()
    {
        $this->depenses = new ArrayCollection();
    }

    // ===== OLD STYLE =====
    public function getId_cat() { return $this->id_cat; }
    public function setId_cat($value) { $this->id_cat = $value; }

    public function getNom_categorie() { return $this->nom_categorie; }
    public function setNom_categorie($value) { $this->nom_categorie = $value; }

    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; }

    public function getIcone_url() { return $this->icone_url; }
    public function setIcone_url($value) { $this->icone_url = $value; }

    // ===== MODERN STYLE (depense branch compatibility) =====
    // "id" alias — used by depense branch templates: cat.id
    public function getId(): ?int { return $this->id_cat; }
    public function getIdCat(): ?int { return $this->id_cat; }

    // "nomCategorie" alias — used by depense branch templates: cat.nomCategorie
    public function getNomCategorie(): ?string { return $this->nom_categorie; }
    public function setNomCategorie(string $nom_categorie): static
    {
        $this->nom_categorie = $nom_categorie;
        return $this;
    }

    public function getIconeUrl(): ?string { return $this->icone_url; }
    public function setIconeUrl(?string $icone_url): static
    {
        $this->icone_url = $icone_url;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom_categorie ?? '';
    }

    // ===== COLLECTION =====
    public function getDepenses(): Collection { return $this->depenses; }

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
            if ($depense->getId_categorie() === $this) {
                $depense->setId_categorie(null);
            }
        }
        return $this;
    }
}