<?php
namespace App\Entity\Enum\Type;

use App\Entity\Enum\Activity\ActivityType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ActivityTypeType extends Type
{
    public const NAME = 'activity_type';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "ENUM('MEETINGS','CONFERENCES','WORKSHOPS','TEAM_BUILDING_ACTIVITIES','LEISURE')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ActivityType
    {
        return $value !== null ? ActivityType::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof ActivityType ? $value->value : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}