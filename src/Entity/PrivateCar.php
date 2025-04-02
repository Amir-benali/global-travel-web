<?php

namespace App\Entity;

use App\Repository\PrivateCarRepository;
use Doctrine\ORM\Mapping as ORM;

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
    private string $brand;

    #[ORM\Column(name: "model", type: "string", length: 30)]
    private string $model;

    #[ORM\Column(name: "num_place", type: "integer")]
    private int $numPlace;

    #[ORM\Column(name: "image", type: "string", length: 500)]
    private string $image;

    #[ORM\ManyToOne(targetEntity: "CarDriver")]
    #[ORM\JoinColumn(name: "id_driver", referencedColumnName: "id")]
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