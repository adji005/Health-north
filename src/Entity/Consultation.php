<?php

namespace App\Entity;

use App\Entity\Ordonnance;
use App\Repository\ConsultationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?RendezVous $rendezVous = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProfessionnelSante $medecin = null;

    /**
     * @var Collection<int, DocumentMedical>
     */
    #[ORM\OneToMany(targetEntity: DocumentMedical::class, mappedBy: 'consultation', orphanRemoval: true)]
    private Collection $documents;

    /**
     * @var Collection<int, Ordonnance>
     */
    #[ORM\OneToMany(targetEntity: Ordonnance::class, mappedBy: 'consultation', orphanRemoval: true)]
    private Collection $ordonnances;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
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

    /**
     * @return Collection<int, DocumentMedical>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(DocumentMedical $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setConsultation($this);
        }

        return $this;
    }

    public function removeDocument(DocumentMedical $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getConsultation() === $this) {
                $document->setConsultation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ordonnance>
     */
    public function getOrdonnances(): Collection
    {
        return $this->ordonnances;
    }

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
            // set the owning side to null (unless already changed)
            if ($ordonnance->getConsultation() === $this) {
                $ordonnance->setConsultation(null);
            }
        }

        return $this;
    }
}