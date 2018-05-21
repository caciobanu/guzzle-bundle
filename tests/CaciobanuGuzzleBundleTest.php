<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Tests;

use Caciobanu\Symfony\GuzzleBundle\CaciobanuGuzzleBundle;
use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\LoggingCompilerPass;
use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Compiler\MiddlewareCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Caciobanu\Symfony\GuzzleBundle\CaciobanuGuzzleBundle
 */
class CaciobanuGuzzleBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $mock = $this->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['addCompilerPass'])
            ->getMock();

        $mock->expects($this->exactly(2))
            ->method('addCompilerPass')
            ->withConsecutive(new LoggingCompilerPass(), new MiddlewareCompilerPass());

        $bundle = new CaciobanuGuzzleBundle();
        $bundle->build($mock);
    }
}
