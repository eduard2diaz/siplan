<?php

namespace App\Security\Voter;

use App\Entity\ActividadGeneral;
use App\Entity\MiembroConsejoDireccion;
use App\Entity\PuntualizacionPlanMensualGeneral;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class PuntualizacionPlanTrabajoGeneralVoter extends Voter
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
        return in_array($attribute, ['DELETE', 'NEW',]) && $subject instanceof PuntualizacionPlanMensualGeneral;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'NEW':
            case 'DELETE':
                return $subject->getPlantrabajo()->getAprobado()==false && $this->decisionManager->decide($token, array('ROLE_COORDINADORINSTITUCIONAL'));
            break;
        }

        return false;
    }
}
