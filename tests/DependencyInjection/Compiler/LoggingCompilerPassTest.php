<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Tests\DependencyInjection\Compiler;

use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\LoggingCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Tests\Fixtures\TestClient;

/**
 * @covers \Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\LoggingCompilerPass
 */
class LoggingCompilerPassTest extends TestCase
{
    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    public function testNoLogger(): void
    {
        $container = new ContainerBuilder();
        $container->register('test', TestClient::class)
            ->addTag('caciobanu_guzzle.loggable');

        (new LoggingCompilerPass())->process($container);
    }

    public function testLogger(): void
    {
        $container = new ContainerBuilder();
        $container->register('test', TestClient::class)
            ->addTag('caciobanu_guzzle.loggable', ['client' => 'test']);

        $container->register('monolog.logger', TestClient::class);

        (new LoggingCompilerPass())->process($container);

        // Check handler definition
        $methodCalls = $container->getDefinition('test')->getMethodCalls();

        $this->assertCount(1, $methodCalls);
        $this->assertEquals('push', $methodCalls[0][0]);
        $this->assertInstanceOf(Reference::class, $methodCalls[0][1][0]);
        $this->assertEquals('caciobanu_guzzle.logger.test', (string) $methodCalls[0][1][0]);
        $this->assertEquals('caciobanu_logging', $methodCalls[0][1][1]);
    }
}
