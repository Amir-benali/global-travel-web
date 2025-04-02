<?php
namespace App\Entity\Enum\Reservation;

enum CarReservationStatus: string
{
    case PENDING = 'PENDING';
    case FAILED = 'FAILED';
    case CONFIRMED = 'CONFIRMED';
    case CANCELED = 'CANCELED';
}