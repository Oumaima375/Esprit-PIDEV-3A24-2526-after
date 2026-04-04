<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Reservation;

#[ORM\Entity]
class Users
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom;

    #[ORM\Column(type: "string", length: 100)]
    private string $prenom;

    #[ORM\Column(type: "string", length: 150)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(type: "string", length: 20)]
    private string $telephone;

    #[ORM\Column(type: "string", length: 255)]
    private string $photo_profil;

    #[ORM\Column(type: "string", length: 20)]
    private string $type_utilisateur;

    #[ORM\Column(type: "boolean")]
    private bool $is_verified;

    #[ORM\Column(type: "string", length: 255)]
    private string $verification_token;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $verification_expiry;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($value)
    {
        $this->prenom = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone($value)
    {
        $this->telephone = $value;
    }

    public function getPhoto_profil()
    {
        return $this->photo_profil;
    }

    public function setPhoto_profil($value)
    {
        $this->photo_profil = $value;
    }

    public function getType_utilisateur()
    {
        return $this->type_utilisateur;
    }

    public function setType_utilisateur($value)
    {
        $this->type_utilisateur = $value;
    }

    public function getIs_verified()
    {
        return $this->is_verified;
    }

    public function setIs_verified($value)
    {
        $this->is_verified = $value;
    }

    public function getVerification_token()
    {
        return $this->verification_token;
    }

    public function setVerification_token($value)
    {
        $this->verification_token = $value;
    }

    public function getVerification_expiry()
    {
        return $this->verification_expiry;
    }

    public function setVerification_expiry($value)
    {
        $this->verification_expiry = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Document::class)]
    private Collection $documents;

        public function getDocuments(): Collection
        {
            return $this->documents;
        }
    
        public function addDocument(Document $document): self
        {
            if (!$this->documents->contains($document)) {
                $this->documents[] = $document;
                $document->setId_user($this);
            }
    
            return $this;
        }
    
        public function removeDocument(Document $document): self
        {
            if ($this->documents->removeElement($document)) {
                // set the owning side to null (unless already changed)
                if ($document->getId_user() === $this) {
                    $document->setId_user(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Reservation::class)]
    private Collection $reservations;
}
