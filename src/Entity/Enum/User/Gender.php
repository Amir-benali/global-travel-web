<?php
namespace App\Entity\Enum\User;

enum Gender: string
{
    case HOMME = 'Homme';
    case FEMME = 'Femme';
    case AUTRE = 'Autre';
}