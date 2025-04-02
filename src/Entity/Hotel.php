<?php

namespace App\Entity;

use App\Repository\HotelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HotelRepository::class)]
#[ORM\Table(name: "hotel")]
class Hotel
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id_hotel_h", type: "integer")]
    private int $idHotelH;

    #[ORM\Column(name: "nom_h", type: "string", length: 255)]
    private string $nomH;

    #[ORM\Column(name: "adresse_h", type: "string", length: 255)]
    private string $adresseH;

    #[ORM\Column(name: "ville_h", type: "string", length: 255)]
    private string $villeH;

    #[ORM\Column(name: "pays_h", type: "string", length: 255)]
    private string $paysH;

    #[ORM\Column(name: "categorie_h", type: "integer")]
    private int $categorieH;

    #[ORM\Column(name: "services_h", type: "string", length: 255)]
    private string $servicesH;

    #[ORM\Column(name: "coordonnees_h", type: "string", length: 255)]
    private string $coordonneesH;

    #[ORM\Column(name: "avis_h", type: "string", length: 255)]
    private string $avisH;

    public function getIdHotelH(): ?int
    {
        return $this->idHotelH;
    }

    public function getNomH(): ?string
    {
        return $this->nomH;
    }

    public function setNomH(string $nomH): static
    {
        $this->nomH = $nomH;

        return $this;
    }

    public function getAdresseH(): ?string
    {
        return $this->adresseH;
    }

    public function setAdresseH(string $adresseH): static
    {
        $this->adresseH = $adresseH;

        return $this;
    }

    public function getVilleH(): ?string
    {
        return $this->villeH;
    }

    public function setVilleH(string $villeH): static
    {
        $this->villeH = $villeH;

        return $this;
    }

    public function getPaysH(): ?string
    {
        return $this->paysH;
    }

    public function setPaysH(string $paysH): static
    {
        $this->paysH = $paysH;

        return $this;
    }

    public function getCategorieH(): ?int
    {
        return $this->categorieH;
    }

    public function setCategorieH(int $categorieH): static
    {
        $this->categorieH = $categorieH;

        return $this;
    }

    public function getServicesH(): ?string
    {
        return $this->servicesH;
    }

    public function setServicesH(string $servicesH): static
    {
        $this->servicesH = $servicesH;

        return $this;
    }

    public function getCoordonneesH(): ?string
    {
        return $this->coordonneesH;
    }

    public function setCoordonneesH(string $coordonneesH): static
    {
        $this->coordonneesH = $coordonneesH;

        return $this;
    }

    public function getAvisH(): ?string
    {
        return $this->avisH;
    }

    public function setAvisH(string $avisH): static
    {
        $this->avisH = $avisH;

        return $this;
    }
}