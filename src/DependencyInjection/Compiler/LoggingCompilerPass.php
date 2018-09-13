<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class LoggingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds('caciobanu_guzzle.loggable');

        if ((\count($taggedServices) > 0) && !$container->hasDefinition('monolog.logger')) {
            throw new LogicException(
                'Logging functionality is available if a "monolog.logger" service implementing "Psr\Log\LoggerInterface"'
                . 'is provided or "symfony/monolog-bundle" is installed.'
            );
        }

        foreach ($taggedServices as $id => $tags) {
            $handler = $container->getDefinition($id);
            $handler->addMethodCall(
                'push',
                [
                    new Reference(sprintf('caciobanu_guzzle.logger.%s', $tags[0]['client'])),
                    'caciobanu_logging',
                ]
            );
        }
    }
}
