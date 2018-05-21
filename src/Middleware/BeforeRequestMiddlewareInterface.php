<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Middleware;

use Psr\Http\Message\RequestInterface;

interface BeforeRequestMiddlewareInterface extends MiddlewareInterface
{
    public function __invoke(RequestInterface $request): RequestInterface;
}
