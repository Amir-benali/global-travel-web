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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CityDataProvider;
use Psr\Log\LoggerInterface;
use Knp\Snappy\Pdf;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HotelController extends AbstractController
{
    private LoggerInterface $logger;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private HttpClientInterface $httpClient;
    private string $huggingfaceApiToken;

    public function __construct(
        LoggerInterface $logger,
        CsrfTokenManagerInterface $csrfTokenManager,
        HttpClientInterface $httpClient,
        string $huggingfaceApiToken
    ) {
        $this->logger = $logger;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->httpClient = $httpClient;
        $this->huggingfaceApiToken = $huggingfaceApiToken;
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
    public function travelHotelindex(EntityManagerInterface $entityManager): Response
    {
        $hotels = $entityManager->getRepository(Hotel::class)->findAll();

        return $this->render('front/hotel/index.html.twig', [
            'hotels' => $hotels,
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

    #[Route('travel/hotel/details/{id}', name: 'front_hotel_details')]
    public function travelDetails(int $id, EntityManagerInterface $entityManager): Response
    {
        $hotel = $entityManager->getRepository(Hotel::class)->find($id);
        if (!$hotel) {
            throw $this->createNotFoundException('Hotel not found');
        }

        return $this->render('front/hotel/chambre/details.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    #[Route('travel/hotel/book', name: 'front_book_hotel')]
    public function travelBookHotelIndex(EntityManagerInterface $entityManager): Response
    {
        $hotels = $entityManager->getRepository(Hotel::class)->findAll();

        return $this->render('front/hotel/book.html.twig', [
            'hotels' => $hotels,
        ]);
    }

    #[Route('/quiz/{id<\d+>}', name: 'play_quiz', methods: ['GET', 'POST'])]
    public function playQuiz(int $id, Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $chambre = $entityManager->getRepository(Chambre::class)->find($id);
        if (!$chambre) {
            throw $this->createNotFoundException('Room not found');
        }

        // Try multiple models to fetch a dynamic quiz question
        $models = [
            'meta-llama/Llama-3.2-1B-Instruct',
            'mistralai/Mixtral-8x7B-Instruct-v0.1',
            'mistralai/Mixtral-8x22B-Instruct-v0.1',
        ];
        $question = null;

        foreach ($models as $model) {
            try {
                $this->logger->info('Attempting to fetch question from model', ['model' => $model]);
                $response = $this->httpClient->request('POST', "https://api-inference.huggingface.co/models/$model", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->huggingfaceApiToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'inputs' => 'Generate a multiple-choice quiz question about the best hotels in the world, featuring hotels from diverse regions (e.g., Asia, Europe, North America, Africa, etc.) as recognized in global rankings like Condé Nast Traveler’s Gold List, Travel + Leisure’s World’s Best, or The World’s 50 Best Hotels. The question should focus on unique features, locations, awards, or history of a top hotel. Return in JSON format: {"question": "", "options": [], "correct": ""}. Ensure the hotel is not limited to Burj Al Arab, Marina Bay Sands, The Plaza, or Bellagio, and include 4 distinct options with one correct answer. Example: {"question": "Which hotel, named the best in the world in 2024 by The World’s 50 Best Hotels, overlooks the Chao Phraya River?", "options": ["Capella Bangkok", "Rosewood Hong Kong", "Passalacqua", "Cheval Blanc Paris"], "correct": "Capella Bangkok"}.',
                        'parameters' => [
                            'max_new_tokens' => 250,
                            'return_full_text' => false,
                        ],
                    ],
                ]);

                // Check response status
                $statusCode = $response->getStatusCode();
                if ($statusCode !== 200) {
                    throw new \Exception("HTTP error: $statusCode");
                }

                $data = $response->toArray();
                $this->logger->debug('Raw API response', ['model' => $model, 'data' => $data]);
                $question = json_decode($data[0]['generated_text'], true);
                if (json_last_error() !== JSON_ERROR_NONE || !isset($question['question'], $question['options'], $question['correct']) || count($question['options']) !== 4 || !in_array($question['correct'], $question['options'])) {
                    throw new \Exception('Invalid JSON or question format');
                }
                $this->logger->info('AI question fetched', ['model' => $model, 'question' => $question['question']]);
                break; // Exit loop on success
            } catch (\Exception $e) {
                $this->logger->error('Failed to fetch AI question from model', [
                    'model' => $model,
                    'error' => $e->getMessage(),
                    'status' => isset($response) ? $response->getStatusCode() : 'N/A',
                    'content' => isset($response) ? $response->getContent(false) : 'N/A',
                    'trace' => $e->getTraceAsString(),
                ]);
                $question = null;
            }
        }

        // If no model succeeded, show an error
        if ($question === null) {
            $this->logger->error('All model attempts failed to fetch a quiz question');
            $this->addFlash('error', 'Unable to fetch a quiz question at this time. Please try again later.');
            return $this->render('front/hotel/chambre/play_quiz.html.twig', [
                'chambre' => $chambre,
                'question' => null,
            ]);
        }

        if ($request->isMethod('POST')) {
            $answer = $request->request->get('answer');
            $this->logger->info('Quiz submitted', ['chambre_id' => $id, 'answer' => $answer, 'correct' => $question['correct']]);

            if ($answer === $question['correct']) {
                $session->set('quiz_discount_' . $id, true);
                $this->addFlash('success', 'Congratulations! You won a 5% discount on this room.');
                return $this->render('front/hotel/chambre/play_quiz.html.twig', [
                    'chambre' => $chambre,
                    'question' => $question,
                    'result' => ['success' => true],
                ]);
            } else {
                $this->addFlash('error', 'Incorrect answer. Try again!');
                return $this->render('front/hotel/chambre/play_quiz.html.twig', [
                    'chambre' => $chambre,
                    'question' => $question,
                    'result' => ['success' => false],
                ]);
            }
        }

        return $this->render('front/hotel/chambre/play_quiz.html.twig', [
            'chambre' => $chambre,
            'question' => $question,
        ]);
    }
}