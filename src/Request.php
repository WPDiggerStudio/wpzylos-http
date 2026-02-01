<?php

declare(strict_types=1);

namespace WPZylos\Framework\Http;

use WPZylos\Framework\Core\Contracts\ContextInterface;

/**
 * HTTP Request wrapper.
 *
 * Wraps $_GET, $_POST, $_FILES, $_SERVER with sanitization helpers.
 * Automatically unslashes input (WordPress adds slashes).
 *
 * @package WPZylos\Framework\Http
 */
final class Request
{
    /**
     * @var array<string, mixed> Query parameters (GET)
     */
    private array $query;

    /**
     * @var array<string, mixed> Request body (POST)
     */
    private array $request;

    /**
     * @var array<string, array<string, mixed>> Uploaded files
     */
    private array $files;

    /**
     * @var array<string, mixed> Server variables
     */
    private array $server;

    /**
     * @var array<string, string> Request headers
     */
    private array $headers;

    /**
     * @var ContextInterface|null Plugin context
     */
    private ?ContextInterface $context;

    /**
     * Create request.
     *
     * @param array<string, mixed> $query Query parameters
     * @param array<string, mixed> $request Request body
     * @param array<string, array<string, mixed>> $files Uploaded files
     * @param array<string, mixed> $server Server variables
     * @param ContextInterface|null $context Plugin context
     */
    public function __construct(
        array $query = [],
        array $request = [],
        array $files = [],
        array $server = [],
        ?ContextInterface $context = null
    ) {
        $this->query = $query;
        $this->request = $request;
        $this->files = $files;
        $this->server = $server;
        $this->context = $context;
        $this->headers = $this->extractHeaders($server);
    }

    /**
     * Capture the current request.
     *
     * @param ContextInterface|null $context Plugin context
     * @return self
     */
    public static function capture(?ContextInterface $context = null): self
    {
        // Unslash WordPress-added slashes
        return new self(
            wp_unslash($_GET),
            wp_unslash($_POST),
            $_FILES,
            $_SERVER,
            $context
        );
    }

    /**
     * Get input value from a query or body.
     *
     * @param string $key Input key
     * @param mixed $default Default value
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get all input.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->request);
    }

    /**
     * Get only specified keys.
     *
     * @param string[] $keys Keys to get
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    /**
     * Get all except specified keys.
     *
     * @param string[] $keys Keys to exclude
     * @return array<string, mixed>
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    /**
     * Check if the key exists.
     *
     * @param string $key Input key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->request[$key]) || isset($this->query[$key]);
    }

    /**
     * Get query parameter.
     *
     * @param string $key Query key
     * @param mixed $default Default value
     * @return mixed
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get post-parameter.
     *
     * @param string $key Post key
     * @param mixed $default Default value
     * @return mixed
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }

    // =========================================================================
    // Sanitized Input Helpers (per-field sanitization)
    // =========================================================================

    /**
     * Get sanitized text input.
     *
     * @param string $key Input key
     * @param string $default Default value
     * @return string
     */
    public function text(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);
        return sanitize_text_field((string) $value);
    }

    /**
     * Get sanitized textarea input.
     *
     * @param string $key Input key
     * @param string $default Default value
     * @return string
     */
    public function textarea(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);
        return sanitize_textarea_field((string) $value);
    }

    /**
     * Get sanitized email input.
     *
     * @param string $key Input key
     * @param string $default Default value
     * @return string
     */
    public function email(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);
        return sanitize_email((string) $value);
    }

    /**
     * Get sanitized URL input.
     *
     * @param string $key Input key
     * @param string $default Default value
     * @return string
     */
    public function url(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);
        return esc_url_raw((string) $value);
    }

    /**
     * Get integer input.
     *
     * @param string $key Input key
     * @param int $default Default value
     * @return int
     */
    public function int(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    /**
     * Get absolute integer input.
     *
     * @param string $key Input key
     * @param int $default Default value
     * @return int
     */
    public function absint(string $key, int $default = 0): int
    {
        return absint($this->input($key, $default));
    }

    /**
     * Get float input.
     *
     * @param string $key Input key
     * @param float $default Default value
     * @return float
     */
    public function float(string $key, float $default = 0.0): float
    {
        return (float) $this->input($key, $default);
    }

    /**
     * Get boolean input.
     *
     * @param string $key Input key
     * @param bool $default Default value
     * @return bool
     */
    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->input($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get sanitized HTML input (post content level).
     *
     * @param string $key Input key
     * @param string $default Default value
     * @return string
     */
    public function html(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);
        return wp_kses_post((string) $value);
    }

    /**
     * Get array input.
     *
     * @param string $key Input key
     * @param array $default Default value
     * @return array
     */
    public function array(string $key, array $default = []): array
    {
        $value = $this->input($key, $default);
        return is_array($value) ? $value : $default;
    }

    // =========================================================================
    // Request Info
    // =========================================================================

    /**
     * Get HTTP method.
     *
     * @return string
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Check if the method matches.
     *
     * @param string $method Method to check
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    /**
     * Check if POST request.
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Check if GET request.
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    /**
     * Check if AJAX request.
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return ($this->header('X-Requested-With') === 'XMLHttpRequest')
            || wp_doing_ajax();
    }

    /**
     * Get request URI.
     *
     * @return string
     */
    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Get path without query string.
     *
     * @return string
     */
    public function path(): string
    {
        $uri = $this->uri();
        $pos = strpos($uri, '?');
        return $pos !== false ? substr($uri, 0, $pos) : $uri;
    }

    /**
     * Get header value.
     *
     * @param string $name Header name
     * @param string|null $default Default value
     * @return string|null
     */
    public function header(string $name, ?string $default = null): ?string
    {
        $name = strtoupper(str_replace('-', '_', $name));
        return $this->headers[$name] ?? $default;
    }

    /**
     * Gets an uploaded file.
     *
     * @param string $key File input name
     * @return array<string, mixed>|null
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get IP address.
     *
     * @return string
     */
    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Get plugin context.
     *
     * @return ContextInterface|null
     */
    public function context(): ?ContextInterface
    {
        return $this->context;
    }

    /**
     * Extract headers from a server array.
     *
     * @param array<string, mixed> $server Server variables
     * @return array<string, string>
     */
    private function extractHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = substr($key, 5);
                $headers[$name] = (string) $value;
            }
        }

        return $headers;
    }
}
