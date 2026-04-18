<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Service;
use App\Repository\OffreRepository;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_offre;

    #[ORM\Column(type: "string", length: 100)]
    private string $titre;

    #[ORM\Column(type: "float")]
    private float $prix;

    #[ORM\Column(type: "integer")]
    private int $duree;

    // ── NEW: destination city name for weather/map ──
    #[ORM\Column(type: "string", length: 150, nullable: true)]
    private ?string $destination = null;

    // ── NEW: availability calendar ──
    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    // ── NEW: weather cache (JSON string) ──
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $weatherCache = null;

    // ── NEW: geo coordinates for Leaflet map ──
    #[ORM\Column(type: "float", nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $longitude = null;

    // ── NEW: favorites count ──
    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $favorisCount = 0;

    // ── NEW: notification flag (admin: offre expirée / prix changé) ──
    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $notificationEnvoyee = false;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: "offres")]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'id_service', onDelete: 'CASCADE')]
    private Service $id_service;

    // ─────────────────────────────────────────────
    //  Original getters/setters (unchanged)
    // ─────────────────────────────────────────────

    public function getId_offre(): ?int { return $this->id_offre; }
    public function setId_offre($value): void { $this->id_offre = $value; }
    public function getId(): ?int { return $this->id_offre; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre($value): void { $this->titre = $value; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix($value): void { $this->prix = $value; }

    public function getDuree(): ?int { return $this->duree; }
    public function setDuree($value): void { $this->duree = $value; }

    public function getId_service(): mixed { return $this->id_service; }
    public function setId_service($value): void { $this->id_service = $value; }

    public function getService(): ?Service { return $this->id_service; }
    public function setService(?Service $service): static { $this->id_service = $service; return $this; }

    public function getIdOffre(): ?int { return $this->id_offre; }
    public function getIdService(): ?Service { return $this->id_service; }
    public function setIdService(?Service $id_service): static { $this->id_service = $id_service; return $this; }

    // ─────────────────────────────────────────────
    //  NEW getters/setters
    // ─────────────────────────────────────────────

    public function getDestination(): ?string { return $this->destination; }
    public function setDestination(?string $destination): static { $this->destination = $destination; return $this; }

    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(?\DateTimeInterface $dateDebut): static { $this->dateDebut = $dateDebut; return $this; }

    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(?\DateTimeInterface $dateFin): static { $this->dateFin = $dateFin; return $this; }

    public function getWeatherCache(): ?string { return $this->weatherCache; }
    public function setWeatherCache(?string $weatherCache): static { $this->weatherCache = $weatherCache; return $this; }

    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): static { $this->latitude = $latitude; return $this; }

    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): static { $this->longitude = $longitude; return $this; }

    public function getFavorisCount(): int { return $this->favorisCount; }
    public function setFavorisCount(int $favorisCount): static { $this->favorisCount = $favorisCount; return $this; }

    public function isNotificationEnvoyee(): bool { return $this->notificationEnvoyee; }
    public function setNotificationEnvoyee(bool $notificationEnvoyee): static { $this->notificationEnvoyee = $notificationEnvoyee; return $this; }

    /**
     * Helper: is this offer currently available (within date range)?
     */
    public function isDisponible(): bool
    {
        if (!$this->dateDebut || !$this->dateFin) {
            return true; // no restriction
        }
        $now = new \DateTime();
        return $now >= $this->dateDebut && $now <= $this->dateFin;
    }

    /**
     * Helper: days until offer expires (null = no expiry set)
     */
    public function joursRestants(): ?int
    {
        if (!$this->dateFin) return null;
        $now = new \DateTime();
        $diff = $now->diff($this->dateFin);
        return $diff->invert ? 0 : (int) $diff->days;
    }
}