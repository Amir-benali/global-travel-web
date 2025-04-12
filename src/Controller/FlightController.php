<?php

        namespace App\Controller;

        use App\Entity\Enum\Flight\FlightStatus;
        use App\Repository\AirlinesRepository;
        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Attribute\Route;
        use App\Entity\Flights;
        use App\Repository\FlightsRepository;
        use Doctrine\ORM\EntityManagerInterface;
        use App\Form\FlightType;

        final class FlightController extends AbstractController
        {
            #[Route('/flight', name: 'app_flight')]
            public function index(FlightsRepository $flightsRepository): Response
            {
                $flights = $flightsRepository->findAll();

                return $this->render('flight/index.html.twig', [
                    'flights' => $flights,
                ]);
            }

            /*#[Route('/flight/create', name: 'app_flight_create')]
            public function createPage(AirlinesRepository $airlinesRepository): Response
            {
                $airlines=$airlinesRepository->findAll();
                $flightStatus=FlightStatus::cases();

                return $this->render('flight/create.html.twig', ['airlines' => $airlines,
                    'flightStatus' => $flightStatus,
                ]);
            }*/

            #[Route('/flight/create', name: 'app_flight_create')]
            public function create(Request $request, EntityManagerInterface $entityManager): Response
            {
                $flight = new Flights();


                // Créer le formulaire
                $form = $this->createForm(FlightType::class, $flight);


                // Gérer la soumission
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    // Calculer la durée
                    $departureTime = $flight->getDepartureTime();
                    $arrivalTime = $flight->getArrivalTime();

                    if ($departureTime && $arrivalTime) {
                        $duration = $arrivalTime->getTimestamp() - $departureTime->getTimestamp();
                        $flight->setDurationPerHours($duration / 3600); // Convertir en heures
                    }

                    $entityManager->persist($flight);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_flight');
                }

                // Rendre la vue du formulaire
                return $this->render('flight/create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            /*#[Route('/flight/save', name: 'app_flight_save', methods: ['POST'])]
            public function save(Request $request, EntityManagerInterface $entityManager, AirlinesRepository $airlinesRepository): Response
            {
                $flight = new Flights();

                // Récupération de l'ID de la compagnie aérienne depuis le formulaire
                $airlineId = $request->request->get('airlineId');
                $airline = $airlinesRepository->find($airlineId);

                if (!$airline) {
                    throw $this->createNotFoundException('La compagnie aérienne spécifiée est introuvable.');
                }

                // Récupération des données du formulaire
                $flight->setFlightNumber($request->request->get('flightNumber'));
                $flight->setAirlineId($airline); // Passe l'objet Airlines
                $flight->setDepartureAirportName($request->request->get('departureAirportName'));
                $flight->setArrivalAirportName($request->request->get('arrivalAirportName'));
                $flight->setDepartureCountry($request->request->get('departureCountry'));
                $flight->setArrivalCountry($request->request->get('arrivalCountry'));
                $flight->setDepartureTime(new \DateTime($request->request->get('departureTime')));
                $flight->setArrivalTime(new \DateTime($request->request->get('arrivalTime')));
                $flight->setDurationPerHours((int) $request->request->get('durationPerHours'));
                $flight->setAvailableSeats((int) $request->request->get('availableSeats'));
                $flight->setFlightBasePrice((float) $request->request->get('flightBasePrice'));

                // Conversion de FlightStatus
                $flightStatus = FlightStatus::tryFrom($request->request->get('flightStatus'));
                if (!$flightStatus) {
                    throw new \InvalidArgumentException('Statut de vol invalide.');
                }
                $flight->setFlightStatus($flightStatus);

                // Sauvegarde dans la base de données
                $entityManager->persist($flight);
                $entityManager->flush();

                // Redirection après sauvegarde
                return $this->redirectToRoute('app_flight');
            }*/

            #[Route('/flight/update/{id}', name: 'app_flight_update', methods: ['GET', 'POST'])]
            public function updatePage(
                int $id,
                Request $request,
                EntityManagerInterface $entityManager,
                FlightsRepository $flightsRepository
            ): Response {
                $flight = $flightsRepository->find($id);

                if (!$flight) {
                    throw $this->createNotFoundException('Vol non trouvé');
                }

                $form = $this->createForm(FlightType::class, $flight);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager->flush();

                    $this->addFlash('success', 'Le vol a été mis à jour avec succès.');

                    return $this->redirectToRoute('app_flight');
                }

                return $this->render('flight/update.html.twig', [
                    'form' => $form->createView(),
                    'flight' => $flight,
                ]);
            }

            #[Route('/flight/delete/{id}', name: 'app_flight_delete', methods: ['POST'])]
            public function delete(int $id, EntityManagerInterface $entityManager, FlightsRepository $flightsRepository): Response
            {
                $flight = $flightsRepository->find($id);

                if (!$flight) {
                    throw $this->createNotFoundException('Vol non trouvé.');
                }

                $entityManager->remove($flight);
                $entityManager->flush();

                $this->addFlash('success', 'Le vol a été supprimé avec succès.');

                return $this->redirectToRoute('app_flight');
            }

        }