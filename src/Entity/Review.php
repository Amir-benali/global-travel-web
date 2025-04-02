<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
    private ?string $commentaire = null;

    #[ORM\Column(name: "note", type: "integer")]
    private int $note;

    #[ORM\Column(name: "dateReview", type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTime $datereview ;

    #[ORM\Column(name: "activityId", type: "integer")]
    private int $activityid;

    #[ORM\Column(name: "userId", type: "integer", nullable: true)]
    private ?int $userid = null;

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

    public function getActivityid(): ?int
    {
        return $this->activityid;
    }

    public function setActivityid(int $activityid): static
    {
        $this->activityid = $activityid;

        return $this;
    }

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(?int $userid): static
    {
        $this->userid = $userid;

        return $this;
    }
}