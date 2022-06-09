<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SliderRepository")
 */
class Slider
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $header;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $textColor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $fileData;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fileType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fileName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function setHeader(?string $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->textColor;
    }

    public function setTextColor(?string $textColor): self
    {
        $this->textColor = $textColor;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getFileData()
    {
        return $this->fileData;
    }

    public function setFileData($fileData): self
    {
        $this->fileData = $fileData;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(?string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
}
