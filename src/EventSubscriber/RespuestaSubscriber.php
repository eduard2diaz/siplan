<?php

namespace App\EventSubscriber;

use App\Entity\Notificacion;
use App\Entity\Respuesta;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Services\NotificacionService;

class RespuestaSubscriber implements EventSubscriber
{
    private $notificacion;

    /**
     * GrupoSubscriber constructor.
     * @param $notificacion
     */
    public function __construct(NotificacionService $notificacion)
    {
        $this->notificacion = $notificacion;
    }
    
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Respuesta) {
           if($entity->getId()->getAsignadapor()->getId()!=$entity->getId()->getResponsable()->getId()){
               $destinatario=$entity->getId()->getAsignadapor();
               $message="El usuario ".$entity->getId()->getResponsable()->getNombre()." dio respuesta a la actividad ".$entity->getId()->getNombre();
               $notificacion=new Notificacion();
               $notificacion->setFecha(new \DateTime());
               $notificacion->setDestinatario($destinatario);
               $notificacion->setDescripcion($message);
               $args->getEntityManager()->persist($notificacion);
               $args->getEntityManager()->flush();
           }
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Respuesta) {
            if($entity->getId()->getAsignadapor()->getId()!=$entity->getId()->getResponsable()->getId()){
                $destinatario=$entity->getId()->getAsignadapor();
                $message="El usuario ".$entity->getId()->getResponsable()->getNombre()." modificó su respuesta a la actividad ".$entity->getId()->getNombre();
                $notificacion=new Notificacion();
                $notificacion->setFecha(new \DateTime());
                $notificacion->setDestinatario($destinatario);
                $notificacion->setDescripcion($message);
                $args->getEntityManager()->persist($notificacion);
                $args->getEntityManager()->flush();

            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $manager = $args->getEntityManager();
        if ($entity instanceof Respuesta) {
            if($entity->getId()->getAsignadapor()->getId()!=$entity->getId()->getResponsable()->getId()){
                $destinatario=$entity->getId()->getAsignadapor();
                $message="El usuario ".$entity->getId()->getResponsable()->getNombre()." eliminó su respuesta a la actividad ".$entity->getId()->getNombre();
                $notificacion=new Notificacion();
                $notificacion->setFecha(new \DateTime());
                $notificacion->setDestinatario($destinatario);
                $notificacion->setDescripcion($message);
                $args->getEntityManager()->persist($notificacion);
                $args->getEntityManager()->flush();
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [
            'postPersist','postUpdate','preRemove',
        ];
    }
}
