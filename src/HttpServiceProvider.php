<?php

declare(strict_types=1);

namespace WPZylos\Framework\Http;

use WPZylos\Framework\Core\Contracts\ApplicationInterface;
use WPZylos\Framework\Core\ServiceProvider;

/**
 * HTTP service provider.
 *
 * Registers Request and Pipeline with the container.
 *
 * @package WPZylos\Framework\Http
 */
class HttpServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(ApplicationInterface $app): void
    {
        parent::register($app);

        // Singleton request for current HTTP request
        $this->singleton(Request::class, fn() => Request::capture($app->context()));
        $this->singleton('request', fn() => $this->make(Request::class));

        // Pipeline factory (new instance each time)
        $this->bind(Pipeline::class, fn() => new Pipeline($app->container()));
    }
}
