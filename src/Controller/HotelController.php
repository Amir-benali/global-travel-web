<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\User;
use App\Entity\Chambre;
use App\Form\HotelType;
use App\Form\ChambreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CityDataProvider;
use Psr\Log\LoggerInterface;
use Knp\Snappy\Pdf;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Form\ReservationHotelType;
use App\Entity\ReservationHotel;
use Symfony\Component\Security\Core\User\UserInterface; 

use Symfony\Component\HttpFoundation\StreamedResponse;

final class HotelController extends AbstractController
{
    private LoggerInterface $logger;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private HttpClientInterface $httpClient;

    public function __construct(
        LoggerInterface $logger,
        CsrfTokenManagerInterface $csrfTokenManager,
        HttpClientInterface $httpClient
    ) {
        $this->logger = $logger;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->httpClient = $httpClient;
    }

    #[Route('/hotel', name: 'app_hotel')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $maxPerPage = 3;

        $repository = $entityManager->getRepository(Hotel::class);
        $queryBuilder = $repository->createQueryBuilder('h');

        if ($search) {
            $queryBuilder->where('h.nomH LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);

        $totalItems = count($paginator);
        $totalPages = max(1, ceil($totalItems / $maxPerPage));
        $currentPage = max(1, min($page, $totalPages));
        $offset = ($currentPage - 1) * $maxPerPage;

        $paginator
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($maxPerPage);

        $hotels = iterator_to_array($paginator);

        $this->logger->info('Hotels fetched', [
            'search' => $search,
            'page' => $currentPage,
            'results' => count($hotels),
            'total' => $totalItems,
            'totalPages' => $totalPages,
            'haveToPaginate' => $totalPages > 1,
        ]);

        if ($request->isXmlHttpRequest()) {
            $this->logger->info('AJAX response sent', [
                'totalPages' => $totalPages,
                'currentPage' => $currentPage,
            ]);
            return $this->json([
                'hotels' => array_map(function ($hotel) {
                    return [
                        'idHotelH' => $hotel->getIdHotelH(),
                        'nomH' => $hotel->getNomH(),
                        'adresseH' => $hotel->getAdresseH(),
                        'villeH' => $hotel->getVilleH(),
                        'paysH' => $hotel->getPaysH(),
                        'categorieH' => $hotel->getCategorieH(),
                        'servicesH' => $hotel->getServicesH(),
                        'csrf_token' => $this->csrfTokenManager->getToken('delete' . $hotel->getIdHotelH())->getValue(),
                    ];
                }, $hotels),
                'totalPages' => $totalPages,
                'currentPage' => $currentPage,
            ]);
        }

        return $this->render('hotel/index.html.twig', [
            'hotels' => $hotels,
            'search' => $search,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'hasPreviousPage' => $currentPage > 1,
            'previousPage' => $currentPage - 1,
            'hasNextPage' => $currentPage < $totalPages,
            'nextPage' => $currentPage + 1,
        ]);
    }
    //PHP’s native CSV
        #[Route('/hotel/csv', name: 'app_hotel_list_csv', methods: ['GET'])]
        public function generateHotelListCsv(EntityManagerInterface $entityManager): StreamedResponse
        {
            $hotels = $entityManager->getRepository(Hotel::class)->findAll();

            $response = new StreamedResponse(function () use ($hotels) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['ID', 'Name', 'Address', 'City', 'Country', 'Category (Stars)', 'Services']);
                foreach ($hotels as $hotel) {
                    fputcsv($output, [
                        $hotel->getIdHotelH(),
                        $hotel->getNomH(),
                        $hotel->getAdresseH(),
                        $hotel->getVilleH(),
                        $hotel->getPaysH(),
                        $hotel->getCategorieH(),
                        $hotel->getServicesH(),
                    ]);
                }
                fclose($output);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'hotel_list.csv'
            ));

