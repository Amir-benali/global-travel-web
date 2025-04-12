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
    private int $id;

    #[ORM\Column(name: "commentaire", type: "text", length: 65535, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100, maxMessage: "Comment cannot exceed {{ limit }} characters.")]
    
    private ?string $commentaire = null;

    #[ORM\Column(name: "note", type: "integer")]
    #[Assert\NotBlank]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "Rating must be between {{ min }} and {{ max }}.",
    )]
    private int $note;


    #[ORM\Column(name: "dateReview", type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTime $datereview ;


    #[ORM\ManyToOne(targetEntity: Activity::class, inversedBy: "reviews")]
    #[ORM\JoinColumn(name: "activityId", referencedColumnName: "id")]
    #[Assert\NotBlank]
    private Activity $activityid;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "reviews")]
    #[ORM\JoinColumn(name: "userId", referencedColumnName: "id", nullable: true)]

    
    private ?User $userid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getDatereview(): ?\DateTimeInterface
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