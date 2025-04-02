<?php
namespace App\Entity\Enum\Type;

use App\Entity\Enum\User\Gender;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class UserGenderType extends Type
{
    public const NAME = 'user_gender';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "ENUM('Homme','Femme','Autre')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Gender
    {
        return $value !== null ? Gender::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof Gender ? $value->value : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}