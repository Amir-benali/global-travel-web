<?php
namespace App\Entity\Enum\Ticket;

enum TicketStatus: string
{
    case BOOKED = 'Booked';
    case NOT_BOOKED = 'Not_Booked';
    case CANCELLED = 'Cancelled';
}