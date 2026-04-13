<?php
// src/Entity/DocumentHistorique.php

namespace App\Entity;

use App\Repository\DocumentHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentHistoriqueRepository::class)]
#[ORM\Table(name: 'document_historique')]
class DocumentHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $idDocument = null;

    #[ORM\Column(length: 255)]
    private ?string $nomDocument = null;

    #[ORM\Column(length: 50)]
    private ?string $action = null; // création, modification, suppression

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $anciennesValeurs = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $nouvellesValeurs = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateAction;

    public function __construct()
    {
        $this->dateAction = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getIdDocument(): ?int { return $this->idDocument; }
    public function setIdDocument(int $id): static { $this->idDocument = $id; return $this; }
    public function getNomDocument(): ?string { return $this->nomDocument; }
    public function setNomDocument(string $nom): static { $this->nomDocument = $nom; return $this; }
    public function getAction(): ?string { return $this->action; }
    public function setAction(string $action): static { $this->action = $action; return $this; }
    public function getAnciennesValeurs(): ?array { return $this->anciennesValeurs; }
    public function setAnciennesValeurs(?array $v): static { $this->anciennesValeurs = $v; return $this; }
    public function getNouvellesValeurs(): ?array { return $this->nouvellesValeurs; }
    public function setNouvellesValeurs(?array $v): static { $this->nouvellesValeurs = $v; return $this; }
    public function getDateAction(): \DateTimeInterface { return $this->dateAction; }
}