<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Middleware;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;

class MiddlewareToCallable
{
    public static function toCallable(MiddlewareInterface $middleware): callable
    {
        switch (true) {
            case ($middleware instanceof BeforeRequestMiddlewareInterface):
                return function (callable $handler) use ($middleware) {
                    return function ($request, array $options) use ($handler, $middleware) {
                        return $handler($middleware($request), $options);
                    };
                };
            case ($middleware instanceof AfterResponseMiddlewareInterface):
                return function (callable $handler) use ($middleware) {
                    return function ($request, array $options) use ($handler, $middleware) {
                        return $handler($request, $options)->then(
                            function ($response) use ($request, $middleware) {
                                return $middleware($response, $request);
                            }
                        );
                    };
                };
            case ($middleware instanceof OnErrorMiddlewareInterface):
                return function (callable $handler) use ($middleware) {
                    return function ($request, array $options) use ($handler, $middleware) {
                        return $handler($request, $options)->then(
                            function ($response) {
                                return $response;
                            },
                            function ($reason) use ($request, $middleware) {
                                $response = $reason instanceof RequestException
                                    ? $reason->getResponse()
                                    : null;
                                $middleware($request, $reason, $response);
                                return \GuzzleHttp\Promise\rejection_for($reason);
                            }
                        );
                    };
                };
            case ($middleware instanceof RetryMiddlewareInterface):
                return Middleware::retry($middleware);
        }

        throw new \RuntimeException('Invalid middleware provided.');
    }
}
