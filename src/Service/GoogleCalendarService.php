<?php // src/Service/GoogleCalendarService.php

namespace App\Service;

use App\Entity\Activity;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Exception as GoogleServiceException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GoogleCalendarService
{
    private Client $client;
    private RequestStack $requestStack;
    private LoggerInterface $logger;

    public function __construct(
        RequestStack $requestStack,
        LoggerInterface $logger,
        string $googleClientSecretPath
    ) {
        $this->requestStack = $requestStack;
        $this->logger = $logger;

        // Configuration client Google
        $this->client = new Client();
        $this->client->setAuthConfig($googleClientSecretPath);
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->setRedirectUri('https://127.0.0.1:8000/google/auth-callback');
        $this->client->setAccessType('offline');
    }

    public function createEvent(Activity $activity): string
    {
        $this->validateToken();
        
        $service = new Calendar($this->client);
        $event = new Event([
            'summary' => $activity->getNomactivity(),
            'start' => $this->createEventDateTime($activity->getDatedebut()),
            'end' => $this->createEventDateTime($activity->getDatefin()),
            'location' => $activity->getLocalisation(),
            'description' => $activity->getDescription()
        ]);

        $createdEvent = $service->events->insert('primary', $event);
        return $createdEvent->getId();
    }

    private function createEventDateTime(\DateTimeInterface $date): EventDateTime
    {
        return new EventDateTime([
            'dateTime' => $date->format(\DateTimeInterface::RFC3339),
            'timeZone' => 'Europe/Paris'
        ]);
    }

    private function validateToken(): void
    {
        $token = $this->requestStack->getSession()->get('google_access_token');
        
        if (empty($token)) {
            throw new \RuntimeException('Google authentication required');
        }

        $this->client->setAccessToken($token);

        if ($this->client->isAccessTokenExpired()) {
            $this->refreshToken();
        }
    }

    private function refreshToken(): void
    {
        $newToken = $this->client->fetchAccessTokenWithRefreshToken();
        $this->requestStack->getSession()->set('google_access_token', $newToken);
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function handleAuthCallback(string $code): void
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        $this->requestStack->getSession()->set('google_access_token', $token);
    }
}