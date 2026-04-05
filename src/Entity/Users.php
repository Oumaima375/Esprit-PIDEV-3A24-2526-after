<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reservation;
// use App\Entity\App\Repository\UsersRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity(fields: ['email'], message: 'Un utilisateur existe déjà avec cette adresse email.', errorPath: 'email')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{

#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column]
private ?int $id = null;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom;

    #[ORM\Column(type: "string", length: 100)]
    private string $prenom;

    #[ORM\Column(type: "string", length: 150)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(type: "string", length: 20)]
private ?string $telephone = null;

    #[ORM\Column(type: "string", length: 255)]
private ?string $photo_profil = null;

    #[ORM\Column(type: "string", length: 20)]
    private string $type_utilisateur;

    #[ORM\Column(type: "boolean")]
    private bool $is_verified;

    #[ORM\Column(type: "string", length: 255)]
    private string $verification_token;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $verification_expiry;

    public function __construct()
    {
        $this->documents   = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    // =========================================================
    // Méthodes requises par UserInterface
    // =========================================================

    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->type_utilisateur)];
    }

    public function eraseCredentials(): void
    {
        // Rien à effacer (pas de mot de passe en clair stocké)
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // =========================================================
    // Getters / Setters existants — inchangés
    // =========================================================

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

    public function getPassword(): string
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

    // =========================================================
    // Getters / Setters Symfony-style (camelCase)
    // =========================================================

    public function getPhotoProfil(): ?string
    {
        return $this->photo_profil;
    }

    public function setPhotoProfil(string $photo_profil): static
    {
        $this->photo_profil = $photo_profil;
        return $this;
    }

    public function getTypeUtilisateur(): ?string
    {
        return $this->type_utilisateur;
    }

    public function setTypeUtilisateur(string $type_utilisateur): static
    {
        $this->type_utilisateur = $type_utilisateur;
        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): static
    {
        $this->is_verified = $is_verified;
        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verification_token;
    }

    public function setVerificationToken(string $verification_token): static
    {
        $this->verification_token = $verification_token;
        return $this;
    }

    public function getVerificationExpiry(): ?\DateTime
    {
        return $this->verification_expiry;
    }

    public function setVerificationExpiry(\DateTime $verification_expiry): static
    {
        $this->verification_expiry = $verification_expiry;
        return $this;
    }

    // =========================================================
    // Relation Documents
    // =========================================================

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
            if ($document->getId_user() === $this) {
                $document->setId_user(null);
            }
        }
        return $this;
    }

    // =========================================================
    // Relation Reservations
    // =========================================================

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Reservation::class)]
    private Collection $reservations;

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setIdUser($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getIdUser() === $this) {
                $reservation->setIdUser(null);
            }
        }
        return $this;
    }
}