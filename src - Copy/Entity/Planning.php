<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Activite;

#[ORM\Entity]
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
}
