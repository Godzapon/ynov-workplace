<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Controller\AcceptGroupRequestController;
use App\Repository\GroupRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GroupRequestRepository::class)]
#[ApiResource(
    operations: [ new Post(security: "is_granted('ROLE_USER')") ],
    denormalizationContext: ['groups' => ['request:write']],
)]
//#[ApiResource(
//    uriTemplate: '/groups/{group_id}/requests/{id}',
//    operations: [ new Get() ],
//    uriVariables: [
//        'group_id' => new Link(toProperty: 'targetGroup', fromClass: Group::class),
//        'id' => new Link(fromClass: GroupRequest::class),
//    ]
//)]
#[ApiResource(
    uriTemplate: '/groups/{group_id}/requests',
    operations: [ new GetCollection() ],
    uriVariables: [
        'group_id' => new Link(toProperty: 'targetGroup', fromClass: Group::class),
    ],
    denormalizationContext: ['groups' => ['request:accept']]
)]
#[ApiResource(
//    operations: [ new Post(security: "is_granted('ROLE_USER')") ],
//    denormalizationContext: ['groups' => ['request:write']],
    operations: [
        new Post(
            uriTemplate: '/group_requests/{id}/accept',
            controller: AcceptGroupRequestController::class,
            description: 'Accept and add to the group the user who have request access',
            name: 'accept_group_request'
        )
    ]
)]
class GroupRequest
{
    public final const PENDING = 0;
    public final const DONE = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'groupRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $targetUser = null;

    #[ORM\ManyToOne(inversedBy: 'groupRequests')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['request:write'])]
    private ?Group $targetGroup = null;

    #[ORM\Column(nullable: true)]
    private ?int $status = self::PENDING;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTargetUser(): ?User
    {
        return $this->targetUser;
    }

    public function setTargetUser(?User $targetUser): self
    {
        $this->targetUser = $targetUser;

        return $this;
    }

    public function getTargetGroup(): ?Group
    {
        return $this->targetGroup;
    }

    public function setTargetGroup(?Group $targetGroup): self
    {
        $this->targetGroup = $targetGroup;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
