<?php

namespace App\Security\Voter;

use App\Entity\ActividadGeneral;
use App\Entity\MiembroConsejoDireccion;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class ActividadGeneralVoter extends Voter
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
        return in_array($attribute, ['EDIT', 'VIEW', 'DELETE', 'NEW',]) && $subject instanceof ActividadGeneral;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        $esmiembro = null!=$this->em->getRepository(MiembroConsejoDireccion::class)->findOneByUsuario($token->getUser());
        $currentdate=new \DateTime('today');
        $eseditable=$currentdate>= $subject->getPlanMensualGeneral()->getEdicionfechainicio() && $currentdate<= $subject->getPlanMensualGeneral()->getEdicionfechafin();
        switch ($attribute) {
            case 'VIEW':
                return $this->decisionManager->decide($token, array('ROLE_COORDINADORINSTITUCIONAL')) || $esmiembro == true ;
            break;
            case 'NEW':
                return $subject->getPlanMensualGeneral()->getAprobado()==false && ($this->decisionManager->decide($token, array('ROLE_COORDINADORINSTITUCIONAL')) || ($esmiembro == true && $eseditable)) ;
            break;
            case 'EDIT':
            case 'DELETE':
                return $subject->getPlanMensualGeneral()->getAprobado()==false && ($this->decisionManager->decide($token, array('ROLE_COORDINADORINSTITUCIONAL')) || ($esmiembro == true && $eseditable && $subject->getUsuario()->getId()==$token->getUser()->getId())) ;
            break;
        }

        return false;
    }
}
