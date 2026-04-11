<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PlanningRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PlanningRepository::class)]
#[ORM\Table(name: 'planning')]
class Planning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['plannings'])]
    private ?int $id_planning = null;

    public function getIdPlanning(): ?int
    {
        return $this->id_planning;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['plannings'])]
    private ?int $id_user = null;

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(int $id_user): self
    {
        $this->id_user = $id_user;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Activite::class, inversedBy: 'plannings')]
    #[ORM\JoinColumn(name: 'id_activite', referencedColumnName: 'id_activite')]
    #[Groups(['plannings'])]
    private ?Activite $activite = null;

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): self
    {
        $this->activite = $activite;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotBlank(message: "La date est obligatoire")]
    #[Assert\GreaterThanOrEqual("today", message: "La date doit être aujourd'hui ou future")]
    #[Groups(['plannings'])]
    private ?\DateTimeInterface $date_activite = null;

    public function getDateActivite(): ?\DateTimeInterface
    {
        return $this->date_activite;
    }

    public function setDateActivite(?\DateTimeInterface $date_activite): self
    {
        $this->date_activite = $date_activite;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "L'heure de début est obligatoire")]
    #[Assert\Regex(
        pattern: '/^\d{2}:\d{2}$/',
        message: "L'heure doit être au format HH:MM"
    )]
    #[Groups(['plannings'])]
    private ?string $heure_debut = null;

    public function getHeureDebut(): ?string
    {
        return $this->heure_debut;
    }

    public function setHeureDebut(?string $heure_debut): self
    {
        $this->heure_debut = $heure_debut;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "La durée est obligatoire")]
    #[Assert\Positive(message: "La durée doit être un nombre positif")]
    #[Assert\LessThanOrEqual(480, message: "La durée ne peut pas dépasser 8h")]
    #[Groups(['plannings'])]
    private ?int $duree = null;

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;
        return $this;
    }
    
}