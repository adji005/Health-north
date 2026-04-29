<?php

namespace App\Entity;

use App\Entity\Patient; // Entité Patient (relation OneToOne)
use App\Entity\ProfessionnelSante; // Entité ProfessionnelSante (relation OneToOne)
use App\Repository\UserRepository; // Repository pour récupérer les utilisateurs en BDD
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**Entité User (Utilisateur)*/
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**Email de connexion (unique)*/
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**Rôles de l'utilisateur ['ROLE_PATIENT'], ['ROLE_DOCTOR'], ou ['ROLE_ADMIN']*/
    #[ORM\Column]
    private array $roles = [];

    /**Mot de passe hashé (bcrypt)*/
    #[ORM\Column]
    private ?string $password = null;

    /**Compte actif ou bloqué*/
    #[ORM\Column]
    private bool $isActive = true;

    /**Date de création du compte*/
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**Relation One-to-One; Un User peut être un Patient*/
    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Patient $patient = null;

    /**Relation One-to-One ,Un User peut être un Médecin*/
    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?ProfessionnelSante $medecin = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**Identifiant utilisé par Symfony pour la connexion*/
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**Retourne les rôles de l'utilisateur,ajoute automatiquement ROLE_USER*/
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // Rôle par défaut
        return array_unique($roles); // Évite les doublons
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**Efface les données sensibles temporaire convention dev*/
    public function eraseCredentials(): void {}

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**Retourne le profil Patient ou Null si User est un médecin ou admin*/
    public function getPatient(): ?Patient
    {
        return $this->patient ?? null;
    }

    /**Retourne le profil patient si l'user est médecin ou Null si User est un patient ou admin*/
    public function getMedecin(): ?ProfessionnelSante
    {
        return $this->medecin ?? null;
    }
}