<?php

namespace App\Security\Voter;

use App\Services\AreaService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Grupo;

class GrupoVoter extends Voter
{
    private $areaService;

    /**
     * GrupoVoter constructor.
     * @param $areaService
     */
    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    /**
     * @return AreaService
     */
    public function getAreaService(): AreaService
    {
        return $this->areaService;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['SHOW', 'EDIT', 'DELETE']) && $subject instanceof Grupo;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'SHOW':
                if ($subject->getCreador()->getId() == $token->getUser()->getId() || true==$this->esJefe($token->getUser(),$subject) || $subject->getIdmiembro()->contains($token->getUser()))
                    return true;
                break;
            case 'EDIT':
            case 'DELETE':
                if ($subject->getCreador()->getId() == $token->getUser()->getId())
                    return true;
                break;
        }

        return false;
    }

    private function esJefe($user,$grupo):bool {
        $subordinados=$this->getAreaService()->subordinadosKey($user);
        dump($subordinados);
        dump($grupo->getIdmiembro());
        foreach ($grupo->getIdmiembro() as $miembro){
            if(in_array($miembro->getId(),$subordinados))
                return true;
        }
        return false;
    }
}
