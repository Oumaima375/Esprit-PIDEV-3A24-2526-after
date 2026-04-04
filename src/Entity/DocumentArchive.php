<?php

namespace App\Entity;

use App\Repository\DocumentArchiveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentArchiveRepository::class)]
#[ORM\Table(name: 'document_archive')]
class DocumentArchive
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $idDocumentOriginal = null;

    #[ORM\Column(length: 255)]
    private ?string $nomDocument = null;

    #[ORM\Column(length: 500)]
    private ?string $cheminFichier = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateArchivage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raison = null;

    public function __construct()
    {
        $this->dateArchivage = new \DateTime();
        $this->raison = 'Expiré';
    }

    public function getId(): ?int { return $this->id; }
    public function getIdDocumentOriginal(): ?int { return $this->idDocumentOriginal; }
    public function setIdDocumentOriginal(?int $id): static { $this->idDocumentOriginal = $id; return $this; }
    public function getNomDocument(): ?string { return $this->nomDocument; }
    public function setNomDocument(string $nom): static { $this->nomDocument = $nom; return $this; }
    public function getCheminFichier(): ?string { return $this->cheminFichier; }
    public function setCheminFichier(string $chemin): static { $this->cheminFichier = $chemin; return $this; }
    public function getDateAjout(): ?\DateTimeInterface { return $this->dateAjout; }
    public function setDateAjout(?\DateTimeInterface $date): static { $this->dateAjout = $date; return $this; }
    public function getDateExpiration(): ?\DateTimeInterface { return $this->dateExpiration; }
    public function setDateExpiration(?\DateTimeInterface $date): static { $this->dateExpiration = $date; return $this; }
    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(?string $cat): static { $this->categorie = $cat; return $this; }
    public function getDateArchivage(): ?\DateTimeInterface { return $this->dateArchivage; }
    public function setDateArchivage(\DateTimeInterface $date): static { $this->dateArchivage = $date; return $this; }
    public function getRaison(): ?string { return $this->raison; }
    public function setRaison(?string $raison): static { $this->raison = $raison; return $this; }
}