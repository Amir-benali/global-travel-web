<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiProxyController extends AbstractController
{
    #[Route('/proxy/predict', name: 'proxy_predict', methods: ['POST'])]
    public function predict(Request $request)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request(
            'POST',
            'https://trip-delay-prediction-efdbdycze8g0f2d5.westeurope-01.azurewebsites.net/predict',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $request->getContent(),
            ]
        );

        return new JsonResponse($response->toArray());
    }
}