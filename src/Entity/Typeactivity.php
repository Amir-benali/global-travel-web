<?php

namespace App\Entity;

use App\Repository\TypeactivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeactivityRepository::class)]
#[ORM\Table(name: "typeactivity")]
#[ORM\UniqueConstraint(name: "nomType", columns: ["nomType"])]
class Typeactivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "nomEvenement", type: "string", length: 30)]
    private string $nomevenement;

    #[ORM\Column(name: "nomType", type: "string", length: 30)]
    private string $nomtype;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomevenement(): ?string
    {
        return $this->nomevenement;
    }

    public function setNomevenement(string $nomevenement): static
    {
        $this->nomevenement = $nomevenement;

        return $this;
    }

    public function getNomtype(): ?string
    {
        return $this->nomtype;
    }

    public function setNomtype(string $nomtype): static
    {
        $this->nomtype = $nomtype;

        return $this;
    }
}