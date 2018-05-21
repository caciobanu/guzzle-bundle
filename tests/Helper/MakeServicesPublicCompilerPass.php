<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Tests\Helper;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakeServicesPublicCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
            $definition->setPrivate(false);
            $definition->setAbstract(false);
        }
    }
}
