<?php

namespace App\Security\Voter;

use App\Entity\MiembroConsejoDireccion;
use App\Entity\PlanMensualGeneral;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class PlanMensualGeneralVoter extends Voter
{
    private $em;
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager, EntityManagerInterface $em)
    {
        $this->decisionManager = $decisionManager;
        $this->em = $em;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['EDIT', 'VIEW', 'DELETE', 'NEW','INDEX']) && $subject instanceof PlanMensualGeneral;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $esmiembro = null!=$this->em->getRepository(MiembroConsejoDireccion::class)->findOneByUsuario($token->getUser());
        switch ($attribute) {
            case 'NEW':
            case 'EDIT':
            case 'DELETE':
                return $this->decisionManager->decide($token, array('ROLE_COORDINADOR'));
                break;
            case 'INDEX':
            case 'VIEW':
                return $this->decisionManager->decide($token, array('ROLE_COORDINADOR')) || $esmiembro == true;
                break;
        }

        return false;
    }
}
