<?php
namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    private string $secretKey;
    private string $publicKey;

    public function __construct(string $secretKey, string $publicKey)
    {
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
        Stripe::setApiKey($this->secretKey);
    }

    public function createCheckoutSession(
        string $successUrl,
        string $cancelUrl,
        array $lineItems,
        string $currency = 'usd',
        string $mode = 'payment'
    ): Session {
        try {
            return Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [$lineItems],
                'mode' => $mode,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Stripe error: '.$e->getMessage());
        }
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}