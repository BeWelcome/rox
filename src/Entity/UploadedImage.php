<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Image uploaded using CKEditor.
 *
 * @ORM\Table(name="uploaded_image")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class UploadedImage
{
    /**
     * @ORM\Column(name="filename", type="string", length=100, nullable=false)
     */
    private string $filename;

    /**
     * @ORM\Column(name="mimetype", type="string", length=100, nullable=false)
     */
    private string $mimeType;

    /**
     * @ORM\Column(name="size", type="integer", nullable=false)
     */
    private int $size = 0;

    /**
     * @ORM\Column(name="width", type="integer", nullable=false)
     */
    private int $width = 0;

    /**
     * @ORM\Column(name="height", type="integer", nullable=false)
     */
    private int $height = 0;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private DateTime $created;

    /**
     * @ORM\Column(name="fileinfo", type="text", length=32, nullable=false)
     */
    private string $fileInfo;

    /**
     * @ORM\Column(name="filehash", type="text", length=64, nullable=false)
     */
    private string $fileHash;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
    }

    public function setFileInfo(string $fileInfo): self
    {
        $this->fileInfo = $fileInfo;

        return $this;
    }

    public function getFileInfo(): ?string
    {
        return $this->fileInfo;
    }

    public function setFileHash(string $fileHash): self
    {
        $this->fileHash = $fileHash;

        return $this;
    }

    public function getFileHash(): ?string
    {
        return $this->fileHash;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }
}
