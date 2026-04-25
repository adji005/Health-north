<?php

namespace App\Entity;

use App\Repository\DossierPatientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DossierPatientRepository::class)]
class DossierPatient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $antecedents = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $maladiesChroniques = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $groupeSanguin = null;

    #[ORM\OneToOne(inversedBy: 'dossier', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAntecedents(): ?string
    {
        return $this->antecedents;
    }

    public function setAntecedents(?string $antecedents): static
    {
        $this->antecedents = $antecedents;
        return $this;
    }

    public function getMaladiesChroniques(): ?string
    {
        return $this->maladiesChroniques;
    }

    public function setMaladiesChroniques(?string $maladiesChroniques): static
    {
        $this->maladiesChroniques = $maladiesChroniques;
        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;
        return $this;
    }

    public function getGroupeSanguin(): ?string
    {
        return $this->groupeSanguin;
    }

    public function setGroupeSanguin(?string $groupeSanguin): static
    {
        $this->groupeSanguin = $groupeSanguin;
        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(Patient $patient): static
    {
        $this->patient = $patient;
        return $this;
    }
}