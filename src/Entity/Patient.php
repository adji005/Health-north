<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateDeNaissance = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(length: 50)]
    private ?string $adresse = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroSecu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $carteVitalePath = null;

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'patient', orphanRemoval: true)]
    private Collection $rendezVous;

    #[ORM\OneToOne(mappedBy: 'patient', cascade: ['persist', 'remove'])]
    private ?DossierPatient $dossier = null;

    /**
     * @var Collection<int, Mutuelle>
     */
    #[ORM\OneToMany(targetEntity: Mutuelle::class, mappedBy: 'patient', orphanRemoval: true)]
    private Collection $mutuelles;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
        $this->mutuelles = new ArrayCollection();
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

    public function getNumeroSecu(): ?string
    {
        return $this->numeroSecu;
    }

    public function setNumeroSecu(?string $numeroSecu): static
    {
        $this->numeroSecu = $numeroSecu;

        return $this;
    }

    public function getCarteVitalePath(): ?string
    {
        return $this->carteVitalePath;
    }

    public function setCarteVitalePath(?string $carteVitalePath): static
    {
        $this->carteVitalePath = $carteVitalePath;

        return $this;
    }

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
    }

    public function addRendezVou(RendezVous $rendezVou): static
    {
        if (!$this->rendezVous->contains($rendezVou)) {
            $this->rendezVous->add($rendezVou);
            $rendezVou->setPatient($this);
        }

        return $this;
    }

    public function removeRendezVou(RendezVous $rendezVou): static
    {
        if ($this->rendezVous->removeElement($rendezVou)) {
            // set the owning side to null (unless already changed)
            if ($rendezVou->getPatient() === $this) {
                $rendezVou->setPatient(null);
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
        // set the owning side of the relation if necessary
        if ($dossier->getPatient() !== $this) {
            $dossier->setPatient($this);
        }

        $this->dossier = $dossier;

        return $this;
    }

    /**
     * @return Collection<int, Mutuelle>
     */
    public function getMutuelles(): Collection
    {
        return $this->mutuelles;
    }

    public function addMutuelle(Mutuelle $mutuelle): static
    {
        if (!$this->mutuelles->contains($mutuelle)) {
            $this->mutuelles->add($mutuelle);
            $mutuelle->setPatient($this);
        }

        return $this;
    }

    public function removeMutuelle(Mutuelle $mutuelle): static
    {
        if ($this->mutuelles->removeElement($mutuelle)) {
            // set the owning side to null (unless already changed)
            if ($mutuelle->getPatient() === $this) {
                $mutuelle->setPatient(null);
            }
        }

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
}