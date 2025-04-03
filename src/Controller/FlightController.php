<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FlightController extends AbstractController
{
    #[Route('/flight', name: 'app_flight')]
    public function index(): Response
    {
        return $this->render('flight/index.html.twig', [
            'controller_name' => 'FlightController',
        ]);
    }

    #[Route('/flight/create', name: 'app_flight_create')]
    public function createPage(): Response
    {
        return $this->render('flight/create.html.twig', [
        ]);
    }

    #[Route('/flight/update/{id}', name: 'app_flight_update')]
    public function updatePage(int $id): Response
    {
        return $this->render('flight/update.html.twig', [
            'id' => $id,

        ]);
    }
}
