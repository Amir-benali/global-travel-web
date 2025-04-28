<?php

namespace App\Entity;

use App\Repository\FlightsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Enum\Flight\FlightStatus;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FlightsRepository::class)]
#[ORM\Table(name: "flights")]
#[ORM\Index(columns: ["airline_id"], name: "flights_ibfk_1")]
#[UniqueEntity(
    fields:["flightNumber"],
    message: "This flight number already exists.",
)]

class Flights
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id_flight", type: "integer")]
    private int $idFlight;

    #[ORM\Column(name: "flight_number", type: "string", length: 15)]

    #[Assert\Length(
        max: 8,
        maxMessage: "Flight number should not be longer than 8 characters."
    )]
    private string $flightNumber;

    #[ORM\Column(name: "departure_airport_name", type: "string", length: 100)]
    private string $departureAirportName;

    #[ORM\Column(name: "arrival_airport_name", type: "string", length: 100)]
    private string $arrivalAirportName;

    #[ORM\Column(name: "departure_time", type: "datetime", nullable: true)]
    private ?\DateTime $departureTime = null;

    #[ORM\Column(name: "arrival_time", type: "datetime", nullable: true)]
    #[Assert\Expression(
        "this.getDepartureTime() !== null && this.getArrivalTime() !== null && this.getDepartureTime().getTimestamp() < this.getArrivalTime().getTimestamp()",
        message: "Arrival date should be after the Departure date."
    )]
    private ?\DateTime $arrivalTime = null;

    #[ORM\Column(name: "duration_per_hours", type: "integer")]
    #[Assert\Positive(message: "Duration should be positif.")]
    private int $durationPerHours;

    #[ORM\Column(name: "seats_number", type: "integer")]
    #[Assert\GreaterThanOrEqual(
        value: 0,
        message: "Seat number can not be equal to 0."
    )]
    #[Assert\LessThanOrEqual(
        value: 50,
        message: "Seat number can not exceed 50."
    )]
    private int $seats_number;

    #[ORM\Column(name: "available_seats", type: "json")]
    private array $availableSeats = [];

    #[ORM\Column(name: "flight_base_price", type: "float", precision: 10, scale: 0)]
    #[Assert\GreaterThan(
        value: 0,
        message: "Base price can not be equal to 0."
    )]
    #[Assert\Type(
        type: "float",
        message: "Base price should be a number."

    )]
    private float $flightBasePrice;

    #[ORM\Column(name: "flight_status", type: "string", enumType: FlightStatus::class)]
    private FlightStatus $flightStatus;

    #[ORM\Column(name: "departure_country", type: "string", length: 150)]
    private string $departureCountry;

    #[ORM\Column(name: "arrival_country", type: "string", length: 150)]
    private string $arrivalCountry;

    #[ORM\ManyToOne(targetEntity: "Airlines")]
    #[ORM\JoinColumn(name: "airline_id", referencedColumnName: "airline_id")]
    private Airlines $airlineId;

    public function getIdFlight(): ?int
    {
        return $this->idFlight;
    }

    public function getFlightNumber(): ?string
    {
        return $this->flightNumber;
    }

    public function setFlightNumber(string $flightNumber): self
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }

    public function getDepartureAirportName(): ?string
    {
        return $this->departureAirportName;
    }

    public function setDepartureAirportName(string $departureAirportName): self
    {
        $this->departureAirportName = $departureAirportName;

        return $this;
    }

    public function getArrivalAirportName(): ?string
    {
        return $this->arrivalAirportName;
    }

    public function setArrivalAirportName(string $arrivalAirportName): self
    {
        $this->arrivalAirportName = $arrivalAirportName;

        return $this;
    }

    public function getDepartureTime(): ?\DateTimeInterface
    {
        return $this->departureTime;
    }

    public function setDepartureTime(?\DateTimeInterface $departureTime): self
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    public function getArrivalTime(): ?\DateTimeInterface
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(?\DateTimeInterface $arrivalTime): self
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    public function getDurationPerHours(): ?int
    {
        return $this->durationPerHours;
    }

    public function setDurationPerHours(int $durationPerHours): self
    {
        $this->durationPerHours = $durationPerHours;

        return $this;
    }

    public function getAvailableSeats(): array
    {
        return $this->availableSeats;
    }

    public function setAvailableSeats(array $availableSeats): self
    {
        $this->availableSeats = $availableSeats;
        return $this;
    }

    public  function setSeatsnumber(int $seatsNumber): self
    {
        $this->seats_number = $seatsNumber;

        return $this;
    }

    public function getSeatsnumber(): ?int
    {
        return $this->seats_number;
    }

    public function getFlightBasePrice(): ?float
    {
        return $this->flightBasePrice;
    }

    public function setFlightBasePrice(float $flightBasePrice): self
    {
        $this->flightBasePrice = $flightBasePrice;

        return $this;
    }

    public function getFlightStatus(): ?FlightStatus
    {
        return $this->flightStatus;
    }

    public function setFlightStatus(FlightStatus $flightStatus): self
    {
        $this->flightStatus = $flightStatus;

        return $this;
    }

    public function getDepartureCountry(): ?string
    {
        return $this->departureCountry;
    }

    public function setDepartureCountry(string $departureCountry): self
    {
        $this->departureCountry = $departureCountry;

        return $this;
    }

    public function getArrivalCountry(): ?string
    {
        return $this->arrivalCountry;
    }

    public function setArrivalCountry(string $arrivalCountry): self
    {
        $this->arrivalCountry = $arrivalCountry;

        return $this;
    }

    public function getAirlineId(): ?Airlines
    {
        return $this->airlineId;
    }
    public function setAirlineId(Airlines $airlineId): self
    {
        $this->airlineId = $airlineId;

        return $this;
    }
    public function getAirlineName(): ?string
    {
        return $this->airlineId ? $this->airlineId->getAirlineName() : null;
    }
}