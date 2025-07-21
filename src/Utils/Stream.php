<?php

namespace Manzowa\Validator\Utils;

class Stream
{
    private $resource;
    private ?int $size;
    private bool $seekable;
    private bool $readable;
    private bool $writable;

    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('La ressource du flux est invalide.');
        }

        $meta = stream_get_meta_data($resource);
        $mode = $meta['mode'];

        $this->resource = $resource;
        $this->seekable = $meta['seekable'];
        $this->readable = strpbrk($mode, 'r+') !== false;
        $this->writable = strpbrk($mode, 'waxc+') !== false;

        $stats = fstat($resource);
        $this->size = isset($stats['size']) ? $stats['size'] : null;
    }

    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }
            return $this->getContents();
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function close(): void
    {
        if ($this->resource) {
            fclose($this->resource);
        }
        $this->detach();
    }

    public function detach()
    {
        $result = $this->resource;
        $this->resource = null;
        $this->size = null;
        $this->seekable = false;
        $this->readable = false;
        $this->writable = false;

        return $result;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function tell(): int
    {
        $pos = ftell($this->resource);
        if ($pos === false) {
            throw new \RuntimeException('Impossible d’obtenir la position du pointeur dans le flux.');
        }
        return $pos;
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Le flux n\'est pas accessible aléatoirement (seekable).');
        }

        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new \RuntimeException('Échec du déplacement dans le flux.');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write($string): int
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Le flux n\'est pas accessible en écriture.');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new \RuntimeException('Échec de l\'écriture dans le flux.');
        }

        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read($length): string
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Le flux n\'est pas accessible en lecture.');
        }

        $data = fread($this->resource, $length);
        if ($data === false) {
            throw new \RuntimeException('Échec de la lecture du flux.');
        }

        return $data;
    }

    public function getContents(): string
    {
        $contents = stream_get_contents($this->resource);
        if ($contents === false) {
            throw new \RuntimeException('Impossible de lire le contenu du flux.');
        }

        return $contents;
    }

    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->resource);
        return $key === null ? $meta : ($meta[$key] ?? null);
    }
}
