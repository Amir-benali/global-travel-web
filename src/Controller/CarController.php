<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CarController extends AbstractController
{
    #[Route('/car', name: 'app_car')]
    public function index(): Response
    {
        return $this->render('car/index.html.twig', [
            'controller_name' => 'CarController',
        ]);
    }

    #[Route('/car/driver', name: 'app_car_driver')]
    public function driverPage(): Response
    {
        return $this->render('car/driver/index.html.twig', [
        ]);
    }

    #[Route('/car/offer', name: 'app_car_offer')]
    public function offerPage(): Response
    {
        return $this->render('car/offer/index.html.twig', [
        ]);
    }

}
