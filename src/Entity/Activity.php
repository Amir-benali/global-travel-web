<?php

namespace App\Entity;

use App\Entity\Enum\Activity\ActivityType;
use App\Repository\ActivityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\Table(name: "activity")]
#[ORM\Index(columns: ["joinHotelId"], name: "joinHotelId")]
#[ORM\Index(columns: ["joinVoitureId"], name: "joinVoitureId")]
#[ORM\Index(columns: ["joinVolsId"], name: "joinVolsId")]
#[ORM\Index(columns: ["user_id"], name: "fk_user_activity")]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "dateDebut", type: "datetime")]
    #[Assert\NotNull(message: "La date de début est obligatoire.")]
    #[Assert\Type("\DateTimeInterface", message: "La date de début doit être une date valide.")]
    private ?\DateTimeInterface $datedebut = null;

    #[ORM\Column(name: "dateFin", type: "datetime")]
    #[Assert\NotNull(message: "La date de fin est obligatoire.")]
    #[Assert\Type("\DateTimeInterface", message: "La date de fin doit être une date valide.")]
    #[Assert\GreaterThanOrEqual(
        propertyPath: "datedebut",
        message: "La date de fin doit être postérieure ou égale à la date de début."
    )]
    private ?\DateTimeInterface $datefin = null;

    #[ORM\Column(name: "description", type: "string", length: 30)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ0-9\s\-_,\.;:()]+$/u",
        message: "La description ne peut contenir que des lettres, chiffres, espaces et ponctuation de base."
    )]
    private string $description = '';

    #[ORM\Column(name: "localisation", type: "string", length: 50)]
    #[Assert\NotBlank(message: "La localisation ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "La localisation doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La localisation ne peut pas dépasser {{ limit }} caractères."
    )]
   
    private string $localisation = ''; // Initialisation avec une chaîne vide

    #[ORM\Column(name: "prixTotal", type: "decimal", precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix ne peut pas être vide.")]
    #[Assert\PositiveOrZero(message: "Le prix doit être un nombre positif ou zéro.")]
    #[Assert\Regex(
        pattern: "/^\d+(\.\d{1,2})?$/",
        message: "Le prix doit être un nombre décimal valide avec maximum 2 décimales."
    )]
    private string $prixtotal = '0';

    #[ORM\Column(name: "nomactivity", type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le nom de l'activité ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le nom de l'activité doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom de l'activité ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ0-9\s\-_,\.;:()]+$/u",
        message: "Le nom de l'activité ne peut contenir que des lettres, chiffres, espaces et ponctuation de base."
    )]
    private string $nomactivity = '';

    #[ORM\Column(name: "typeActivity", type: "string", enumType: ActivityType::class, length: 50)]
    #[Assert\NotBlank(message: "Le type d'activité ne peut pas être vide.")]
    private ActivityType $typeactivity;

    #[ORM\ManyToOne(targetEntity: "Hotel")]
    #[ORM\JoinColumn(name: "joinHotelId", referencedColumnName: "id_hotel_h", nullable: false)]
    #[Assert\NotNull(message: "Veuillez sélectionner un hôtel.")]
    private ?Hotel $joinhotel = null;

    #[ORM\ManyToOne(targetEntity: "PrivateCar")]
    #[ORM\JoinColumn(name: "joinVoitureId", referencedColumnName: "id", nullable: false)]
    #[Assert\NotNull(message: "Veuillez sélectionner une voiture.")]
    private ?PrivateCar $joinvoiture = null;

    #[ORM\ManyToOne(targetEntity: "Flights")]
    #[ORM\JoinColumn(name: "joinVolsId", referencedColumnName: "id_flight", nullable: true)]
    private ?Flights $joinvols = null;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatedebut(): ?\DateTimeInterface
    {
        return $this->datedebut;
    }

    public function setDatedebut(?\DateTimeInterface $datedebut): static
    {
        $this->datedebut = $datedebut;
        return $this;
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(?\DateTimeInterface $datefin): static
    {
        $this->datefin = $datefin;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = trim($description); // Nettoyage des espaces
        return $this;
    }

    public function getLocalisation(): string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation ?? ''; // Transforme null en chaîne vide
        return $this;
    }

    public function getPrixtotal(): string
    {
        return $this->prixtotal;
    }

    public function setPrixtotal(string $prixtotal): static
    {
        $this->prixtotal = $prixtotal;
        return $this;
    }

    public function getNomactivity(): string
    {
        return $this->nomactivity;
    }

    public function setNomactivity(string $nomactivity): static
    {
        $this->nomactivity = $nomactivity;
        return $this;
    }

    public function getTypeactivity(): ActivityType
    {
        return $this->typeactivity;
    }

    public function setTypeactivity(ActivityType $typeactivity): static
    {
        $this->typeactivity = $typeactivity;
        return $this;
    }

    public function getJoinhotel(): ?Hotel
    {
        return $this->joinhotel;
    }

    public function setJoinhotel(?Hotel $joinhotel): static
    {
        $this->joinhotel = $joinhotel;
        return $this;
    }

    public function getJoinvoiture(): ?PrivateCar
    {
        return $this->joinvoiture;
    }

    public function setJoinvoiture(?PrivateCar $joinvoiture): static
    {
        $this->joinvoiture = $joinvoiture;
        return $this;
    }

    public function getJoinvols(): ?Flights
    {
        return $this->joinvols;
    }

    public function setJoinvols(?Flights $joinvols): static
    {
        $this->joinvols = $joinvols;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}