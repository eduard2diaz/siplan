<?php

namespace App\DataFixtures;

use App\Entity\Cargo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use App\Entity\Area;

class CargoFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $cargos = array(
            array('area' => 'Dirección','cargos'=>array(
                'Director General'
            )),
            array('area' => 'Economía','cargos'=>array(
                'Directora Económicas',
                'Informatico'
            )),
            array('area' => 'Recursos Humanos','cargos'=>array(
                'Directora Recursos Humanos'
            )),
        );
        foreach ($cargos as $cargo) {
            $padre=$manager->getRepository('App:Area')->findOneBy(array('nombre'=>$cargo['area']));
            if($padre!=null){
                foreach ($cargo['cargos'] as $value){
                    $entidad=new Cargo();
                    $entidad->setNombre($value);
                    $entidad->setArea($padre);
                    $manager->persist($entidad);
                }
            }
        }
        $manager->flush();

    }

    public function getOrder()
    {
        return 3;
    }
}
