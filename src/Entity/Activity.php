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
#[ORM\Index(columns: ["user_id"], name: "fk_user")]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "dateDebut", type: "datetime")]
    #[Assert\NotNull(message: "Please select a start date.")]    
    private \DateTime $datedebut;

    #[ORM\Column(name: "dateFin", type: "datetime")]
    #[Assert\NotNull(message: "Please select a finish date.")]    
    // #[Assert\DateTime]
    #[Assert\GreaterThanOrEqual(propertyPath: "datedebut", message: "The end date must be greater than or equal to the start date.")]
    private \DateTime $datefin;

    #[ORM\Column(name: "description", type: "string", length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30, maxMessage: "Description cannot exceed {{ limit }} characters.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s]+$/",
        message: "Description can only contain letters, numbers, and spaces."
    )]
    private string $description;

    #[ORM\Column(name: "localisation", type: "string", length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30, maxMessage: "Location cannot exceed {{ limit }} characters.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s]+$/",
        message: "Location can only contain letters, numbers, and spaces."
    )]
    private string $localisation;

    #[ORM\Column(name: "prixTotal", type: "decimal", precision: 30, scale: 0)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: "/^\d+(\.\d{1,2})?$/",
        message: "Price must be a valid decimal number with up to two decimal places."
    )]
    private string $prixtotal;

    #[ORM\Column(name: "nomActivity", type: "string", length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100, maxMessage: "Activity name cannot exceed {{ limit }} characters.")]
    private string $nomactivity;

    
    #[ORM\Column(name: "typeActivity", type: "string", enumType: ActivityType::class, length: 50)]
    #[Assert\NotBlank]
    private ActivityType $typeactivity;

    #[ORM\ManyToOne(targetEntity: "Hotel")]
    #[ORM\JoinColumn(name: "joinHotelId", referencedColumnName: "id_hotel_h")]
    #[Assert\NotBlank]
    private Hotel $joinhotel;

    #[ORM\ManyToOne(targetEntity: "PrivateCar")]
    #[ORM\JoinColumn(name: "joinVoitureId", referencedColumnName: "id")]
    #[Assert\NotBlank]
    private PrivateCar $joinvoiture;

    #[ORM\ManyToOne(targetEntity: "Flights")]
    #[ORM\JoinColumn(name: "joinVolsId", referencedColumnName: "id_flight", nullable: true)]
    private ?Flights $joinvols=null;

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

    public function setDatedebut(\DateTimeInterface $datedebut): static
    {
        $this->datedebut = $datedebut;

        return $this;
    }

/**
 * Get the end date of the activity.
 *
 * @return \DateTimeInterface|null Returns the end date if set, null otherwise.
 */

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): static
    {
        $this->datefin = $datefin;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getPrixtotal(): ?string
    {
        return $this->prixtotal;
    }

    public function setPrixtotal(string $prixtotal): static
    {
        $this->prixtotal = $prixtotal;

        return $this;
    }

    public function getNomactivity(): ?string
    {
        return $this->nomactivity;
    }

    public function setNomactivity(string $nomactivity): static
    {
        $this->nomactivity = $nomactivity;

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
    public function getTypeactivity(): ?ActivityType
    {
     return $this->typeactivity;
     }
public function setTypeactivity(ActivityType $typeactivity): static
     {
     $this->typeactivity = $typeactivity;

         return $this;
    }
    

   
}