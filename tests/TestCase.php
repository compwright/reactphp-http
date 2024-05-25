<?php

namespace React\Tests\Http;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function expectCallableOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }

    protected function expectCallableOnceWith($value)
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($value);

        return $mock;
    }

    protected function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        return $mock;
    }

    protected function createCallableMock()
    {
        if (method_exists('PHPUnit\Framework\MockObject\MockBuilder', 'addMethods')) {
            // PHPUnit 9+
            return $this->getMockBuilder('stdClass')->addMethods(['__invoke'])->getMock();
        } else {
            // legacy PHPUnit 4 - PHPUnit 8
            return $this->getMockBuilder('stdClass')->setMethods(['__invoke'])->getMock();
        }
    }

    public function assertContainsString($needle, $haystack)
    {
        if (method_exists($this, 'assertStringContainsString')) {
            // PHPUnit 7.5+
            $this->assertStringContainsString($needle, $haystack);
        } else {
            // legacy PHPUnit 4 - PHPUnit 7.5
            $this->assertContains($needle, $haystack);
        }
    }

    public function assertNotContainsString($needle, $haystack)
    {
        if (method_exists($this, 'assertStringNotContainsString')) {
            // PHPUnit 7.5+
            $this->assertStringNotContainsString($needle, $haystack);
        } else {
            // legacy PHPUnit 4 - PHPUnit 7.5
            $this->assertNotContains($needle, $haystack);
        }
    }

    public function setExpectedException($exception, $exceptionMessage = '', $exceptionCode = null)
    {
        $this->expectException($exception);
        if ($exceptionMessage !== '') {
            $this->expectExceptionMessage($exceptionMessage);
        }
        if ($exceptionCode !== null) {
            $this->expectExceptionCode($exceptionCode);
        }
    }

}
