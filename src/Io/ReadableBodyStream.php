<?php

namespace React\Http\Io;

use Evenement\EventEmitter;
use Psr\Http\Message\StreamInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

/**
 * @internal
 */
class ReadableBodyStream extends EventEmitter implements ReadableStreamInterface, StreamInterface
{
    private $input;
    private $position = 0;
    private $size;
    private $closed = false;

    public function __construct(ReadableStreamInterface $input, $size = null)
    {
        $this->input = $input;
        $this->size = $size;

        $that = $this;
        $pos =& $this->position;
        $input->on('data', function ($data) use ($that, &$pos, $size) {
            $that->emit('data', array($data));

            $pos += \strlen($data);
            if ($size !== null && $pos >= $size) {
                $that->handleEnd();
            }
        });
        $input->on('error', function ($error) use ($that) {
            $that->emit('error', array($error));
            $that->close();
        });
        $input->on('end', array($that, 'handleEnd'));
        $input->on('close', array($that, 'close'));
    }

    /**
     * @inheritdoc
     */
    public function close(): void
    {
        if (!$this->closed) {
            $this->closed = true;
            $this->input->close();

            $this->emit('close');
            $this->removeAllListeners();
        }
    }

    /**
     * @inheritdoc
     */
    public function isReadable(): bool
    {
        return $this->input->isReadable();
    }

    /**
     * @inheritdoc
     */
    public function pause(): void
    {
        $this->input->pause();
    }

    /**
     * @inheritdoc
     */
    public function resume(): void
    {
        $this->input->resume();
    }

    /**
     * @inheritdoc
     */
    public function pipe(WritableStreamInterface $dest, array $options = array())
    {
        Util::pipe($this, $dest, $options);

        return $dest;
    }

    /**
     * @inheritdoc
     */
    public function eof(): bool
    {
        return !$this->isReadable();
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return '';
    }

    public function detach()
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @inheritdoc
     */
    public function tell(): int
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function write(string $string): int
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function read(int $length): string
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function getContents(): string
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(?string $key = null)
    {
        return ($key === null) ? array() : null;
    }

    /** @internal */
    public function handleEnd()
    {
        if ($this->position !== $this->size && $this->size !== null) {
            $this->emit('error', array(new \UnderflowException('Unexpected end of response body after ' . $this->position . '/' . $this->size . ' bytes')));
        } else {
            $this->emit('end');
        }

        $this->close();
    }
}
