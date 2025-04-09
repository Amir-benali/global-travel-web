<?php

namespace App\Controller;

use App\Entity\PrivateCar;
use App\Form\CarFormType;
use App\Form\DataTransformer\StringToFileTransformer;
use App\Repository\CarDriverRepository;
use App\Repository\CarOfferRepository;
use App\Repository\PrivateCarRepository;
use App\Service\AzureBlobService;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CarController extends AbstractController
{
    #[Route('/upload', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(Request $request, AzureBlobService $azureBlobService): Response
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('image');
        
        if ($file) {
            try {
                $imageUrl = $azureBlobService->uploadImage($file);
                return $this->json(['url' => $imageUrl]);
            } catch (\Exception $e) {
                return $this->json(['error' => $e->getMessage()], 500);
            }
        }
        
        return $this->json(['error' => 'No file uploaded'], 400);
    }

    #[Route('/car', name: 'app_car')]
    public function index(PrivateCarRepository $carRep): Response
    {
        $cars = $carRep->findAll();

        return $this->render('car/index.html.twig', [
            'cars'=> $cars,
        ]);
    }
    #[Route('/car/create', name: 'app_car_create')]
    public function createPage(ManagerRegistry $doctrine, Request $request, AzureBlobService $azureBlobService): Response
    {
        $car = new PrivateCar();
        $form = $this->createForm(CarFormType::class, $car);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            
            if ($file) {
            try {
                $imageUrl = $azureBlobService->uploadImage($file);
                $car->setImage($imageUrl);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Image upload failed: '.$e->getMessage());
                return $this->redirectToRoute('app_car_create');
            }
            }

            // Set other car properties
            $car->setBrand($form->get('brand')->getData());
            $car->setModel($form->get('model')->getData());
            $car->setNumPlace($form->get('numPlace')->getData());
            $car->setIdDriver($form->get('idDriver')->getData());

            // Persist the car
            $entityManager = $doctrine->getManager();
            $entityManager->persist($car);
            $entityManager->flush();

            $this->addFlash('success', 'Car created successfully!');
            return $this->redirectToRoute('app_car');
        }

        // Ensure client-side validation errors are displayed
        return $this->render('car/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/car/edit/{id}', name: 'app_car_update')]
    public function editPage(ManagerRegistry $doctrine, Request $request, PrivateCar $car, AzureBlobService $azureBlobService): Response
    {
        // Store the current image URL before handling the form
        $currentImageUrl = $car->getImage();
        
        $form = $this->createForm(CarFormType::class, $car);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload only if a new file was provided
            $uploadedFile = $form->get('image')->getData();
            
            if ($uploadedFile) {
                try {
                    $imageUrl = $azureBlobService->uploadImage($uploadedFile);
                    $car->setImage($imageUrl);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Image upload failed: '.$e->getMessage());
                    return $this->redirectToRoute('app_car_edit', ['id' => $car->getId()]);
                }
            } else {
                // If no new file was uploaded, keep the existing image
                $car->setImage($currentImageUrl);
            }
    
            // Update other properties and persist
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
    
            return $this->redirectToRoute('app_car');
        }
    
        return $this->render('car/update.html.twig', [
            'form' => $form->createView(),
            'currentImageUrl' => $currentImageUrl, 
        ]);
    }

    #[Route('/car/delete/{id}', name: 'app_car_delete')]
    public function deletePage(ManagerRegistry $doctrine, PrivateCar $car): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($car);
        $entityManager->flush();

        return $this->redirectToRoute('app_car');
    }

    #[Route('/car/driver', name: 'app_car_driver')]
    public function driverPage(CarDriverRepository $drivers): Response
    {
        return $this->render('car/driver/index.html.twig', [
            'drivers' => $drivers->findAll(),
        ]);
    }

    #[Route('/car/offer', name: 'app_car_offer')]
    public function offerPage(CarOfferRepository $offers): Response
    {
        return $this->render('car/offer/index.html.twig', [
            'offers' => $offers->findAll(),
        ]);
    }

}
