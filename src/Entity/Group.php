<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['group:read']]),
        new Post(denormalizationContext: ['groups' => ['group:write']], security: "is_granted('ROLE_USER')"),
        new Get(normalizationContext: ['groups' => ['group:read', 'group:inspect']]),
        new Delete(security: "is_granted('ROLE_ADMIN') or object.owner == user"),
        new Patch(
            denormalizationContext: ['groups' => ['group:write']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and object.owner == user"
        )
    ],
)]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['group:write', 'group:inspect', 'group:read'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'ownedGroups')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['group:inspect', 'group:read'])]
    public ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'subscribedGroups')]
    #[Groups(['group:inspect'])]
    private Collection $members;

    #[Link(toProperty: 'targetGroup')]
    #[ORM\OneToMany(mappedBy: 'targetGroup', targetEntity: GroupRequest::class, orphanRemoval: true)]
    private Collection $groupRequests;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['group:write', 'group:inspect', 'group:read'])]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'relatedGroup', targetEntity: Thread::class)]
    private Collection $threads;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->groupRequests = new ArrayCollection();
        $this->threads = new ArrayCollection();
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
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        $this->members->removeElement($member);

        return $this;
    }

    /**
     * @return Collection<int, GroupRequest>
     */
    public function getGroupRequests(): Collection
    {
        return $this->groupRequests;
    }

    public function addGroupRequest(GroupRequest $groupRequest): self
    {
        if (!$this->groupRequests->contains($groupRequest)) {
            $this->groupRequests->add($groupRequest);
            $groupRequest->setTargetGroup($this);
        }

        return $this;
    }

    public function removeGroupRequest(GroupRequest $groupRequest): self
    {
        if ($this->groupRequests->removeElement($groupRequest)) {
            // set the owning side to null (unless already changed)
            if ($groupRequest->getTargetGroup() === $this) {
                $groupRequest->setTargetGroup(null);
            }
        }

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

    /**
     * @return Collection<int, Thread>
     */
    public function getThreads(): Collection
    {
        return $this->threads;
    }

    public function addThread(Thread $thread): self
    {
        if (!$this->threads->contains($thread)) {
            $this->threads->add($thread);
            $thread->setRelatedGroup($this);
        }

        return $this;
    }

    public function removeThread(Thread $thread): self
    {
        if ($this->threads->removeElement($thread)) {
            // set the owning side to null (unless already changed)
            if ($thread->getRelatedGroup() === $this) {
                $thread->setRelatedGroup(null);
            }
        }

        return $this;
    }
}
