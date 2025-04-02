<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\Table(name: "activity")]
#[ORM\Index(columns: ["joinHotelId"], name: "joinHotelId")]
#[ORM\Index(columns: ["joinVoitureId"], name: "joinVoitureId")]
#[ORM\Index(columns: ["joinVolsId"], name: "joinVolsId")]
#[ORM\Index(columns: ["user_id"], name: "fk_user")]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "dateDebut", type: "datetime")]
    private \DateTime $datedebut;

    #[ORM\Column(name: "dateFin", type: "datetime")]
    private \DateTime $datefin;

    #[ORM\Column(name: "description", type: "string", length: 30)]
    private string $description;

    #[ORM\Column(name: "localisation", type: "string", length: 30)]
    private string $localisation;

    #[ORM\Column(name: "prixTotal", type: "decimal", precision: 30, scale: 0)]
    private string $prixtotal;

    #[ORM\Column(name: "nomActivity", type: "string", length: 100)]
    private string $nomactivity;

    #[ORM\Column(name: "typeActivity", type: "string", length: 0)]
    private string $typeactivity;

    #[ORM\Column(name: "joinHotelId", type: "integer")]
    private int $joinhotelid;

    #[ORM\Column(name: "joinVoitureId", type: "integer")]
    private int $joinvoitureid;

    #[ORM\Column(name: "joinVolsId", type: "integer")]
    private int $joinvolsid;

    #[ORM\Column(name: "user_id", type: "integer", nullable: true)]
    private ?int $userId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatedebut(): ?\DateTimeInterface
    {
        return $this->datedebut;
    }

    public function setDatedebut(\DateTimeInterface $datedebut): static
    {
        $this->datedebut = $datedebut;

        return $this;
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): static
    {
        $this->datefin = $datefin;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getPrixtotal(): ?string
    {
        return $this->prixtotal;
    }

    public function setPrixtotal(string $prixtotal): static
    {
        $this->prixtotal = $prixtotal;

        return $this;
    }

    public function getNomactivity(): ?string
    {
        return $this->nomactivity;
    }

    public function setNomactivity(string $nomactivity): static
    {
        $this->nomactivity = $nomactivity;

        return $this;
    }

    public function getTypeactivity(): ?string
    {
        return $this->typeactivity;
    }

    public function setTypeactivity(string $typeactivity): static
    {
        $this->typeactivity = $typeactivity;

        return $this;
    }

    public function getJoinhotelid(): ?int
    {
        return $this->joinhotelid;
    }

    public function setJoinhotelid(int $joinhotelid): static
    {
        $this->joinhotelid = $joinhotelid;

        return $this;
    }

    public function getJoinvoitureid(): ?int
    {
        return $this->joinvoitureid;
    }

    public function setJoinvoitureid(int $joinvoitureid): static
    {
        $this->joinvoitureid = $joinvoitureid;

        return $this;
    }

    public function getJoinvolsid(): ?int
    {
        return $this->joinvolsid;
    }

    public function setJoinvolsid(int $joinvolsid): static
    {
        $this->joinvolsid = $joinvolsid;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}