<?php
namespace App\Entity\Enum\Type;

use App\Entity\Enum\Reservation\CarReservationStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class CarReservationStatusType extends Type
{
    public const NAME = 'car_reservation_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "ENUM('PENDING','FAILED','CONFIRMED','CANCELED')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CarReservationStatus
    {
        return $value !== null ? CarReservationStatus::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof CarReservationStatus ? $value->value : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}