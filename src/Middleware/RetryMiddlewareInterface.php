<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Middleware;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RetryMiddlewareInterface extends MiddlewareInterface
{
    public function __invoke(
        int $retries,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?RequestException $exception = null
    ): bool;
}
