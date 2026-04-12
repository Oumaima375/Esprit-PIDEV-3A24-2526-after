<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity(fields: ['titre'], message: 'Un service avec ce titre existe déjà.')]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le titre doit avoir au moins 3 caractères.', maxMessage: 'Le titre ne peut pas dépasser 255 caractères.')]
    private ?string $titre = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(max: 1000, maxMessage: 'La description ne peut pas dépasser 1000 caractères.')]
    private ?string $description = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(mappedBy: "service", targetEntity: Offre::class)]
    private Collection $offres;

    public function __construct()
    {
        $this->offres = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }

    public function getOffres(): Collection { return $this->offres; }

    public function __toString(): string { return $this->titre ?? ''; }
}