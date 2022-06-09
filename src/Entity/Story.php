<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StoryRepository")
 */
class Story
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="stories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

//    /**
//     * @ORM\Column(type="blob", nullable=true)
//     */
//    private $fileData;
//
//    /**
//     * @ORM\Column(type="string", length=255, nullable=true)
//     */
//    private $fileType;
//
//    /**
//     * @ORM\Column(type="string", length=255, nullable=true)
//     */
//    private $fileName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdOn;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $insta;

    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $twitter;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tags;

  
    

  
    /**
     * @ORM\Column(type="integer")
     */
    private $hits;


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="stories")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="story")
     */
    private $comments;

   
    /**
     * @ORM\OneToMany(targetEntity=ChannelRefference::class, mappedBy="story")
     */
    private $channelRefferences;

    /**
     * @ORM\OneToMany(targetEntity=Youtube::class, mappedBy="story")
     */
    private $youtubes;

    /**
     * @ORM\OneToMany(targetEntity=FacebookWatch::class, mappedBy="story")
     */
    private $facebookWatches;

    /**
     * @ORM\OneToMany(targetEntity=Twitter::class, mappedBy="story")
     */
    private $twitters;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isApproved;

    /**
     * @ORM\OneToMany(targetEntity=StoryImages::class, mappedBy="story")
     */
    private $storyImages;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSaved;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instagramTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->channelRefferences = new ArrayCollection();
        $this->youtubes = new ArrayCollection();
        $this->facebookWatches = new ArrayCollection();
        $this->twitters = new ArrayCollection();
        $this->storyImages = new ArrayCollection();
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

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
//
//    public function getFileData()
//    {
//        return $this->fileData;
//    }
//
//    public function setFileData($fileData): self
//    {
//        $this->fileData = $fileData;
//
//        return $this;
//    }
//
//    public function getFileType(): ?string
//    {
//        return $this->fileType;
//    }
//
//    public function setFileType(?string $fileType): self
//    {
//        $this->fileType = $fileType;
//
//        return $this;
//    }
//
//    public function getFileName(): ?string
//    {
//        return $this->fileName;
//    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

   public function getCreatedOn(): ?\DateTimeInterface
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTimeInterface $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    public function getInsta(): ?string
    {
        return $this->insta;
    }

    public function setInsta(?string $insta): self
    {
        $this->insta = $insta;

        return $this;
    }

 

    

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

   
    public function getHits(): ?int
    {
        return $this->hits;
    }

    public function setHits(int $hits): self
    {
        $this->hits = $hits;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setStory($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getStory() === $this) {
                $comment->setStory(null);
            }
        }

        return $this;
    }

  

    public function getChannelRefference(): ?ChannelRefference
    {
        return $this->channelRefference;
    }

    public function setChannelRefference(?ChannelRefference $channelRefference): self
    {
        $this->channelRefference = $channelRefference;

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
            $channelRefference->setStory($this);
        }

        return $this;
    }

    public function removeChannelRefference(ChannelRefference $channelRefference): self
    {
        if ($this->channelRefferences->removeElement($channelRefference)) {
            // set the owning side to null (unless already changed)
            if ($channelRefference->getStory() === $this) {
                $channelRefference->setStory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Youtube[]
     */
    public function getYoutubes(): Collection
    {
        return $this->youtubes;
    }

    public function addYoutube(Youtube $youtube): self
    {
        if (!$this->youtubes->contains($youtube)) {
            $this->youtubes[] = $youtube;
            $youtube->setStory($this);
        }

        return $this;
    }

    public function removeYoutube(Youtube $youtube): self
    {
        if ($this->youtubes->removeElement($youtube)) {
            // set the owning side to null (unless already changed)
            if ($youtube->getStory() === $this) {
                $youtube->setStory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FacebookWatch[]
     */
    public function getFacebookWatches(): Collection
    {
        return $this->facebookWatches;
    }

    public function addFacebookWatch(FacebookWatch $facebookWatch): self
    {
        if (!$this->facebookWatches->contains($facebookWatch)) {
            $this->facebookWatches[] = $facebookWatch;
            $facebookWatch->setStory($this);
        }

        return $this;
    }

    public function removeFacebookWatch(FacebookWatch $facebookWatch): self
    {
        if ($this->facebookWatches->removeElement($facebookWatch)) {
            // set the owning side to null (unless already changed)
            if ($facebookWatch->getStory() === $this) {
                $facebookWatch->setStory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Twitter[]
     */
    public function getTwitters(): Collection
    {
        return $this->twitters;
    }

    public function addTwitter(Twitter $twitter): self
    {
        if (!$this->twitters->contains($twitter)) {
            $this->twitters[] = $twitter;
            $twitter->setStory($this);
        }

        return $this;
    }

    public function removeTwitter(Twitter $twitter): self
    {
        if ($this->twitters->removeElement($twitter)) {
            // set the owning side to null (unless already changed)
            if ($twitter->getStory() === $this) {
                $twitter->setStory(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getIsApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): self
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    /**
     * @return Collection|StoryImages[]
     */
    public function getStoryImages(): Collection
    {
        return $this->storyImages;
    }

    public function addStoryImage(StoryImages $storyImage): self
    {
        if (!$this->storyImages->contains($storyImage)) {
            $this->storyImages[] = $storyImage;
            $storyImage->setStory($this);
        }

        return $this;
    }

    public function removeStoryImage(StoryImages $storyImage): self
    {
        if ($this->storyImages->removeElement($storyImage)) {
            // set the owning side to null (unless already changed)
            if ($storyImage->getStory() === $this) {
                $storyImage->setStory(null);
            }
        }

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getIsSaved(): ?bool
    {
        return $this->isSaved;
    }

    public function setIsSaved(bool $isSaved): self
    {
        $this->isSaved = $isSaved;

        return $this;
    }

    public function getInstagramTitle(): ?string
    {
        return $this->instagramTitle;
    }

    public function setInstagramTitle(?string $instagramTitle): self
    {
        $this->instagramTitle = $instagramTitle;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

   

    

}
