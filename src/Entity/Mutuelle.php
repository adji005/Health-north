<?php

namespace App\Entity;

use App\Repository\MutuelleRepository;
use Doctrine\ORM\Mapping as ORM;

/**Entité Mutuelle Représente la mutuelle  d'un patient*/
#[ORM\Entity(repositoryClass: MutuelleRepository::class)]
class Mutuelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**Nom de l'organisme de mutuelle*/
    #[ORM\Column(length: 100)]
    private ?string $organisme = null;

    /*Numéro d'adhérent à la mutuelle*/
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroAdherent = null;

    /**Taux de remboursement */
    #[ORM\Column(nullable: true)]
    private ?float $tauxRemboursement = null;

    /**Relation one-to-One : une mutuelle pour un patient*/
    #[ORM\OneToOne(inversedBy: 'mutuelle')]
    #[ORM\JoinColumn(nullable: false)]
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
