<?php

namespace App\Entity;

use App\Repository\AirlinesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AirlinesRepository::class)]
#[ORM\Table(name: "airlines")]
#[ORM\UniqueConstraint(name: "airline_name", columns: ["airline_name"])]
class Airlines
{
    #[ORM\Id]
    #[ORM\Column(name: "airline_id", type: "integer")]
    private int $airlineId;

    #[ORM\Id]
    #[ORM\Column(name: "airline_name", type: "string", length: 100)]
    private string $airlineName;

    #[ORM\Column(name: "airline_iata_code", type: "string", length: 15)]
    private string $airlineIataCode;

    #[ORM\Column(name: "airline_country", type: "string", length: 50)]
    private string $airlineCountry;

    public function getAirlineId(): ?int
    {
        return $this->airlineId;
    }

    public function getAirlineName(): ?string
    {
        return $this->airlineName;
    }

    public function getAirlineIataCode(): ?string
    {
        return $this->airlineIataCode;
    }

    public function setAirlineIataCode(string $airlineIataCode): static
    {
        $this->airlineIataCode = $airlineIataCode;

        return $this;
    }

    public function getAirlineCountry(): ?string
    {
        return $this->airlineCountry;
    }

    public function setAirlineCountry(string $airlineCountry): static
    {
        $this->airlineCountry = $airlineCountry;

        return $this;
    }
}