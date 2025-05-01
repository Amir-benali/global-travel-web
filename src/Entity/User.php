<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['email'],
    message: 'This email is already in use.',
    errorPath: 'email'
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user")]
#[ORM\UniqueConstraint(name: "email", columns: ["email"])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "genre", type: "string", length: 10, nullable: true)]
    #[Assert\NotBlank(message: "Please select the gender")]
    #[Assert\Choice(choices: ["male", "female", "other"], message: "Choose a valid gender")]
    private ?string $genre = null;

    #[ORM\Column(name: "date_naissance", type: "date", nullable: true)]
    #[Assert\NotBlank(message: "Please enter your birth date")]
    #[Assert\LessThan(
        value: "today",
        message: "Birth date cannot be in the future"
    )]
    #[Assert\GreaterThan(
        value: "-120 years",
        message: "Please enter a valid birth date"
    )]
    private ?\DateTime $dateNaissance = null;

    #[ORM\Column(name: "adresse", type: "string", length: 255, nullable: true)]
    private ?string $adresse = null;


    #[ORM\Column(name: "email", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Please enter your email address")]
    #[Assert\Email(message: "Please enter a valid email address (example@domain.com)")]
    #[Assert\Length(
        max: 180,
        maxMessage: "Email should not be longer than {{ limit }} characters"
    )]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $roles = 'EMPLOYEE';

    #[ORM\Column(name: "password", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Please enter a password", groups: ["registration"])]
    #[Assert\Length(
        min: 8,
        minMessage: "Your password should be at least {{ limit }} characters",
        max: 4096
    )]
    // #[Assert\Regex(
    //     pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/",
    //     message: "Must contain: 1 uppercase, 1 lowercase, 1 number, and 1 special character (@$!%*?&#)"
    // )]
    private string $password;

    #[ORM\Column(name: "firstname", type: "string", length: 100)]
    #[Assert\NotBlank(message: "Please enter your first name")]
    #[Assert\Length(
        min: 2,
        max: 15,
        maxMessage: "First name should not be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s\-]+$/",
        message: "First name should contain only letters"
    )]
    private string $firstname;

    #[ORM\Column(name: "lastname", type: "string", length: 100)]
    #[Assert\NotBlank(message: "Please enter your last name")]
    #[Assert\Length(
        min: 2,
        max: 15,
        maxMessage: "Last name should not be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s\-]+$/",
        message: "Last name should contain only letters"
    )]
    private string $lastname;

    #[ORM\Column(name: "phone_number", type: "string", length: 20, nullable: true)]
    #[Assert\NotBlank(message: "Please enter your phone number")]
    #[Assert\Regex(
        pattern: "/^\+?[0-9]{8,15}$/",
        message: "Please enter a valid phone number (8-15 digits, optional + prefix)"
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column(name: "image", type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(name: "statut", type: "string", length: 20)]
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

    /**
     * @var Collection<int, CarReservation>
     */
    #[ORM\ManyToMany(targetEntity: CarReservation::class, mappedBy: 'user')]
    private Collection $carReservations;

    public function __construct()
    {
        $this->statut = 'actif';
        $this->roles = 'EMPLOYEE';
        $this->carReservations = new ArrayCollection();
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getPassword(): string
    {
        return $this->password;
    }

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

    public function getRoles(): array
    {
        $roles = [];
    
        if (!empty($this->roles)) {
            $roles = array_map('trim', explode(',', $this->roles));
        }
    
        // ROLE_USER is always required
        $roles[] = 'ROLE_USER';
    
        return array_unique($roles);
    }
    
    public function setRoles(string|array $roles): static
    {
        if (is_array($roles)) {
            $this->roles = implode(',', $roles);
        } else {
            $this->roles = $roles;
        }
    
        return $this;
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

    /**
     * @return Collection<int, CarReservation>
     */
    public function getCarReservations(): Collection
    {
        return $this->carReservations;
    }

    public function addCarReservation(CarReservation $carReservation): static
    {
        if (!$this->carReservations->contains($carReservation)) {
            $this->carReservations->add($carReservation);
            $carReservation->addUser($this);
        }

        return $this;
    }

    public function removeCarReservation(CarReservation $carReservation): static
    {
        if ($this->carReservations->removeElement($carReservation)) {
            $carReservation->removeUser($this);
        }

        return $this;
    }
}