<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use App\Entity\Cargo;

class CargoVoter extends Voter
{
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        return ((in_array($attribute, ['DELETE'])) && ($subject instanceof Cargo));
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case 'DELETE':
                if ($this->decisionManager->decide($token, array('ROLE_ADMIN')) && $token->getUser()->getCargo()->getId() != $subject->getId())
                    return true;
                break;
        }

        return false;
    }
}
