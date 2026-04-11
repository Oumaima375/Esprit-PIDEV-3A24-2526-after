<?php

namespace App\Entity;

<<<<<<< HEAD
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;
use App\Repository\DocumentRepository;

#[ORM\Entity(repositoryClass: App\Repository\DocumentRepository::class)]
class Document
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_document;

    #[ORM\Column(type: "string", length: 150)]
    private string $nom_document;

    #[ORM\Column(type: "string", length: 255)]
    private string $chemin_fichier;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_ajout;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_expiration;

    #[ORM\ManyToOne(targetEntity: Categorie_document::class, inversedBy: "documents")]
    #[ORM\JoinColumn(name: 'id_categorie', referencedColumnName: 'id_categorie', onDelete: 'CASCADE')]
    private Categorie_document $id_categorie;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "documents")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $id_user;

    public function getId_document()
    {
        return $this->id_document;
    }

    public function setId_document($value)
    {
        $this->id_document = $value;
    }

    public function getNom_document()
    {
        return $this->nom_document;
    }

    public function setNom_document($value)
    {
        $this->nom_document = $value;
    }

    public function getChemin_fichier()
    {
        return $this->chemin_fichier;
    }

    public function setChemin_fichier($value)
    {
        $this->chemin_fichier = $value;
    }

    public function getDate_ajout()
    {
        return $this->date_ajout;
    }

    public function setDate_ajout($value)
    {
        $this->date_ajout = $value;
    }

    public function getDate_expiration()
    {
        return $this->date_expiration;
    }

    public function setDate_expiration($value)
    {
        $this->date_expiration = $value;
    }

    public function getId_categorie()
    {
        return $this->id_categorie;
    }

    public function setId_categorie($value)
    {
        $this->id_categorie = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
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

    public function setDateAjout(\DateTime $date_ajout): static
    {
        $this->date_ajout = $date_ajout;

        return $this;
    }

    public function getDateExpiration(): ?\DateTime
    {
        return $this->date_expiration;
    }

    public function setDateExpiration(\DateTime $date_expiration): static
    {
        $this->date_expiration = $date_expiration;

        return $this;
    }

    public function getIdCategorie(): ?Categorie_document
    {
        return $this->id_categorie;
    }

    public function setIdCategorie(?Categorie_document $id_categorie): static
    {
        $this->id_categorie = $id_categorie;

        return $this;
    }

    public function getIdUser(): ?Users
    {
        return $this->id_user;
    }

    public function setIdUser(?Users $id_user): static
    {
        $this->id_user = $id_user;

        return $this;
    }
=======
use App\Repository\DocumentRepository;
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

    // TODO after merge — décommentez après intégration User
    // #[ORM\ManyToOne(targetEntity: User::class)]
    // #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', nullable: true)]
    // private ?User $user = null;

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

    // TODO after merge — décommentez après intégration User
    // public function getUser(): ?User { return $this->user; }
    // public function setUser(?User $user): static { $this->user = $user; return $this; }
>>>>>>> gestionDocument-symfony
}