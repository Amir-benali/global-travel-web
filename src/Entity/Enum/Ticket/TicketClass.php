<?php
namespace App\Entity\Enum\Ticket;

enum TicketClass: string
{
    case ECONOMY = 'Economy';
    case BUSINESS = 'Business';
    case FIRST_CLASS = 'First_Class';
}