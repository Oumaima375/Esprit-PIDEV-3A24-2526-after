<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\ActiviteRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActiviteRepository::class)]
#[ORM\Table(name: 'activite')]
class Activite
{
   #[ORM\Id]
#[ORM\GeneratedValue(strategy: 'AUTO')]
#[ORM\Column(name: 'id_activite', type: 'integer')]
#[Groups(['activites', 'plannings'])]
private ?int $id_activite = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['activites', 'plannings'])]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['activites', 'plannings'])]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['activites', 'plannings'])]
    private ?string $categorie = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['activites', 'plannings'])]
    private ?string $lieu = null;

    #[ORM\Column(type: 'decimal', nullable: true)]
    #[Groups(['activites', 'plannings'])]
    private ?float $prix = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

  

    #[ORM\ManyToOne(targetEntity: Voyage::class, inversedBy: 'activites')]
    #[ORM\JoinColumn(name: 'id_voyage', referencedColumnName: 'id_voyage')]
    private ?Voyage $voyage = null;

    #[ORM\OneToMany(targetEntity: Planning::class, mappedBy: 'activite')]
    private Collection $plannings;

    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'activite', orphanRemoval: true)]
    private Collection $avis;

    public function __construct()
    {
        $this->plannings = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    public function getId_activite(): ?int { return $this->id_activite; }
    public function getIdActivite(): ?int { return $this->id_activite; }
    public function setId_activite(int $id_activite): self { $this->id_activite = $id_activite; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(?string $categorie): self { $this->categorie = $categorie; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): self { $this->lieu = $lieu; return $this; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(?float $prix): self { $this->prix = $prix; return $this; }

      public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }

    public function getVoyage(): ?Voyage { return $this->voyage; }
    public function setVoyage(?Voyage $voyage): self { $this->voyage = $voyage; return $this; }

    public function getPlannings(): Collection
    {
        if (!$this->plannings instanceof Collection) {
            $this->plannings = new ArrayCollection();
        }
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): self
    {
        if (!$this->getPlannings()->contains($planning)) {
            $this->getPlannings()->add($planning);
        }
        return $this;
    }

    public function removePlanning(Planning $planning): self
    {
        $this->getPlannings()->removeElement($planning);
        return $this;
    }

    public function getAvis(): Collection
    {
        if (!$this->avis instanceof Collection) {
            $this->avis = new ArrayCollection();
        }
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setActivite($this);
        }
        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            if ($avi->getActivite() === $this) {
                $avi->setActivite(null);
            }
        }
        return $this;
    }
}