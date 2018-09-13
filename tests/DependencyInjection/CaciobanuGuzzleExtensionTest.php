<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Tests\DependencyInjection;

use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\CaciobanuGuzzleExtension;
use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\LoggingCompilerPass;
use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\MiddlewareCompilerPass;
use Caciobanu\Symfony\GuzzleBundle\Tests\DependencyInjection\Fixtures\TestClient;
use Caciobanu\Symfony\GuzzleBundle\Tests\Helper\MakeServicesPublicCompilerPass;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Caciobanu\Symfony\GuzzleBundle\CaciobanuGuzzleBundle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Caciobanu\Symfony\GuzzleBundle\DependencyInjection\CaciobanuGuzzleExtension
 */
class CaciobanuGuzzleExtensionTest extends TestCase
{
    public function testNoClients(): void
    {
        $container = $this->createContainer('no_clients');

        $this->assertTrue($container->hasDefinition('caciobanu_guzzle.handler_stack.abstract'));
        $this->assertEquals(
            HandlerStack::class,
            $container->getDefinition('caciobanu_guzzle.handler_stack.abstract')->getClass()
        );
        $this->assertEquals(
            [
                HandlerStack::class,
                'create',
            ],
            $container->getDefinition('caciobanu_guzzle.handler_stack.abstract')->getFactory()
        );

        $this->assertTrue($container->hasDefinition('caciobanu_guzzle.message_formatter.abstract'));
        $this->assertEquals(
            MessageFormatter::class,
            $container->getDefinition('caciobanu_guzzle.message_formatter.abstract')->getClass()
        );
        $this->assertEquals(
            [
                MessageFormatter::DEBUG,
            ],
            $container->getDefinition('caciobanu_guzzle.message_formatter.abstract')->getArguments()
        );

        $this->assertTrue($container->hasDefinition('caciobanu_guzzle.logger.abstract'));
        $this->assertEquals(
            \Closure::class,
            $container->getDefinition('caciobanu_guzzle.logger.abstract')->getClass()
        );
        $this->assertEquals(
            [
                new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                '',
                'info',
            ],
            $container->getDefinition('caciobanu_guzzle.logger.abstract')->getArguments()
        );
        $this->assertEquals(
            [
                Middleware::class,
                'log',
            ],
            $container->getDefinition('caciobanu_guzzle.logger.abstract')->getFactory()
        );
        $this->assertEquals(
            [
                'monolog.logger' => [0 => ['channel' => 'caciobanu_guzzle']],
            ],
            $container->getDefinition('caciobanu_guzzle.logger.abstract')->getTags()
        );
    }

    public function testOneClientMinimal(): void
    {
        $container = $this->createContainer('one_client_minimal');

        $this->assertTrue($container->hasDefinition('caciobanu_guzzle.client.default'));

        $definition = $container->getDefinition('caciobanu_guzzle.client.default');
        $this->assertEquals(Client::class, $definition->getClass());
        $this->assertTrue($definition->isPublic());

        $arguments = $definition->getArguments();
        $this->assertEquals('https://test.com/path/?query=1#fragment', $arguments[0]['base_uri']);

        $handlerDefinition = $arguments[0]['handler'];
        $this->assertEquals(HandlerStack::class, $handlerDefinition->getClass());
    }

    public function testOverrideClientClass(): void
    {
        $container = $this->createContainer('override_client_class');

        $this->assertEquals(TestClient::class, $container->getDefinition('caciobanu_guzzle.client.default')->getClass());
    }

    public function testOptions(): void
    {
        $container = $this->createContainer('options');
        $arguments = $container->getDefinition('caciobanu_guzzle.client.default')->getArguments();

        $this->assertEquals(30, $arguments[0]['timeout']);
        $this->assertEquals(['User-Agent' => 'test agent'], $arguments[0]['headers']);
    }

    public function testLogging(): void
    {
        $container = $this->createContainer('logging');

        // Check first client with logging.
        $definition = $container->getDefinition('caciobanu_guzzle.client.default');
        $arguments = $definition->getArguments();
        /** @var Definition $handlerDefinition */
        $handlerDefinition = $arguments[0]['handler'];

        $this->assertCount(1, $handlerDefinition->getTags());
        $this->assertTrue($handlerDefinition->hasTag('caciobanu_guzzle.loggable'));
        $this->assertEquals(
            [
                0 => ['client' => 'default'],
            ],
            $handlerDefinition->getTag('caciobanu_guzzle.loggable')
        );

        $this->assertTrue($container->hasDefinition('caciobanu_guzzle.message_formatter.default'));
        $this->assertEquals(
            MessageFormatter::class,
            $container->getDefinition('caciobanu_guzzle.message_formatter.default')->getClass()
        );
        $this->assertEquals(
            [
                MessageFormatter::DEBUG,
            ],
            $container->getDefinition('caciobanu_guzzle.message_formatter.default')->getArguments()
        );

        $this->assertTrue($container->hasDefinition('caciobanu_guzzle.logger.default'));
        $this->assertEquals(
            \Closure::class,
            $container->getDefinition('caciobanu_guzzle.logger.default')->getClass()
        );
        $this->assertEquals(
            [
                new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference('caciobanu_guzzle.message_formatter.default'),
                'info',
            ],
            $container->getDefinition('caciobanu_guzzle.logger.default')->getArguments()
        );
        $this->assertEquals(
            [
                Middleware::class,
                'log',
            ],
            $container->getDefinition('caciobanu_guzzle.logger.default')->getFactory()
        );

        // Check second client with no logging enabled.
        $definition = $container->getDefinition('caciobanu_guzzle.client.no_logging');
        $arguments = $definition->getArguments();
        /** @var Definition $handlerDefinition */
        $handlerDefinition = $arguments[0]['handler'];

        $this->assertCount(0, $handlerDefinition->getTags());
        $this->assertFalse($handlerDefinition->hasTag('caciobanu_guzzle.loggable'));
        $this->assertFalse($container->hasDefinition('caciobanu_guzzle.message_formatter.no_logging'));
        $this->assertFalse($container->hasDefinition('caciobanu_guzzle.logger.no_logging'));
    }

    private function createContainer(string $configFile): ContainerBuilder
    {
        $container = new ContainerBuilder(new ParameterBag(array_merge([
            'kernel.bundles' => ['CaciobanuGuzzleBundle' => CaciobanuGuzzleBundle::class],
        ])));
        $container->registerExtension(new CaciobanuGuzzleExtension());
        $container->addCompilerPass(new LoggingCompilerPass());
        $container->addCompilerPass(new MiddlewareCompilerPass());
        $container->addCompilerPass(new MakeServicesPublicCompilerPass());

        $container->register('logger', TestClient::class);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Fixtures/yml'));
        $loader->load($configFile . '.yml');

        $container->compile();

        return $container;
    }
}
