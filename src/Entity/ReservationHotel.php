<?php

namespace App\Entity;

use App\Repository\ReservationHotelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationHotelRepository::class)]
#[ORM\Table(name: "reservation_hotel")]
#[ORM\Index(columns: ["id_chambre_j"], name: "id_chambre_j")]
#[ORM\UniqueConstraint(name: "unique_user_reservation", columns: ["id_user", "id_chambre_j", "date_checkin_h"])]
class ReservationHotel
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id_reservation_h", type: "integer")]
    private ?int $idReservationH = null;

    #[ORM\Column(name: "date_checkin_h", type: "date")]
    private ?\DateTimeInterface $dateCheckinH = null;

    #[ORM\Column(name: "date_checkout_h", type: "date")]
    private ?\DateTimeInterface $dateCheckoutH = null;

    #[ORM\Column(name: "nombre_chambres_h", type: "integer")]
    private ?int $nombreChambresH = null;

    #[ORM\Column(name: "statut_h", type: "string", length: 255)]
    private ?string $statutH = null;

    #[ORM\Column(name: "moyen_Paiement_h", type: "string", length: 255)]
    private ?string $moyenPaiementH = null;

    #[ORM\ManyToOne(targetEntity: Chambre::class)]
    #[ORM\JoinColumn(name: "id_chambre_j", referencedColumnName: "id_chambre_h", nullable: false)]
    private ?Chambre $idChambreJ = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;

    public function getIdReservationH(): ?int
    {
        return $this->idReservationH;
    }

    public function getDateCheckinH(): ?\DateTimeInterface
    {
        return $this->dateCheckinH;
    }

    public function setDateCheckinH(\DateTimeInterface $dateCheckinH): static
    {
        $this->dateCheckinH = $dateCheckinH;
        return $this;
    }

    public function getDateCheckoutH(): ?\DateTimeInterface
    {
        return $this->dateCheckoutH;
    }

    public function setDateCheckoutH(\DateTimeInterface $dateCheckoutH): static
    {
        $this->dateCheckoutH = $dateCheckoutH;
        return $this;
    }

    public function getNombreChambresH(): ?int
    {
        return $this->nombreChambresH;
    }

    public function setNombreChambresH(int $nombreChambresH): static
    {
        $this->nombreChambresH = $nombreChambresH;
        return $this;
    }

    public function getStatutH(): ?string
    {
        return $this->statutH;
    }

    public function setStatutH(string $statutH): static
    {
        $this->statutH = $statutH;
        return $this;
    }

    public function getMoyenPaiementH(): ?string
    {
        return $this->moyenPaiementH;
    }

    public function setMoyenPaiementH(string $moyenPaiementH): static
    {
        $this->moyenPaiementH = $moyenPaiementH;
        return $this;
    }

    public function getIdChambreJ(): ?Chambre
    {
        return $this->idChambreJ;
    }

    public function setIdChambreJ(?Chambre $idChambreJ): static
    {
        $this->idChambreJ = $idChambreJ;
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