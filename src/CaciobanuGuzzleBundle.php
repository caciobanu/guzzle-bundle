<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle;

use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\LoggingCompilerPass;
use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\MiddlewareCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CaciobanuGuzzleBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new LoggingCompilerPass());
        $container->addCompilerPass(new MiddlewareCompilerPass());
    }
}
