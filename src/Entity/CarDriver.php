<?php

namespace App\Entity;

use App\Repository\CarDriverRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CarDriverRepository::class)]
#[ORM\Table(name: "car_driver")]
class CarDriver
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "first_name", type: "string", length: 30)]
    #[Assert\NotBlank(message: 'First name is required')]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'First name must be at least {{ limit }} characters long',
        maxMessage: 'First name cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z]+$/",
        message: 'First name can only contain letters'
    )]
    private string $firstName;

    #[ORM\Column(name: "last_name", type: "string", length: 30)]
    #[Assert\NotBlank(message: 'Last name is required')]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'Last name must be at least {{ limit }} characters long',
        maxMessage: 'Last name cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z]+$/",
        message: 'Last name can only contain letters'
    )]
    private string $lastName;

    #[ORM\Column(name: "phone", type: "string", length: 15)]
    #[Assert\NotBlank(message: 'Phone number is required')]
    #[Assert\Length(
        min: 10,
        max: 15,
        minMessage: 'Phone number must be at least {{ limit }} characters long',
        maxMessage: 'Phone number cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: "/^\+?[0-9]+$/",
        message: 'Phone number can only contain numbers and an optional leading +'
    )]

    private string $phone;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}