            return $response;
        }

    #[Route('/hotel/pdf', name: 'app_hotel_list_pdf', methods: ['GET'])]
    public function generateHotelListPdf(EntityManagerInterface $entityManager, Pdf $snappy): Response
    {
        $hotels = $entityManager->getRepository(Hotel::class)->findAll();
        $html = $this->renderView('hotel/pdf_list.html.twig', ['hotels' => $hotels]);

        $pdfContent = $snappy->getOutputFromHtml($html);
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'hotel_list.pdf'
        ));

        return $response;
    }

    #[Route('/hotel/create', name: 'app_hotel_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, CityDataProvider $cityDataProvider): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $countries = $cityDataProvider->getCountries();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hotel);
            $entityManager->flush();
            $this->addFlash('success', 'Hotel created successfully!');
            return $this->redirectToRoute('app_hotel');
        }

        return $this->render('hotel/create.html.twig', [
            'form' => $form->createView(),
            'countries' => $countries
        ]);
    }

    #[Route('/get-cities/{countryCode}', name: 'app_get_cities', methods: ['GET'])]
    public function getCities(string $countryCode, CityDataProvider $cityDataProvider, LoggerInterface $logger): JsonResponse
    {
        try {
            $logger->info('Fetching cities for country', ['code' => $countryCode]);

            if (!preg_match('/^[A-Za-z]{2}$/', $countryCode)) {
                throw new \InvalidArgumentException('Invalid country code format');
            }

            $cities = $cityDataProvider->getCitiesForCountry(strtoupper($countryCode));

            return $this->json([
                'success' => true,
                'cities' => $cities
            ]);

        } catch (\Exception $e) {
            $logger->error('Cities endpoint error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Failed to load city data',
                'cities' => []
            ], 400);
        }
    }

    #[Route('/hotel/update/{id}', name: 'app_hotel_update')]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, CityDataProvider $cityDataProvider): Response
    {
        $hotel = $entityManager->getRepository(Hotel::class)->find($id);
        if (!$hotel) {
            throw $this->createNotFoundException('Hotel not found');
        }

        $countries = $cityDataProvider->getCountries();
        $this->logger->info('Countries passed to update form', ['countries' => array_keys($countries)]);

        if (empty($countries)) {
            $this->logger->warning('No countries available for update form');
            $this->addFlash('error', 'No countries available. Please try again later.');
            $countries = [];
        }

        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Hotel updated successfully!');
                return $this->redirectToRoute('app_hotel');
            } catch (\Exception $e) {
                $this->logger->error('Error updating hotel: ' . $e->getMessage(), ['id' => $id]);
                $this->addFlash('error', 'Failed to update hotel. Please try again.');
            }
        }

        return $this->render('hotel/update.html.twig', [
            'form' => $form->createView(),
            'countries' => $countries
        ]);
    }

    #[Route('/hotel/details/{id}', name: 'app_hotel_details')]
    public function details(int $id, EntityManagerInterface $entityManager): Response
    {
        $hotel = $entityManager->getRepository(Hotel::class)->find($id);
        if (!$hotel) {
            throw $this->createNotFoundException('Hotel not found');
        }

        $chambres = $entityManager->getRepository(Chambre::class)->findBy(['hotel' => $hotel]);

        return $this->render('hotel/details.html.twig', [
            'hotel' => $hotel,
            'chambres' => $chambres,
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

            $this->addFlash('success', 'Chambre updated successfully!');
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

    #[Route('travel/hotel', name: 'front_hotel')]
    public function travelHotelindex(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $maxPerPage = 3;

        $repository = $entityManager->getRepository(Hotel::class);
        $queryBuilder = $repository->createQueryBuilder('h');

        if ($search) {
            $queryBuilder->where('h.nomH LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);

        $totalItems = count($paginator);
        $totalPages = max(1, ceil($totalItems / $maxPerPage));
        $currentPage = max(1, min($page, $totalPages));
        $offset = ($currentPage - 1) * $maxPerPage;

        $paginator
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($maxPerPage);

        $hotels = iterator_to_array($paginator);

        $this->logger->info('Front hotels fetched', [
            'search' => $search,
            'page' => $currentPage,
            'results' => count($hotels),
            'total' => $totalItems,
            'totalPages' => $totalPages,
            'haveToPaginate' => $totalPages > 1,
        ]);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'hotels' => array_map(function ($hotel) {
                    return [
                        'idHotelH' => $hotel->getIdHotelH(),
                        'nomH' => $hotel->getNomH(),
                        'adresseH' => $hotel->getAdresseH(),
                        'villeH' => $hotel->getVilleH(),
                        'paysH' => $hotel->getPaysH(),
                        'categorieH' => $hotel->getCategorieH(),
                        'servicesH' => $hotel->getServicesH(),
                    ];
                }, $hotels),
                'totalPages' => $totalPages,
                'currentPage' => $currentPage,
            ]);
        }

        return $this->render('front/hotel/index.html.twig', [
            'hotels' => $hotels,
            'search' => $search,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'hasPreviousPage' => $currentPage > 1,
            'previousPage' => $currentPage - 1,
            'hasNextPage' => $currentPage < $totalPages,
            'nextPage' => $currentPage + 1,
        ]);
    }

    #[Route('travel/chambre', name: 'front_chambre')]
    public function travelChambreIndex(EntityManagerInterface $entityManager): Response
    {
        $chambres = $entityManager->getRepository(Chambre::class)->findAll();

        return $this->render('front/hotel/chambre/index.html.twig', [
            'chambres' => $chambres,
        ]);
    }

    #[Route('/travel/hotel/details/{id}', name: 'front_hotel_details')]
    public function travelDetails(int $id, EntityManagerInterface $entityManager): Response
    {
        $hotel = $entityManager->getRepository(Hotel::class)->find($id);
        if (!$hotel) {
            throw $this->createNotFoundException('Hotel not found');
        }

        $chambres = $entityManager->getRepository(Chambre::class)->findBy(['hotel' => $hotel]);

        return $this->render('front/hotel/chambre/details.html.twig', [
            'hotel' => $hotel,
            'chambres' => $chambres,
        ]);
    }

    #[Route('/travel/hotel/reservation/{chambreId}', name: 'front_hotel_reservation')]
    public function travelHotelReservation(int $chambreId, Request $request, EntityManagerInterface $entityManager, ?UserInterface $user): Response
    {
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to make a reservation.');
            return $this->redirectToRoute('app_login');
        }

        $chambre = $entityManager->getRepository(Chambre::class)->find($chambreId);
        if (!$chambre) {
            throw $this->createNotFoundException('Room not found');
        }

        $reservation = new ReservationHotel();
        $reservation->setIdChambreJ($chambre);
        $reservation->setUser($user);
        $reservation->setStatutH('pending');

        $form = $this->createForm(ReservationHotelType::class, $reservation, [
            'room_type' => $chambre->getTypeChambreH(),
            'room_price' => $chambre->getPrixNuitH() . ' EUR',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($reservation);
                $entityManager->flush();
                $this->addFlash('success', 'Reservation created successfully!');
                return $this->redirectToRoute('front_book_hotel');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to create reservation: ' . $e->getMessage());
            }
        }

        return $this->render('front/hotel/reservation.html.twig', [
            'form' => $form->createView(),
            'chambre' => $chambre,
            'hotel' => $chambre->getHotel(),
        ]);
    }

    #[Route('/travel/hotel/book', name: 'front_book_hotel')]
    public function travelBookHotelIndex(EntityManagerInterface $entityManager, ?UserInterface $user): Response
    {
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view reservations.');
            return $this->redirectToRoute('app_login');
        }

        $reservations = $entityManager->getRepository(ReservationHotel::class)->findBy(['user' => $user]);

        return $this->render('front/hotel/book.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/travel/hotel/reservation/details/{id}', name: 'front_reservation_details')]
    public function reservationDetails(int $id, EntityManagerInterface $entityManager, ?UserInterface $user): Response
    {
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view reservation details.');
            return $this->redirectToRoute('app_login');
        }

        $reservation = $entityManager->getRepository(ReservationHotel::class)->find($id);
        if (!$reservation || $reservation->getUser() !== $user) {
            throw $this->createNotFoundException('Reservation not found or access denied.');
        }

        return $this->render('front/hotel/reservation_details.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/travel/hotel/reservation/delete/{id}', name: 'front_reservation_delete', methods: ['POST'])]
    public function deleteReservation(int $id, Request $request, EntityManagerInterface $entityManager, ?UserInterface $user): Response
    {
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to delete a reservation.');
            return $this->redirectToRoute('app_login');
        }

        $reservation = $entityManager->getRepository(ReservationHotel::class)->find($id);
        if (!$reservation || $reservation->getUser() !== $user) {
            throw $this->createNotFoundException('Reservation not found or access denied.');
        }

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
            $this->addFlash('success', 'Reservation deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('front_book_hotel');
    }
}