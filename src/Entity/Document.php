<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use App\Entity\Users;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'document')]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id_document', type: 'integer')]
    private ?int $idDocument = null;

    #[ORM\Column(name: 'nom_document', length: 255)]
    private ?string $nomDocument = null;

    #[ORM\Column(name: 'chemin_fichier', length: 500)]
    private ?string $cheminFichier = null;

    #[ORM\Column(name: 'date_ajout', type: 'date')]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\Column(name: 'date_expiration', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\ManyToOne(targetEntity: CategorieDocument::class)]
    #[ORM\JoinColumn(name: 'id_categorie', referencedColumnName: 'id_categorie')]
    private ?CategorieDocument $categorie = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Users $idUser = null;

    public function getIdDocument(): ?int { return $this->idDocument; }

    public function getNomDocument(): ?string { return $this->nomDocument; }
    public function setNomDocument(string $nomDocument): static { $this->nomDocument = $nomDocument; return $this; }

    public function getCheminFichier(): ?string { return $this->cheminFichier; }
    public function setCheminFichier(string $cheminFichier): static { $this->cheminFichier = $cheminFichier; return $this; }

    public function getDateAjout(): ?\DateTimeInterface { return $this->dateAjout; }
    public function setDateAjout(\DateTimeInterface $dateAjout): static { $this->dateAjout = $dateAjout; return $this; }

    public function getDateExpiration(): ?\DateTimeInterface { return $this->dateExpiration; }
    public function setDateExpiration(?\DateTimeInterface $dateExpiration): static { $this->dateExpiration = $dateExpiration; return $this; }

    public function getCategorie(): ?CategorieDocument { return $this->categorie; }
    public function setCategorie(?CategorieDocument $categorie): static { $this->categorie = $categorie; return $this; }

    public function getIdUser(): ?Users { return $this->idUser; }
    public function setIdUser(?Users $idUser): static { $this->idUser = $idUser; return $this; }
}