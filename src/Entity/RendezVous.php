<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateHeure = null;

    #[ORM\Column(length: 20)]
    private string $statut = 'confirme';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motif = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ordonnancePath = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProfessionnelSante $medecin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?ConsultationType $consultationType = null;

    #[ORM\OneToOne(mappedBy: 'rendezVous', cascade: ['persist', 'remove'])]
private ?Consultation $consultation = null;

public function getConsultation(): ?Consultation
{
    return $this->consultation;
}

public function setConsultation(?Consultation $consultation): static
{
    if ($consultation === null && $this->consultation !== null) {
        $this->consultation->setRendezVous(null);
    }

    if ($consultation !== null && $consultation->getRendezVous() !== $this) {
        $consultation->setRendezVous($this);
    }

    $this->consultation = $consultation;

    return $this;
}
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateHeure(): ?\DateTimeImmutable
    {
        return $this->dateHeure;
    }

    public function setDateHeure(\DateTimeImmutable $dateHeure): static
    {
        $this->dateHeure = $dateHeure;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): static
    {
        $this->motif = $motif;
        return $this;
    }

    public function getOrdonnancePath(): ?string
    {
        return $this->ordonnancePath;
    }

    public function setOrdonnancePath(?string $ordonnancePath): static
    {
        $this->ordonnancePath = $ordonnancePath;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;
        return $this;
    }

    public function getMedecin(): ?ProfessionnelSante
    {
        return $this->medecin;
    }

    public function setMedecin(?ProfessionnelSante $medecin): static
    {
        $this->medecin = $medecin;
        return $this;
    }

    public function getConsultationType(): ?ConsultationType
    {
        return $this->consultationType;
    }

    public function setConsultationType(?ConsultationType $consultationType): static
    {
        $this->consultationType = $consultationType;
        return $this;
    }

    public function canPatientCancel(): bool
    {
        if ($this->statut === 'annule') {
            return false;
        }
        $now = new \DateTimeImmutable();
        $diff = $this->dateHeure->getTimestamp() - $now->getTimestamp();
        return $diff > 86400;
    }

    public function annuler(): void
    {
        $this->statut = 'annule';
    }
}