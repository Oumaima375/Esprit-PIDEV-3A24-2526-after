<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Document;
use App\Repository\Categorie_documentRepository;

#[ORM\Entity(repositoryClass: App\Repository\Categorie_documentRepository::class)]
class Categorie_document
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_categorie;

    #[ORM\Column(type: "string", length: 100)]
    private string $libelle;

    #[ORM\Column(type: "string", length: 255)]
    private string $description;

    public function getId_categorie()
    {
        return $this->id_categorie;
    }

    public function setId_categorie($value)
    {
        $this->id_categorie = $value;
    }

    public function getLibelle()
    {
        return $this->libelle;
    }

    public function setLibelle($value)
    {
        $this->libelle = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_categorie", targetEntity: Document::class)]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

        public function getDocuments(): Collection
        {
            return $this->documents;
        }
    
        public function addDocument(Document $document): self
        {
            if (!$this->documents->contains($document)) {
                $this->documents[] = $document;
                $document->setId_categorie($this);
            }
    
            return $this;
        }
    
        public function removeDocument(Document $document): self
        {
            if ($this->documents->removeElement($document)) {
                // set the owning side to null (unless already changed)
                if ($document->getId_categorie() === $this) {
                    $document->setId_categorie(null);
                }
            }
    
            return $this;
        }

        public function getIdCategorie(): ?int
        {
            return $this->id_categorie;
        }
}
