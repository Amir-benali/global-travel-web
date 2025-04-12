<?php
// src/Service/AzureBlobService.php

namespace App\Service;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use Symfony\Component\HttpFoundation\File\File;
use Psr\Http\Message\RequestInterface;

class AzureBlobService
{
    private string $connectionString;
    private string $containerName;
    private bool $disableSslVerification;

    public function __construct(string $connectionString, string $containerName, bool $disableSslVerification = false)
    {
        $this->connectionString = $connectionString;
        $this->containerName = $containerName;
        $this->disableSslVerification = $disableSslVerification;
    }

    private function getBlobServiceClient(): BlobRestProxy
    {
        $blobService = BlobRestProxy::createBlobService($this->connectionString);
        
        if ($this->disableSslVerification) {
            $blobService->pushMiddleware(function (callable $handler) {
                return function (RequestInterface $request, array $options) use ($handler) {
                    $options['verify'] = false;
                    return $handler($request, $options);
                };
            });
        }
        
        return $blobService;
    }

    private function getBlobContainerClient(): BlobRestProxy
    {
        return $this->getBlobServiceClient();
    }

    public function uploadImage(File $file): string
    {
        $containerClient = $this->getBlobContainerClient();
        $blobName = uniqid() . '-' . $file->getFilename(); // Unique blob name
        
        $content = fopen($file->getRealPath(), 'r');
        
        $options = new CreateBlockBlobOptions();
        $options->setContentType($file->getMimeType());
        
        $containerClient->createBlockBlob(
            $this->containerName,
            $blobName,
            $content,
            $options
        );
        
        // Return the URL of the uploaded blob
        preg_match('/AccountName=([^;]+)/', $this->connectionString, $matches);
        $accountName = $matches[1] ?? 'unknown';

        return sprintf(
            "https://%s.blob.core.windows.net/%s/%s",
            $accountName,
            $this->containerName,
            $blobName
        );
    }
}