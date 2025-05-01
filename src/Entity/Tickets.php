<?php

namespace App\Entity;

use App\Repository\TicketsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

    #[ORM\Entity(repositoryClass: TicketsRepository::class)]
    #[ORM\Table(name: "tickets")]
    #[ORM\Index(columns: ["id_flight"], name: "flight_id")]
    #[ORM\Index(columns: ["passenger_id"], name: "passenger_id")]
    #[ORM\Index(columns: ["selected_user"], name: "selected_user")]
    class Tickets
    {
        public function __construct()
        {
            $this->ticketBookingDate = new \DateTime();
        }

        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: "IDENTITY")]
        #[ORM\Column(name: "ticket_id", type: "integer")]
        private int $ticketId;

        #[ORM\Column(name: "seat_number", type: "string", length: 15)]
        private string $seatNumber;

        #[ORM\Column(name: "ticket_class", type: "string", length: 0)]
        private string $ticketClass;

        #[ORM\Column(name: "ticket_price", type: "float", precision: 10, scale: 0, nullable: true)]
        private ?float $ticketPrice = null;

        #[ORM\Column(name: "ticket_status", type: "string", length: 0)]
        private string $ticketStatus;

        #[ORM\Column(name: "ticket_booking_date", type: "datetime", nullable: true, options: ["default" => "CURRENT_TIMESTAMP"])]
        private ?\DateTime $ticketBookingDate;

        #[ORM\Column(name: "passenger_email", type: "string", length: 100)]
        private string $passengerEmail;

        #[ORM\ManyToOne(targetEntity: "Flights")]
        #[ORM\JoinColumn(name: "id_flight", referencedColumnName: "id_flight")]
        private Flights $idFlight;

        #[ORM\ManyToOne(targetEntity: "User")]
        #[ORM\JoinColumn(name: "passenger_id", referencedColumnName: "id")]
        private User $passenger;

        #[ORM\ManyToOne(targetEntity: "User")]
        #[ORM\JoinColumn(name: "selected_user", referencedColumnName: "id")]
        private User $selectedUser;

    public function getTicketId(): ?int
    {
        return $this->ticketId;
    }

    public function getSeatNumber(): ?string
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(string $seatNumber): static
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    public function getTicketClass(): ?string
    {
        return $this->ticketClass;
    }

    public function setTicketClass(string $ticketClass): static
    {
        $this->ticketClass = $ticketClass;

        return $this;
    }

    public function getTicketPrice(): ?float
    {
        return $this->ticketPrice;
    }

    public function setTicketPrice(?float $ticketPrice): static
    {
        $this->ticketPrice = $ticketPrice;

        return $this;
    }

    public function getTicketStatus(): ?string
    {
        return $this->ticketStatus;
    }

    public function setTicketStatus(string $ticketStatus): static
    {
        $this->ticketStatus = $ticketStatus;

        return $this;
    }

    public function getTicketBookingDate(): ?\DateTimeInterface
    {
        return $this->ticketBookingDate;
    }

    public function setTicketBookingDate(?\DateTimeInterface $ticketBookingDate): static
    {
        $this->ticketBookingDate = $ticketBookingDate;

        return $this;
    }

    public function getPassengerEmail(): ?string
    {
        return $this->passengerEmail;
    }

    public function setPassengerEmail(string $passengerEmail): static
    {
        $this->passengerEmail = $passengerEmail;

        return $this;
    }

    public function getIdFlight(): ?Flights
    {
        return $this->idFlight;
    }

    public function setIdFlight(?Flights $idFlight): static
    {
        $this->idFlight = $idFlight;

        return $this;
    }

    public function getPassenger(): ?User
    {
        return $this->passenger;
    }

    public function setPassenger(?User $passenger): static
    {
        $this->passenger = $passenger;

        return $this;
    }

    public function getSelectedUser(): ?User
    {
        return $this->selectedUser;
    }

    public function setSelectedUser(?User $selectedUser): static
    {
        $this->selectedUser = $selectedUser;

        return $this;
    }
}