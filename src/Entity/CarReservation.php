<?php

namespace App\Entity;

use App\Repository\CarReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarReservationRepository::class)]
#[ORM\Table(name: "car_reservation")]
#[ORM\Index(columns: ["route_id"], name: "fk_id_route_reservation")]
#[ORM\Index(columns: ["offer_id"], name: "fk_id_offer_reservation")]
#[ORM\Index(columns: ["user_id"], name: "fk_id_user_reservation")]
class CarReservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "date", type: "date")]
    private \DateTime $date;

    #[ORM\Column(name: "status", type: "string", length: 0)]
    private string $status;

    #[ORM\ManyToOne(targetEntity: "CarOffer")]
    #[ORM\JoinColumn(name: "offer_id", referencedColumnName: "id")]
    private CarOffer $offer;

    #[ORM\ManyToOne(targetEntity: "CarRoute")]
    #[ORM\JoinColumn(name: "route_id", referencedColumnName: "id")]
    private CarRoute $route;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    private User $user;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOffer(): ?CarOffer
    {
        return $this->offer;
    }

    public function setOffer(?CarOffer $offer): static
    {
        $this->offer = $offer;

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