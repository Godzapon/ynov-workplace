<?php
# api/src/Entity/User.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Controller\SelfInfoController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use App\State\UserPasswordHasher;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(processor: UserPasswordHasher::class),
        new Get(normalizationContext: ['groups' => ['user:read', 'user:inspect']]),
        new Get(
            uriTemplate: '/users/{id}/info',
            controller: SelfInfoController::class,
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_USER')",
            name: 'self_info'
        )
//        new Put(processor: UserPasswordHasher::class),
//        new Patch(processor: UserPasswordHasher::class),
//        new Delete(),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:create', 'user:update']],
)]
#[ApiResource(
    uriTemplate: '/groups/{id}/members',
    operations: [ new GetCollection() ],
    uriVariables: [
        'id' => new Link(toProperty: 'subscribedGroups', fromClass: Group::class),
    ],
    denormalizationContext: ['groups' => ['group:read']]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['user:read'])]
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['user:create'])]
    #[Groups(['user:create', 'user:update'])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Group::class, orphanRemoval: true)]
    #[Groups(['user:inspect'])]
    private Collection $ownedGroups;

    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'members')]
    #[Groups(['user:inspect'])]
    private Collection $subscribedGroups;

    #[ORM\OneToMany(mappedBy: 'targetUser', targetEntity: GroupRequest::class, orphanRemoval: true)]
    private Collection $groupRequests;

    #[ORM\Column(length: 255)]
    #[Groups(['user:create', 'user:update', 'user:read'])]
    private ?string $nickname = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Thread::class, orphanRemoval: true)]
    private Collection $threads;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Message::class)]
    private Collection $messages;

    public function __construct()
    {
        $this->ownedGroups = new ArrayCollection();
        $this->subscribedGroups = new ArrayCollection();
        $this->groupRequests = new ArrayCollection();
        $this->threads = new ArrayCollection();
        $this->messages = new ArrayCollection();
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $painPassword): self
    {
        $this->plainPassword = $painPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getOwnedGroups(): Collection
    {
        return $this->ownedGroups;
    }

    public function addOwnedGroup(Group $ownedGroup): self
    {
        if (!$this->ownedGroups->contains($ownedGroup)) {
            $this->ownedGroups->add($ownedGroup);
            $ownedGroup->setOwner($this);
        }

        return $this;
    }

    public function removeOwnedGroup(Group $ownedGroup): self
    {
        if ($this->ownedGroups->removeElement($ownedGroup)) {
            // set the owning side to null (unless already changed)
            if ($ownedGroup->getOwner() === $this) {
                $ownedGroup->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getSubscribedGroups(): Collection
    {
        return $this->subscribedGroups;
    }

    public function addSubscribedGroup(Group $subscribedGroup): self
    {
        if (!$this->subscribedGroups->contains($subscribedGroup)) {
            $this->subscribedGroups->add($subscribedGroup);
            $subscribedGroup->addMember($this);
        }

        return $this;
    }

    public function removeSubscribedGroup(Group $subscribedGroup): self
    {
        if ($this->subscribedGroups->removeElement($subscribedGroup)) {
            $subscribedGroup->removeMember($this);
        }

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
            $groupRequest->setTargetUser($this);
        }

        return $this;
    }

    public function removeGroupRequest(GroupRequest $groupRequest): self
    {
        if ($this->groupRequests->removeElement($groupRequest)) {
            // set the owning side to null (unless already changed)
            if ($groupRequest->getTargetUser() === $this) {
                $groupRequest->setTargetUser(null);
            }
        }

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

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
            $thread->setOwner($this);
        }

        return $this;
    }

    public function removeThread(Thread $thread): self
    {
        if ($this->threads->removeElement($thread)) {
            // set the owning side to null (unless already changed)
            if ($thread->getOwner() === $this) {
                $thread->setOwner(null);
            }
        }

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
            $message->setOwner($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getOwner() === $this) {
                $message->setOwner(null);
            }
        }

        return $this;
    }
}