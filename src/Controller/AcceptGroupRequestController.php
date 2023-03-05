<?php

namespace App\Controller;

use App\Entity\GroupRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AcceptGroupRequestController extends AbstractController
{
    public function __construct(

    ) {}

    public function __invoke(GroupRequest $groupRequest): GroupRequest
    {
        // Si le user loggué est le owner du group, il peut accepter la requête
        $group = $groupRequest->getTargetGroup();
        if ($group->getOwner() === $this->getUser()) {
            $user = $groupRequest->getTargetUser();
            $group->addMember($user);
            $groupRequest->setStatus(GroupRequest::DONE);
            return $groupRequest;
        } else {
            throw new AccessDeniedHttpException();
        }
    }
}