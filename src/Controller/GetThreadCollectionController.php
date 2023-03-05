<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetThreadCollectionController extends AbstractController
{
    public function __invoke(): array
    {
        $user = $this->getUser();
        /** @var User $user */
        $groups = $user->getSubscribedGroups()->getValues();
        return array_merge(...array_map(fn($g) => /** @var Group $g */ $g->getThreads()->getValues(), $groups));
    }
}