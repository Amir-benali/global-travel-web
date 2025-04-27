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



  #[Route('/flight/create', name: 'app_flight_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, \App\Service\AirportService $airportService): Response
    {
        $flight = new Flights();

        // Créer le formulaire
        $form = $this->createForm(FlightType::class, $flight, [
            'airport_service' => $airportService,
        ]);

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

            // Générer les sièges disponibles
            $seatsNumber = $flight->getSeatsnumber();
            $availableSeats = [];
            $rows = ceil($seatsNumber / 5); // Supposons 2 colonnes (A et B)
            foreach (range(1, $rows) as $row) {
                foreach (['A', 'B','C','D','E'] as $column) {
                    if (count($availableSeats) < $seatsNumber) {
                        $availableSeats[] = $row . $column;
                    }
                }
            }
            $flight->setAvailableSeats($availableSeats);

            $entityManager->persist($flight);
            $entityManager->flush();

            return $this->redirectToRoute('app_flight');
        }

        // Rendre la vue du formulaire
        return $this->render('flight/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/flight/update/{id}', name: 'app_flight_update', methods: ['GET', 'POST'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        FlightsRepository $flightsRepository,
        \App\Service\AirportService $airportService
    ): Response {
        $flight = $flightsRepository->find($id);

        if (!$flight) {
            throw $this->createNotFoundException('Flight not found');
        }

        $form = $this->createForm(FlightType::class, $flight, [
            'airport_service' =>$airportService,]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Calculer la durée
            $departureTime = $flight->getDepartureTime();
            $arrivalTime = $flight->getArrivalTime();

            if ($departureTime && $arrivalTime) {
                $duration = $arrivalTime->getTimestamp() - $departureTime->getTimestamp();
                $flight->setDurationPerHours($duration / 3600);
            }

            // Générer les sièges disponibles
            $seatsNumber=$flight->getSeatsnumber();
            $availableSeats=[];
            $rows=ceil($seatsNumber/5);
            foreach (range(1,$rows) as $row){
                foreach (((['A','B','C','D','E']))   as $column){
                    if (count($availableSeats)<$seatsNumber){
                        $availableSeats[]=$row.$column;
                    }
                }
            }
            $flight->setAvailableSeats(($availableSeats));


            $entityManager->flush();

            return $this->redirectToRoute('app_flight');
        }

        return $this->render('flight\update.html.twig', [
            'form' => $form->createView(),
            'flight' => $flight,
        ]);
    }

    #[Route('/flight/delete/{id}', name: 'app_flight_delete')]
    public function delete(int $id, EntityManagerInterface $entityManager, FlightsRepository $flightsRepository): Response
    {
        $flight = $flightsRepository->find($id);

        if (!$flight) {
            throw $this->createNotFoundException('Flight not found');
        }

        $entityManager->remove($flight);
        $entityManager->flush();


        return $this->redirectToRoute('app_flight');
    }
}