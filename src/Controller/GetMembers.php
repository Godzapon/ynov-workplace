<?php

namespace App\Controller;

use App\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetMembers extends AbstractController
{
    public function __invoke(Group $group): array
    {
        return $group->getMembers()->getValues();
    }
}