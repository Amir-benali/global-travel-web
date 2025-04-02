<?php

namespace App\Entity;

use App\Repository\FlightsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlightsRepository::class)]
#[ORM\Table(name: "flights")]
#[ORM\Index(columns: ["airline_name"], name: "flights_ibfk_1")]
class Flights
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id_flight", type: "integer")]
    private int $idFlight;

    #[ORM\Column(name: "flight_number", type: "string", length: 15)]
    private string $flightNumber;

    #[ORM\Column(name: "departure_airport_name", type: "string", length: 100)]
    private string $departureAirportName;

    #[ORM\Column(name: "arrival_airport_name", type: "string", length: 100)]
    private string $arrivalAirportName;

    #[ORM\Column(name: "departure_time", type: "datetime", nullable: true)]
    private ?\DateTime $departureTime = null;

    #[ORM\Column(name: "arrival_time", type: "datetime", nullable: true)]
    private ?\DateTime $arrivalTime = null;

    #[ORM\Column(name: "duration_per_hours", type: "integer")]
    private int $durationPerHours;

    #[ORM\Column(name: "available_seats", type: "integer")]
    private int $availableSeats;

    #[ORM\Column(name: "flight_base_price", type: "float", precision: 10, scale: 0)]
    private float $flightBasePrice;

    #[ORM\Column(name: "flight_status", type: "string", length: 0)]
    private string $flightStatus;

    #[ORM\Column(name: "departure_country", type: "string", length: 150)]
    private string $departureCountry;

    #[ORM\Column(name: "arrival_country", type: "string", length: 150)]
    private string $arrivalCountry;

    #[ORM\ManyToOne(targetEntity: "Airlines")]
    #[ORM\JoinColumn(name: "airline_name", referencedColumnName: "airline_name")]
    private Airlines $airlineName;

    public function getIdFlight(): ?int
    {
        return $this->idFlight;
    }

    public function getFlightNumber(): ?string
    {
        return $this->flightNumber;
    }

    public function setFlightNumber(string $flightNumber): static
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }

    public function getDepartureAirportName(): ?string
    {
        return $this->departureAirportName;
    }

    public function setDepartureAirportName(string $departureAirportName): static
    {
        $this->departureAirportName = $departureAirportName;

        return $this;
    }

    public function getArrivalAirportName(): ?string
    {
        return $this->arrivalAirportName;
    }

    public function setArrivalAirportName(string $arrivalAirportName): static
    {
        $this->arrivalAirportName = $arrivalAirportName;

        return $this;
    }

    public function getDepartureTime(): ?\DateTimeInterface
    {
        return $this->departureTime;
    }

    public function setDepartureTime(?\DateTimeInterface $departureTime): static
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    public function getArrivalTime(): ?\DateTimeInterface
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(?\DateTimeInterface $arrivalTime): static
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    public function getDurationPerHours(): ?int
    {
        return $this->durationPerHours;
    }

    public function setDurationPerHours(int $durationPerHours): static
    {
        $this->durationPerHours = $durationPerHours;

        return $this;
    }

    public function getAvailableSeats(): ?int
    {
        return $this->availableSeats;
    }

    public function setAvailableSeats(int $availableSeats): static
    {
        $this->availableSeats = $availableSeats;

        return $this;
    }

    public function getFlightBasePrice(): ?float
    {
        return $this->flightBasePrice;
    }

    public function setFlightBasePrice(float $flightBasePrice): static
    {
        $this->flightBasePrice = $flightBasePrice;

        return $this;
    }

    public function getFlightStatus(): ?string
    {
        return $this->flightStatus;
    }

    public function setFlightStatus(string $flightStatus): static
    {
        $this->flightStatus = $flightStatus;

        return $this;
    }

    public function getDepartureCountry(): ?string
    {
        return $this->departureCountry;
    }

    public function setDepartureCountry(string $departureCountry): static
    {
        $this->departureCountry = $departureCountry;

        return $this;
    }

    public function getArrivalCountry(): ?string
    {
        return $this->arrivalCountry;
    }

    public function setArrivalCountry(string $arrivalCountry): static
    {
        $this->arrivalCountry = $arrivalCountry;

        return $this;
    }

    public function getAirlineName(): ?Airlines
    {
        return $this->airlineName;
    }

    public function setAirlineName(?Airlines $airlineName): static
    {
        $this->airlineName = $airlineName;

        return $this;
    }
}