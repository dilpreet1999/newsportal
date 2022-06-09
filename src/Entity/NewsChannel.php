<?php

namespace App\Entity;

use App\Repository\NewsChannelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NewsChannelRepository::class)
 */
class NewsChannel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    
    /**
     * @ORM\OneToMany(targetEntity=ChannelRefference::class, mappedBy="name")
     */
    private $channelRefferences;

    /**
     * @ORM\Column(type="blob")
     */
    private $fileData;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

 


    public function __construct()
    {
        $this->channelRefferences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

   

    /**
     * @return Collection|ChannelRefference[]
     */
    public function getChannelRefferences(): Collection
    {
        return $this->channelRefferences;
    }

    public function addChannelRefference(ChannelRefference $channelRefference): self
    {
        if (!$this->channelRefferences->contains($channelRefference)) {
            $this->channelRefferences[] = $channelRefference;
            $channelRefference->setName($this);
        }

        return $this;
    }

    public function removeChannelRefference(ChannelRefference $channelRefference): self
    {
        if ($this->channelRefferences->removeElement($channelRefference)) {
            // set the owning side to null (unless already changed)
            if ($channelRefference->getName() === $this) {
                $channelRefference->setName(null);
            }
        }

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

    public function setFileType(string $fileType): self
    {
        $this->fileType = $fileType;

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

   

   
}
