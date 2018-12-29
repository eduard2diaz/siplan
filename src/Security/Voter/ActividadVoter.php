<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Actividad;

class ActividadVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['NEW', 'DELETE', 'EDIT', 'VIEW'])
            && $subject instanceof Actividad;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'NEW':
            case 'EDIT':
            case 'DELETE':
                {
                    if (($subject->getResponsable()->getJefe() != null && $subject->getResponsable()->getJefe()->getId() == $token->getUser()->getId() && $subject->getAsignadapor()->getId() == $token->getUser()->getId()) || ($subject->getResponsable()->getId() == $token->getUser()->getId()))
                        return true;

                    break;
                }
            case 'VIEW':
                return ($subject->getResponsable()->esJefe($token->getUser())) || ($subject->getResponsable()->getId() == $token->getUser()->getId());
                break;
        }

        return false;
    }
}
