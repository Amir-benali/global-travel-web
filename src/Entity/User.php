<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user")]
#[ORM\UniqueConstraint(name: "email", columns: ["email"])]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "genre", type: "string", length: 0, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(name: "date_naissance", type: "date", nullable: true)]
    private ?\DateTime $dateNaissance = null;

    #[ORM\Column(name: "adresse", type: "string", length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(name: "email", type: "string", length: 255)]
    private string $email;

    #[ORM\Column(name: "roles", type: "string", length: 50, nullable: true)]
    private ?string $roles = null;

    #[ORM\Column(name: "password", type: "string", length: 255)]
    private string $password;

    #[ORM\Column(name: "firstname", type: "string", length: 100)]
    private string $firstname;

    #[ORM\Column(name: "lastname", type: "string", length: 100)]
    private string $lastname;

    #[ORM\Column(name: "phone_number", type: "string", length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(name: "image", type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(name: "statut", type: "string", length: 0)]
    private string $statut;

    #[ORM\Column(name: "privileges", type: "string", length: 255, nullable: true)]
    private ?string $privileges = null;

    #[ORM\Column(name: "poste", type: "string", length: 255, nullable: true)]
    private ?string $poste = null;

    #[ORM\Column(name: "departement", type: "string", length: 255, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(name: "reset_token", type: "string", length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(name: "reset_token_expiry", type: "datetime", nullable: true)]
    private ?\DateTime $resetTokenExpiry = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
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

    public function getRoles(): ?string
    {
        return $this->roles;
    }

    public function setRoles(?string $roles): static
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPrivileges(): ?string
    {
        return $this->privileges;
    }

    public function setPrivileges(?string $privileges): static
    {
        $this->privileges = $privileges;

        return $this;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(?string $poste): static
    {
        $this->poste = $poste;

        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(?string $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getResetTokenExpiry(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiry;
    }

    public function setResetTokenExpiry(?\DateTimeInterface $resetTokenExpiry): static
    {
        $this->resetTokenExpiry = $resetTokenExpiry;

        return $this;
    }
}