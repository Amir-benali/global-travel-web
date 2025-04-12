<?php

    namespace App\Entity;

    use App\Repository\AirlinesRepository;
    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;

    #[ORM\Entity(repositoryClass: AirlinesRepository::class)]
    #[ORM\Table(name: "airlines")]
    #[ORM\UniqueConstraint(name: "airline_name", columns: ["airline_name"])]
    class Airlines
    {
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: "IDENTITY")]
        #[ORM\Column(name: "airline_id", type: "integer")]
        private int $airlineId;

        #[ORM\Column(name: "airline_name", type: "string", length: 100)]
        #[Assert\NotBlank(message: "Le nom de la compagnie est obligatoire.")]
        #[Assert\Length(
            max: 100,
            maxMessage: "Le nom de la compagnie ne peut pas dépasser {{ limit }} caractères."
        )]
        private string $airlineName;

        #[ORM\Column(name: "airline_iata_code", type: "string", length: 15)]
        #[Assert\NotBlank(message: "Le code IATA est obligatoire.")]
        #[Assert\Length(
            max: 15,
            maxMessage: "Le code IATA ne peut pas dépasser {{ limit }} caractères."
        )]
        private string $airlineIataCode;

        #[ORM\Column(name: "airline_country", type: "string", length: 50)]
        #[Assert\NotBlank(message: "Le pays est obligatoire.")]
        #[Assert\Length(
            max: 50,
            maxMessage: "Le pays ne peut pas dépasser {{ limit }} caractères."
        )]
        private string $airlineCountry;

        public function getAirlineId(): ?int
        {
            return $this->airlineId;
        }

        public function getAirlineName(): ?string
        {
            return $this->airlineName;
        }

        public function setAirlineName(string $airlineName): static
        {
            $this->airlineName = $airlineName;

            return $this;
        }

        public function getAirlineIataCode(): ?string
        {
            return $this->airlineIataCode;
        }

        public function setAirlineIataCode(string $airlineIataCode): static
        {
            $this->airlineIataCode = $airlineIataCode;

            return $this;
        }

        public function getAirlineCountry(): ?string
        {
            return $this->airlineCountry;
        }

        public function setAirlineCountry(string $airlineCountry): static
        {
            $this->airlineCountry = $airlineCountry;

            return $this;
        }
    }
