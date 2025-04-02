<?php
namespace App\Entity\Enum\Type;

use App\Entity\Enum\Flight\FlightStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class FlightStatusType extends Type
{
    public const NAME = 'flight_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "ENUM('Scheduled','Delayed','Cancelled','Completed')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?FlightStatus
    {
        return $value !== null ? FlightStatus::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof FlightStatus ? $value->value : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}