<?php

namespace App\Entity;

use App\Repository\ProfessionnelSanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessionnelSanteRepository::class)]
class ProfessionnelSante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    private ?string $specialite = null;

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

    #[ORM\Column(length: 1)]
    private ?string $secteur = null;

    #[ORM\Column]
    private ?bool $conventionneSecu = null;

    #[ORM\Column(nullable: true)]
    private ?array $modeDePaiement = null;

    #[ORM\Column(nullable: true)]
    private ?array $horaires = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoPath = null;

    #[ORM\Column(length: 20)]
    private string $statut = 'en_attente';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $justificatifPath = null;

    /**
     * @var Collection<int, ConsultationType>
     */
    #[ORM\ManyToMany(targetEntity: ConsultationType::class)]
    private Collection $consultationTypes;

    /**
     * @var Collection<int, Disponibilite>
     */
    #[ORM\OneToMany(targetEntity: Disponibilite::class, mappedBy: 'medecin', orphanRemoval: true)]
    private Collection $disponibilites;

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'medecin', orphanRemoval: true)]
    private Collection $rendezVous;

    public function __construct()
    {
        $this->consultationTypes = new ArrayCollection();
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

    public function getModeDePaiement(): ?array
    {
        return $this->modeDePaiement;
    }

    public function setModeDePaiement(?array $modeDePaiement): static
    {
        $this->modeDePaiement = $modeDePaiement;
        return $this;
    }

    public function getHoraires(): ?array
    {
        return $this->horaires;
    }

    public function setHoraires(?array $horaires): static
    {
        $this->horaires = $horaires;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    public function setPhotoPath(?string $photoPath): static
    {
        $this->photoPath = $photoPath;
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

    public function getJustificatifPath(): ?string
    {
        return $this->justificatifPath;
    }

    public function setJustificatifPath(?string $justificatifPath): static
    {
        $this->justificatifPath = $justificatifPath;
        return $this;
    }

    public function getConsultationTypes(): Collection
    {
        return $this->consultationTypes;
    }

    public function addConsultationType(ConsultationType $consultationType): static
    {
        if (!$this->consultationTypes->contains($consultationType)) {
            $this->consultationTypes->add($consultationType);
        }
        return $this;
    }

    public function removeConsultationType(ConsultationType $consultationType): static
    {
        $this->consultationTypes->removeElement($consultationType);
        return $this;
    }

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

    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
    }

    public function addRendezVou(RendezVous $rendezVou): static
    {
        if (!$this->rendezVous->contains($rendezVou)) {
            $this->rendezVous->add($rendezVou);
            $rendezVou->setMedecin($this);
        }
        return $this;
    }

    public function removeRendezVou(RendezVous $rendezVou): static
    {
        if ($this->rendezVous->removeElement($rendezVou)) {
            if ($rendezVou->getMedecin() === $this) {
                $rendezVou->setMedecin(null);
            }
        }
        return $this;
    }
}