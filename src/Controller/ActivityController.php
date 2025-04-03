<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ActivityController extends AbstractController
{
    #[Route('/activity', name: 'app_activity')]
    public function index(): Response
    {
        return $this->render('activity/index.html.twig', [
            'controller_name' => 'ActivityController',
        ]);
    }

    #[Route('/activity/details/{id}', name: 'app_activity_details')]
    public function detailsPage(int $id): Response
    {
        return $this->render('activity/details.html.twig', [
            'id' => $id,

        ]);
    }

    #[Route('/activity/create/', name: 'app_activity_create')]
    public function createPage(): Response
    {
        return $this->render('activity/create.html.twig', [

        ]);
    }

    #[Route('/activity/update/{id}', name: 'app_activity_update')]
    public function updatePage(int $id): Response
    {
        return $this->render('activity/update.html.twig', [
            'id' => $id,

        ]);
    }


}
