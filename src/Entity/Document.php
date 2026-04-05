<?php

namespace App\Entity;

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
}
