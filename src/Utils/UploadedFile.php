<?php

namespace Manzowa\Validator\Utils;

use Manzowa\Validator\Utils\Stream;

class UploadedFile
{
    private string $file;
    private ?string $clientFilename;
    private ?string $clientMediaType;
    private ?int $size;
    private int $error;
    private bool $moved = false;

    public function __construct(
        string $file,
        ?int $size,
        int $error,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ) {
        if (!is_int($error) || $error < UPLOAD_ERR_OK || $error > UPLOAD_ERR_EXTENSION) {
            throw new \InvalidArgumentException('Code d’erreur de téléchargement invalide.');
        }

        $this->file = $file;
        $this->size = $size;
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    public function getStream(): Stream
    {
        if ($this->moved) {
            throw new \RuntimeException('Impossible d\'obtenir le flux après déplacement du fichier.');
        }

        if (!is_readable($this->file)) {
            throw new \RuntimeException('Fichier non lisible.');
        }

        $resource = fopen($this->file, 'rb');

        if ($resource === false) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier en lecture.');
        }

        return new Stream($resource);
    }

    public function moveTo($targetPath): void
    {
        if (!is_string($targetPath) || trim($targetPath) === '') {
            throw new \InvalidArgumentException('Le chemin de destination est invalide.');
        }

        if ($this->moved) {
            throw new \RuntimeException('Le fichier a déjà été déplacé.');
        }

        $dirname = dirname($targetPath);
        if (!is_dir($dirname) || !is_writable($dirname)) {
            throw new \RuntimeException('Le dossier de destination est invalide ou non accessible en écriture.');
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Impossible de déplacer un fichier en erreur.');
        }

        if (php_sapi_name() === 'cli' || !is_uploaded_file($this->file)) {
            $moved = rename($this->file, $targetPath);
        } else {
            $moved = move_uploaded_file($this->file, $targetPath);
        }

        if (!$moved) {
            throw new \RuntimeException('Le déplacement du fichier a échoué.');
        }

        $this->moved = true;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    public function getMimeTypeFromFile(): ?string
    {
        if (!is_file($this->file)) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($this->file);
    }
}
