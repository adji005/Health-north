<?php

namespace App\Entity;

use App\Repository\MutuelleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MutuelleRepository::class)]
class Mutuelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $organisme = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroAdherent = null;

    #[ORM\Column(nullable: true)]
    private ?float $tauxRemboursement = null;

    #[ORM\ManyToOne(inversedBy: 'mutuelles')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Patient $patient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganisme(): ?string
    {
        return $this->organisme;
    }

    public function setOrganisme(string $organisme): static
    {
        $this->organisme = $organisme;

        return $this;
    }

    public function getNumeroAdherent(): ?string
    {
        return $this->numeroAdherent;
    }

    public function setNumeroAdherent(string $numeroAdherent): static
    {
        $this->numeroAdherent = $numeroAdherent;

        return $this;
    }

    public function getTauxRemboursement(): ?float
    {
        return $this->tauxRemboursement;
    }

    public function setTauxRemboursement(?float $tauxRemboursement): static
    {
        $this->tauxRemboursement = $tauxRemboursement;

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
}
