<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface AfterResponseMiddlewareInterface extends MiddlewareInterface
{
    public function __invoke(ResponseInterface $response, RequestInterface $request): ResponseInterface;
}
