<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Capitulo;

class CapituloFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $capitulos = ['Trabajo político ideológico  y de organización del partido', 'Funcionamiento y control del estado',
            'Funcionamiento y control del gobierno', 'Funciones y encargos estatal de los OACE y CAP',
            'Funcionamiento interno', 'Defensa, orden interior y defensa civil'];

        foreach ($capitulos as $value) {
            $capitulo = new Capitulo();
            $capitulo->setNombre($value);
            $manager->persist($capitulo);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }


}
