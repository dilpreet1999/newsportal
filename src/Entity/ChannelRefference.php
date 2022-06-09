<?php

namespace App\Entity;

use App\Repository\ChannelRefferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChannelRefferenceRepository::class)
 */
class ChannelRefference
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
    private $title;

   
   

    /**
     * @ORM\Column(type="datetime")
     */
    private $publishedOn;

    /**
     * @ORM\ManyToOne(targetEntity=Story::class, inversedBy="channelRefferences")
     */
    private $story;

    /**
     * @ORM\ManyToOne(targetEntity=NewsChannel::class, inversedBy="channelRefferences")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $website;

   
   

   

    public function __construct()
    {
        $this->stories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

   

    public function getPublishedOn(): ?\DateTimeInterface
    {
        return $this->publishedOn;
    }

    public function setPublishedOn(\DateTimeInterface $publishedOn): self
    {
        $this->publishedOn = $publishedOn;

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

    public function getName(): ?NewsChannel
    {
        return $this->name;
    }

    public function setName(?NewsChannel $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        $this->website = $website;

        return $this;
    }

  

    

}
