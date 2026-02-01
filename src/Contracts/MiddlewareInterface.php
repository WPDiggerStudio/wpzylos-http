<?php

declare(strict_types=1);

namespace WPZylos\Framework\Http\Contracts;

use WPZylos\Framework\Http\Request;
use WPZylos\Framework\Http\Response;

/**
 * Middleware interface.
 *
 * @package WPZylos\Framework\Http
 */
interface MiddlewareInterface
{
    /**
     * Handle the request.
     *
     * @param Request $request Current request
     * @param callable $next Next middleware in a pipeline
     * @return Response
     */
    public function handle(Request $request, callable $next): Response;
}
