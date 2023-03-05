<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Thread;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GetThreadController extends AbstractController
{
    public function __invoke(Thread $thread): Thread
    {
        /** @var User $user */
        $user = $this->getUser();
        if (in_array($thread->getRelatedGroup(), $user->getSubscribedGroups()->getValues())) {
            return $thread;
        } else {
            throw new AccessDeniedHttpException();
        }
    }
}