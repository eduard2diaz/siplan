<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use App\Entity\Usuario;

class UsuarioFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //Generando el usuario administrador
           $usuario = new Usuario();
           $usuario->setNombre('Administrador');
           $usuario->setCorreo('admin@dcorazon.cu');
           $usuario->setUsuario('administrador');
           $password = 'administrador'; //administrador
           $usuario->setPassword($password);
           $usuario->setActivo(true);
           $rol = $manager->getRepository('App:Rol')->findOneBy(array(
               'nombre' => 'ROLE_ADMIN'
           ));
           $area = $manager->getRepository('App:Area')->findOneBy(array(
               'nombre' => 'Economía'
           ));
           $cargo = $manager->getRepository('App:Cargo')->findOneBy(array(
               'nombre' => 'Informatico',
            //   'area' => 'Economía'
           ));
           $usuario->getIdrol()->add($rol);
           $usuario->setArea($area);
           $usuario->setCargo($cargo);
           $manager->persist($usuario);
           $manager->flush();
    }
    public function getOrder()
    {
        return 4;
    }
}
