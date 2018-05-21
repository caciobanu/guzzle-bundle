<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CaciobanuGuzzleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configs = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.xml');

        foreach ($configs['clients'] as $name => $config) {
            $handlerDefinition = $this->createHandler($container, $name, $config['logging']);

            $arguments = [
                'base_uri' => $config['base_uri'],
                'handler'  => $handlerDefinition,
            ];

            if (isset($config['options']) && \is_array($config['options'])) {
                foreach ($config['options'] as $key => $value) {
                    $arguments[$key] = $value;
                }
            }

            $clientDefinition = new Definition($config['client_class'], [$arguments]);
            $container->setDefinition(
                sprintf('caciobanu_guzzle.client.%s', $name),
                $clientDefinition
            )->setPublic(true);
        }
    }

    private function createHandler(ContainerBuilder $container, string $clientName, bool $logging): Definition
    {
        $handlerDefinition = new ChildDefinition('caciobanu_guzzle.handler_stack.abstract');

        if ($logging) {
            $handlerDefinition->addTag('caciobanu_guzzle.loggable', ['client' => $clientName]);
            $handlerDefinition->setPublic(true);
        }

        $container->setDefinition(
            sprintf('caciobanu_guzzle.handler.%s', $clientName),
            $handlerDefinition
        );


        return $handlerDefinition;
    }
}
