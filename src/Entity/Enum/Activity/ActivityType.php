<?php
namespace App\Entity\Enum\Activity;

enum ActivityType: string
{
    case MEETINGS = 'MEETINGS';
    case CONFERENCES = 'CONFERENCES';
    case WORKSHOPS = 'WORKSHOPS';
    case TEAM_BUILDING_ACTIVITIES = 'TEAM_BUILDING_ACTIVITIES';
    case LEISURE = 'LEISURE';


    public function toString(): string
    {
        return $this->value;
    }
}