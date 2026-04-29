<?php

namespace App\Entity;

use App\Repository\OrdonnanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/*Entité Ordonnance, Représente une ordonnance médicale (fichier PDF) liée à une consultation*/
#[ORM\Entity(repositoryClass: OrdonnanceRepository::class)]
class Ordonnance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**Chemin vers le fichier PDF de l'ordonnance*/
    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    /**Date de création de l'ordonnance*/
    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

/**Relation Many-to-One vers Consultation, en théorie : plusieurs ordonnances possibles mais en pratique : une seule ordonnance par consultation
 */
    #[ORM\ManyToOne(inversedBy: 'ordonnances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Consultation $consultation = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getConsultation(): ?Consultation
    {
        return $this->consultation;
    }

    public function setConsultation(?Consultation $consultation): static
    {
        $this->consultation = $consultation;
        return $this;
    }
}