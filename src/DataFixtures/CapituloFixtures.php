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
        $capitulos = [
            ['nombre' => 'Trabajo político ideológico  y de organización del partido', 'numero' => 1],
            ['nombre' => 'Funcionamiento y control del estado', 'numero' => 2],
            ['nombre' => 'Funcionamiento y control del gobierno', 'numero' => 3],
            ['nombre' => 'Funciones y encargos estatal de los OACE y CAP', 'numero' => 4],
            ['nombre' => 'Funcionamiento interno', 'numero' => 5],
            ['nombre' => 'Defensa, orden interior y defensa civil', 'numero' => 6],
        ];

        foreach ($capitulos as $value) {
            $capitulo = new Capitulo();
            $capitulo->setNombre($value['nombre']);
            $capitulo->setNumero($value['numero']);
            $manager->persist($capitulo);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }


}
