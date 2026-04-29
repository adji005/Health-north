<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**Entité Patient Représente les informations d'un patient*/
#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    /**Date de naissance du patient*/
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateDeNaissance = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(length: 50)]
    private ?string $adresse = null;

    /**Relation One-to-One vers User*/
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /** collection rendez-vous du patient*/
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'patient', orphanRemoval: true)]
    private Collection $rendezVous;

    /**Dossier médical du patient (antécédents, allergies, etc.)*/
    #[ORM\OneToOne(mappedBy: 'patient', cascade: ['persist', 'remove'])]
    private ?DossierPatient $dossier = null;

    /**Mutuelle du patient*/
    #[ORM\OneToOne(mappedBy: 'patient', cascade: ['persist', 'remove'])]
    private ?Mutuelle $mutuelle = null;

    public function __construct()
    {
        //initialisation de la collections rdv
        $this->rendezVous = new ArrayCollection();
    }


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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getDateDeNaissance(): ?\DateTime
    {
        return $this->dateDeNaissance;
    }

    public function setDateDeNaissance(\DateTime $dateDeNaissance): static
    {
        $this->dateDeNaissance = $dateDeNaissance;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }



    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Retourne les rendez-vous du patient
     */
    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
    }

    public function addRendezVous(RendezVous $rendezVous): static
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous->add($rendezVous);
            $rendezVous->setPatient($this);
        }
        return $this;
    }

    public function removeRendezVous(RendezVous $rendezVous): static
    {
        if ($this->rendezVous->removeElement($rendezVous)) {
            if ($rendezVous->getPatient() === $this) {
                $rendezVous->setPatient(null);
            }
        }
        return $this;
    }

    public function getDossier(): ?DossierPatient
    {
        return $this->dossier;
    }

    public function setDossier(DossierPatient $dossier): static
    {
        if ($dossier->getPatient() !== $this) {
            $dossier->setPatient($this);
        }
        $this->dossier = $dossier;
        return $this;
    }
/**Retourne la mutuelle du patient (une seule)*/
public function getMutuelle(): ?Mutuelle
{
    return $this->mutuelle;
}

public function setMutuelle(?Mutuelle $mutuelle): static
{
    if ($mutuelle === null && $this->mutuelle !== null) {
        $this->mutuelle->setPatient(null);
    }

    if ($mutuelle !== null && $mutuelle->getPatient() !== $this) {
        $mutuelle->setPatient($this);
    }

    $this->mutuelle = $mutuelle;

    return $this;
}
}