<?php

namespace App\Security\Voter;

use App\Entity\PuntualizacionPlanTrabajo;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Plantrabajo;

class PuntualizacionPlanTrabajoVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['DELETE', 'NEW','VIEW'])
            && $subject instanceof PuntualizacionPlanTrabajo;
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
            case 'DELETE':
                return $subject->getPlantrabajo()->getUsuario()->getId() == $token->getUser()->getId();
            break;
            case 'VIEW':
                return $subject->getPlantrabajo()->getUsuario()->esSubordinado($token->getUser()) || $subject->getPlantrabajo()->getUsuario()->getId() == $token->getUser()->getId();
            break;
        }

        return false;
    }
}
