<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CategorieDocumentRepository;

#[ORM\Entity(repositoryClass: CategorieDocumentRepository::class)]
#[ORM\Table(name: 'categorie_document')]
class CategorieDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_categorie = null;

    public function getId_categorie(): ?int
    {
        return $this->id_categorie;
    }

    public function setId_categorie(int $id_categorie): self
    {
        $this->id_categorie = $id_categorie;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $libelle = null;

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;
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

    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'categorieDocument')]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        if (!$this->documents instanceof Collection) {
            $this->documents = new ArrayCollection();
        }
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->getDocuments()->contains($document)) {
            $this->getDocuments()->add($document);
        }
        return $this;
    }

    public function removeDocument(Document $document): self
    {
        $this->getDocuments()->removeElement($document);
        return $this;
    }

    public function getIdCategorie(): ?int
    {
        return $this->id_categorie;
    }

}
