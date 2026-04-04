<?php

namespace App\Entity;

use App\Repository\CategorieDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieDocumentRepository::class)]
#[ORM\Table(name: 'categorie_document')]
class CategorieDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id_categorie', type: 'integer')]
    private ?int $idCategorie = null;
    #[ORM\Column(name: 'libelle', length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(name: 'description', length: 255, nullable: true)]
    private ?string $description = null;

    public function getIdCategorie(): ?int { return $this->idCategorie; }
    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(string $libelle): static { $this->libelle = $libelle; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
}