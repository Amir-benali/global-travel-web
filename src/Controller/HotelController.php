<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\Chambre;
use App\Form\HotelType;
use App\Form\ChambreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HotelController extends AbstractController
{
    // CRUD for Hotel
    #[Route('/hotel', name: 'app_hotel')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $hotels = $entityManager->getRepository(Hotel::class)->findAll();

        return $this->render('hotel/index.html.twig', [
            'hotels' => $hotels,
        ]);
    }

    #[Route('/hotel/create', name: 'app_hotel_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hotel);
            $entityManager->flush();

            $this->addFlash('success', 'Hotel created successfully!');
            return $this->redirectToRoute('app_hotel');
        }

        return $this->render('hotel/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/hotel/update/{id}', name: 'app_hotel_update')]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $hotel = $entityManager->getRepository(Hotel::class)->find($id);
        if (!$hotel) {
            throw $this->createNotFoundException('Hotel not found');
        }

        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Hotel updated successfully!');
            return $this->redirectToRoute('app_hotel');
        }

        return $this->render('hotel/update.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/hotel/details/{id}', name: 'app_hotel_details')]
    public function details(int $id, EntityManagerInterface $entityManager): Response
    {
        $hotel = $entityManager->getRepository(Hotel::class)->find($id);
        if (!$hotel) {
            throw $this->createNotFoundException('Hotel not found');
        }

        return $this->render('hotel/details.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    #[Route('/hotel/delete/{id}', name: 'app_hotel_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $hotel = $entityManager->getRepository(Hotel::class)->find($id);
        if (!$hotel) {
            throw $this->createNotFoundException('Hotel not found');
        }

        $entityManager->remove($hotel);
        $entityManager->flush();

        return $this->redirectToRoute('app_hotel');
    }

    // CRUD for Chambre
    #[Route('/chambre', name: 'app_chambre')]
    public function chambreIndex(EntityManagerInterface $entityManager): Response
    {
        $chambres = $entityManager->getRepository(Chambre::class)->findAll();

        return $this->render('hotel/chambre/index.html.twig', [
            'chambres' => $chambres,
        ]);
    }

    #[Route('/chambre/create', name: 'app_chambre_create')]
    public function createChambre(Request $request, EntityManagerInterface $entityManager): Response
    {
        $chambre = new Chambre();
        $form = $this->createForm(ChambreType::class, $chambre);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($chambre);
            $entityManager->flush();

            return $this->redirectToRoute('app_chambre');
        }

        return $this->render('hotel/chambre/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/chambre/update/{id}', name: 'app_chambre_update')]
    public function updateChambre(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $chambre = $entityManager->getRepository(Chambre::class)->find($id);
        if (!$chambre) {
            throw $this->createNotFoundException('Chambre not found');
        }

        $form = $this->createForm(ChambreType::class, $chambre);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_chambre');
        }

        return $this->render('hotel/chambre/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/chambre/details/{id}', name: 'app_chambre_details')]
    public function detailsChambre(int $id, EntityManagerInterface $entityManager): Response
    {
        $chambre = $entityManager->getRepository(Chambre::class)->find($id);
        if (!$chambre) {
            throw $this->createNotFoundException('Chambre not found');
        }

        return $this->render('hotel/chambre/details.html.twig', [
            'chambre' => $chambre,
        ]);
    }

    #[Route('/chambre/delete/{id}', name: 'app_chambre_delete', methods: ['POST'])]
    public function deleteChambre(int $id, EntityManagerInterface $entityManager): Response
    {
        $chambre = $entityManager->getRepository(Chambre::class)->find($id);
        if (!$chambre) {
            throw $this->createNotFoundException('Chambre not found');
        }

        $entityManager->remove($chambre);
        $entityManager->flush();

        return $this->redirectToRoute('app_chambre');
    }
}