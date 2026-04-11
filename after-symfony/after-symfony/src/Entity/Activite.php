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
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['activites', 'plannings'])]
    private ?int $id_activite = null;

    public function getId_activite(): ?int
    {
        return $this->id_activite;
    }

    public function setId_activite(int $id_activite): self
    {
        $this->id_activite = $id_activite;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
#[Groups(['activites', 'plannings'])]

private ?string $nom = null;
    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

   #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['activites', 'plannings'])]

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

      #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['activites', 'plannings'])]

    private ?string $categorie = null;

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

     #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['activites', 'plannings'])]
 
    private ?string $lieu = null;
    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    #[Groups(['activites', 'plannings'])]

    private ?float $prix = null;

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Voyage::class, inversedBy: 'activites')]
    #[ORM\JoinColumn(name: 'id_voyage', referencedColumnName: 'id_voyage')]
    private ?Voyage $voyage = null;

    public function getVoyage(): ?Voyage
    {
        return $this->voyage;
    }

    public function setVoyage(?Voyage $voyage): self
    {
        $this->voyage = $voyage;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Planning::class, mappedBy: 'activite')]
    private Collection $plannings;

    public function __construct()
    {
        $this->plannings = new ArrayCollection();
    }

    /**
     * @return Collection<int, Planning>
     */
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

    public function getIdActivite(): ?int
    {
        return $this->id_activite;
    }
}