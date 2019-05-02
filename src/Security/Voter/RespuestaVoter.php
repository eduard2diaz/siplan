<?php

namespace App\Security\Voter;

use App\Entity\Respuesta;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Actividad;

class RespuestaVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['NEW','EDIT', 'DELETE', 'VIEW'])
            && $subject instanceof Respuesta;
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
            case 'DELETE':
            case 'EDIT':
                {
                    return $subject->getId()->getPlantrabajo()->getUsuario()->getId() == $token->getUser()->getId();
                    break;
                }
            case 'VIEW':
                return ($subject->getId()->getPlantrabajo()->getUsuario()->getId() == $token->getUser()->getId() || $subject->getId()->getPlantrabajo()->getUsuario()->esSubordinado($token->getUser()));
                break;
        }

        return false;
    }
}
