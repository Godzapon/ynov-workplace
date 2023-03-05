<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Group;
use App\Entity\GroupRequest;
use App\Entity\Message;
use App\Entity\Thread;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SetAutomaticFieldsToEntity implements EventSubscriberInterface
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['setData', EventPriorities::PRE_WRITE],
        ];
    }

    public function setData(ViewEvent $event)
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ($this->needExtraData($entity)) {
            if (Request::METHOD_POST === $method) {
                $this->setProperties($entity, $this->tokenStorage->getToken()->getUser());
            }
            if (Request::METHOD_PATCH === $method) {
                $this->updateProperties($entity);
            }
        }
    }

    /**
     * @param mixed $entity
     * @return bool
     */
    public function needExtraData(mixed $entity): bool
    {
        return $entity instanceof GroupRequest || $entity instanceof Group || $entity instanceof Thread || $entity instanceof Message;
    }

    /**
     * @param mixed $entity
     * @param UserInterface $user
     * @return void
     */
    public function setProperties(mixed $entity, UserInterface $user): void
    {
        /** @var User $user */
        switch (get_class($entity)) {
            case GroupRequest::class:
                /** @var GroupRequest $entity */
                $entity->setTargetUser($user);
                break;
            case Group::class:
                /** @var Group $entity */
                $entity->setOwner($user);
                break;
            case Thread::class:
                /** @var Thread $entity */
                $entity->setOwner($user);
                $slugger = new AsciiSlugger();
                $entity->setSlug($slugger->slug($entity->getTitle()));
                break;
            case Message::class:
                /** @var Thread $entity */
                $entity->setOwner($user);
                break;
        }
    }

    private function updateProperties(mixed $entity)
    {
        switch (get_class($entity)) {
            case Thread::class:
                /** @var Thread $entity */
                $slugger = new AsciiSlugger();
                $entity->setSlug($slugger->slug($entity->getTitle()));
                break;
            case Message::class:
                /** @var Thread $entity */
                $entity->setModifiedAt(new \DateTime('now'));
                $entity->setHasBeenEdited(true);
                break;
        }
    }
}