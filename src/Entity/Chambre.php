<?php

namespace App\Entity;

use App\Repository\ChambreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChambreRepository::class)]
#[ORM\Table(name: "chambre")]
#[ORM\Index(columns: ["id_hotel_j"], name: "forkei1")]
class Chambre
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id_chambre_h", type: "integer")]
    private int $idChambreH;

    #[ORM\Column(name: "type_chambre_h", type: "string", length: 255)]
    #[Assert\NotBlank(message: "The type of chambre cannot be blank.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s]+$/",
        message: "The type of chambre can only contain letters and spaces."
    )]
    private string $typeChambreH;

    #[ORM\Column(name: "prix_nuit_h", type: "integer")]
    #[Assert\NotBlank(message: "The price per night cannot be blank.")]
    #[Assert\Range(
        min: 20,
        max: 300,
        notInRangeMessage: "The price per night must be between {{ min }} and {{ max }}."
    )]
    private int $prixNuitH;

    #[ORM\Column(name: "dispo_h", type: "date")]
    #[Assert\NotBlank(message: "The availability date cannot be blank.")]
    #[Assert\GreaterThan("today", message: "The availability date must be in the future.")]
    private \DateTimeInterface $dispoH;

    #[ORM\Column(name: "option_h", type: "string", length: 255)]
    #[Assert\NotBlank(message: "The options field cannot be blank.")]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: "The options must be at least {{ limit }} characters long.",
        maxMessage: "The options cannot be longer than {{ limit }} characters."
    )]
    private string $optionH;

    #[ORM\ManyToOne(targetEntity: Hotel::class)]
    #[ORM\JoinColumn(name: "id_hotel_j", referencedColumnName: "id_hotel_h", nullable: false)]
    #[Assert\NotNull(message: "You must select a hotel.")]
    private Hotel $hotel;

    // Getters and Setters

    public function getIdChambreH(): ?int
    {
        return $this->idChambreH;
    }

    public function getTypeChambreH(): ?string
    {
        return $this->typeChambreH;
    }

    public function setTypeChambreH(string $typeChambreH): static
    {
        $this->typeChambreH = $typeChambreH;

        return $this;
    }

    public function getPrixNuitH(): ?int
    {
        return $this->prixNuitH;
    }

    public function setPrixNuitH(int $prixNuitH): static
    {
        $this->prixNuitH = $prixNuitH;

        return $this;
    }

    public function getDispoH(): ?\DateTimeInterface
    {
        return $this->dispoH;
    }

    public function setDispoH(\DateTimeInterface $dispoH): static
    {
        $this->dispoH = $dispoH;

        return $this;
    }

    public function getOptionH(): ?string
    {
        return $this->optionH;
    }

    public function setOptionH(string $optionH): static
    {
        $this->optionH = $optionH;

        return $this;
    }

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(Hotel $hotel): static
    {
        $this->hotel = $hotel;

        return $this;
    }
}