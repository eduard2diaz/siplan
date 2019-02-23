<?php

namespace App\DataFixtures;

use App\Entity\ARC;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArcFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $arcs=[
            ['nombre'=>'Formación de pregrado','objetivo'=>''],
            ['nombre'=>'Formación de postgrado y capacitación','objetivo'=>''],
            ['nombre'=>'Extensión universitaria','objetivo'=>''],
            ['nombre'=>'Ciencia,tecnología e innovación','objetivo'=>''],
            ['nombre'=>'Cpital humano','objetivo'=>'']
        ];

        foreach ($arcs as $arc){
            $obj=new ARC();
            $obj->setNombre($arc['nombre']);
            $obj->setObjetivos($arc['objetivo']);
            $manager->persist($obj);
        }
        $manager->flush();
    }
}
