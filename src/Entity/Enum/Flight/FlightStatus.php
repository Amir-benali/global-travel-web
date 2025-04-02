<?php
namespace App\Entity\Enum\Flight;

enum FlightStatus: string
{
    case SCHEDULED = 'Scheduled';
    case DELAYED = 'Delayed';
    case CANCELLED = 'Cancelled';
    case COMPLETED = 'Completed';
}