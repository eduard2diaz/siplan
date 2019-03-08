<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutListener  implements LogoutHandlerInterface
{
    private $doctrine;

    /**
     * LogoutListener constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return mixed
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    public function logout(Request $Request, Response $Response, TokenInterface $token) {
        $em=$this->getDoctrine()->getManager();
        $token->getUser()->setUltimologout(new \DateTime());
        $em->persist($token->getUser());
        $em->flush();
    }

}
