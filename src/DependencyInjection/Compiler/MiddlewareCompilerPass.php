<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler;

use Caciobanu\Symfony\GuzzleBundle\Middleware\MiddlewareToCallable;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class MiddlewareCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds('caciobanu_guzzle.middleware');

        foreach ($taggedServices as $id => $tags) {
            if (!isset($tags[0]['client'])) {
                throw new LogicException('Missing tag property "client".');
            }

            $clientName = $tags[0]['client'];

            $closureBuilderDefinition = new Definition('Closure');
            $closureBuilderDefinition->setFactory([
                MiddlewareToCallable::class,
                'toCallable',
            ]);
            $closureBuilderDefinition->setArguments([new Reference($id)]);
            $closureBuilderDefinitionId = sprintf(
                'caciobanu_guzzle.handler.%s.middleware.%s',
                $clientName,
                $container->getDefinition($id)->getClass()
            );

            $container->setDefinition(
                $closureBuilderDefinitionId,
                $closureBuilderDefinition
            );

            $handler = $container->getDefinition(sprintf('caciobanu_guzzle.handler.%s', $clientName));

            $handler->addMethodCall(
                'push',
                [
                    new Reference($closureBuilderDefinitionId),
                    $id,
                ]
            );
        }
    }
}
