<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaire = null;

    #[ORM\Column]
    private ?int $note = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Activite::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(name: 'activite_id', referencedColumnName: 'id_activite', nullable: false)]
    private ?Activite $activite = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'auteur_id', referencedColumnName: 'id', nullable: false)]
    private ?Users $auteur = null;

    public function getId(): ?int { return $this->id; }

    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getNote(): ?int { return $this->note; }
    public function setNote(int $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getActivite(): ?Activite { return $this->activite; }
    public function setActivite(?Activite $activite): static
    {
        $this->activite = $activite;
        return $this;
    }

    public function getAuteur(): ?Users { return $this->auteur; }
    public function setAuteur(?Users $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }
}
