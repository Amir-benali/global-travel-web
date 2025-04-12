<?php

namespace App\Entity;

use App\Repository\PrivateCarRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PrivateCarRepository::class)]
#[ORM\Table(name: "private_car")]
#[ORM\Index(columns: ["id_driver"], name: "fk_id_driver_private_car")]
class PrivateCar
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "brand", type: "string", length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30, min: 2)]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9\s]+$/", message: "The brand can only contain letters, numbers, and spaces.")]
    private string $brand;

    #[ORM\Column(name: "model", type: "string", length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30, min: 2)]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9\s]+$/", message: "The model can only contain letters, numbers, and spaces.")]
    private string $model;

    #[ORM\Column(name: "num_place", type: "integer")]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Range(min: 1, max: 10, notInRangeMessage: "The number of places must be between {{ min }} and {{ max }}.")]
    private int $numPlace;

    #[ORM\Column(name: "image", type: "string", length: 500)]
    #[Assert\NotBlank(message: "Please upload an image.")]
    private string $image;

    #[ORM\ManyToOne(targetEntity: "CarDriver")]
    #[ORM\JoinColumn(name: "id_driver", referencedColumnName: "id")]
    #[Assert\NotNull]
    private CarDriver $idDriver;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getNumPlace(): ?int
    {
        return $this->numPlace;
    }

    public function setNumPlace(int $numPlace): static
    {
        $this->numPlace = $numPlace;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getIdDriver(): ?CarDriver
    {
        return $this->idDriver;
    }

    public function setIdDriver(?CarDriver $idDriver): static
    {
        $this->idDriver = $idDriver;

        return $this;
    }
}