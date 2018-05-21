<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Tests\DependencyInjection\Compiler;

use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\LoggingCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
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

        // Check formatter definition
        $this->assertTrue($container->has('caciobanu_guzzle.message_formatter.test'));

        /** @var ChildDefinition $formatterDef */
        $formatterDef = $container->getDefinition('caciobanu_guzzle.message_formatter.test');
        $this->assertInstanceOf(ChildDefinition::class, $formatterDef);
        $this->assertEquals('caciobanu_guzzle.message_formatter.abstract', $formatterDef->getParent());


        // Check logger definition
        $this->assertTrue($container->has('caciobanu_guzzle.logger.test'));

        /** @var ChildDefinition $loggerDef */
        $loggerDef = $container->getDefinition('caciobanu_guzzle.logger.test');
        $this->assertInstanceOf(ChildDefinition::class, $loggerDef);
        $this->assertEquals('caciobanu_guzzle.logger.abstract', $loggerDef->getParent());

        $this->assertCount(1, $loggerDef->getArguments());
        $this->assertInstanceOf(Reference::class, $loggerDef->getArgument(1));
        $this->assertEquals('caciobanu_guzzle.message_formatter.test', (string) $loggerDef->getArgument(1));

        // Check handler definition
        $methodCalls = $container->getDefinition('test')->getMethodCalls();

        $this->assertCount(1, $methodCalls);
        $this->assertEquals('push', $methodCalls[0][0]);
        $this->assertInstanceOf(Reference::class, $methodCalls[0][1][0]);
        $this->assertEquals('caciobanu_guzzle.logger.test', (string) $methodCalls[0][1][0]);
        $this->assertEquals('caciobanu_logging', $methodCalls[0][1][1]);
    }
}
