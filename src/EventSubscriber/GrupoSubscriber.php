<?php

namespace App\EventSubscriber;

use App\Entity\Notificacion;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\Grupo;
use App\Entity\SolicitudGrupo;

class GrupoSubscriber implements EventSubscriber
{

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

                    $notificacion=new Notificacion();
                    $notificacion->setFecha($fecha);
                    $notificacion->setDestinatario($value);
                    $notificacion->setDescripcion("El usuario ".$entity->getCreador()." lo añadió al grupo ".$entity->getNombre());
                    $manager->persist($notificacion);
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

                    $notificacion=new Notificacion();
                    $notificacion->setFecha($fecha);
                    $notificacion->setDestinatario($value);
                    $notificacion->setDescripcion("El usuario ".$entity->getCreador()." lo añadió al grupo ".$entity->getNombre());
                    $manager->persist($notificacion);
                }
            }

            $solicitudes=$manager->getRepository('App:SolicitudGrupo')->findByGrupo($entity);
            foreach ($solicitudes as $value){
                if(!$entity->getIdmiembro()->contains($value->getUsuario())){
                    $manager->remove($value);
                    $notificacion=new Notificacion();
                    $notificacion->setFecha($fecha);
                    $notificacion->setDestinatario($value->getUsuario());
                    $notificacion->setDescripcion("El usuario ".$entity->getCreador()." lo eliminó del grupo ".$entity->getNombre());
                    $manager->persist($notificacion);
                }
            }
            $manager->flush();
        }
    }

    public function getSubscribedEvents()
    {
        return [
            'postPersist' => 'postPersist',
            'postUpdate' => 'postUpdate',
        ];
    }
}
