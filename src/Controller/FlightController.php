<?php

namespace App\Controller;

use App\Entity\Enum\Flight\FlightStatus;
use App\Entity\Enum\Ticket\TicketStatus;
use App\Entity\Tickets;
use App\Form\TicketFormType;
use App\Repository\AirlinesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Flights;
use App\Repository\FlightsRepository;
use App\Entity\FlightReservations;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\FlightType;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
                foreach (['A', 'B', 'C', 'D', 'E'] as $column) {
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
        int                         $id,
        Request                     $request,
        EntityManagerInterface      $entityManager,
        FlightsRepository           $flightsRepository,
        \App\Service\AirportService $airportService
    ): Response
    {
        $flight = $flightsRepository->find($id);

        if (!$flight) {
            throw $this->createNotFoundException('Flight not found');
        }

        $form = $this->createForm(FlightType::class, $flight, [
            'airport_service' => $airportService,]);
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
            $seatsNumber = $flight->getSeatsnumber();
            $availableSeats = [];
            $rows = ceil($seatsNumber / 5);
            foreach (range(1, $rows) as $row) {
                foreach (((['A', 'B', 'C', 'D', 'E'])) as $column) {
                    if (count($availableSeats) < $seatsNumber) {
                        $availableSeats[] = $row . $column;
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

    #[Route('/travel/flight', name: 'front_flight')]
    public function travelIndex(FlightsRepository $flightsRepository): Response
    {
        $flights = $flightsRepository->findAll();

        return $this->render('front/flight/index.html.twig', [
            'flights' => $flights,
        ]);
    }

    #[Route('/travel/airlines', name: 'front_flight_airline', methods: ['GET'])]
    public function travelAirlinesIndex(AirlinesRepository $airlinesRepository): Response
    {
        return $this->render('front/flight/airline_index.html.twig', [
            'airlines' => $airlinesRepository->findAll(),
        ]);
    }

   #[Route('/travel/flight/book', name: 'front_book_flight')]
   public function travelBookFlightIndex(
       FlightsRepository $flightsRepository,
       \App\Repository\TicketsRepository $ticketsRepository,
       \Symfony\Bundle\SecurityBundle\Security $security
   ): Response {
       $currentUser = $security->getUser(); // Récupère l'utilisateur connecté

       if (!$currentUser) {
           throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos tickets.');
       }

       $tickets = $ticketsRepository->findBy(['passenger' => $currentUser->getId()]); // Correction ici

       return $this->render('front/flight/book.html.twig', [
           'tickets' => $tickets,
       ]);
   }



    #[Route('/travel/flight/bookFlight/{id}', name: 'book_flight', methods: ['GET', 'POST'])]
    public function bookFlight(
        int                    $id,
        Request                $request,
        FlightsRepository      $flightsRepository,
        EntityManagerInterface $entityManager,
        MailerInterface        $mailer
    ): Response
    {
        $flight = $flightsRepository->find($id);

        if (!$flight) {
            throw $this->createNotFoundException('Vol introuvable');
        }

        $ticket = new Tickets();
        $ticket->setTicketStatus(TicketStatus::BOOKED->value);
        $ticket->setIdFlight($flight);

        $form = $this->createForm(TicketFormType::class, $ticket, [
            'available_seats' => $flight->getAvailableSeats(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedUser = $ticket->getSelectedUser();
            if ($selectedUser) {
                $ticket->setPassenger($selectedUser);
                $ticket->setPassengerEmail($selectedUser->getEmail());
            }

            $basePrice = $flight->getFlightBasePrice();
            $ticketClass = $ticket->getTicketClass();
            $priceMultiplier = match ($ticketClass) {
                'Economy' => 1.0,
                'Business' => 1.5,
                'First_Class' => 2.0,
                default => 1.0,
            };

            $ticketPrice = $basePrice * $priceMultiplier;
            $ticket->setTicketPrice($ticketPrice);

            $ticket->setTicketBookingDate(new \DateTime());

            $flightReservation = new \App\Entity\FlightReservations();
            $flightReservation->setBookingDate(new \DateTime());
            $flightReservation->setStatus('Confirmed');
            $flightReservation->setFlightId($flight->getIdFlight());
            $flightReservation->setUserId($selectedUser->getId());
            $flightReservation->setSeat($ticket->getSeatNumber());

            $entityManager->persist($ticket);
            $entityManager->persist($flightReservation);
            $entityManager->flush();

            // Envoi de l'email
            $email = (new Email())
                ->from('GlobalTravel@gmail.com')
                ->to($ticket->getPassengerEmail())
                ->subject('Your Ticket Booking Confirmation')
                ->html(sprintf(
                    '<p>Dear %s,</p>
                <p>Thank you for booking with us!</p>
                <p>Here are your ticket details:</p>
                <ul>
                    <li><strong>Ticket ID:</strong> %s</li>
                    <li><strong>Flight:</strong> %s -----> %s</li>
                    <li><strong>Departure:</strong> %s</li>
                    <li><strong>Arrival:</strong> %s</li>
                    <li><strong>Class:</strong> %s</li>
                    <li><strong>Price:</strong> %.2f €</li>
                </ul>
                <p>We hope you enjoy your journey!</p>
                <img src="cid:logo" alt="Global Travel Logo" style="width:200px;"/>
                <p>Best regards,<br>Global Travel Team</p>',
                    $ticket->getPassenger()->getFirstname(),
                    $ticket->getTicketId(),
                    $flight->getDepartureCountry().($flight->getDepartureAirportName()),
                    $flight->getArrivalCountry().($flight->getArrivalAirportName()),
                    $flight->getDepartureTime()->format('d/m/Y H:i'),
                    $flight->getArrivalTime()->format('d/m/Y H:i'),
                    $ticket->getTicketClass(),
                    $ticket->getTicketPrice()
                ))
                ->embed(
                    fopen($this->getParameter('kernel.project_dir') . '/assets/images/logo.jpg', 'r'),
                    'logo'
                );

            $mailer->send($email);

            return $this->redirectToRoute('front_flight');
        }

        return $this->render('front/flight/bookFlight.html.twig', [
            'form' => $form->createView(),
            'flight' => $flight,
        ]);
    }

        #[Route('/ticket/cancel/{id}', name: 'cancel_flight', methods: ['POST'])]
    public function cancelTicket(
        int $id,
        \App\Repository\TicketsRepository $ticketsRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \Symfony\Bundle\SecurityBundle\Security $security
    ): Response {
        $currentUser = $security->getUser();

        if (!$currentUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour annuler un ticket.');
        }

        $ticket = $ticketsRepository->find($id);

        if (!$ticket || $ticket->getPassenger() !== $currentUser) {
            throw $this->createNotFoundException('Ticket introuvable ou non autorisé.');
        }

        $entityManager->remove($ticket);
        $entityManager->flush();

        $this->addFlash('success', 'Le ticket a été supprimé avec succès.');

        return $this->redirectToRoute('front_book_flight');
    }

    #[Route('/ticket/download', name: 'download_tickets', methods: ['GET'])]
    public function downloadTickets(
        \Symfony\Bundle\SecurityBundle\Security $security,
        \App\Repository\TicketsRepository $ticketsRepository,
        \Knp\Snappy\Pdf $knpSnappyPdf
    ): Response {
        $currentUser = $security->getUser();

        $tickets = $ticketsRepository->findBy(['passenger' => $currentUser->getId()]);

        $html = $this->renderView('front/flight/tickets_pdf.html.twig', [
            'tickets' => $tickets,
        ]);

        $pdfContent = $knpSnappyPdf->getOutputFromHtml($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="tickets.pdf"',
        ]);
    }




    #[Route('/ticket/qr-code/{id}', name: 'generate_qr_code', methods: ['GET'])]
    public function generateQrCode(
        int $id,
        \App\Repository\TicketsRepository $ticketsRepository
    ): Response {
        $ticket = $ticketsRepository->find($id);

        if (!$ticket) {
            throw $this->createNotFoundException('Ticket introuvable.');
        }

        $qrCodeData = sprintf(
            "Ticket ID: %s\nPassenger: %s\nDeparture: %s (%s)\nArrival: %s (%s)\nClass: %s\nPrice: %.2f \nStatus: %s\nReservation date: %s",
            $ticket->getTicketId(),
            $ticket->getPassenger()->getFirstname() . ' ' . $ticket->getPassenger()->getLastname(),
            $ticket->getIdFlight()->getDepartureAirportName(),
            $ticket->getIdFlight()->getDepartureTime()->format('d/m/Y H:i'),
            $ticket->getIdFlight()->getArrivalAirportName(),
            $ticket->getIdFlight()->getArrivalTime()->format('d/m/Y H:i'),
            $ticket->getTicketClass(),
            $ticket->getTicketPrice(),
            $ticket->getTicketStatus(),
            $ticket->getTicketBookingDate()->format('d/m/Y H:i')
        );

        $qrCode = new QrCode($qrCodeData);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
        ]);
    }

}