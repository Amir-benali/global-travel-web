<?php
namespace App\Entity\Enum\Type;

use App\Entity\Enum\Ticket\TicketClass;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TicketClassType extends Type
{
    public const NAME = 'ticket_class';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "ENUM('Economy','Business','First_Class')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TicketClass
    {
        return $value !== null ? TicketClass::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof TicketClass ? $value->value : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}