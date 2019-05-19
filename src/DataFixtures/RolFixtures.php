<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use App\Entity\Rol;

class RolFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $roles = array(
            array('nombre' => 'ROLE_ADMIN'),
            array('nombre' => 'ROLE_DIRECTIVOINSTITUCIONAL'),
            array('nombre' => 'ROLE_DIRECTIVO'),
            array('nombre' => 'ROLE_COORDINADORINSTITUCIONAL'),
            array('nombre' => 'ROLE_COORDINADORAREA'),
            array('nombre' => 'ROLE_USER'),
        );

        foreach ($roles as $rol) {
            $entidad = new Rol();
            $entidad->setNombre($rol['nombre']);
            $manager->persist($entidad);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
