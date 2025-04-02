<?php
namespace App\Entity\Enum\Type;

use App\Entity\Enum\Ticket\TicketStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TicketStatusType extends Type
{
    public const NAME = 'ticket_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "ENUM('Booked','Not_Booked','Cancelled')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TicketStatus
    {
        return $value !== null ? TicketStatus::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof TicketStatus ? $value->value : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}