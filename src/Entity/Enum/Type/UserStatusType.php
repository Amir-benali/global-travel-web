<?php
namespace App\Entity\Enum\Type;

use App\Entity\Enum\User\Status;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class UserStatusType extends Type
{
    public const NAME = 'user_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "ENUM('Actif','Inactif','Suspendu')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Status
    {
        return $value !== null ? Status::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof Status ? $value->value : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}