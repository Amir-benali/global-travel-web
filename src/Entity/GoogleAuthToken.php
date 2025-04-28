<?php

namespace App\Entity;

use App\Repository\GoogleAuthTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoogleAuthTokenRepository::class)]
#[ORM\Table(name: "google_auth_tokens")]
class GoogleAuthToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private string $accessToken;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: 'integer')]
    private int $expiresAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getAccessToken(): string { return $this->accessToken; }
    public function getRefreshToken(): ?string { return $this->refreshToken; }
    public function getExpiresAt(): int { return $this->expiresAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    // Setters
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function setExpiresAt(int $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isExpired(): bool
    {
        return time() >= $this->expiresAt;
    }
    public function updateFromArray(array $token): void
{
    $this->accessToken = $token['access_token'];
    $this->expiresAt = time() + ($token['expires_in'] ?? 3600);
    
    if (isset($token['refresh_token'])) {
        $this->refreshToken = $token['refresh_token'];
    }
}
}
