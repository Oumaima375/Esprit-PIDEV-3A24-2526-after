<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\DocumentRepository;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'document')]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_document = null;

    public function getId_document(): ?int
    {
        return $this->id_document;
    }

    public function setId_document(int $id_document): self
    {
        $this->id_document = $id_document;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom_document = null;

    public function getNom_document(): ?string
    {
        return $this->nom_document;
    }

    public function setNom_document(string $nom_document): self
    {
        $this->nom_document = $nom_document;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $chemin_fichier = null;

    public function getChemin_fichier(): ?string
    {
        return $this->chemin_fichier;
    }

    public function setChemin_fichier(string $chemin_fichier): self
    {
        $this->chemin_fichier = $chemin_fichier;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_ajout = null;

    public function getDate_ajout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function setDate_ajout(?\DateTimeInterface $date_ajout): self
    {
        $this->date_ajout = $date_ajout;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_expiration = null;

    public function getDate_expiration(): ?\DateTimeInterface
    {
        return $this->date_expiration;
    }

    public function setDate_expiration(?\DateTimeInterface $date_expiration): self
    {
        $this->date_expiration = $date_expiration;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: CategorieDocument::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(name: 'id_categorie', referencedColumnName: 'id_categorie')]
    private ?CategorieDocument $categorieDocument = null;

    public function getCategorieDocument(): ?CategorieDocument
    {
        return $this->categorieDocument;
    }

    public function setCategorieDocument(?CategorieDocument $categorieDocument): self
    {
        $this->categorieDocument = $categorieDocument;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id')]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getIdDocument(): ?int
    {
        return $this->id_document;
    }

    public function getNomDocument(): ?string
    {
        return $this->nom_document;
    }

    public function setNomDocument(string $nom_document): static
    {
        $this->nom_document = $nom_document;

        return $this;
    }

    public function getCheminFichier(): ?string
    {
        return $this->chemin_fichier;
    }

    public function setCheminFichier(string $chemin_fichier): static
    {
        $this->chemin_fichier = $chemin_fichier;

        return $this;
    }

    public function getDateAjout(): ?\DateTime
    {
        return $this->date_ajout;
    }

    public function setDateAjout(?\DateTime $date_ajout): static
    {
        $this->date_ajout = $date_ajout;

        return $this;
    }

    public function getDateExpiration(): ?\DateTime
    {
        return $this->date_expiration;
    }

    public function setDateExpiration(?\DateTime $date_expiration): static
    {
        $this->date_expiration = $date_expiration;

        return $this;
    }

}
