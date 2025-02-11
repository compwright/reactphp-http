<?php

namespace React\Tests\Http\Io;

use React\Http\Io\ChunkedEncoder;
use React\Stream\ReadableStreamInterface;
use React\Stream\ThroughStream;
use React\Stream\WritableStreamInterface;
use React\Tests\Http\TestCase;

class ChunkedEncoderTest extends TestCase
{
    private $input;
    private $chunkedStream;

    /**
     * @before
     */
    public function setUpChunkedStream()
    {
        $this->input = new ThroughStream();
        $this->chunkedStream = new ChunkedEncoder($this->input);
    }

    public function testChunked()
    {
        $this->chunkedStream->on('data', $this->expectCallableOnceWith("5\r\nhello\r\n"));
        $this->input->emit('data', ['hello']);
    }

    public function testEmptyString()
    {
        $this->chunkedStream->on('data', $this->expectCallableNever());
        $this->input->emit('data', ['']);
    }

    public function testBiggerStringToCheckHexValue()
    {
        $this->chunkedStream->on('data', $this->expectCallableOnceWith("1a\r\nabcdefghijklmnopqrstuvwxyz\r\n"));
        $this->input->emit('data', ['abcdefghijklmnopqrstuvwxyz']);
    }

    public function testHandleClose()
    {
        $this->chunkedStream->on('close', $this->expectCallableOnce());

        $this->input->close();

        $this->assertFalse($this->chunkedStream->isReadable());
    }

    public function testHandleError()
    {
        $this->chunkedStream->on('error', $this->expectCallableOnce());
        $this->chunkedStream->on('close', $this->expectCallableOnce());

        $this->input->emit('error', [new \RuntimeException()]);

        $this->assertFalse($this->chunkedStream->isReadable());
    }

    public function testPauseStream()
    {
        $input = $this->createMock(ReadableStreamInterface::class);
        $input->expects($this->once())->method('pause');

        $parser = new ChunkedEncoder($input);
        $parser->pause();
    }

    public function testResumeStream()
    {
        $input = $this->createMock(ReadableStreamInterface::class);
        $input->expects($this->once())->method('pause');

        $parser = new ChunkedEncoder($input);
        $parser->pause();
        $parser->resume();
    }

    public function testPipeStream()
    {
        $dest = $this->createMock(WritableStreamInterface::class);

        $ret = $this->chunkedStream->pipe($dest);

        $this->assertSame($dest, $ret);
    }
}
