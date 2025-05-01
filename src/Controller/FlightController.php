<?php

namespace App\Controller;

use App\Entity\Enum\Flight\FlightStatus;
use App\Entity\Enum\Ticket\TicketStatus;
use App\Entity\Tickets;
use App\Form\TicketFormType;
use App\Repository\AirlinesRepository;
use Doctrine\Persistence\ManagerRegistry;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        MailerInterface        $mailer,
        UrlGeneratorInterface  $urlGenerator
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

            $selectedSeat = $ticket->getSeatNumber();
            $unavailableSeats = $flight->getUnavailableSeats();
            $unavailableSeats[] = $selectedSeat;
            $flight->setUnavailableSeats($unavailableSeats);
            $flight->setSeatsnumber($flight->getSeatsnumber() - 1);

            $entityManager->persist($ticket);
            $entityManager->flush();

            // Envoi de l'email
            $email = (new Email())
    ->from('GlobalTravel@gmail.com')
    ->to($ticket->getPassengerEmail())
    ->subject('Your Ticket Booking Confirmation')
    ->html(sprintf(
        '<p>Dear %s,</p>
        <p>Thank you for booking with Global Travel!</p>
        <p>Here are your ticket details:</p>
        <table style="border-collapse: collapse; width: 100%%; margin: 20px 0;">
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;"><strong>Ticket ID:</strong></td>
                <td style="border: 1px solid #ddd; padding: 8px;">%s</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;"><strong>Flight:</strong></td>
                <td style="border: 1px solid #ddd; padding: 8px;">%s → %s</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;"><strong>Departure:</strong></td>
                <td style="border: 1px solid #ddd; padding: 8px;">%s</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;"><strong>Arrival:</strong></td>
                <td style="border: 1px solid #ddd; padding: 8px;">%s</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;"><strong>Class:</strong></td>
                <td style="border: 1px solid #ddd; padding: 8px;">%s</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;"><strong>Price:</strong></td>
                <td style="border: 1px solid #ddd; padding: 8px;">%.2f €</td>
            </tr>
        </table>
        <p>We hope you enjoy your journey!</p>
        <p style="margin-top: 20px;">Best regards,<br>Global Travel Team</p>
        <img src="cid:logo" alt="Global Travel Logo" style="width:200px; margin-top: 20px;"/>',
        $ticket->getPassenger()->getFirstname(),
        $ticket->getTicketId(),
        $flight->getDepartureCountry() . ' (' . $flight->getDepartureAirportName() . ')',
        $flight->getArrivalCountry() . ' (' . $flight->getArrivalAirportName() . ')',
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

            // Création de la session Stripe
            \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

            $successUrl = $urlGenerator->generate('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $cancelUrl = $urlGenerator->generate('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => sprintf('Flight Ticket: %s → %s', $flight->getDepartureCountry(), $flight->getArrivalCountry()),
                        ],
                        'unit_amount' => (int)($ticketPrice * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
            ]);


            return $this->redirect($session->url, 303);
        }

        return $this->render('front/flight/bookFlight.html.twig', [
            'form' => $form->createView(),
            'flight' => $flight,
        ]);
    }



    #[Route('travel/flight/payment/success', name: 'flight_payment_success')]
    public function flightPaymentSuccess(Request $req, ManagerRegistry $doctrine, MailerInterface $mailer): Response
    {
        $amount = $req->query->get('amount', 0.0);
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $sessionId = $req->query->get('session_id');

        if (!$sessionId) {
            throw $this->createNotFoundException('No session ID found in the request.');
        }

        $session = \Stripe\Checkout\Session::retrieve($sessionId);
        $ticketId = $session->metadata->ticket_id;

        $entityManager = $doctrine->getManager();
        $ticket = $entityManager->getRepository(Tickets::class)->find($ticketId);

        if (!$ticket) {
            throw $this->createNotFoundException('Ticket not found.');
        }

        if ($amount <= 0) {
            throw $this->createNotFoundException('Invalid transaction data.');
        }

        // Mise à jour du ticket
        $ticket->setTicketStatus(TicketStatus::BOOKED->value);
        $entityManager->persist($ticket);

        // Création de la réservation
        $reservation = new FlightReservations();
        $reservation->setFlightId($ticket->getIdFlight());
        $reservation->setBookingDate(new \DateTime());
        $entityManager->persist($reservation);

        $entityManager->flush();

        // Envoi de l'email
        try {
            $flight = $ticket->getIdFlight();
            $email = (new Email())
                ->from('no-reply@globaltravel.com')
                ->to($ticket->getPassengerEmail())
                ->subject('Your Flight Booking Confirmation')
                ->html(sprintf(
                    '<p>Dear %s,</p>
                    <p>Your payment of %.2f € has been successfully processed.</p>
                    <p>Here are your flight and ticket details:</p>
                    <ul>
                        <li><strong>Flight:</strong> %s → %s</li>
                        <li><strong>Departure:</strong> %s (%s)</li>
                        <li><strong>Arrival:</strong> %s (%s)</li>
                        <li><strong>Class:</strong> %s</li>
                        <li><strong>Seat:</strong> %s</li>
                        <li><strong>Price:</strong> %.2f €</li>
                    </ul>
                    <p>Thank you for choosing Global Travel.</p>',
                    $ticket->getPassenger()->getFirstname(),
                    $amount,
                    $flight->getDepartureCountry(),
                    $flight->getArrivalCountry(),
                    $flight->getDepartureAirportName(),
                    $flight->getDepartureTime()->format('d/m/Y H:i'),
                    $flight->getArrivalAirportName(),
                    $flight->getArrivalTime()->format('d/m/Y H:i'),
                    $ticket->getTicketClass(),
                    $ticket->getSeatNumber(),
                    $ticket->getTicketPrice()
                ));

            $mailer->send($email);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while sending the email: ' . $e->getMessage());
        }

        return $this->render('payment/success.html.twig', [
            'amount' => $amount,
        ]);
    }



   #[Route('travel/flight/payment/cancel', name: 'flight_payment_cancel')]
    public function flightPaymentCancel(Request $req, ManagerRegistry $doctrine): Response
    {
        $ticketId = null;
        if ($req->request->has('ticket_id')) {
            $ticketId = $req->request->get('ticket_id');
        } elseif ($req->query->has('ticket_id')) {
            $ticketId = $req->query->get('ticket_id');
        }

        $entityManager = $doctrine->getManager();
        $ticket = $entityManager->getRepository(Tickets::class)->find($ticketId);

        if ($ticket) {
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('front_flight');
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

        $flight = $ticket->getIdFlight();
        if ($flight) {
            // Récupérer le numéro de siège
            $seatNumber = $ticket->getSeatNumber();

            $availableSeats = $flight->getAvailableSeats();
            if (!in_array($seatNumber, $availableSeats, true)) {
                $availableSeats[] = $seatNumber;
                $flight->setAvailableSeats($availableSeats);
            }


            $flight->setSeatsnumber($flight->getSeatsnumber() + 1);

            $entityManager->persist($flight);
        }

        $entityManager->remove($ticket);
        $entityManager->flush();

        $this->addFlash('success', 'Le ticket a été annulé avec succès.');

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