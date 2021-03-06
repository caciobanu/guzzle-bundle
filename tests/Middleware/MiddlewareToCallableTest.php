<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Tests\Middleware;

use Caciobanu\Symfony\GuzzleBundle\Middleware\MiddlewareInterface;
use Caciobanu\Symfony\GuzzleBundle\Middleware\MiddlewareToCallable;
use Caciobanu\Symfony\GuzzleBundle\Middleware\BeforeRequestMiddlewareInterface;
use Caciobanu\Symfony\GuzzleBundle\Middleware\OnErrorMiddlewareInterface;
use Caciobanu\Symfony\GuzzleBundle\Middleware\AfterResponseMiddlewareInterface;
use Caciobanu\Symfony\GuzzleBundle\Middleware\RetryMiddlewareInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers \Caciobanu\Symfony\GuzzleBundle\Middleware\MiddlewareToCallable
 */
class MiddlewareToCallableTest extends TestCase
{
    /**
     * @expectedException \GuzzleHttp\Exception\RequestException
     */
    public function testBeforeRequestMiddleware(): void
    {
        $middleware = $this->getMockBuilder(BeforeRequestMiddlewareInterface::class)
            ->getMock();

        $middleware->expects($this->exactly(2))
            ->method('__invoke');

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200),
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $handler->push(MiddlewareToCallable::toCallable($middleware));

        $client->request('GET', '/');
        $client->request('GET', '/');
    }

    /**
     * @expectedException \GuzzleHttp\Exception\RequestException
     */
    public function testAfterResponseMiddleware(): void
    {
        $middleware = $this->getMockBuilder(AfterResponseMiddlewareInterface::class)
            ->getMock();

        $middleware->expects($this->exactly(1))
            ->method('__invoke');

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200),
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $handler->push(MiddlewareToCallable::toCallable($middleware));

        $client->request('GET', '/');
        $client->request('GET', '/');
    }

    /**
     * @expectedException \GuzzleHttp\Exception\RequestException
     */
    public function testErrorMiddleware(): void
    {
        $middleware = $this->getMockBuilder(OnErrorMiddlewareInterface::class)
            ->getMock();

        $middleware->expects($this->exactly(1))
            ->method('__invoke');
//            ->willReturnArgument(1);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200),
            new RequestException("Error Communicating with Server", new Request('GET', '/')),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $handler->push(MiddlewareToCallable::toCallable($middleware));

        $client->request('GET', '/');
        $client->request('GET', '/');
    }

    public function testRetryMiddleware(): void
    {
        $middleware = new class implements RetryMiddlewareInterface {
            public function __invoke(
                int $retries,
                RequestInterface $request,
                ?ResponseInterface $response = null,
                ?RequestException $exception = null
            ): bool {
                if (null !== $exception) {
                    return true;
                }

                if (null !== $response && 500 === $response->getStatusCode()) {
                    return true;
                }

                return false;
            }
        };

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new RequestException("Error Communicating with Server", new Request('GET', '/')),
            new Response(500),
            new Response(200),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $handler->push(MiddlewareToCallable::toCallable($middleware));

        $response = $client->request('GET', '/');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidMiddleware(): void
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)
            ->getMock();

        MiddlewareToCallable::toCallable($middleware);
    }
}
