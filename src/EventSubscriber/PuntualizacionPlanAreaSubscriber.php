<?php

namespace App\EventSubscriber;

use App\Entity\MiembroConsejoDireccion;
use App\Entity\Notificacion;
use App\Entity\PuntualizacionPlanMensualArea;
use App\Entity\PuntualizacionPlanMensualGeneral;
use App\Entity\Usuario;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Services\NotificacionService;

class PuntualizacionPlanAreaSubscriber implements EventSubscriber
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
        $manager = $args->getEntityManager();
        if ($entity instanceof PuntualizacionPlanMensualArea) {
            $miembros = $manager->getRepository(Usuario::class)->findByArea($entity->getPlantrabajo()->getArea());
            foreach ($miembros as $value) {
                if ($entity->getUsuario()->getId() != $value->getId()) {
                    $message = "El usuario " . $entity->getUsuario()->getNombre() . " creó la puntualización " . $entity->getActividad();
                    $notificacion=new Notificacion();
                    $notificacion->setFecha(new \DateTime());
                    $notificacion->setDestinatario($value);
                    $notificacion->setDescripcion($message);
                    $manager->persist($notificacion);
                    $manager->flush();
                }
            }
        }
    }


    public function getSubscribedEvents()
    {
        return [
            'postPersist',
        ];
    }
}
