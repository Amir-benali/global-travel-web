<?php

namespace App\Entity;

use App\Repository\CarOfferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CarOfferRepository::class)]
#[ORM\Table(name: "car_offer")]
#[ORM\Index(columns: ["route_id"], name: "fk_id_route_offer")]
#[ORM\Index(columns: ["car_id"], name: "fk_id_car_offer")]
class CarOffer
{
    public function __construct()
    {
        $this->date = new \DateTime();
    }
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "description", type: "string", length: 75)]
    #[Assert\NotBlank(message: 'Description is required')]
    #[Assert\Length(
        min: 5,
        max: 75,
        minMessage: 'Description must be at least {{ limit }} characters long',
        maxMessage: 'Description cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s]+$/",
        message: 'Description can only contain letters, numbers, and spaces'
    )]
    private string $description;

    #[ORM\Column(name: "date", type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Assert\NotBlank(message: 'Date is required')]
    private \DateTime $date;

    #[ORM\Column(name: "price", type: "float", precision: 10, scale: 0, options: ["default" => 0])]
    #[Assert\NotBlank(message: 'Price is required')]
    #[Assert\Positive(message: 'Price must be a positive number')]
    #[Assert\Range(
        min: 0,
        max: 10000,
        notInRangeMessage: 'Price must be between {{ min }} and {{ max }}',
    )]
    #[Assert\Type(
        type: "float",
        message: 'Price must be a number'
    )]
    #[Assert\Regex(
        pattern: "/^\d+(\.\d{1,2})?$/",
        message: 'Price must be a valid number with up to 2 decimal places'
    )]
    private float $price = 0;

    #[ORM\ManyToOne(targetEntity: "PrivateCar")]
    #[ORM\JoinColumn(name: "car_id", referencedColumnName: "id")]
    #[Assert\NotBlank(message: 'Car is required')]
    private PrivateCar $car;

    #[ORM\ManyToOne(targetEntity: "CarRoute")]
    #[ORM\JoinColumn(name: "route_id", referencedColumnName: "id")]
    private CarRoute $route;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCar(): ?PrivateCar
    {
        return $this->car;
    }

    public function setCar(?PrivateCar $car): static
    {
        $this->car = $car;

        return $this;
    }

    public function getRoute(): ?CarRoute
    {
        return $this->route;
    }

    public function setRoute(?CarRoute $route): static
    {
        $this->route = $route;

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
}