<?php

namespace App\Entity;

use App\Repository\ProfessionnelSanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**Entité ProfessionnelSante, représente les infos du médecin et le compte peut se retrouver sous 3 statuts possibles : 'en_attente'(en attente de validation par l'admin) 'valide' 'refuse'*/
#[ORM\Entity(repositoryClass: ProfessionnelSanteRepository::class)]
class ProfessionnelSante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**Relation One-to-One vers User*/
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    /**Spécialité médicale*/
    #[ORM\Column(length: 100)]
    private ?string $specialite = null;

    /**Numéro RPPS (Répertoire Partagé des Professionnels de Santé tt les médecin en ont un )*/
    #[ORM\Column(length: 100)]
    private ?string $numeroRPPS = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 100)]
    private ?string $ville = null;

    #[ORM\Column(length: 10)]
    private ?string $codePostal = null;

    /**Secteur conventionné (1, 2, ou 3)*/
    #[ORM\Column(length: 1)]
    private ?string $secteur = null;

    /**Médecin conventionné Sécurité Sociale*/
    #[ORM\Column]
    private ?bool $conventionneSecu = null;

    /**Statut de validation : 'en_attente', 'valide', 'refuse'*/
    #[ORM\Column(length: 20)]
    private string $statut = 'en_attente';

    /**disponibilités du médecin*/
    #[ORM\OneToMany(targetEntity: Disponibilite::class, mappedBy: 'medecin', orphanRemoval: true)]
    private Collection $disponibilites;

    /**Collection des rendez-vous du médecin*/
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'medecin', orphanRemoval: true)]
    private Collection $rendezVous;

    public function __construct()
    {
        $this->disponibilites = new ArrayCollection();
        $this->rendezVous = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;
        return $this;
    }

    public function getNumeroRPPS(): ?string
    {
        return $this->numeroRPPS;
    }

    public function setNumeroRPPS(string $numeroRPPS): static
    {
        $this->numeroRPPS = $numeroRPPS;
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

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getSecteur(): ?string
    {
        return $this->secteur;
    }

    public function setSecteur(string $secteur): static
    {
        $this->secteur = $secteur;
        return $this;
    }

    public function isConventionneSecu(): ?bool
    {
        return $this->conventionneSecu;
    }

    public function setConventionneSecu(bool $conventionneSecu): static
    {
        $this->conventionneSecu = $conventionneSecu;
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

    /**Retourne les disponibilités du médecin*/
    public function getDisponibilites(): Collection
    {
        return $this->disponibilites;
    }

    public function addDisponibilite(Disponibilite $disponibilite): static
    {
        if (!$this->disponibilites->contains($disponibilite)) {
            $this->disponibilites->add($disponibilite);
            $disponibilite->setMedecin($this);
        }
        return $this;
    }

    public function removeDisponibilite(Disponibilite $disponibilite): static
    {
        if ($this->disponibilites->removeElement($disponibilite)) {
            if ($disponibilite->getMedecin() === $this) {
                $disponibilite->setMedecin(null);
            }
        }
        return $this;
    }

    /**Retourne les rendez-vous du médecin*/
    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
    }

    public function addRendezVous(RendezVous $rendezVous): static
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous->add($rendezVous);
            $rendezVous->setMedecin($this);
        }
        return $this;
    }

    public function removeRendezVous(RendezVous $rendezVous): static
    {
        if ($this->rendezVous->removeElement($rendezVous)) {
            if ($rendezVous->getMedecin() === $this) {
                $rendezVous->setMedecin(null);
            }
        }
        return $this;
    }
}