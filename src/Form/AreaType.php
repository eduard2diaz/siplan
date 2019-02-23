<?php

namespace App\Form;

use App\Entity\Area;
use App\Services\AreaService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AreaType extends AbstractType
{
    private $area_service;

    /**
     * AreaType constructor.
     * @param $area_service
     */
    public function __construct(AreaService $area_service)
    {
        $this->area_service = $area_service;
    }

    /**
     * @return AreaService
     */
    public function getAreaService(): AreaService
    {
        return $this->area_service;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $area=$options['data'];
        $builder
            ->add('nombre', TextType::class,array('attr'=>array('autocomplete'=>'off','class'=>'form-control input-xlarge')));
            if(null==$area->getId())
                $builder->add('padre',null,array('label'=>'Área padre','attr'=>array('class'=>'form-control input-medium')));
            else
                $builder->add('padre',null,array('label'=>'Área padre','choices'=>$this->getAreaService()->areasNoHijas($area),
                    'attr'=>array('class'=>'form-control input-medium')));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Area::class,
        ]);
    }
}
