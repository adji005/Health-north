<?php

namespace App\Entity;

use App\Repository\DisponibiliteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**Entité Disponibilite, représente un créneau horaire où un médecin est disponible*/
#[ORM\Entity(repositoryClass: DisponibiliteRepository::class)]
class Disponibilite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**Date de la disponibilité*/
    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date = null;

    /**Heure de début (ex: 14h00)*/
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $heureDebut = null;

    /**Heure de fin */
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $heureFin = null;

    /**Relation Many-to-One Plusieurs disponibilités pour un médecin*/
    #[ORM\ManyToOne(inversedBy: 'disponibilites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProfessionnelSante $medecin = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getHeureDebut(): ?\DateTime
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTime $heureDebut): static
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getHeureFin(): ?\DateTime
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTime $heureFin): static
    {
        $this->heureFin = $heureFin;
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
}