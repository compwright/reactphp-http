<?php

namespace React\Http\Io;

use Psr\Http\Message\StreamInterface;

/**
 * [Internal] PSR-7 message body implementation using an in-memory buffer
 *
 * @internal
 */
class BufferedBody implements StreamInterface
{
    private $buffer = '';
    private $position = 0;
    private $closed = false;

    /**
     * @param string $buffer
     */
    public function __construct($buffer)
    {
        $this->buffer = $buffer;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        if ($this->closed) {
            return '';
        }

        $this->seek(0);

        return $this->getContents();
    }

    /**
     * @inheritdoc
     */
    public function close(): void
    {
        $this->buffer = '';
        $this->position = 0;
        $this->closed = true;
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        $this->close();

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getSize(): ?int
    {
        return $this->closed ? null : \strlen($this->buffer);
    }

    /**
     * @inheritdoc
     */
    public function tell(): int
    {
        if ($this->closed) {
            throw new \RuntimeException('Unable to tell position of closed stream');
        }

        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function eof(): bool
    {
        return $this->position >= \strlen($this->buffer);
    }

    /**
     * @inheritdoc
     */
    public function isSeekable(): bool
    {
        return !$this->closed;
    }

    /**
     * @inheritdoc
     */
    public function seek($offset, $whence = \SEEK_SET): void
    {
        if ($this->closed) {
            throw new \RuntimeException('Unable to seek on closed stream');
        }

        $old = $this->position;

        if ($whence === \SEEK_SET) {
            $this->position = $offset;
        } elseif ($whence === \SEEK_CUR) {
            $this->position += $offset;
        } elseif ($whence === \SEEK_END) {
            $this->position = \strlen($this->buffer) + $offset;
        } else {
            throw new \InvalidArgumentException('Invalid seek mode given');
        }

        if (!\is_int($this->position) || $this->position < 0) {
            $this->position = $old;
            throw new \RuntimeException('Unable to seek to position');
        }
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @inheritdoc
     */
    public function isWritable(): bool
    {
        return !$this->closed;
    }

    /**
     * @inheritdoc
     */
    public function write(string $string): int
    {
        if ($this->closed) {
            throw new \RuntimeException('Unable to write to closed stream');
        }

        if ($string === '') {
            return 0;
        }

        if ($this->position > 0 && !isset($this->buffer[$this->position - 1])) {
            $this->buffer = \str_pad($this->buffer, $this->position, "\0");
        }

        $len = \strlen($string);
        $this->buffer = \substr($this->buffer, 0, $this->position) . $string . \substr($this->buffer, $this->position + $len);
        $this->position += $len;

        return $len;
    }

    /**
     * @inheritdoc
     */
    public function isReadable(): bool
    {
        return !$this->closed;
    }

    /**
     * @inheritdoc
     */
    public function read(int $length): string
    {
        if ($this->closed) {
            throw new \RuntimeException('Unable to read from closed stream');
        }

        if ($length < 1) {
            throw new \InvalidArgumentException('Invalid read length given');
        }

        if ($this->position + $length > \strlen($this->buffer)) {
            $length = \strlen($this->buffer) - $this->position;
        }

        if (!isset($this->buffer[$this->position])) {
            return '';
        }

        $pos = $this->position;
        $this->position += $length;

        return \substr($this->buffer, $pos, $length);
    }

    /**
     * @inheritdoc
     */
    public function getContents(): string
    {
        if ($this->closed) {
            throw new \RuntimeException('Unable to read from closed stream');
        }

        if (!isset($this->buffer[$this->position])) {
            return '';
        }

        $pos = $this->position;
        $this->position = \strlen($this->buffer);

        return \substr($this->buffer, $pos);
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($key = null): ?array
    {
        return $key === null ? array() : null;
    }
}
