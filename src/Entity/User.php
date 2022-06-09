<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

   

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=13)
     */
    private $mobile;

    /**
     * @ORM\OneToMany(targetEntity=Story::class, mappedBy="user")
     */
    private $stories;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="user")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Twitter::class, mappedBy="user")
     */
    private $twitters;

    /**
     * @ORM\OneToMany(targetEntity=Youtube::class, mappedBy="user")
     */
    private $youtubes;

    /**
     * @ORM\OneToMany(targetEntity=FacebookWatch::class, mappedBy="user")
     */
    private $facebookWatches;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    private $facebookId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $googleId;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $emailVerificationCode;

 
    public function __construct()
    {
        $this->stories = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->twitters = new ArrayCollection();
        $this->youtubes = new ArrayCollection();
        $this->facebookWatches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

  
    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @return Collection|Story[]
     */
    public function getStories(): Collection
    {
        return $this->stories;
    }

    public function addStory(Story $story): self
    {
        if (!$this->stories->contains($story)) {
            $this->stories[] = $story;
            $story->setUser($this);
        }

        return $this;
    }

    public function removeStory(Story $story): self
    {
        if ($this->stories->removeElement($story)) {
            // set the owning side to null (unless already changed)
            if ($story->getUser() === $this) {
                $story->setUser(null);
            }
        }

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
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
            $twitter->setUser($this);
        }

        return $this;
    }

    public function removeTwitter(Twitter $twitter): self
    {
        if ($this->twitters->removeElement($twitter)) {
            // set the owning side to null (unless already changed)
            if ($twitter->getUser() === $this) {
                $twitter->setUser(null);
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
            $youtube->setUser($this);
        }

        return $this;
    }

    public function removeYoutube(Youtube $youtube): self
    {
        if ($this->youtubes->removeElement($youtube)) {
            // set the owning side to null (unless already changed)
            if ($youtube->getUser() === $this) {
                $youtube->setUser(null);
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
            $facebookWatch->setUser($this);
        }

        return $this;
    }

    public function removeFacebookWatch(FacebookWatch $facebookWatch): self
    {
        if ($this->facebookWatches->removeElement($facebookWatch)) {
            // set the owning side to null (unless already changed)
            if ($facebookWatch->getUser() === $this) {
                $facebookWatch->setUser(null);
            }
        }

        return $this;
    }

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setFacebookId(?string $facebookId): self
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->emailVerificationCode;
    }

    public function setEmailVerificationCode(string $emailVerificationCode): self
    {
        $this->emailVerificationCode = $emailVerificationCode;

        return $this;
    }

 
}
