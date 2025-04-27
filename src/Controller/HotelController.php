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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CityDataProvider;
use Psr\Log\LoggerInterface;
use Knp\Snappy\Pdf;
use Doctrine\ORM\Tools\Pagination\Paginator; // Use Doctrine Paginator

final class HotelController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/hotel', name: 'app_hotel')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $maxPerPage = 3; // 3 hotels per page for testing

        $repository = $entityManager->getRepository(Hotel::class);
        $queryBuilder = $repository->createQueryBuilder('h');

        if ($search) {
            $queryBuilder->where('h.nomH LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        // Create Doctrine Paginator
        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);

        // Calculate pagination details
        $totalItems = count($paginator);
        $totalPages = max(1, ceil($totalItems / $maxPerPage));
        $currentPage = max(1, min($page, $totalPages));
        $offset = ($currentPage - 1) * $maxPerPage;

        // Apply pagination to the query
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
                        'csrf_token' => $this->container->get('security.csrf.token_manager')->getToken('delete' . $hotel->getIdHotelH())->getValue(),
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

    #[Route('/hotel/pdf', name: 'app_hotel_list_pdf', methods: ['GET'])]
    public function generateHotelListPdf(
        EntityManagerInterface $entityManager,
        Pdf $snappy
    ): Response
    {
        $hotels = $entityManager->getRepository(Hotel::class)->findAll();
        $html = $this->renderView('hotel/pdf_list.html.twig', [
            'hotels' => $hotels,
        ]);

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
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        CityDataProvider $cityDataProvider
    ): Response
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
    public function getCities(
        string $countryCode,
        CityDataProvider $cityDataProvider,
        LoggerInterface $logger
    ): JsonResponse
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
}