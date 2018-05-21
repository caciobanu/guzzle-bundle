<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
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
            $clientName = $tags[0]['client'];

            $handler = $container->getDefinition($id);

            $formatterDefinition = new ChildDefinition('caciobanu_guzzle.message_formatter.abstract');
            $container->setDefinition(
                sprintf('caciobanu_guzzle.message_formatter.%s', $clientName),
                $formatterDefinition
            );

            $loggerDefinition = new ChildDefinition('caciobanu_guzzle.logger.abstract');
            $loggerDefinition->replaceArgument(
                1,
                new Reference(sprintf('caciobanu_guzzle.message_formatter.%s', $clientName))
            );
            $container->setDefinition(
                sprintf('caciobanu_guzzle.logger.%s', $clientName),
                $loggerDefinition
            );

            $handler->addMethodCall(
                'push',
                [
                    new Reference(sprintf('caciobanu_guzzle.logger.%s', $clientName)),
                    'caciobanu_logging',
                ]
            );
        }
    }
}
