<?php

namespace App\Entity;

use App\Repository\StoryImagesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StoryImagesRepository::class)
 */
class StoryImages
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="blob")
     */
    private $fileData;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileType;

    /**
     * @ORM\ManyToOne(targetEntity=Story::class, inversedBy="storyImages")
     */
    private $story;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPrimary;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getStory(): ?Story
    {
        return $this->story;
    }

    public function setStory(?Story $story): self
    {
        $this->story = $story;

        return $this;
    }

    public function getIsPrimary(): ?bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $isPrimary): self
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }
}
