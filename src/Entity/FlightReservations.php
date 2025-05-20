<?php

namespace App\Entity;

use App\Repository\FlightReservationsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlightReservationsRepository::class)]
#[ORM\Table(name: "flight_reservations")]
#[ORM\Index(columns: ["user_id"], name: "fk_user_id_flight_reservations")]
#[ORM\Index(columns: ["flight_id"], name: "flight_id")]
class FlightReservations
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "booking_date", type: "date")]
    private \DateTime $bookingDate;

    #[ORM\Column(name: "status", type: "string", length: 50)]
    private string $status;

    #[ORM\Column(name: "flight_id", type: "integer")]
    private int $flightId;

    #[ORM\Column(name: "user_id", type: "integer")]
    private int $userId;

    #[ORM\Column(name: "seat",type: "string")]
    private string $seat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookingDate(): ?\DateTimeInterface
    {
        return $this->bookingDate;
    }

    public function setBookingDate(\DateTimeInterface $bookingDate): static
    {
        $this->bookingDate = $bookingDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getFlightId(): ?int
    {
        return $this->flightId;
    }

    public function setFlightId(int $flightId): static
    {
        $this->flightId = $flightId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getSeat(): ?string
    {
        return $this->seat;
    }

    public function setSeat(string $seat): static
    {
        $this->seat = $seat;

        return $this;
    }
}