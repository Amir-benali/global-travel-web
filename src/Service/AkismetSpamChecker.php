<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

class AkismetSpamChecker
{
    private string $akismetApiKey;
    private string $akismetBlogUrl;
    private HttpClientInterface $httpClient;
    private ?LoggerInterface $logger;

    public function __construct(
        string $akismetApiKey,
        string $akismetBlogUrl,
        HttpClientInterface $httpClient,
        LoggerInterface $logger = null
    ) {
        $this->akismetApiKey = $akismetApiKey;
        $this->akismetBlogUrl = $akismetBlogUrl;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function isSpam(
        string $content,
        ?string $authorEmail = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): bool {
        try {
            // Log des paramètres envoyés
            $this->logDebug('Checking spam with Akismet', [
                'content' => substr($content, 0, 50) . '...', // Truncate pour les logs
                'authorEmail' => $authorEmail,
                'ip' => $ip,
                'userAgent' => $userAgent
            ]);

            $response = $this->httpClient->request('POST', "https://{$this->akismetApiKey}.rest.akismet.com/1.1/comment-check", [
                'body' => [
                    'blog' => $this->akismetBlogUrl,
                    'comment_content' => $content,
                    'comment_author_email' => $authorEmail,
                    'user_ip' => $ip,
                    'user_agent' => $userAgent,
                    'comment_type' => 'comment'
                ]
            ]);

            $result = $response->getContent();

            // Log du résultat brut
            $this->logInfo("Akismet response: $result");

            if ($result === 'true') {
                $this->logInfo('Spam detected by Akismet');
                return true;
            }

            if ($result === 'false') {
                $this->logDebug('Valid content according to Akismet');
                return false;
            }

            throw new \RuntimeException("Akismet API error: $result");

        } catch (ClientException $e) {
            $this->logError('Akismet API client error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return false;
            
        } catch (\Exception $e) {
            $this->logError('Unexpected error during spam check', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function logDebug(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->debug("[Akismet] $message", $context);
        }
    }

    private function logInfo(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->info("[Akismet] $message", $context);
        }
    }

    private function logError(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->error("[Akismet] $message", $context);
        }
    }
}