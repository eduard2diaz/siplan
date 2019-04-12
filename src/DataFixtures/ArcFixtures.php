<?php

namespace App\DataFixtures;

use App\Entity\ARC;
use App\Entity\Subcapitulo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ArcFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $arcs=[
            ['nombre'=>'Formación de pregrado','objetivo'=>'','capitulo'=>'Funciones y encargos estatal de los OACE y CAP','subcapitulo'=>'Ministerio de Educación Superior(MES)'],
            ['nombre'=>'Formación de postgrado y capacitación','objetivo'=>'','capitulo'=>'Funciones y encargos estatal de los OACE y CAP','subcapitulo'=>'Ministerio de Educación Superior(MES)'],
            ['nombre'=>'Extensión universitaria','objetivo'=>'','capitulo'=>'Funciones y encargos estatal de los OACE y CAP','subcapitulo'=>'Ministerio de Educación Superior(MES)'],
            ['nombre'=>'Ciencia,tecnología e innovación','objetivo'=>'','capitulo'=>'Funciones y encargos estatal de los OACE y CAP','subcapitulo'=>'Ministerio de Educación Superior(MES)'],
            ['nombre'=>'Capital humano','objetivo'=>'','capitulo'=>'Funciones y encargos estatal de los OACE y CAP','subcapitulo'=>'Ministerio de Educación Superior(MES)']
        ];

        foreach ($arcs as $arc){
            $subcapitulo=$manager->getRepository(Subcapitulo::class)->findOneByNombre($arc['subcapitulo']);
            if(!$subcapitulo)
                continue;

            $obj=new ARC();
            $obj->setNombre($arc['nombre']);
            $obj->setObjetivos($arc['objetivo']);
            $obj->setSubcapitulo($subcapitulo);
            $obj->setCapitulo($subcapitulo->getCapitulo());
            $manager->persist($obj);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 3;// TODO: Implement getOrder() method.
    }


}
