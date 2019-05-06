<?php
/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 22/5/2018
 * Time: 11:41
 */

namespace App\Services;
use App\Entity\Notificacion;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface as EntityManager;

class NotificacionService
{
    private $em;

    /**
     * NotificacionService constructor.
     * @param $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return EntityManager
     */
    public function getEm(): EntityManager
    {
        return $this->em;
    }

    public function nuevaNotificacion($destinatario,$descripcion){
        if(null==$destinatario || null==$descripcion)
            throw new \Exception('No fueron enviados suficientes parámetros');

        $em=$this->getEm();
        $destinatarioObj=$em->getRepository(Usuario::class)->find($destinatario);
        if(null==$destinatarioObj)
            throw new \Exception('El destinatario no existe');

        $notificacion=new Notificacion();
        $notificacion->setFecha(new \DateTime());
        $notificacion->setDestinatario($destinatarioObj);
        $notificacion->setDescripcion($descripcion);

        //Se utilizo la funcion merge y no persist pues el persist fallaba cuando lo llamaba dentro de un for
        $em->merge($notificacion);
        $em->flush();
    }

}