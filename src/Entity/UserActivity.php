<?php

namespace App\Entity;

use App\Repository\UserActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserActivityRepository::class)]
#[ORM\Table(name: "user_activity")]
#[ORM\Index(columns: ["user_id"], name: "fk_user_id_user_activity")]
#[ORM\Index(columns: ["activity_id"], name: "activity_id")]
class UserActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer")]
    private int $id;

    #[ORM\Column(name: "user_id", type: "integer", nullable: true)]
    private ?int $userId = null;

    #[ORM\Column(name: "activity_id", type: "integer", nullable: true)]
    private ?int $activityId = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getActivityId(): ?int
    {
        return $this->activityId;
    }

    public function setActivityId(?int $activityId): static
    {
        $this->activityId = $activityId;

        return $this;
    }
}