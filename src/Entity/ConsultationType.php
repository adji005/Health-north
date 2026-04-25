<?php

namespace App\Entity;

use App\Repository\ConsultationTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationTypeRepository::class)]
class ConsultationType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $dureeEnMin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDureeEnMin(): ?int
    {
        return $this->dureeEnMin;
    }

    public function setDureeEnMin(int $dureeEnMin): static
    {
        $this->dureeEnMin = $dureeEnMin;

        return $this;
    }
}
