<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le titre doit avoir au moins 3 caractères.')]
    private ?string $titre = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: 'Le prix est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix doit être positif.')]
    #[Assert\GreaterThanOrEqual(value: 10, message: 'Le prix minimum est 10 €.')]
    private ?float $prix = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: 'La durée est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'La durée ne peut pas être négative.')]
    private ?int $duree = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: "offres")]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Veuillez choisir un service.')]
    private ?Service $service = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(float $prix): static { $this->prix = $prix; return $this; }

    public function getDuree(): ?int { return $this->duree; }
    public function setDuree(int $duree): static { $this->duree = $duree; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }

    public function getService(): ?Service { return $this->service; }
    public function setService(?Service $service): static { $this->service = $service; return $this; }

    public function __toString(): string { return $this->titre ?? ''; }
}