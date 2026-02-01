<?php

declare(strict_types=1);

namespace WPZylos\Framework\Http;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use WPZylos\Framework\Http\Contracts\MiddlewareInterface;

/**
 * Middleware pipeline.
 *
 * Executes middleware in sequence, passing a request through each.
 *
 * @package WPZylos\Framework\Http
 */
class Pipeline
{
    /**
     * @var ContainerInterface DI container for resolving middleware
     */
    private ContainerInterface $container;

    /**
     * @var array<string|MiddlewareInterface|callable> Middleware stack
     */
    private array $middleware = [];

    /**
     * @var Request|null Request being processed
     */
    private ?Request $request = null;

    /**
     * Create pipeline.
     *
     * @param ContainerInterface $container DI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Set the request to send through.
     *
     * @param Request $request Request object
     *
     * @return static
     */
    public function send(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Set middleware to pipe through.
     *
     * @param array<string|MiddlewareInterface|callable> $middleware
     *
     * @return static
     */
    public function through(array $middleware): static
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * Run the pipeline with a final destination.
     *
     * @param callable $destination Final handler (receives Request, returns Response)
     *
     * @return Response
     * @throws \JsonException
     */
    public function then(callable $destination): Response
    {
        if ($this->request === null) {
            throw new RuntimeException('No request set on pipeline.');
        }

        $pipeline = array_reduce(
            array_reverse($this->middleware),
            $this->carry(),
            fn(Request $request) => $this->prepareResponse($destination($request))
        );

        return $pipeline($this->request);
    }

    /**
     * Create the middleware carry function.
     *
     * @return callable
     */
    private function carry(): callable
    {
        return function (callable $next, mixed $middleware): callable {
            return function (Request $request) use ($next, $middleware): Response {
                $instance = $this->resolveMiddleware($middleware);

                if ($instance instanceof MiddlewareInterface) {
                    return $instance->handle($request, $next);
                }

                // Callable middleware (guaranteed by resolveMiddleware return type)
                return $this->prepareResponse($instance($request, $next));
            };
        };
    }

    /**
     * Resolve middleware to an instance.
     *
     * @param mixed $middleware Middleware identifier
     *
     * @return MiddlewareInterface|callable
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function resolveMiddleware(mixed $middleware): MiddlewareInterface|callable
    {
        if ($middleware instanceof MiddlewareInterface || is_callable($middleware)) {
            return $middleware;
        }

        if (is_string($middleware)) {
            return $this->container->get($middleware);
        }

        throw new InvalidArgumentException('Cannot resolve middleware.');
    }

    /**
     * Prepare handler return value as Response.
     *
     * @param mixed $response Handler result
     *
     * @return Response
     * @throws \JsonException
     */
    private function prepareResponse(mixed $response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        if (is_string($response)) {
            return Response::html($response);
        }

        if (is_array($response)) {
            return Response::json($response);
        }

        if ($response === null) {
            return Response::empty();
        }

        return Response::html((string) $response);
    }
}
