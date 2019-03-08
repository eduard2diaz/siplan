<?php

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
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

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $em=$this->getDoctrine()->getManager();
        $user=$event->getAuthenticationToken()->getUser();
        $user->setUltimologin(new \DateTime());
        $em->persist($user);
        $em->flush();
    }
}
