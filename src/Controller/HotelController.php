<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HotelController extends AbstractController
{
    #[Route('/hotel', name: 'app_hotel')]
    public function index(): Response
    {
        return $this->render('hotel/index.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }
    
    #[Route('/hotel/create', name: 'app_hotel_create')]
    public function createPage(): Response
    {
        return $this->render('hotel/create.html.twig', [
        ]);
    }

    #[Route('/hotel/update/{id}', name: 'app_hotel_update')]
    public function updatePage(int $id): Response
    {
        return $this->render('hotel/update.html.twig', [
            'id' => $id,

        ]);
    }
    #[Route('/hotel/details/{id}', name: 'app_hotel_details')]
    public function detailsPage(int $id): Response
    {
        return $this->render('hotel/details.html.twig', [
            'id' => $id,

        ]);
    }

}
