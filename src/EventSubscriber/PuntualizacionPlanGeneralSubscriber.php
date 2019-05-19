<?php

namespace App\EventSubscriber;

use App\Entity\MiembroConsejoDireccion;
use App\Entity\Notificacion;
use App\Entity\PuntualizacionPlanMensualGeneral;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Services\NotificacionService;

class PuntualizacionPlanGeneralSubscriber implements EventSubscriber
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
        if ($entity instanceof PuntualizacionPlanMensualGeneral) {
            $miembros = $manager->getRepository(MiembroConsejoDireccion::class)->findAll();
            foreach ($miembros as $value) {
                if ($entity->getUsuario()->getId() != $value->getUsuario()->getId()) {
                    $message = "El usuario " . $entity->getUsuario()->getNombre() . " creó la puntualización " . $entity->getActividad();
                    $notificacion=new Notificacion();
                    $notificacion->setFecha(new \DateTime());
                    $notificacion->setDestinatario($value->getUsuario());
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
