<?php

namespace App\Controller;

use App\Entity\CarDriver;
use App\Entity\CarOffer;
use App\Entity\CarReservation;
use App\Entity\CarRoute;
use App\Entity\PrivateCar;
use App\Form\CarFormType;
use App\Form\DataTransformer\StringToFileTransformer;
use App\Form\DriverFormType;
use App\Form\OfferFormType;
use App\Repository\CarDriverRepository;
use App\Repository\CarOfferRepository;
use App\Repository\CarReservationRepository;
use App\Repository\CarRouteRepository;
use App\Repository\PrivateCarRepository;
use App\Repository\UserRepository;
use App\Service\AzureBlobService;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Google\Service\ShoppingContent\Amount;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    public function createOfferPage(ManagerRegistry $doctrine, Request $request,CarOfferRepository $fetchOffers): Response
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
            'form' => $form->createView()
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
    public function seatSelection(int $id,CarOfferRepository $offer,UserRepository $user): Response
    {
        // Get seat count from request or set default
        $seatCount = $offer->find($id)->getCar()->getNumPlace(); ;
        $basePrice = $offer->find($id)->getPrice();
        // Validate seat count (minimum 4, maximum 8)
        // $seatCount = max(4, min(8, $seatCount));
        
        $employees = $user->findBy(['roles' => 'ROLE_EMPLOYEE']);  
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
            'offer'=> $offer->find($id),
            'employees' => $employees,
            'reservedSeats' => $offer->find($id)->getReservedSeats(),
        ]);
    }

    #[Route('/car/book/map/{id}', name: 'app_car_map')]
    public function seatMap(int $id, Request $request, CarOfferRepository $offer, UserRepository $user): Response
    {
        $basePrice = $offer->find($id)->getPrice();
        $totalPrice = 0;
        $employees = $user->findBy(['roles' => 'ROLE_EMPLOYEE']);
        
        // Get selected seats data from the form
        $selectedSeatsJson = $request->request->get('selected_seats');
        $selectedSeats = [];
        
        if ($selectedSeatsJson) {
            // Handle both JSON string and direct array formats
            if (is_string($selectedSeatsJson) && (substr($selectedSeatsJson, 0, 1) === '[' || substr($selectedSeatsJson, 0, 1) === '{')) {
                $selectedSeats = json_decode($selectedSeatsJson, true) ?: [];
            } else {
                $selectedSeats = $selectedSeatsJson;
            }
        }
        
        // Get employee assignments
        $employeeAssignmentsJson = $request->request->get('employee_assignments');
        $employeeAssignments = [];
        
        if ($employeeAssignmentsJson) {
            if (is_string($employeeAssignmentsJson)) {
                $employeeAssignments = json_decode($employeeAssignmentsJson, true) ?: [];
            } else {
                $employeeAssignments = $employeeAssignmentsJson;
            }
        }
        
        // Process selected seats to calculate price and get seat details
        $processedSeats = [];
        $seatPositions = [
            'A2' => 'Front passenger seat',
            'B1' => 'Back left seat',
            'B2' => 'Back right seat',
            'B3' => 'Back middle seat',
            'C1' => 'Third row left seat',
            'C2' => 'Third row right seat',
            'C3' => 'Third row middle seat',
            'D1' => 'Fourth row left seat',
            'D2' => 'Fourth row middle seat',
            'D3' => 'Fourth row right seat'
        ];
        
        // If selectedSeats is array of IDs, convert to objects with price and position
        if (is_array($selectedSeats)) {
            foreach ($selectedSeats as $seatId) {
                $price = 0;
                switch ($seatId) {
                    case 'A2':
                    case 'B1':
                    case 'B2':
                        $price = 0.15 * $basePrice;
                        break;
                    case 'B3':
                    case 'C1':
                        $price = 0.12 * $basePrice;
                        break;
                    case 'C2':
                    case 'C3':
                        $price = 0.10 * $basePrice;
                        break;
                    case 'D1':
                    case 'D2':
                    case 'D3':
                        $price = 0.08 * $basePrice;
                        break;
                }
                
                $totalPrice += $price;
                $processedSeats[] = [
                    'id' => $seatId,
                    'price' => $price,
                    'position' => $seatPositions[$seatId] ?? 'Passenger seat',
                    'employeeId' => $employeeAssignments[$seatId] ?? null
                ];
            }
        }
        
        // Get assigned employee details for display
        $assignedEmployees = [];
        foreach ($employeeAssignments as $seatId => $employeeId) {
            if ($employeeId) {
                $employee = $user->find($employeeId);
                if ($employee) {
                    $assignedEmployees[$seatId] = [
                        'id' => $employeeId,
                        'firstName' => $employee->getFirstName(),
                        'lastName' => $employee->getLastName(),
                    ];
                }
            }
        }
        
        return $this->render('car/book/map.html.twig', [
            'selectedSeats' => $processedSeats,
            'totalPrice' => $totalPrice,
            'offer' => $offer->find($id),
            'car' => $offer->find($id)->getCar(),
            'employees' => $employees,
            'assignedEmployees' => $assignedEmployees,
            'seatPositions' => $seatPositions,
            'offerId' => $id
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
    public function travelOfferBookPage(CarReservationRepository $reservation, UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();
        return $this->render('front/car/book.html.twig', [
            'reservations' => $currentUser->getCarReservations()
        ]);
    }
    #[Route('travel/car/offer/checkout', name: 'front_car_offer_checkout')]
    public function checkout(Request $req,UserRepository $user,ManagerRegistry $doctrine): Response
    {
        // Check if total_price exists in request parameters, try both POST and GET
        $price = 0;
        if ($req->request->has('total_price')) {
            $price = floatval($req->request->get('total_price'));
        } elseif ($req->query->has('total_price')) {
            $price = floatval($req->query->get('total_price'));
        } else {
            // Handle the missing parameter case
            $this->addFlash('error', 'Total price was not provided');
            return $this->redirectToRoute('front_book_car');
        }
        // Retrieve form data
        $startCoords = $req->request->get('start_coords');
        $destinations = $req->request->get('destinations');
        $assignedEmployees = $req->request->get('assigned_employees');
        $assignedSeats = $req->request->get('assigned_seats');
        $offerId = $req->request->get('selected_offer');

        $offer = new CarOffer();
        // Handle JSON values if needed
        if (is_string($assignedEmployees) && !empty($assignedEmployees)) {
            $assignedEmployees = json_decode($assignedEmployees, true);
        }
        if (is_string($assignedSeats) && !empty($assignedSeats)) {
            $assignedSeats = json_decode($assignedSeats, true);
        }

        if (is_string($offerId) && !empty($offerId)) {
            $offerId = json_decode($offerId, true);
            $offer = $doctrine->getRepository(CarOffer::class)->find($offerId);
        }
        $entityManager = $doctrine->getManager();

    
        $route = new CarRoute();
        $route->setLocationStart($startCoords);
        $route->setLocationDestination($destinations);
        $route->setDateStart($offer->getdate());
    

        $reservation = new CarReservation();
        $reservation->setDate(new \DateTime());
        $reservation->setRoute($route);
        $reservation->setOffer($offer);
        $reservation->setStatus('PENDING');

        foreach ($assignedEmployees as $seatId => $employeeId) {
            if ($employeeId) {

                $employee = $user->find($employeeId);

                if ($employee) {
                    $reservation->addUser($employee);

                }
            }
        }
        
        $entityManager->persist($route);
        $entityManager->persist($reservation);
        $entityManager->flush();

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        
        // Generate absolute URLs
        $successUrl = $this->generateUrl('car_payment_success', ['amount' => $price , 'offer_id'=>$offer->getId(),'seats'=> json_encode(array_keys($assignedEmployees)),'reservation'=> $reservation->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('car_payment_cancel', [ 'reservation'=> $reservation->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Dev Product',
                    ],
                    'unit_amount' => $price * 100, 
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,

        ]);
        
        return $this->redirect($session->url, 303);
    }
    #[Route('travel/car/offer/success', name: 'car_payment_success')]
    public function success(Request $req,ManagerRegistry $doctrine): Response
    {
        $amount = 0;
        if ($req->request->has('amount')) {
            $amount = floatval($req->request->get('amount'));
        } elseif ($req->query->has('amount')) {
            $amount = floatval($req->query->get('amount'));
        }
        // Get offer ID from request
        $offerId = null;
        if ($req->request->has('offer_id')) {
            $offerId = $req->request->get('offer_id');
        } elseif ($req->query->has('offer_id')) {
            $offerId = $req->query->get('offer_id');
        }
        
        // Get reservation ID from request
        $reservationId = null;
        if ($req->request->has('reservation')) {
            $reservationId = $req->request->get('reservation');
        } elseif ($req->query->has('reservation')) {
            $reservationId = $req->query->get('reservation');
        }
        
        // Get seats from request
        $seats = null;
        if ($req->request->has('seats')) {
            $seats = $req->request->get('seats');
        } elseif ($req->query->has('seats')) {
            $seats = $req->query->get('seats');
        }

        // Parse the seats data to ensure it's an array
        if (is_string($seats)) {
            if (strpos($seats, '[') === 0) {
            // It's a JSON string, decode it to array
            $seats = json_decode($seats, true);
            } else {
            // Try to unserialize if it's not a JSON string
            $decodedSeats = unserialize($seats);
            if ($decodedSeats !== false) {
                $seats = $decodedSeats;
            } else {
                // If all else fails, convert to array with one item
                $seats = [$seats];
            }
            }
        }
        
        $entityManager = $doctrine->getManager();
        $offer = $entityManager->getRepository(CarOffer::class)->find($offerId);
        
        // Get existing reserved seats
        $currentReservedSeats = $offer->getReservedSeats() ?: [];
        
        // Combine existing seats with new seats
        $allReservedSeats = array_merge($currentReservedSeats, $seats);
        
        // Remove any duplicates
        $allReservedSeats = array_unique($allReservedSeats);
        
        // Update the offer with the combined seats
        $offer->setReservedSeats($allReservedSeats);
        $reservation = $entityManager->getRepository(CarReservation::class)->find($reservationId);
        $reservation->setStatus('CONFIRMED');
        $entityManager->persist($offer);
        $entityManager->persist($reservation);
        $entityManager->flush();

        return $this->render('payment/success.html.twig', [
            'amount' => $amount,
        ]);
    }

    #[Route('travel/car/offer/cancel', name: 'car_payment_cancel')]
    public function cancel(Request $req,ManagerRegistry $doctrine): Response
    {
        $reservationId = null;
        if ($req->request->has('reservation')) {
            $reservationId = $req->request->get('reservation');
        } elseif ($req->query->has('reservation')) {
            $reservationId = $req->query->get('reservation');
        }
        $entityManager = $doctrine->getManager();
        $reservation = $entityManager->getRepository(CarReservation::class)->find($reservationId);
        $reservation->setStatus('CANCELED');
        $entityManager->persist($reservation);
        $entityManager->flush();

        return $this->render('payment/cancel.html.twig');
    }

    #[Route('travel/car/book/{id}', name: 'front_book_car_details')]
    public function travelOfferBookDetailsPage(CarReservationRepository $reservation, CarOfferRepository $offer, int $id): Response
    {

        // Get offer and calculate base price
        $reservationData = $reservation->find($id);
        $offerData = $reservationData->getOffer();
        $basePrice = $offerData->getPrice();
        $totalPrice = 0;

        // Get the reserved seats
        $selectedSeats = $offerData->getReservedSeats();

        // Process selected seats with their prices and positions
        $processedSeats = [];
        $seatPositions = [
            'A2' => 'Front passenger seat',
            'B1' => 'Back left seat',
            'B2' => 'Back right seat',
            'B3' => 'Back middle seat',
            'C1' => 'Third row left seat',
            'C2' => 'Third row right seat',
            'C3' => 'Third row middle seat',
            'D1' => 'Fourth row left seat',
            'D2' => 'Fourth row middle seat',
            'D3' => 'Fourth row right seat'
        ];

        // Process each seat with price calculation
        if (is_array($selectedSeats)) {
            foreach ($selectedSeats as $seatId) {
                $price = 0;
                switch ($seatId) {
                    case 'A2':
                    case 'B1':
                    case 'B2':
                        $price = 0.15 * $basePrice;
                        break;
                    case 'B3':
                    case 'C1':
                        $price = 0.12 * $basePrice;
                        break;
                    case 'C2':
                    case 'C3':
                        $price = 0.10 * $basePrice;
                        break;
                    case 'D1':
                    case 'D2':
                    case 'D3':
                        $price = 0.08 * $basePrice;
                        break;
                }
                
                $totalPrice += $price;
                $processedSeats[] = [
                    'id' => $seatId,
                    'price' => $price,
                    'position' => $seatPositions[$seatId] ?? 'Passenger seat',
                ];
            }
        }
        return $this->render('front/car/book_details.html.twig', [
            'reservation' => $reservation->find($id),
            'offer' => $offer->find($reservation->find($id)->getOffer()),
            'car' => $reservation->find($id)->getOffer()->getCar(),
            'assignedEmployees' => $reservation->find($id)->getUser(0),
            'start' => $reservation->find($id)->getRoute()->getLocationStart(),
            'destinations' => $reservation->find($id)->getRoute()->getLocationDestination(),
            'totalPrice' => $totalPrice,
            'selectedSeats' => $processedSeats,
            'seatPositions' => $seatPositions,
        ]);
    }
}
