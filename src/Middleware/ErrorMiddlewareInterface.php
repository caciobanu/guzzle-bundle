<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ErrorMiddlewareInterface extends MiddlewareInterface
{
    public function __invoke(
        RequestInterface $request,
        \Exception $exception,
        ?ResponseInterface $response = null
    ): ?ResponseInterface;
}
