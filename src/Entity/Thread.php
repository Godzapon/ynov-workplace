<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\GetThreadCollectionController;
use App\Controller\GetThreadController;
use App\Controller\SearchThreadCollectionController;
use App\Repository\ThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ThreadRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'content' => 'partial'])]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/threads',
            controller: GetThreadCollectionController::class,
            normalizationContext: ['groups' => ['thread:read']],
            security: "is_granted('ROLE_USER')",
            name: 'threads_limited'
        ),
        new GetCollection(
            uriTemplate: '/search',
//            controller: GetThreadCollectionController::class,
            normalizationContext: ['groups' => ['thread:read']],
            security: "is_granted('ROLE_USER')",
            filters: ['app.thread.search_filter'],
            name: 'threads_search'
        ),
        new Post(denormalizationContext: ['groups' => ['thread:write']], security: "is_granted('ROLE_USER')"),
        new Get(
            uriTemplate: '/threads/{id}',
            controller: GetThreadController::class,
            normalizationContext: ['groups' => ['thread:read', 'thread:inspect']],
            security: "is_granted('ROLE_USER')",
            name: 'thread_limited'
        ),
        new Delete(security: "is_granted('ROLE_ADMIN') or object.owner == user"),
        new Patch(
            denormalizationContext: ['groups' => ['thread:write']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and object.owner == user"
        )
    ],
)]
class Thread
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['thread:write', 'thread:inspect', 'thread:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['thread:inspect', 'thread:read'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['thread:write', 'thread:inspect'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'threads')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['thread:inspect', 'thread:read'])]
    private ?User $owner = null;

    #[Groups(['thread:inspect'])]
    #[ORM\OneToMany(mappedBy: 'thread', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $messages;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'threads')]
    #[Groups(['thread:write', 'thread:inspect', 'thread:read'])]
    private ?Group $relatedGroup = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable('now');
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setThread($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getThread() === $this) {
                $message->setThread(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRelatedGroup(): ?Group
    {
        return $this->relatedGroup;
    }

    public function setRelatedGroup(?Group $relatedGroup): self
    {
        $this->relatedGroup = $relatedGroup;

        return $this;
    }
}
