<?php

declare(strict_types=1);

namespace WPZylos\Framework\Http;

/**
 * HTTP Response.
 *
 * Simple response object for sending output.
 * WP-friendly: doesn't assume PSR-7 or Symfony.
 *
 * @package WPZylos\Framework\Http
 */
final class Response
{
    /**
     * @var string Response body
     */
    private string $content;

    /**
     * @var int HTTP status code
     */
    private int $statusCode;

    /**
     * @var array<string, string> Response headers
     */
    private array $headers;

    /**
     * Create response.
     *
     * @param string $content Response body
     * @param int $statusCode HTTP status code
     * @param array<string, string> $headers Response headers
     */
    public function __construct(
        string $content = '',
        int $statusCode = 200,
        array $headers = []
    ) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Create HTML response.
     *
     * @param string $html HTML content
     * @param int $status HTTP status code
     * @return self
     */
    public static function html(string $html, int $status = 200): self
    {
        return new self($html, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * Create JSON response.
     *
     * @param mixed $data Data to encode
     * @param int $status HTTP status code
     *
     * @return self
     * @throws \JsonException
     */
    public static function json(mixed $data, int $status = 200): self
    {
        $content = (string) json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return new self($content, $status, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * Create a redirect response.
     *
     * @param string $url Redirect URL
     * @param int $status HTTP status code
     * @return self
     */
    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => esc_url_raw($url)]);
    }

    /**
     * Create an empty response.
     *
     * @param int $status HTTP status code
     * @return self
     */
    public static function empty(int $status = 204): self
    {
        return new self('', $status);
    }

    /**
     * Create error response.
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     *
     * @return self
     * @throws \JsonException
     */
    public static function error(string $message, int $status = 500): self
    {
        return self::json(['error' => $message], $status);
    }

    /**
     * Get response content.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set response content.
     *
     * @param string $content Response body
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set the status code.
     *
     * @param int $statusCode HTTP status code
     * @return self
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get headers.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set a header.
     *
     * @param string $name Header name
     * @param string $value Header value
     * @return self
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Send a response to a client.
     *
     * @return void
     */
    public function send(): void
    {
        // Don't send headers if already sent
        if (!headers_sent()) {
            http_response_code($this->statusCode);

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        echo $this->content;
    }

    /**
     * Send and exit.
     *
     * @return void
     */
    public function sendAndExit(): void
    {
        $this->send();
        exit;
    }
}
