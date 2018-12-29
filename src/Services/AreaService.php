<?php
/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 22/5/2018
 * Time: 11:41
 */

namespace App\Services;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Entity\Area;
use App\Entity\Usuario;

class AreaService
{
    private $em;

    /**
     * AreaService constructor.
     * @param $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return mixed
     */
    public function getEm()
    {
        return $this->em;
    }

    /*
     * FUNCION QUE LLAMA A UNA FUNCION RECURSIVA PARA OBTENER LAS AREAS HIJAS DE UNA DETERMINADA AREA
     */
    public function areasHijas(Area $area,$esAdmin=false){
        if(!$esAdmin){
            $array=[$area];
            return $this->areasHijasAux($area,$array);
        }else
            return $this->getEm()->getRepository('App:Area')->findAll();
    }

    /*
     * FUNCION RECURSIVA QUE DEVUELVE LAS AREAS HIJAS DE UNA AREA
     */
    private function areasHijasAux(Area $area,&$areas){

        $em=$this->getEm();
        $hijos=$em->createQuery('SELECT a FROM App:Area a JOIN a.padre p WHERE p.id=:id')->setParameter('id',$area->getId())->getResult();
        foreach ($hijos as $hijo){
            $areas[]=$hijo;
            $this->areasHijasAux($hijo,$areas);
        }
        return $areas;
    }

    /*
     * FUNCION QUE A PARTIR DE LOS DATOS OBTENIDOS DE LA FUNCION ANTERIOR, DEVUELVE LAS AREAS NO HIJAS DE
     * UNA DETERMINADA AREA
     */
    public function areasNoHijas(Area $area){
        $hijas=$this->areasHijas($area);
        $em=$this->getEm();
        if(empty($hijas)) {
            $consulta = $em->createQuery('SELECT a FROM App:Area a WHERE a.id!= :id ');
            $consulta->setParameter('id' , $area->getId());
        }else{
            $consulta=$em->createQuery('SELECT a FROM App:Area a WHERE a.id!= :id AND NOT a  IN (:hijas)');
            $consulta->setParameters(array('hijas'=>$hijas,'id'=>$area->getId()));
        }
        return $consulta->getResult();
    }

    /*
     * FUNCION QUE LLAMA A LA FUNCION RECURSIVA PARA OBTENER LOS SUBORDINADOS
     */
    public function subordinados(Usuario $usuario){
        $array=array();
        return $this->subordinadosAux($usuario,$array);
    }

    /*
     * FUNCION RECURSIVA QUE OBTIENE LOS SUPORDINADOS DE UNA DETERMINADA PERSONA
     */
    private function subordinadosAux(Usuario $usuario,&$subordinados){
        $em=$this->getEm();
        $hijos=$em->getRepository('App:Usuario')->findByJefe($usuario);
        foreach ($hijos as $hijo){
            $subordinados[]=$hijo;
            $this->subordinadosAux($hijo,$subordinados);
        }
        return $subordinados;
    }

    /*
     * OBTIENE EL LISTADO DE DIRECTIVOS DE LA INSTITUCION
     */
    public function obtenerDirectivos($id=null){
        if(!$id)
            $consulta=$this->getEm()->createQuery("SELECT u FROM App:Usuario u join u.idrol r WHERE r.nombre= :nombre")->setParameters(array('nombre'=>'ROLE_DIRECTIVO'));
        else
            $consulta=$this->getEm()->createQuery("SELECT u FROM App:Usuario u join u.idrol r WHERE u.id!= :id AND r.nombre= :nombre")->setParameters(array('id'=>$id,'nombre'=>'ROLE_DIRECTIVO'));
        return  $consulta->getResult();
    }


}