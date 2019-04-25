<?php

namespace App\EventListener;

use App\Entity\MiembroConsejoDireccion;
use Symfony\Component\HttpFoundation\Session\Session;
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

        $esmiembro = null!=$em->getRepository(MiembroConsejoDireccion::class)->findOneByUsuario($event->getAuthenticationToken()->getUser());
        /*Creo una variable de sesion que indica si el usuario es miembro o no del consejo de direccion, como vez es
        solo crearla no tienes que asignarsela a nadie
        */
        $session=new Session();
        $session->set('esmiembroconsejodireccion',$esmiembro);
    }
}
