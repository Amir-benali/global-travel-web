<?php
namespace App\Entity\Enum\User;

enum Status: string
{
    case ACTIF = 'Actif';
    case INACTIF = 'Inactif';
    case SUSPENDU = 'Suspendu';
}