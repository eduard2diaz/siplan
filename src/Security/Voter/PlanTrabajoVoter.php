<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Plantrabajo;

class PlanTrabajoVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['DELETE', 'NEW', 'EDIT', 'VIEW'])
            && $subject instanceof Plantrabajo;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            //Solo pueden modificar o agregar plan de trabajo,el usuario o su jefe
            case 'NEW':
            case 'EDIT':
            case 'DELETE':
                {
                    if (($subject->getUsuario()->getJefe() != null && $subject->getUsuario()->getJefe()->getId() == $token->getUser()->getId()) || ($subject->getUsuario()->getId() == $token->getUser()->getId()))
                        return true;

                    return false;
                    break;
                }
            case 'VIEW':
                return ($subject->getUsuario()->esJefe($token->getUser())) || ($subject->getUsuario()->getId() == $token->getUser()->getId());
            break;
        }

        return false;
    }
}
