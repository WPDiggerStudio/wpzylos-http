<?php

declare(strict_types=1);

namespace WPZylos\Framework\Http\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Core\Contracts\ContextInterface;
use WPZylos\Framework\Http\Request;

/**
 * Tests for Request class.
 */
class RequestTest extends TestCase
{
    public function testTextSanitizesInput(): void
    {
        $_POST['name'] = '<script>alert(1)</script>John';

        $context = $this->createMock(ContextInterface::class);
        $request = Request::capture($context);

        $name = $request->text('name');

        $this->assertStringNotContainsString('<script>', $name);
        $this->assertStringContainsString('John', $name);
    }

    public function testIntReturnsInteger(): void
    {
        $_POST['age'] = '25';

        $context = $this->createMock(ContextInterface::class);
        $request = Request::capture($context);

        $age = $request->int('age');

        $this->assertSame(25, $age);
    }

    public function testBoolReturnsBool(): void
    {
        $_POST['active'] = '1';

        $context = $this->createMock(ContextInterface::class);
        $request = Request::capture($context);

        $active = $request->bool('active');

        $this->assertTrue($active);
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        $_POST['name'] = 'John';

        $context = $this->createMock(ContextInterface::class);
        $request = Request::capture($context);

        $this->assertTrue($request->has('name'));
    }

    public function testHasReturnsFalseForMissingKey(): void
    {
        $context = $this->createMock(ContextInterface::class);
        $request = Request::capture($context);

        $this->assertFalse($request->has('nonexistent'));
    }

    public function testOnlyReturnsSubset(): void
    {
        $_POST = ['a' => 1, 'b' => 2, 'c' => 3];

        $context = $this->createMock(ContextInterface::class);
        $request = Request::capture($context);

        $only = $request->only(['a', 'c']);

        $this->assertArrayHasKey('a', $only);
        $this->assertArrayHasKey('c', $only);
        $this->assertArrayNotHasKey('b', $only);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_GET = [];
        $_REQUEST = [];
    }
}
