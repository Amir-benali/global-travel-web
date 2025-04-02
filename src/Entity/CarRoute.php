<?php

namespace App\Entity;

use App\Repository\CarRouteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarRouteRepository::class)]
#[ORM\Table(name: "car_route")]
class CarRoute
{
    public function __construct()
    {
        $this->dateStart = new \DateTime();
    }
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "date_start", type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTime $dateStart;

    #[ORM\Column(name: "location_start", type: "string", length: 50)]
    private string $locationStart;

    #[ORM\Column(name: "location_destination", type: "string", length: 50)]
    private string $locationDestination;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getLocationStart(): ?string
    {
        return $this->locationStart;
    }

    public function setLocationStart(string $locationStart): static
    {
        $this->locationStart = $locationStart;

        return $this;
    }

    public function getLocationDestination(): ?string
    {
        return $this->locationDestination;
    }

    public function setLocationDestination(string $locationDestination): static
    {
        $this->locationDestination = $locationDestination;

        return $this;
    }
}