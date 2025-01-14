<?php

namespace React\Tests\Http\Io;

use React\Http\Io\BufferedBody;
use React\Http\Io\UploadedFile;
Use React\Tests\Http\TestCase;

class UploadedFileTest extends TestCase
{
    public static function failtyErrorProvider()
    {
        yield ['a'];
        yield [null];
        yield [-1];
        yield [9];
    }

    /**
     * @dataProvider failtyErrorProvider
     */
    public function testFailtyError($error)
    {
        $stream = new BufferedBody('');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid error code, must be an UPLOAD_ERR_* constant');
        new UploadedFile($stream, 0, $error, 'foo.bar', 'foo/bar');
    }

    public function testNoMoveFile()
    {
        $stream = new BufferedBody('');
        $uploadedFile = new UploadedFile($stream, 0, UPLOAD_ERR_OK, 'foo.bar', 'foo/bar');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not implemented');
        $uploadedFile->moveTo('bar.foo');
    }

    public function testGetters()
    {
        $stream = new BufferedBody('');
        $uploadedFile = new UploadedFile($stream, 0, UPLOAD_ERR_OK, 'foo.bar', 'foo/bar');
        self::assertSame($stream,       $uploadedFile->getStream());
        self::assertSame(0,             $uploadedFile->getSize());
        self::assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
        self::assertSame('foo.bar',     $uploadedFile->getClientFilename());
        self::assertSame('foo/bar',     $uploadedFile->getClientMediaType());
    }

    public function testGetStreamOnFailedUpload()
    {
        $stream = new BufferedBody('');
        $uploadedFile = new UploadedFile($stream, 0, UPLOAD_ERR_NO_FILE, 'foo.bar', 'foo/bar');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot retrieve stream due to upload error');
        $uploadedFile->getStream();
    }
}
