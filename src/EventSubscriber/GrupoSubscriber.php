<?php

namespace App\EventSubscriber;

use App\Entity\Notificacion;
use App\Services\NotificacionService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\Grupo;
use App\Entity\SolicitudGrupo;

class GrupoSubscriber implements EventSubscriber
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
        if ($entity instanceof Grupo) {
            $miembros = $entity->getIdmiembro();
            $fecha = new \DateTime();
            foreach ($miembros as $value) {
                if (!$manager->getRepository('App:SolicitudGrupo')->findOneBy([
                    'grupo' => $entity,
                    'usuario' => $value
                ])) {
                    $solicitud = new SolicitudGrupo();
                    $solicitud->setGrupo($entity);
                    $solicitud->setUsuario($value);
                    $solicitud->setEstado(0);
                    $solicitud->setFecha($fecha);
                    $manager->persist($solicitud);

                    $message = "El usuario " . $entity->getCreador()->getNombre() . " lo agregó al grupo " . $entity->getNombre();
                    $this->notificacion->nuevaNotificacion($value->getId(), $message);

                }
            }
            $manager->flush();
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $manager = $args->getEntityManager();
        if ($entity instanceof Grupo) {
            $miembros = $entity->getIdmiembro();
            $fecha = new \DateTime();
            foreach ($miembros as $value) {
                if (!$manager->getRepository('App:SolicitudGrupo')->findOneBy([
                    'grupo' => $entity,
                    'usuario' => $value
                ])) {
                    $solicitud = new SolicitudGrupo();
                    $solicitud->setGrupo($entity);
                    $solicitud->setUsuario($value);
                    $solicitud->setEstado(0);
                    $solicitud->setFecha($fecha);
                    $manager->persist($solicitud);
                }
            }
            $manager->flush();
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $manager = $args->getEntityManager();
        if ($entity instanceof Grupo) {
            $miembros = $entity->getIdmiembro();
            $fecha = new \DateTime();
            foreach ($miembros as $value) {
                $message = "El usuario " . $entity->getCreador()->getNombre() . " eliminó el grupo " . $entity->getNombre();
                $this->notificacion->nuevaNotificacion($value->getId(), $message);
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
