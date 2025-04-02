<?php

namespace App\Entity;

use App\Repository\ChambreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
    private string $typeChambreH;

    #[ORM\Column(name: "prix_nuit_h", type: "integer")]
    private int $prixNuitH;

    #[ORM\Column(name: "dispo_h", type: "date")]
    private \DateTime $dispoH;

    #[ORM\Column(name: "option_h", type: "string", length: 255)]
    private string $optionH;

    #[ORM\Column(name: "id_hotel_j", type: "integer")]
    private int $idHotelJ;

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

    public function getIdHotelJ(): ?int
    {
        return $this->idHotelJ;
    }

    public function setIdHotelJ(int $idHotelJ): static
    {
        $this->idHotelJ = $idHotelJ;

        return $this;
    }
}