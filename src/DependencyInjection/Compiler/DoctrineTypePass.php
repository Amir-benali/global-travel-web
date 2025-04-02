<?php
// src/DependencyInjection/Compiler/DoctrineTypePass.php
namespace App\DependencyInjection\Compiler;

use App\Entity\Enum\Type\CustomTypeRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineTypePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        CustomTypeRegistry::register(); // Fixed this line
    }
}