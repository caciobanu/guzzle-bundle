<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Tests\DependencyInjection\Compiler;

use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\MiddlewareCompilerPass;
use Caciobanu\Symfony\GuzzleBundle\Middleware\MiddlewareToCallable;
use Caciobanu\Symfony\GuzzleBundle\Tests\DependencyInjection\Fixtures\TestClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\MiddlewareCompilerPass
 */
class MiddlewareCompilerPassTest extends TestCase
{
    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    public function testMissingProperty()
    {
        $container = new ContainerBuilder();
        $container->register('test', TestClient::class)
            ->addTag('caciobanu_guzzle.middleware');

        (new MiddlewareCompilerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testMissingClient()
    {
        $container = new ContainerBuilder();
        $container->register('test', TestClient::class)
            ->addTag('caciobanu_guzzle.middleware', ['client' => 'test2']);

        (new MiddlewareCompilerPass())->process($container);
    }

    public function testMiddleware()
    {
        $container = new ContainerBuilder();
        $container->register('caciobanu_guzzle.handler.test', TestClient::class)
            ->addTag('caciobanu_guzzle.middleware', ['client' => 'test']);

        (new MiddlewareCompilerPass())->process($container);

        // Check closure definiton
        $closureDefId = sprintf(
            'caciobanu_guzzle.handler.%s.middleware.%s',
            'test',
            TestClient::class
        );

        $this->assertTrue($container->has($closureDefId));

        $closureDef = $container->getDefinition($closureDefId);
        $this->assertInstanceOf(Definition::class, $closureDef);
        $this->assertEquals(\Closure::class, $closureDef->getClass());
        $this->assertCount(2, $closureDef->getFactory());
        $this->assertEquals(MiddlewareToCallable::class, $closureDef->getFactory()[0]);
        $this->assertEquals('toCallable', $closureDef->getFactory()[1]);
        $this->assertCount(1, $closureDef->getArguments());
        $this->assertInstanceOf(Reference::class, $closureDef->getArgument(0));
        $this->assertEquals('caciobanu_guzzle.handler.test', (string) $closureDef->getArgument(0));

        // Check handler definition
        $methodCalls = $container->getDefinition('caciobanu_guzzle.handler.test')->getMethodCalls();

        $this->assertCount(1, $methodCalls);
        $this->assertEquals('push', $methodCalls[0][0]);
        $this->assertInstanceOf(Reference::class, $methodCalls[0][1][0]);
        $this->assertEquals($closureDefId, (string) $methodCalls[0][1][0]);
        $this->assertEquals('caciobanu_guzzle.handler.test', $methodCalls[0][1][1]);
    }
}
