<?php
namespace App\Entity\Enum\Type;

use Doctrine\DBAL\Types\Type;

class CustomTypeRegistry
{
    public static function register(): void
    {
        Type::addType('activity_type', ActivityTypeType::class);
        Type::addType('car_reservation_status', CarReservationStatusType::class);
        Type::addType('flight_status', FlightStatusType::class);
        Type::addType('ticket_class', TicketClassType::class);
        Type::addType('ticket_status', TicketStatusType::class);
        Type::addType('user_gender', UserGenderType::class);
        Type::addType('user_status', UserStatusType::class);
    }
}