<?php

namespace App\Entity;
use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: "review")]
#[ORM\Index(columns: ["activityId"], name: "review_ibfk_1")]
#[ORM\Index(columns: ["userId"], name: "fk_review_user")]
class Review
{
    public function __construct()
    {
        $this->datereview = new \DateTime();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "commentaire", type: "text", length: 65535)]
    #[Assert\NotBlank(message: "Le commentaire ne peut pas être vide.")]
    #[Assert\Length(
        min: 2,
        max: 1000,
        minMessage: "Le commentaire doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le commentaire ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ0-9\s\-_,.!?;:()'\"\p{P}]+$/u",
        message: "Le commentaire contient des caractères non autorisés."
    )]
    private string $commentaire = '';

    #[ORM\Column(name: "note", type: "integer")]
    #[Assert\NotBlank(message: "La note est obligatoire.")]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "La note doit être comprise entre {{ min }} et {{ max }}.",
        invalidMessage: "La note doit être un nombre entier."
    )]
    private int $note;

    #[ORM\Column(name: "dateReview", type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTimeInterface $datereview;

    #[ORM\ManyToOne(targetEntity: Activity::class, inversedBy: "reviews")]
    #[ORM\JoinColumn(name: "activityId", referencedColumnName: "id", nullable: false)]
    #[Assert\NotNull(message: "L'activité associée est obligatoire.")]
    private Activity $activityid;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "reviews")]
    #[ORM\JoinColumn(name: "userId", referencedColumnName: "id", nullable: true)]
    private ?User $userid = null;

    // Getters and Setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = trim($commentaire);
        return $this;
    }

    public function getNote(): int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getDatereview(): \DateTimeInterface
    {
        return $this->datereview;
    }

    public function setDatereview(\DateTimeInterface $datereview): static
    {
        $this->datereview = $datereview;
        return $this;
    }

    public function getUserid(): ?User
    {
        return $this->userid;
    }

    public function setUserid(?User $userid): static
    {
        $this->userid = $userid;
        return $this;
    }

    public function getActivityid(): Activity
    {
        return $this->activityid;
    }

    public function setActivityid(Activity $activityid): static
    {
        $this->activityid = $activityid;
        return $this;
    }
}