<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use App\Entity\Area;

class AreaFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $areas = array(
            array('nombre' => 'Dirección'),
            array('nombre' => 'Economía','padre'=>'Dirección'),
            array('nombre' => 'Recursos Humanos','padre'=>'Dirección'),
        );
        foreach ($areas as $area) {
            $entidad = new Area();
            $entidad->setNombre($area['nombre']);
            if(array_key_exists('padre',$area)){
                $padre=$manager->getRepository('App:Area')->findOneBy(array('nombre'=>$area['padre']));
                 $entidad->setPadre($padre);
            }
            $manager->persist($entidad);
            $manager->flush();
        }
    }

    public function getOrder()
    {
        return 2;
    }
}
