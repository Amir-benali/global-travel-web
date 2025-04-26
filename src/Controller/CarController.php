<?php

namespace App\Controller;

use App\Entity\CarDriver;
use App\Entity\CarOffer;
use App\Entity\PrivateCar;
use App\Form\CarFormType;
use App\Form\DataTransformer\StringToFileTransformer;
use App\Form\DriverFormType;
use App\Form\OfferFormType;
use App\Repository\CarDriverRepository;
use App\Repository\CarOfferRepository;
use App\Repository\CarRouteRepository;
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
                    return $this->redirectToRoute('app_car_update', ['id' => $car->getId()]);
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
    #[Route('/car/driver/create', name: 'app_car_driver_create')]
    public function createDriverPage(ManagerRegistry $doctrine, Request $request): Response
    {
        $driver = new CarDriver();
        $form = $this->createForm(DriverFormType::class, $driver);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the driver
            $entityManager = $doctrine->getManager();
            $entityManager->persist($driver);
            $entityManager->flush();

            return $this->redirectToRoute('app_car_driver');
        }

        return $this->render('car/driver/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/car/driver/edit/{id}', name: 'app_car_driver_update')]
    public function editDriverPage(ManagerRegistry $doctrine, Request $request, CarDriver $driver): Response
    {
        $form = $this->createForm(DriverFormType::class, $driver);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update the driver
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('app_car_driver');
        }

        return $this->render('car/driver/update.html.twig', [
            'form' => $form->createView(),
            'driver' => $driver,
        ]);
    }
    #[Route('/car/driver/delete/{id}', name: 'app_car_driver_delete')]
    public function deleteDriverPage(ManagerRegistry $doctrine, CarDriver $driver): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($driver);
        $entityManager->flush();

        return $this->redirectToRoute('app_car_driver');
    }

    #[Route('/car/offer', name: 'app_car_offer')]
    public function offerPage(CarOfferRepository $offers): Response
    {
        return $this->render('car/offer/index.html.twig', [
            'offers' => $offers->findAll(),
        ]);
    }
    #[Route('/car/offer/create', name: 'app_car_offer_create')]
    public function createOfferPage(ManagerRegistry $doctrine, Request $request): Response
    {
        $offer = new CarOffer();
        $form = $this->createForm(OfferFormType::class, $offer);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the offer
            $entityManager = $doctrine->getManager();
            $entityManager->persist($offer);
            $entityManager->flush();

            return $this->redirectToRoute('app_car_offer');
        }

        return $this->render('car/offer/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/car/offer/edit/{id}', name: 'app_car_offer_update')]
    public function editOfferPage(ManagerRegistry $doctrine, Request $request, CarOffer $offer): Response
    {
        $form = $this->createForm(OfferFormType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update the offer
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('app_car_offer');
        }

        return $this->render('car/offer/update.html.twig', [
            'form' => $form->createView(),
            'offer' => $offer,
        ]);
    }
    #[Route('/car/offer/delete/{id}', name: 'app_car_offer_delete')]
    public function deleteOfferPage(ManagerRegistry $doctrine, CarOffer $offer): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($offer);
        $entityManager->flush();

        return $this->redirectToRoute('app_car_offer');
    }
    #[Route('/car/offer/details/{id}', name: 'app_car_offer_details')]
    public function offerDetailPage(CarOffer $offer): Response
    {
        return $this->render('car/offer/details.html.twig', [
            'offer' => $offer,
        ]);
    }



    #[Route('/car/book/seats/{id}', name: 'app_car_book_seats')]
    public function seatSelection(int $id,CarOfferRepository $offer): Response
    {
        // Get seat count from request or set default
        $seatCount = $offer->find($id)->getCar()->getNumPlace(); ;
        $basePrice = $offer->find($id)->getPrice();
        // Validate seat count (minimum 4, maximum 8)
        // $seatCount = max(4, min(8, $seatCount));
        
        // Define seat prices based on seat count
        $seatPrices = [
            'A2' => 0.15 * $basePrice,
            'B1' => 0.15 * $basePrice,
            'B2' => 0.15 * $basePrice,
            'B3' => 0.12 * $basePrice,
            'C1' => 0.12 * $basePrice,
            'C2' => 0.10 * $basePrice,
            'C3' => 0.10 * $basePrice,
            'D1' => 0.08 * $basePrice,
            'D2' => 0.08 * $basePrice,
            'D3' => 0.08 * $basePrice,
        ];
        
        return $this->render('car/book/seat_selection.html.twig', [
            'seatCount' => $seatCount,
            'seatPrices' => $seatPrices,
            'id' => $offer->find($id)->getId(),
        ]);
    }

    #[Route('/car/book/map/{id}', name: 'app_car_map')]
    public function seatMap(int $id, Request $request, CarOfferRepository $offer): Response
    {


        $basePrice = $offer->find($id)->getPrice();
        $totalPrice = 0;
        $selectedSeats = $request->request->get('selected_seats');
        if (is_string($selectedSeats) && substr($selectedSeats, 0, 1) === '[' && substr($selectedSeats, -1) === ']') {
            $selectedSeats = json_decode($selectedSeats, true) ?: [];
        }
        
        // Ensure $selectedSeats is an array before looping
        if (!is_array($selectedSeats)) {
            return $this->redirectToRoute('app_car_book_seats', ['id' => $id]);
        }

        foreach ($selectedSeats as $seat) {
            // Calculate total price based on selected seats
            switch ($seat['id']) {
                case 'A2':
                case 'B1':
                case 'B2':
                    $totalPrice += 0.15 * $basePrice;
                    break;
                case 'B3':
                case 'C1':
                    $totalPrice += 0.12 * $basePrice;
                    break;
                case 'C2':
                case 'C3':
                    $totalPrice += 0.10 * $basePrice;
                    break;
                case 'D1':
                case 'D2':
                case 'D3':
                    $totalPrice += 0.08 * $basePrice;
                    break;
            }
        }

        // // Debug: Log selected seats to the console
        // dump($selectedSeats);
        // dump([
        //     'selectedSeats' => $selectedSeats,
        //     'basePrice' => $basePrice,
        //     'totalPrice' => $totalPrice
        // ]);

        // // Alternative if you want to log to server logs
        // error_log('Selected seats: ' . json_encode($selectedSeats));

        return $this->render('car/book/map.html.twig', [
            'selectedSeats' => $selectedSeats,
            'totalPrice' => $totalPrice,
            'offer'=> $offer->find($id),
            'car'=> $offer->find($id)->getCar(),
        ]);
    }

    #[Route('/travel/car', name: 'front_car')]
    public function travelCarIndex(PrivateCarRepository $carRep): Response
    {
        $cars = $carRep->findAll();

        return $this->render('front/car/index.html.twig', [
            'cars'=> $cars,
        ]);
    }
    #[Route('/travel/car/driver', name: 'front_car_driver')]
    public function travelDriverPage(CarDriverRepository $drivers): Response
    {
        return $this->render('front/car/driver/index.html.twig', [
            'drivers' => $drivers->findAll(),
        ]);
    }
    #[Route('travel/car/offer', name: 'front_car_offer')]
    public function travelOfferPage(CarOfferRepository $offers): Response
    {
        return $this->render('front/car/offer/index.html.twig', [
            'offers' => $offers->findAll(),
        ]);
    }
    #[Route('travel/car/book', name: 'front_book_car')]
    public function travelOfferBookPage(CarOfferRepository $offers): Response
    {
        return $this->render('front/car/book.html.twig', [
            'offers' => $offers->findAll(),
        ]);
    }
}
