<?php

namespace App\Entity;

use App\Entity\Ordonnance;
use App\Repository\ConsultationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**Entité Consultation*/
#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**Notes du médecin (diagnostic, observations, etc.)*/
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    /**Date de création de la consultation*/
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**Relation One-to-One : Une consultation par RDV*/
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?RendezVous $rendezVous = null;

    /**Relation Many-to-One : Patient consulté*/
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    /**Relation Many-to-One : Médecin qui a consulté*/
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProfessionnelSante $medecin = null;

    /** ordonnances liées à cette consultation*/
    #[ORM\OneToMany(targetEntity: Ordonnance::class, mappedBy: 'consultation', orphanRemoval: true)]
    private Collection $ordonnances;

    public function __construct()
    {
        $this->ordonnances = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
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

    public function getRendezVous(): ?RendezVous
    {
        return $this->rendezVous;
    }

    public function setRendezVous(RendezVous $rendezVous): static
    {
        $this->rendezVous = $rendezVous;
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

    /**Retourne toutes les ordonnances*/
    public function getOrdonnances(): Collection
    {
        return $this->ordonnances;
    }

    /**Retourne la première ordonnance */
    public function getOrdonnance(): ?Ordonnance
    {
        return $this->ordonnances->first() ?: null;
    }

    public function addOrdonnance(Ordonnance $ordonnance): static
    {
        if (!$this->ordonnances->contains($ordonnance)) {
            $this->ordonnances->add($ordonnance);
            $ordonnance->setConsultation($this);
        }
        return $this;
    }

    public function removeOrdonnance(Ordonnance $ordonnance): static
    {
        if ($this->ordonnances->removeElement($ordonnance)) {
            if ($ordonnance->getConsultation() === $this) {
                $ordonnance->setConsultation(null);
            }
        }
        return $this;
    }
}