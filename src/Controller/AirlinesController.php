<?php

namespace App\Controller;

use App\Entity\Airlines;
use App\Form\AirlinesType;
use App\Repository\AirlinesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/airlines')]
class AirlinesController extends AbstractController
{
    #[Route('/', name: 'app_airlines_index', methods: ['GET'])]
    public function index(AirlinesRepository $airlinesRepository): Response
    {
        return $this->render('flight/airline_index.html.twig', [
            'airlines' => $airlinesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_airlines_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $airline = new Airlines();
        $form = $this->createForm(AirlinesType::class, $airline);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($airline);
            $entityManager->flush();

            $this->addFlash('success', 'La compagnie aérienne a été créée avec succès.');

            return $this->redirectToRoute('app_airlines_index');
        }

        return $this->render('flight/airline_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_airlines_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Airlines $airline, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AirlinesType::class, $airline);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La compagnie aérienne a été mise à jour avec succès.');

            return $this->redirectToRoute('app_airlines_index');
        }

        return $this->render('flight/airline_edit.html.twig', [
            'form' => $form->createView(),
            'airline' => $airline,
        ]);
    }

    #[Route('/{id}', name: 'app_airlines_delete', methods: ['POST'])]
    public function delete(Request $request, Airlines $airline, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $airline->getAirlineId(), $request->request->get('_token'))) {
            $entityManager->remove($airline);
            $entityManager->flush();

            $this->addFlash('success', 'La compagnie aérienne a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_airlines_index');
    }
}