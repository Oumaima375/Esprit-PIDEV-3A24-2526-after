<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Activite;
use App\Repository\PlanningRepository;

#[ORM\Entity(repositoryClass: App\Repository\PlanningRepository::class)]
class Planning
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_planning;

    #[ORM\Column(type: "integer")]
    private int $id_user;

    #[ORM\ManyToOne(targetEntity: Activite::class, inversedBy: "plannings")]
    #[ORM\JoinColumn(name: 'id_activite', referencedColumnName: 'id_activite', onDelete: 'CASCADE')]
    private Activite $id_activite;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_activite;

    #[ORM\Column(type: "string")]
    private string $heure_debut;

    #[ORM\Column(type: "integer")]
    private int $duree;

    public function getId_planning()
    {
        return $this->id_planning;
    }

    public function setId_planning($value)
    {
        $this->id_planning = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getId_activite()
    {
        return $this->id_activite;
    }

    public function setId_activite($value)
    {
        $this->id_activite = $value;
    }

    public function getDate_activite()
    {
        return $this->date_activite;
    }

    public function setDate_activite($value)
    {
        $this->date_activite = $value;
    }

    public function getHeure_debut()
    {
        return $this->heure_debut;
    }

    public function setHeure_debut($value)
    {
        $this->heure_debut = $value;
    }

    public function getDuree()
    {
        return $this->duree;
    }

    public function setDuree($value)
    {
        $this->duree = $value;
    }

    public function getIdPlanning(): ?int
    {
        return $this->id_planning;
    }

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(int $id_user): static
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getDateActivite(): ?\DateTime
    {
        return $this->date_activite;
    }

    public function setDateActivite(\DateTime $date_activite): static
    {
        $this->date_activite = $date_activite;

        return $this;
    }

    public function getHeureDebut(): ?string
    {
        return $this->heure_debut;
    }

    public function setHeureDebut(string $heure_debut): static
    {
        $this->heure_debut = $heure_debut;

        return $this;
    }

    public function getIdActivite(): ?Activite
    {
        return $this->id_activite;
    }

    public function setIdActivite(?Activite $id_activite): static
    {
        $this->id_activite = $id_activite;

        return $this;
    }
}