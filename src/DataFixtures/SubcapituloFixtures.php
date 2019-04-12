<?php

namespace App\DataFixtures;

use App\Entity\Capitulo;
use App\Entity\Subcapitulo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SubcapituloFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $subcapitulos = [
            ['capitulo' => 'Trabajo político ideológico  y de organización del partido', 'nombre' => 'Partido comunista de Cuba(Actividades orientadas por el Comité Provincial y Municipal)'],
            ['capitulo' => 'Trabajo político ideológico  y de organización del partido', 'nombre' => 'Partido comunista de Cuba(Activiades orientadas por el Comité de la UNAH)'],
            ['capitulo' => 'Trabajo político ideológico  y de organización del partido', 'nombre' => 'Funcionamiento interno de la organización en la UNAH'],
            ['capitulo' => 'Trabajo político ideológico  y de organización del partido', 'nombre' => 'Conmemoraciones'],
            ['capitulo' => 'Funcionamiento y control del estado', 'nombre' => 'Asamblea Nacional(Provincial y el MEIJ) del Poder Popular'],
            ['capitulo' => 'Funcionamiento y control del estado', 'nombre' => 'Asamblea Provincial del Poder Popular'],
            ['capitulo' => 'Funcionamiento y control del gobierno', 'nombre' => 'Comisión permanente para la implementación y desarrollo'],
            ['capitulo' => 'Funcionamiento y control del gobierno', 'nombre' => 'Secretaría del Consejo de Ministros'],
            ['capitulo' => 'Funcionamiento y control del gobierno', 'nombre' => 'Planificación de actividades'],
            ['capitulo' => 'Funcionamiento y control del gobierno', 'nombre' => 'En las entidades del Sistema MES'],
            ['capitulo' => 'Funcionamiento y control del gobierno', 'nombre' => 'Ministerio de Ciencia, Tecnología y Medio ambiente'],
            ['capitulo' => 'Funcionamiento y control del gobierno', 'nombre' => 'Instituto Nacional de Deporte, Educación Fisica y Recreación'],
            ['capitulo' => 'Funcionamiento y control del gobierno', 'nombre' => 'Ministerio de la Agricultura'],
            ['capitulo' => 'Funcionamiento y control del gobierno', 'nombre' => 'Grupo de planificación de actividades'],
            ['capitulo' => 'Funciones y encargos estatal de los OACE y CAP', 'nombre' => 'Ministerio de Educación Superior(MES)'],
            ['capitulo' => 'Funciones y encargos estatal de los OACE y CAP', 'nombre' => 'Actos y Eventos del MES'],
            ['capitulo' => 'Funciones y encargos estatal de los OACE y CAP', 'nombre' => 'Actos y Eventos de la UNAH'],
            ['capitulo' => 'Funcionamiento interno', 'nombre' => 'Actividades internas del Ministerio de Educación Superior(MES)'],
            ['capitulo' => 'Funcionamiento interno', 'nombre' => 'Funcionamiento interno de la UNAH'],
            ['capitulo' => 'Defensa, orden interior y defensa civil', 'nombre' => 'Actividades de la Defensa y Orden Interior en la UNAH'],
        ];

        foreach ($subcapitulos as $value) {
            $capitulo = $manager->getRepository(Capitulo::class)->findOneByNombre($value['capitulo']);
            if (!$capitulo)
                continue;

            $subcapitulo = new Subcapitulo();
            $subcapitulo->setNombre($value['nombre']);
            $subcapitulo->setCapitulo($capitulo);
            $manager->persist($subcapitulo);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }


}
