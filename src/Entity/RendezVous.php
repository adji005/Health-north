<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**Entité RendezVous, Représente un rendez-vous médical entre un patient et un médecin. Il existe plusieurs Statuts possibles : 'confirme' 'annule' 'termine'*/


#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**Date et heure du rendez-vous*/
    #[ORM\Column]
    private ?\DateTimeImmutable $dateHeure = null;

    /**Statut du RDV*/
    #[ORM\Column(length: 20)]
    private string $statut = 'confirme';

    /*Motif de consultation (optionnel)*/
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motif = null;

    /**Date de création du RDV*/
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**Relation Many-to-One, Plusieurs RDV pour un patient*/
    #[ORM\ManyToOne(inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    /**Relation Many-to-One, Plusieurs RDV pour un médecin*/
    #[ORM\ManyToOne(inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProfessionnelSante $medecin = null;

    /**Relation One-to-One : Une consultation par RDV*/
    #[ORM\OneToOne(mappedBy: 'rendezVous', cascade: ['persist', 'remove'])]
    private ?Consultation $consultation = null;


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


    /**Vérifie si le patient peut annuler ce RDV car j'ai ajouté une règle qui fait qu'il ne peut pas l'annuler a moins de 24h du rdv*/
    public function canPatientCancel(): bool
    {
    
        if ($this->statut === 'annule') {
            return false;
        }

        // Calculer le délai entre maintenant et le RDV
        $now = new \DateTimeImmutable();
        $diff = $this->dateHeure->getTimestamp() - $now->getTimestamp();

        // 86400 secondes = 24 heures
        return $diff > 86400;
    }

    /**Annuler le rendez-vous*/
    public function annuler(): void
    {
        $this->statut = 'annule';
    }
}